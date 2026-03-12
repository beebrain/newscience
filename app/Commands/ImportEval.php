<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * นำเข้าข้อมูลประเมินผลการสอนจาก academic_sci (ตาราง teachingEvaluate, evaluatescore, emailEvaluate)
 * เข้า newScience (teaching_evaluations, evaluation_scores, evaluation_referees).
 *
 * ใช้: php spark import:eval [source-group]
 * โดย source-group (เช่น edoclocal) ต้องชี้ไปที่ DB academic_sci ใน .env หรือใช้ default แล้วตั้ง database=academic_sci
 */
class ImportEval extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'import:eval';
    protected $description = 'Import evaluation data from academic_sci (teachingEvaluate, evaluatescore, emailEvaluate) to newScience';
    protected $usage       = 'import:eval [source-group]. Example: import:eval edoclocal';
    protected $arguments   = [];
    protected $options     = [];

    /** ชื่อตารางต้นทางใน academic_sci (case-insensitive match) */
    private const SOURCE_TEACHING = 'teachingEvaluate';
    private const SOURCE_SCORES   = 'evaluatescore';
    private const SOURCE_REFEREES = 'emailEvaluate';

    private function getSourceConfig(string $sourceGroup): array
    {
        $base = (new Database())->default;
        $dbName = env("database.{$sourceGroup}.database") ?: 'academic_sci';
        return array_merge($base, [
            'hostname' => (string) (env("database.{$sourceGroup}.hostname") ?? $base['hostname'] ?? 'localhost'),
            'database' => (string) $dbName,
            'username' => (string) (env("database.{$sourceGroup}.username") ?? $base['username'] ?? 'root'),
            'password' => (string) (env("database.{$sourceGroup}.password") ?? $base['password'] ?? ''),
            'DBDriver' => (string) (env("database.{$sourceGroup}.DBDriver") ?? $base['DBDriver'] ?? 'MySQLi'),
            'port'     => (int) (env("database.{$sourceGroup}.port") ?? $base['port'] ?? 3306),
        ]);
    }

    private function connectGroup(string $group, ?array $configOverride = null): ?\CodeIgniter\Database\BaseConnection
    {
        $config = $configOverride ?? $this->getSourceConfig($group);
        if (empty($config['database'])) {
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
            CLI::error('Connection failed: ' . $e->getMessage());
            return null;
        }
    }

    private function findTable(array $tables, string $want): ?string
    {
        $wantLower = strtolower($want);
        foreach ($tables as $t) {
            if (strtolower($t) === $wantLower) {
                return $t;
            }
        }
        return null;
    }

    /**
     * แมปแถวจาก teachingEvaluate → teaching_evaluations (snake_case)
     */
    private function mapTeachingRow(array $row): array
    {
        $map = [
            'uid' => 'uid',
            'status' => 'status',
            'FirstName' => 'first_name',
            'LastName' => 'last_name',
            'titlethai' => 'title_thai',
            'curriculum' => 'curriculum',
            'position' => 'position',
            'positionmajor' => 'position_major',
            'positionmajorid' => 'position_major_id',
            'startdate' => 'start_date',
            'subjectid' => 'subject_id',
            'subjectname' => 'subject_name',
            'subjectcredit' => 'subject_credit',
            'subjectTeacher' => 'subject_teacher',
            'subjectdetail' => 'subject_detail',
            'filedoc' => 'file_doc',
            'linkvideo' => 'link_video',
            'submitdate' => 'submit_date',
            'stopdate' => 'stop_date',
            'detail' => 'detail',
            'teaching_data' => 'teaching_data',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
        $out = [];
        foreach ($map as $src => $dst) {
            if (array_key_exists($src, $row)) {
                $out[$dst] = $row[$src];
            }
        }
        return $out;
    }

    /**
     * แมปแถวจาก evaluatescore → evaluation_scores
     */
    private function mapScoreRow(array $row, array $teachingIdMap): ?array
    {
        $teachingId = $row['teachingid'] ?? null;
        if ($teachingId === null || $teachingId === '') {
            return null;
        }
        $newTeachingId = $teachingIdMap[(int) $teachingId] ?? null;
        if ($newTeachingId === null) {
            return null;
        }
        $out = [
            'teaching_id' => $newTeachingId,
            'email' => $row['email'] ?? '',
            'name' => $row['name'] ?? null,
            'comment' => $row['comment'] ?? null,
            'file_doc' => $row['filedoc'] ?? null,
            'score' => $row['score'] ?? null,
            'comment_date' => $row['commentdate'] ?? null,
            'send_date' => $row['senddate'] ?? null,
            'status' => $row['status'] ?? 0,
            'ref_num' => $row['refnum'] ?? null,
            'created_at' => $row['timestamp'] ?? $row['created_at'] ?? null,
        ];
        return $out;
    }

    /**
     * แมปแถวจาก emailEvaluate → evaluation_referees
     */
    private function mapRefereeRow(array $row): array
    {
        return [
            'email' => $row['email'] ?? '',
            'name' => $row['name'] ?? null,
            'status' => $row['status'] ?? 1,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    public function run(array $params): int
    {
        CLI::write('=== Import ข้อมูลประเมินผลการสอน academic_sci → newScience ===', 'yellow');
        CLI::newLine();

        $sourceGroup = $params[0] ?? 'edoclocal';
        $sourceConfig = $this->getSourceConfig($sourceGroup);
        $sourceConfig['database'] = $sourceConfig['database'] ?: 'academic_sci';

        $source = $this->connectGroup($sourceGroup, $sourceConfig);
        if ($source === null) {
            return 1;
        }

        $target = Database::connect();
        if (! $target->tableExists('teaching_evaluations') || ! $target->tableExists('evaluation_scores') || ! $target->tableExists('evaluation_referees')) {
            CLI::error('Target DB must have teaching_evaluations, evaluation_scores, evaluation_referees. Run: php spark migrate');
            $source->close();
            return 1;
        }

        $allTables = $source->listTables();
        $teachingTable = $this->findTable($allTables, self::SOURCE_TEACHING);
        $scoresTable   = $this->findTable($allTables, self::SOURCE_SCORES);
        $refereesTable = $this->findTable($allTables, self::SOURCE_REFEREES);

        if (! $teachingTable) {
            CLI::error("Source database does not have table '" . self::SOURCE_TEACHING . "'. Ensure DB name is academic_sci.");
            $source->close();
            return 1;
        }

        $teachingIdMap = [];
        $rows = $source->table($teachingTable)->get()->getResultArray();
        foreach ($rows as $row) {
            $oldId = isset($row['id']) ? (int) $row['id'] : null;
            $data = $this->mapTeachingRow($row);
            $target->table('teaching_evaluations')->insert($data);
            $newId = $target->insertID();
            if ($oldId !== null && $newId) {
                $teachingIdMap[$oldId] = (int) $newId;
            }
        }
        CLI::write("Import teaching_evaluations จาก {$teachingTable}: " . count($teachingIdMap) . " แถว", 'green');

        if ($scoresTable) {
            $scoreRows = $source->table($scoresTable)->get()->getResultArray();
            $count = 0;
            foreach ($scoreRows as $row) {
                $data = $this->mapScoreRow($row, $teachingIdMap);
                if ($data !== null) {
                    $target->table('evaluation_scores')->insert($data);
                    $count++;
                }
            }
            CLI::write("Import evaluation_scores จาก {$scoresTable}: {$count} แถว", 'green');
        } else {
            CLI::write('ข้าม evaluation_scores (ไม่พบตาราง ' . self::SOURCE_SCORES . ')', 'yellow');
        }

        if ($refereesTable) {
            $refRows = $source->table($refereesTable)->get()->getResultArray();
            foreach ($refRows as $row) {
                $data = $this->mapRefereeRow($row);
                if (($data['email'] ?? '') !== '') {
                    $target->table('evaluation_referees')->insert($data);
                }
            }
            CLI::write("Import evaluation_referees จาก {$refereesTable}: " . count($refRows) . " แถว", 'green');
        } else {
            CLI::write('ข้าม evaluation_referees (ไม่พบตาราง ' . self::SOURCE_REFEREES . ')', 'yellow');
        }

        $source->close();
        CLI::newLine();
        CLI::write('Import เสร็จสิ้น', 'green');
        return 0;
    }
}
