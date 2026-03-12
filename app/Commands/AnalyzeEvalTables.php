<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * สำรวจตารางที่เกี่ยวกับประเมินผลการสอนใน sci-edoc (edoclocal)
 * แสดง SHOW CREATE TABLE, จำนวนแถว, ตัวอย่าง 5 แถว และ export เป็น docs/eval-tables-analysis.md
 */
class AnalyzeEvalTables extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'edoc:analyze-eval';
    protected $description = 'Analyze evaluation-related tables in sci-edoc (edoclocal) and export to docs/eval-tables-analysis.md';
    protected $usage       = 'edoc:analyze-eval [source-group]. Example: edoc:analyze-eval edoclocal';
    protected $arguments   = [];
    protected $options     = [
        'source' => 'Database group for source (sci-edoc), e.g. edoclocal',
    ];

    /** คำที่ใช้กรองชื่อตาราง (มีคำใดคำหนึ่งในชื่อ = เกี่ยวกับประเมิน) */
    private const KEYWORDS = ['eval', 'assess', 'teach', 'survey', 'quest', 'score', 'rating', 'form', 'subject', 'course'];

    private function getDbConfig(string $group): array
    {
        $base = (new Database())->default;
        $database = (string) (env("database.{$group}.database") ?? $base['database'] ?? '');
        if ($database === '' && ($group === 'edoclocal' || $group === 'edoc')) {
            $database = 'academic_sci';
        }
        return array_merge($base, [
            'hostname' => (string) (env("database.{$group}.hostname") ?? $base['hostname'] ?? 'localhost'),
            'database' => $database,
            'username' => (string) (env("database.{$group}.username") ?? $base['username'] ?? 'root'),
            'password' => (string) (env("database.{$group}.password") ?? $base['password'] ?? ''),
            'DBDriver' => (string) (env("database.{$group}.DBDriver") ?? $base['DBDriver'] ?? 'MySQLi'),
            'port'     => (int) (env("database.{$group}.port") ?? $base['port'] ?? 3306),
        ]);
    }

    private function connectGroup(string $group): ?\CodeIgniter\Database\BaseConnection
    {
        $config = $this->getDbConfig($group);
        if (empty($config['database'])) {
            CLI::write("  Group '{$group}' has no database in .env.", 'yellow');
            return null;
        }
        try {
            $fullConfig = array_merge((new Database())->default, $config);
            $db = Database::connect($fullConfig);
            if (method_exists($db, 'initialize')) {
                $db->initialize();
            }
            return $db;
        } catch (\Throwable $e) {
            CLI::error("Could not connect to group '{$group}': " . $e->getMessage());
            return null;
        }
    }

    private function tableMatchesEval(string $tableName): bool
    {
        $lower = strtolower($tableName);
        foreach (self::KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array{createTable: string, rowCount: int, sample: array}
     */
    private function analyzeOneTable(\CodeIgniter\Database\BaseConnection $db, string $tableName): array
    {
        $result = ['createTable' => '', 'rowCount' => 0, 'sample' => []];

        $escaped = '`' . str_replace('`', '``', $tableName) . '`';
        $createResult = $db->query("SHOW CREATE TABLE {$escaped}");
        if ($createResult) {
            $row = $createResult->getRowArray();
            if ($row) {
                $result['createTable'] = $row['Create Table'] ?? $row['create table'] ?? '';
            }
        }

        try {
            $result['rowCount'] = (int) $db->table($tableName)->countAllResults();
        } catch (\Throwable $e) {
            $result['rowCount'] = -1;
        }

        try {
            $result['sample'] = $db->table($tableName)->limit(5)->get()->getResultArray();
        } catch (\Throwable $e) {
            $result['sample'] = [];
        }

        return $result;
    }

    private function buildMarkdown(string $sourceGroup, array $tablesData): string
    {
        $lines = [
            '# การวิเคราะห์ตารางประเมินผลการสอนใน sci-edoc (edoclocal)',
            '',
            'สร้างจากคำสั่ง: `php spark edoc:analyze-eval ' . $sourceGroup . '`',
            'วันที่: ' . date('Y-m-d H:i:s'),
            '',
            '---',
            '',
        ];

        foreach ($tablesData as $tableName => $data) {
            $lines[] = '## ตาราง `' . $tableName . '`';
            $lines[] = '';
            $lines[] = '**จำนวนแถว:** ' . ($data['rowCount'] >= 0 ? (string) $data['rowCount'] : '(error)');
            $lines[] = '';
            $lines[] = '### SHOW CREATE TABLE';
            $lines[] = '';
            $lines[] = '```sql';
            $lines[] = $data['createTable'] ?: '-- (empty or error)';
            $lines[] = '```';
            $lines[] = '';
            $lines[] = '### ตัวอย่างข้อมูล (5 แถวแรก)';
            $lines[] = '';

            if (empty($data['sample'])) {
                $lines[] = '_ไม่มีข้อมูล_';
            } else {
                $lines[] = '| ' . implode(' | ', array_keys($data['sample'][0])) . ' |';
                $lines[] = '| ' . implode(' | ', array_fill(0, count($data['sample'][0]), '---')) . ' |';
                foreach ($data['sample'] as $row) {
                    $cells = [];
                    foreach ($row as $v) {
                        if ($v === null) {
                            $cells[] = 'NULL';
                        } else {
                            $s = (string) $v;
                            if (strlen($s) > 80) {
                                $s = mb_substr($s, 0, 77) . '…';
                            }
                            $cells[] = str_replace(['|', "\n", "\r"], ['\\|', ' ', ' '], $s);
                        }
                    }
                    $lines[] = '| ' . implode(' | ', $cells) . ' |';
                }
            }
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    public function run(array $params): int
    {
        CLI::write('=== สำรวจตารางประเมินผลการสอนใน sci-edoc (edoclocal) ===', 'yellow');
        CLI::newLine();

        $sourceGroup = $params[0] ?? CLI::getOption('source') ?? 'edoclocal';
        CLI::write("Source group: {$sourceGroup}", 'cyan');

        $db = $this->connectGroup($sourceGroup);
        if ($db === null) {
            return 1;
        }

        $allTables = $db->listTables();
        $evalTables = array_values(array_filter($allTables, fn ($t) => $this->tableMatchesEval($t)));

        if (empty($evalTables)) {
            CLI::write('ไม่พบตารางที่ชื่อมีคำว่า: ' . implode(', ', self::KEYWORDS), 'yellow');
            $db->close();
            $outPath = FCPATH . '..' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'eval-tables-analysis.md';
            if (! is_dir(dirname($outPath))) {
                @mkdir(dirname($outPath), 0755, true);
            }
            $content = "# การวิเคราะห์ตารางประเมินผลการสอนใน sci-edoc (edoclocal)\n\nไม่มีตารางที่ตรงกับคำกรอง (eval, assess, teach, survey, quest, score, rating, form, subject, course).\n";
            file_put_contents($outPath, $content);
            CLI::write('เขียนผลไปที่: ' . $outPath, 'green');
            return 0;
        }

        CLI::write('พบตารางที่เกี่ยวข้อง ' . count($evalTables) . ' ตาราง: ' . implode(', ', $evalTables), 'green');
        CLI::newLine();

        $tablesData = [];
        foreach ($evalTables as $tableName) {
            CLI::write("  วิเคราะห์ {$tableName}...", 'cyan');
            $tablesData[$tableName] = $this->analyzeOneTable($db, $tableName);
        }

        $db->close();

        $docsDir = FCPATH . '..' . DIRECTORY_SEPARATOR . 'docs';
        if (! is_dir($docsDir)) {
            @mkdir($docsDir, 0755, true);
        }
        $outPath = $docsDir . DIRECTORY_SEPARATOR . 'eval-tables-analysis.md';
        $markdown = $this->buildMarkdown($sourceGroup, $tablesData);
        file_put_contents($outPath, $markdown);

        CLI::write('Export เสร็จ: ' . $outPath, 'green');
        CLI::newLine();
        return 0;
    }
}
