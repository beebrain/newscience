<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * นำเข้าข้อมูลประเมินผลการสอนจาก sci-edoc (ตาราง teachingEvaluate, evaluatescore, emailEvaluate, user)
 * เข้า newScience (evaluate_teaching, evaluate_scores, evaluation_referees).
 *
 * ใช้: php spark import:eval [source-group]
 * เช่น: php spark import:eval edocserver     (ดึงจาก server จริง)
 *       php spark import:eval edoclocal      (ดึงจาก local clone)
 *       php spark import:eval edocserver --clear  (ล้างข้อมูลเดิมก่อน import)
 */
class ImportEval extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'import:eval';
    protected $description = 'Import evaluation data from sci-edoc (teachingEvaluate, evaluatescore, emailEvaluate) to newScience';
    protected $usage       = 'import:eval [source-group]. Example: import:eval edocserver';
    protected $arguments   = [];
    protected $options     = [
        'clear' => 'Clear target tables before importing',
    ];

    /** ชื่อตารางต้นทางใน sci-edoc (case-insensitive match) */
    private const SOURCE_TEACHING = 'teachingEvaluate';
    private const SOURCE_SCORES   = 'evaluatescore';
    private const SOURCE_REFEREES = 'emailEvaluate';
    private const SOURCE_USER     = 'user';

    private function getSourceConfig(string $sourceGroup): array
    {
        $base = (new Database())->default;
        $dbName = env("database.{$sourceGroup}.database") ?: 'sci-edoc';
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
     * แมปแถวจาก teachingEvaluate → evaluate_teaching (snake_case)
     */
    private function mapTeachingRow(array $row, array $uidEmailMap = []): array
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
        // Populate email from uid → email map
        $uid = $row['uid'] ?? null;
        if ($uid !== null && isset($uidEmailMap[(int) $uid])) {
            $out['email'] = $uidEmailMap[(int) $uid];
        }
        return $out;
    }

    /**
     * แมปแถวจาก evaluatescore → evaluate_scores
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
            'institution' => $row['institution'] ?? null,
            'expertise' => $row['expertise'] ?? null,
            'phone' => $row['phone'] ?? null,
            'status' => $row['status'] ?? 1,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    /**
     * สร้าง uid → email map จากตาราง user ต้นทาง
     */
    private function buildUidEmailMap($source, string $userTable): array
    {
        $map = [];
        try {
            $rows = $source->table($userTable)->select('email')->get()->getResultArray();
            foreach ($rows as $row) {
                $email = $row['email'] ?? null;
                if ($email !== null && $email !== '') {
                    // EdocSci uses email as PK, so use email itself as the value
                    // UID may not be in this table; we'll match by uid from teachingEvaluate
                    $map[] = $email;
                }
            }
            // Try to get uid mapping if the table has a uid/id column
            $fields = $source->getFieldNames($userTable);
            $hasUid = in_array('uid', $fields, true) || in_array('id', $fields, true);
            if ($hasUid) {
                $map = [];
                $uidCol = in_array('uid', $fields, true) ? 'uid' : 'id';
                $rows = $source->table($userTable)->select("{$uidCol}, email")->get()->getResultArray();
                foreach ($rows as $row) {
                    $uid = $row[$uidCol] ?? null;
                    $email = $row['email'] ?? null;
                    if ($uid !== null && $email !== null && $email !== '') {
                        $map[(int) $uid] = $email;
                    }
                }
            }
        } catch (\Throwable $e) {
            CLI::write('Warning: Could not read user table for email mapping: ' . $e->getMessage(), 'yellow');
        }
        return $map;
    }

    public function run(array $params): int
    {
        CLI::write('=== Import ข้อมูลประเมินผลการสอน sci-edoc → newScience ===', 'yellow');
        CLI::newLine();

        $sourceGroup = $params[0] ?? 'edocserver';
        $sourceConfig = $this->getSourceConfig($sourceGroup);
        $sourceConfig['database'] = $sourceConfig['database'] ?: 'sci-edoc';

        CLI::write("Connecting to source: {$sourceConfig['hostname']} / {$sourceConfig['database']} ...", 'light_gray');
        $source = $this->connectGroup($sourceGroup, $sourceConfig);
        if ($source === null) {
            return 1;
        }
        CLI::write('Connected to source DB', 'green');

        $target = Database::connect();
        if (! $target->tableExists('evaluate_teaching') || ! $target->tableExists('evaluate_scores') || ! $target->tableExists('evaluation_referees')) {
            CLI::error('Target DB must have evaluate_teaching, evaluate_scores, evaluation_referees. Run: php spark migrate');
            $source->close();
            return 1;
        }

        // --clear: ล้างข้อมูลเดิมก่อน import
        $doClear = CLI::getOption('clear') !== null;
        if ($doClear) {
            CLI::write('Clearing target tables...', 'yellow');
            $target->query('SET FOREIGN_KEY_CHECKS = 0');
            $target->table('evaluate_scores')->truncate();
            $target->table('evaluate_teaching')->truncate();
            $target->table('evaluation_referees')->truncate();
            $target->query('SET FOREIGN_KEY_CHECKS = 1');
            CLI::write('Target tables cleared', 'green');
        }

        $allTables = $source->listTables();
        CLI::write('Source tables found: ' . implode(', ', $allTables), 'light_gray');

        $teachingTable = $this->findTable($allTables, self::SOURCE_TEACHING);
        $scoresTable   = $this->findTable($allTables, self::SOURCE_SCORES);
        $refereesTable = $this->findTable($allTables, self::SOURCE_REFEREES);
        $userTable     = $this->findTable($allTables, self::SOURCE_USER);

        if (! $teachingTable) {
            CLI::error("Source database does not have table '" . self::SOURCE_TEACHING . "'. Ensure DB name is academic_sci.");
            $source->close();
            return 1;
        }

        // Build uid → email map from source user table
        $uidEmailMap = [];
        if ($userTable) {
            CLI::write("Building uid→email map from {$userTable}...", 'light_gray');
            $uidEmailMap = $this->buildUidEmailMap($source, $userTable);
            CLI::write('  Found ' . count($uidEmailMap) . ' user email mappings', 'green');
        } else {
            CLI::write('Warning: user table not found — email field will be empty', 'yellow');
        }

        $teachingIdMap = [];
        $rows = $source->table($teachingTable)->get()->getResultArray();
        CLI::write("Found " . count($rows) . " rows in {$teachingTable}", 'light_gray');
        foreach ($rows as $row) {
            $oldId = isset($row['id']) ? (int) $row['id'] : null;
            $data = $this->mapTeachingRow($row, $uidEmailMap);
            $target->table('evaluate_teaching')->insert($data);
            $newId = $target->insertID();
            if ($oldId !== null && $newId) {
                $teachingIdMap[$oldId] = (int) $newId;
            }
        }
        CLI::write("Import evaluate_teaching จาก {$teachingTable}: " . count($teachingIdMap) . " แถว", 'green');

        if ($scoresTable) {
            $scoreRows = $source->table($scoresTable)->get()->getResultArray();
            $count = 0;
            foreach ($scoreRows as $row) {
                $data = $this->mapScoreRow($row, $teachingIdMap);
                if ($data !== null) {
                    $target->table('evaluate_scores')->insert($data);
                    $count++;
                }
            }
            CLI::write("Import evaluate_scores จาก {$scoresTable}: {$count} แถว", 'green');
        } else {
            CLI::write('ข้าม evaluate_scores (ไม่พบตาราง ' . self::SOURCE_SCORES . ')', 'yellow');
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
