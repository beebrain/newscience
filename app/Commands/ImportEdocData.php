<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class ImportEdocData extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'import:edoc';
    protected $description = 'Import Edoc data from sci-edoc (use edoclocal when DB is cloned locally)';
    protected $usage = 'import:edoc [source-group]. Example: import:edoc edoclocal';
    protected $arguments = [];
    protected $options = [];

    private function getSourceConfig(string $sourceGroup): array
    {
        $base = (new \Config\Database())->default;
        return array_merge($base, [
            'hostname' => (string) (env("database.{$sourceGroup}.hostname") ?? $base['hostname'] ?? 'localhost'),
            'database' => (string) (env("database.{$sourceGroup}.database") ?? $base['database'] ?? ''),
            'username' => (string) (env("database.{$sourceGroup}.username") ?? $base['username'] ?? 'root'),
            'password' => (string) (env("database.{$sourceGroup}.password") ?? $base['password'] ?? ''),
            'DBDriver' => (string) (env("database.{$sourceGroup}.DBDriver") ?? $base['DBDriver'] ?? 'MySQLi'),
            'port'     => (int) (env("database.{$sourceGroup}.port") ?? $base['port'] ?? 3306),
        ]);
    }

    /** ย้อนหลังได้แค่ 5 ปี (เทียบปี พ.ศ. ปัจจุบัน) */
    private const YEARS_BACK = 5;

    /** ค.ศ. → พ.ศ. */
    private const CE_TO_BE = 543;

    /**
     * ปี พ.ศ. ปัจจุบัน
     */
    private function currentYearBE(): int
    {
        return (int) date('Y') + self::CE_TO_BE;
    }

    /**
     * แปลงปีที่ได้ (ค.ศ. หรือ พ.ศ.) เป็นปี พ.ศ. แล้วเช็คช่วง 5 ปีย้อนหลัง
     * เก่ากว่า 5 ปี = การ convert ไม่ถูกต้อง → คืน null
     */
    private function normalizeToYearBE(int $raw): ?int
    {
        $yearBE = $raw >= 2400 ? $raw : $raw + self::CE_TO_BE;
        $currentBE = $this->currentYearBE();
        $yearMin = $currentBE - self::YEARS_BACK;
        if ($yearBE >= $yearMin && $yearBE <= $currentBE) {
            return $yearBE;
        }
        return null;
    }

    /**
     * ดึงปีจาก officeiddoc / regisdate / datedoc แล้วคืนเฉพาะปี พ.ศ. ที่อยู่ในช่วง 5 ปีย้อนหลัง
     * มาตรฐาน: เก็บและใช้ปี พ.ศ. เท่านั้น; เก่ากว่า 5 ปีถือว่า convert ไม่ถูกต้อง
     */
    private function extractDocYear(?string $officeiddoc, $regisdate, $datedoc): ?int
    {
        $raw = $this->extractDocYearRaw($officeiddoc, $regisdate, $datedoc);
        if ($raw === null || $raw < 2000) {
            return null;
        }
        return $this->normalizeToYearBE($raw);
    }

    private function extractDocYearRaw(?string $officeiddoc, $regisdate, $datedoc): ?int
    {
        $office = (string) ($officeiddoc ?? '');
        if ($office !== '') {
            if (preg_match('/\b(25\d{2})\b/', $office, $m)) {
                return (int) $m[1];
            }
            if (preg_match('/\b(20\d{2})\b/', $office, $m)) {
                return (int) $m[1];
            }
        }
        foreach ([$regisdate, $datedoc] as $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (is_numeric($v)) {
                $y = (int) $v;
                if ($y > 2000) {
                    return $y;
                }
                continue;
            }
            $parsed = is_string($v) ? date_parse($v) : null;
            if ($parsed && !empty($parsed['year'])) {
                return (int) $parsed['year'];
            }
            if (preg_match('/\b(25\d{2})\b/', (string) $v, $m)) {
                return (int) $m[1];
            }
            if (preg_match('/\b(20\d{2})\b/', (string) $v, $m)) {
                return (int) $m[1];
            }
        }
        return null;
    }

    /**
     * Normalize fileaddress เป็น JSON array string เสมอ: ["file1.pdf","file2.pdf"]
     * รองรับต้นทาง: ชื่อไฟล์เดี่ยว, คั่นด้วย comma, หรือ JSON อยู่แล้ว
     */
    private function normalizeFileAddressToJson(?string $fileaddress): ?string
    {
        if ($fileaddress === null || trim($fileaddress) === '') {
            return '[]';
        }
        $raw = trim($fileaddress);
        $list = [];
        $decoded = @json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded as $f) {
                $f = is_string($f) ? trim($f, " \"'[]") : (string) $f;
                if ($f !== '') {
                    $list[] = $f;
                }
            }
        } else {
            $parts = array_map('trim', explode(',', $raw));
            foreach ($parts as $p) {
                $p = trim($p, " \"'[]");
                if ($p !== '') {
                    $list[] = $p;
                }
            }
            if (empty($list)) {
                $clean = trim($raw, " \"'[]");
                if ($clean !== '') {
                    $list[] = $clean;
                }
            }
        }
        $list = array_values(array_filter(array_map(function ($f) {
            return trim(preg_replace('/["\'\[\]\s]+$/', '', preg_replace('/^["\'\[\]\s]+/', '', (string) $f)));
        }, $list), function ($f) {
            return $f !== '';
        }));
        return json_encode($list); // ว่างได้เป็น []
    }

    /**
     * แมป doctype (หรือข้อความจาก officeiddoc) เป็น volume_type ตาม edoc_volumes
     * หากแมปไม่ได้ให้ใช้ announcement (ประกาศ)
     */
    private function doctypeToVolumeType(?string $doctype): string
    {
        $t = trim((string) ($doctype ?? ''));
        if ($t === '') {
            return 'announcement';
        }
        if (strpos($t, 'รับภายใน') !== false) {
            return 'receive_internal';
        }
        if (strpos($t, 'รับภายนอก') !== false || strpos($t, 'ภายนอก') !== false) {
            return 'external';
        }
        if (strpos($t, 'ส่งภายใน') !== false) {
            return 'send_internal';
        }
        if (strpos($t, 'คำสั่ง') !== false) {
            return 'order';
        }
        if (strpos($t, 'ประกาศ') !== false) {
            return 'announcement';
        }
        return 'announcement';
    }

    /** cache: (year, volume_type) => volume_id */
    private $volumeIdCache = [];

    /** เล่มปีรวม (เก่ากว่า 5 ปี) — year=0, volume_type=announcement */
    private const COMBINED_VOLUME_YEAR = 0;
    private const COMBINED_VOLUME_LABEL = 'ปีรวม (เก่ากว่า 5 ปี)';

    /**
     * หา volume_id ของเล่ม "ปีรวม" สำหรับเอกสารที่เก่ากว่า 5 ปี
     */
    private function getOrCreateCombinedVolumeId($targetDB): ?int
    {
        $key = self::COMBINED_VOLUME_YEAR . ':announcement';
        if (isset($this->volumeIdCache[$key])) {
            return $this->volumeIdCache[$key];
        }
        if (!$targetDB->tableExists('edoc_volumes')) {
            return null;
        }
        $row = $targetDB->table('edoc_volumes')
            ->where('year', self::COMBINED_VOLUME_YEAR)
            ->where('volume_type', 'announcement')
            ->limit(1)
            ->get()
            ->getRowArray();
        if (!empty($row['id'])) {
            $this->volumeIdCache[$key] = (int) $row['id'];
            return $this->volumeIdCache[$key];
        }
        $targetDB->table('edoc_volumes')->insert([
            'year'          => self::COMBINED_VOLUME_YEAR,
            'volume_type'   => 'announcement',
            'volume_label'  => self::COMBINED_VOLUME_LABEL,
            'is_active'     => 1,
            'created_by'    => null,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $id = $targetDB->insertID();
        if ($id) {
            $this->volumeIdCache[$key] = (int) $id;
            return $this->volumeIdCache[$key];
        }
        return null;
    }

    /**
     * หา volume_id จาก edoc_volumes ตาม year + volume_type ถ้าไม่มีให้สร้างเล่มปีนั้น
     */
    private function getOrCreateVolumeId($targetDB, int $year, string $volumeType): ?int
    {
        $key = "{$year}:{$volumeType}";
        if (isset($this->volumeIdCache[$key])) {
            return $this->volumeIdCache[$key];
        }
        if (!$targetDB->tableExists('edoc_volumes')) {
            return null;
        }
        $row = $targetDB->table('edoc_volumes')
            ->where('year', $year)
            ->where('volume_type', $volumeType)
            ->limit(1)
            ->get()
            ->getRowArray();
        if (!empty($row['id'])) {
            $this->volumeIdCache[$key] = (int) $row['id'];
            return $this->volumeIdCache[$key];
        }
        $volumeModel = new \App\Models\Edoc\EdocVolumeModel();
        $volumeModel->createYearVolumes($year, null);
        $row = $targetDB->table('edoc_volumes')
            ->where('year', $year)
            ->where('volume_type', $volumeType)
            ->limit(1)
            ->get()
            ->getRowArray();
        if (!empty($row['id'])) {
            $this->volumeIdCache[$key] = (int) $row['id'];
            return $this->volumeIdCache[$key];
        }
        return null;
    }

    public function run(array $params)
    {
        $sourceGroup = $params[0] ?? 'edoclocal';
        CLI::write("Starting Edoc import (source: {$sourceGroup} → target: default)...", 'yellow');

        $sourceConfig = $this->getSourceConfig($sourceGroup);
        if (empty($sourceConfig['database'])) {
            CLI::error("database.{$sourceGroup}.database is not set in .env");
            return 1;
        }

        $sourceDB = \Config\Database::connect($sourceConfig);
        $targetDB = \Config\Database::connect();

        $totalSource = 0;
        $totalImported = 0;
        $totalFailed = 0;

        try {
            // Import edoctitle — ทุกรายการ ด้วยค่าเริ่มต้นถ้าไม่มี
            CLI::write('Importing edoctitle...', 'yellow');
            $edoctitles = $sourceDB->query('SELECT * FROM edoctitle')->getResultArray();
            $totalSource = count($edoctitles);
            $targetHasVolume = $targetDB->fieldExists('volume_id', 'edoctitle') && $targetDB->fieldExists('doc_year', 'edoctitle');
            if ($targetHasVolume && $targetDB->tableExists('edoc_volumes')) {
                CLI::write('Clearing edoc_volumes and resetting volume_id in edoctitle (ย้อนหลัง 5 ปีเท่านั้น)...', 'yellow');
                $targetDB->table('edoctitle')->update(['volume_id' => null]);
                $targetDB->query('TRUNCATE TABLE edoc_volumes');
                $this->volumeIdCache = [];
            }
            $count = 0;
            $failList = [];
            foreach ($edoctitles as $doc) {
                try {
                    $iddoc = (int) ($doc['iddoc'] ?? 0);
                    $volId = isset($doc['volume_id']) ? (int) $doc['volume_id'] : null;
                    $docYear = isset($doc['doc_year']) ? (int) $doc['doc_year'] : null;
                    $regis = $doc['regisdate'] ?? $doc['datedoc'] ?? null;
                    $regisStr = $regis ? (is_string($regis) ? $regis : date('Y-m-d H:i:s', is_numeric($regis) ? (int) $regis : time())) : date('Y-m-d H:i:s');
                    $officeiddoc = $doc['officeiddoc'] ?? null;
                    $doctype = $doc['doctype'] ?? null;
                    $fileaddressJson = $this->normalizeFileAddressToJson($doc['fileaddress'] ?? null);
                    // sci-edoc ไม่มี volume_id/doc_year → หาจาก officeiddoc และ doctype
                    if ($targetHasVolume && ($volId === null || $volId === 0) && ($docYear === null || $docYear === 0)) {
                        $docYear = $this->extractDocYear($officeiddoc, $doc['regisdate'] ?? null, $doc['datedoc'] ?? null);
                        if ($docYear !== null && $docYear > 0) {
                            $volumeType = $this->doctypeToVolumeType($doctype);
                            $volId = $this->getOrCreateVolumeId($targetDB, $docYear, $volumeType);
                        } else {
                            // เก่ากว่า 5 ปี → ใส่เล่ม "ปีรวม"
                            $rawYear = $this->extractDocYearRaw($officeiddoc, $doc['regisdate'] ?? null, $doc['datedoc'] ?? null);
                            if ($rawYear !== null && $rawYear >= 2000) {
                                $docYear = $rawYear >= 2400 ? $rawYear : $rawYear + self::CE_TO_BE;
                                $volId = $this->getOrCreateCombinedVolumeId($targetDB);
                            }
                        }
                    }
                    if ($targetHasVolume) {
                        $targetDB->query("INSERT INTO edoctitle 
                            (iddoc, volume_id, doc_year, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, userid, pages, copynum, `order`, regisdate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            volume_id = VALUES(volume_id),
                            doc_year = VALUES(doc_year),
                            officeiddoc = VALUES(officeiddoc),
                            title = VALUES(title),
                            datedoc = VALUES(datedoc),
                            doctype = VALUES(doctype),
                            owner = VALUES(owner),
                            participant = VALUES(participant),
                            fileaddress = VALUES(fileaddress),
                            userid = VALUES(userid),
                            pages = VALUES(pages),
                            copynum = VALUES(copynum),
                            `order` = VALUES(`order`),
                            regisdate = VALUES(regisdate)", [
                            $iddoc,
                            $volId ?: null,
                            $docYear ?: null,
                            $officeiddoc,
                            $doc['title'] ?? null,
                            $doc['datedoc'] ?? null,
                            $doctype,
                            $doc['owner'] ?? null,
                            $doc['participant'] ?? null,
                            $fileaddressJson,
                            $doc['userid'] ?? null,
                            (int) ($doc['pages'] ?? 0),
                            (int) ($doc['copynum'] ?? 1),
                            trim((string) ($doc['order'] ?? '')),
                            $regisStr
                        ]);
                    } else {
                        $targetDB->query("INSERT INTO edoctitle 
                            (iddoc, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, userid, pages, copynum, `order`, regisdate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            officeiddoc = VALUES(officeiddoc),
                            title = VALUES(title),
                            datedoc = VALUES(datedoc),
                            doctype = VALUES(doctype),
                            owner = VALUES(owner),
                            participant = VALUES(participant),
                            fileaddress = VALUES(fileaddress),
                            userid = VALUES(userid),
                            pages = VALUES(pages),
                            copynum = VALUES(copynum),
                            `order` = VALUES(`order`),
                            regisdate = VALUES(regisdate)", [
                            $iddoc,
                            $doc['officeiddoc'] ?? null,
                            $doc['title'] ?? null,
                            $doc['datedoc'] ?? null,
                            $doc['doctype'] ?? null,
                            $doc['owner'] ?? null,
                            $doc['participant'] ?? null,
                            $fileaddressJson,
                            $doc['userid'] ?? null,
                            (int) ($doc['pages'] ?? 0),
                            (int) ($doc['copynum'] ?? 1),
                            trim((string) ($doc['order'] ?? '')),
                            $regisStr
                        ]);
                    }
                    $count++;
                } catch (\Throwable $e) {
                    $totalFailed++;
                    $failList[] = ($doc['iddoc'] ?? '?') . ': ' . $e->getMessage();
                }
                if ($count % 500 == 0 && $count > 0) {
                    CLI::write("  edoctitle: {$count}...", 'green');
                }
            }
            $totalImported += $count;
            CLI::write("edoctitle: นำเข้าได้ {$count} / {$totalSource} รายการ" . ($totalFailed > 0 ? ", ล้มเหลว {$totalFailed}" : ''), 'green');
            if (!empty($failList) && count($failList) <= 20) {
                foreach (array_slice($failList, 0, 10) as $msg) {
                    CLI::write("  - {$msg}", 'red');
                }
                if (count($failList) > 10) {
                    CLI::write("  ... และอีก " . (count($failList) - 10) . " รายการ", 'red');
                }
            }

            // Import edoctag — ทุกรายการ
            CLI::write('Importing edoctag and linking with users by email...', 'yellow');

            $userTable = $targetDB->tableExists('user') ? 'user' : 'users';
            $userMap = [];
            if ($targetDB->tableExists($userTable)) {
                $users = $targetDB->query("SELECT uid, email, gf_name, gl_name FROM {$userTable}")->getResultArray();
                foreach ($users as $user) {
                    $key = trim((string) ($user['gf_name'] ?? '')) . ' ' . trim((string) ($user['gl_name'] ?? ''));
                    if ($key !== ' ') {
                        $userMap[$key] = $user['email'] ?? null;
                    }
                }
            }

            $edoctags = $sourceDB->query('SELECT * FROM edoctag')->getResultArray();
            $tagCount = 0;
            $tagFail = 0;
            $linkedCount = 0;
            $targetHasIdtag = $targetDB->fieldExists('idtag', 'edoctag');
            $targetHasFirstLast = $targetDB->fieldExists('first_name', 'edoctag');
            if (!$targetHasFirstLast && !$targetHasIdtag) {
                CLI::error('Target edoctag has neither idtag nor first_name/last_name columns.');
                return 1;
            }
            foreach ($edoctags as $tag) {
                try {
                    $idtag = $tag['idtag'] ?? $tag['id'] ?? null;
                    $firstName = $tag['gf_name'] ?? $tag['first_name'] ?? '';
                    $lastName = $tag['gl_name'] ?? $tag['last_name'] ?? '';
                    $email = $tag['email'] ?? null;
                    $tagKey = trim((string) $firstName) . ' ' . trim((string) $lastName);
                    if ($tagKey !== ' ' && isset($userMap[$tagKey])) {
                        $email = $userMap[$tagKey];
                        $linkedCount++;
                    }

                    if ($targetHasFirstLast) {
                        $targetDB->query("INSERT INTO edoctag (first_name, last_name, nickname, email) 
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), nickname = VALUES(nickname), email = VALUES(email)", [
                            $firstName ?: null,
                            $lastName ?: null,
                            $tag['nickname'] ?? null,
                            $email
                        ]);
                    } else {
                        $targetDB->query("INSERT INTO edoctag (idtag, gf_name, gl_name, nickname, office_idtag, email, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE gf_name = VALUES(gf_name), gl_name = VALUES(gl_name), nickname = VALUES(nickname), office_idtag = VALUES(office_idtag), email = VALUES(email)", [
                            $idtag,
                            $firstName ?: null,
                            $lastName ?: null,
                            $tag['nickname'] ?? null,
                            $tag['office_idtag'] ?? null,
                            $email,
                            $tag['created_at'] ?? date('Y-m-d H:i:s'),
                            $tag['updated_at'] ?? date('Y-m-d H:i:s')
                        ]);
                    }
                    $tagCount++;
                } catch (\Throwable $e) {
                    $tagFail++;
                }
            }
            $totalImported += $tagCount;
            CLI::write("edoctag: นำเข้าได้ {$tagCount} / " . count($edoctags) . " รายการ" . ($tagFail > 0 ? ", ล้มเหลว {$tagFail}" : '') . " (linked {$linkedCount} with users)", 'green');

            // Import edoctaggroups (skip if target has no such table, e.g. newScience uses edoc_tag_groups)
            if ($targetDB->tableExists('edoctaggroups')) {
                CLI::write('Importing edoctaggroups...', 'yellow');
                $groups = $sourceDB->query('SELECT * FROM edoctaggroups')->getResultArray();
            $count = 0;
            foreach ($groups as $group) {
                $targetDB->query("INSERT INTO edoctaggroups 
                    (group_id, group_name, idtag, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    group_name = VALUES(group_name),
                    idtag = VALUES(idtag)", [
                    $group['group_id'],
                    $group['group_name'],
                    $group['idtag'],
                    $group['created_at'] ?? date('Y-m-d H:i:s'),
                    $group['updated_at'] ?? date('Y-m-d H:i:s')
                ]);
                $count++;
            }
                CLI::write("Imported {$count} edoctaggroups records", 'green');
            } else {
                CLI::write('Skipping edoctaggroups (target has no edoctaggroups table).', 'yellow');
            }

            if ($targetDB->tableExists('documentviews')) {
                CLI::write('Importing documentviews...', 'yellow');
                $views = $sourceDB->query('SELECT * FROM documentviews')->getResultArray();
                $count = 0;
                foreach ($views as $view) {
                    $targetDB->query("INSERT INTO documentviews 
                        (view_id, iddoc, idtag, view_time, ip_address, user_agent, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        view_time = VALUES(view_time)", [
                        $view['view_id'],
                        $view['iddoc'],
                        $view['idtag'],
                        $view['view_time'],
                        $view['ip_address'] ?? null,
                        $view['user_agent'] ?? null,
                        $view['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $count++;
                    if ($count % 500 == 0) {
                        CLI::write("  Imported {$count} views...", 'green');
                    }
                }
                CLI::write("Imported {$count} documentviews records", 'green');
            } else {
                CLI::write('Skipping documentviews (target has no documentviews table).', 'yellow');
            }

            CLI::write('Edoc data import completed successfully!', 'green');
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
