<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Migrate Edoc data from name-based (participant = names) to email-based (edoc_document_tags.tag_email).
 * Handles "ทุกคน" by tagging all users with edoc access.
 */
class MigrateEdocToNewStructure extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'edoc:migrate-to-new-structure';
    protected $description = 'Migrate Edoc participant names to edoc_document_tags (email-based); optional: tag groups and document views from source DB';
    protected $usage       = 'edoc:migrate-to-new-structure [options]';
    protected $arguments  = [];
    protected $options    = [
        'analyze'   => 'Run analysis only (counts, participant format, unique names)',
        'dry-run'   => 'Simulate migration without writing to database',
        'source-db' => 'Source database name for old tables (e.g. sci-edoc) for tag groups and document views',
    ];

    /** @var array<string, string> name (trimmed) => email */
    private array $nameToEmailMap = [];

    /** @var list<string> emails of all users with edoc access (for "ทุกคน") */
    private array $edocUserEmails = [];

    /** @var array<int, string> idtag => email (when source DB has edoctag) */
    private array $idtagToEmailMap = [];

    private bool $dryRun = false;

    private string $unmatchedLogPath = '';

    public function run(array $params)
    {
        $this->dryRun = CLI::getOption('dry-run') === true;

        if ($this->dryRun) {
            CLI::write('DRY RUN: No data will be written.', 'yellow');
        }

        $db = \Config\Database::connect();

        // --- Analysis only ---
        if (CLI::getOption('analyze') === true) {
            $this->runAnalysis($db);
            return 0;
        }

        try {
            // 1. Build name-to-email map (user + student_user, optional source edoctag)
            CLI::write('Building name-to-email map...', 'yellow');
            $this->buildNameToEmailMap($db);
            $sourceDb = $this->getSourceDb();
            if ($sourceDb !== null) {
                $this->buildIdtagToEmailFromSource($sourceDb);
            }
            CLI::write('  Map size: ' . count($this->nameToEmailMap) . ' name(s)', 'green');

            // 2. Load list of all users with edoc access (for "ทุกคน")
            CLI::write('Loading edoc-access user emails (for "ทุกคน")...', 'yellow');
            $this->loadEdocUserEmails($db);
            CLI::write('  Edoc users: ' . count($this->uniques($this->edocUserEmails)) . ' email(s)', 'green');

            // 3. Enrich edoctitle (volume_id, doc_year) if needed
            CLI::write('Enriching edoctitle (volume_id, doc_year)...', 'yellow');
            $this->enrichEdoctitle($db);

            // 4. Migrate participant names -> edoc_document_tags
            CLI::write('Migrating participant names to edoc_document_tags...', 'yellow');
            $this->migrateParticipantsToDocumentTags($db);

            // 5. Migrate edoctaggroups -> edoc_tag_groups (if source DB available)
            if ($sourceDb !== null && $this->tableExists($sourceDb, 'edoctaggroups')) {
                CLI::write('Migrating edoctaggroups -> edoc_tag_groups...', 'yellow');
                $this->migrateTagGroups($sourceDb, $db);
            }

            // 6. Migrate documentviews -> document_views (if source DB available)
            if ($sourceDb !== null && $this->tableExists($sourceDb, 'documentviews')) {
                CLI::write('Migrating documentviews -> document_views...', 'yellow');
                $this->migrateDocumentViews($sourceDb, $db);
            }

            $this->runValidationReport($db);
            CLI::write('Edoc migration completed successfully.', 'green');
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
            if (function_exists('log_message')) {
                log_message('error', '[MigrateEdocToNewStructure] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
            return 1;
        }

        return 0;
    }

    /**
     * Run analysis: counts, participant format, unique names.
     */
    private function runAnalysis($db): void
    {
        CLI::write('=== Edoc migration analysis ===', 'cyan');

        $count = $db->table('edoctitle')->countAllResults();
        CLI::write("edoctitle count: {$count}", 'green');

        if ($this->tableExists($db, 'edoc_document_tags')) {
            $countTags = $db->table('edoc_document_tags')->countAllResults();
            CLI::write("edoc_document_tags count: {$countTags}", 'green');
        }

        $uniqueNames = [];
        $everyoneCount = 0;
        $sampleParticipants = [];
        $batchSize = 1000;
        $offset = 0;
        do {
            $docs = $db->table('edoctitle')
                ->select('iddoc, participant')
                ->where('participant IS NOT NULL', null, false)
                ->where('participant !=', '')
                ->orderBy('iddoc', 'ASC')
                ->limit($batchSize, $offset)
                ->get()
                ->getResultArray();
            foreach ($docs as $row) {
                $p = (string) $row['participant'];
                if (count($sampleParticipants) < 5) {
                    $sampleParticipants[$row['iddoc']] = mb_substr($p, 0, 80) . (mb_strlen($p) > 80 ? '...' : '');
                }
                $parts = array_map('trim', explode(',', $p));
                foreach ($parts as $name) {
                    if ($name === '') {
                        continue;
                    }
                    $uniqueNames[$name] = ($uniqueNames[$name] ?? 0) + 1;
                    if ($name === 'ทุกคน') {
                        $everyoneCount++;
                    }
                }
            }
            $offset += $batchSize;
        } while (count($docs) === $batchSize);

        CLI::write('Sample participant values (first 5):', 'yellow');
        $i = 0;
        foreach ($sampleParticipants as $iddoc => $val) {
            if ($i >= 5) {
                break;
            }
            CLI::write("  iddoc={$iddoc}: " . $val);
            $i++;
        }

        CLI::write('Unique participant names (all documents): ' . count($uniqueNames), 'green');
        CLI::write('Documents with "ทุกคน": ' . $everyoneCount, 'green');

        $topNames = $uniqueNames;
        arsort($topNames);
        CLI::write('Top 15 participant names:', 'yellow');
        $j = 0;
        foreach ($topNames as $name => $cnt) {
            if ($j >= 15) {
                break;
            }
            CLI::write("  \"{$name}\" => {$cnt}");
            $j++;
        }

        CLI::write('=== End analysis ===', 'cyan');
    }

    private function tableExists($db, string $table): bool
    {
        return $db->tableExists($table);
    }

    /**
     * Build unified name => email from user, student_user, and optionally source edoctag.
     */
    private function buildNameToEmailMap($db): void
    {
        $this->nameToEmailMap = [];

        if ($this->tableExists($db, 'user')) {
            $cols = ['email', 'gf_name', 'gl_name'];
            if ($db->fieldExists('thai_name', 'user') && $db->fieldExists('thai_lastname', 'user')) {
                $cols[] = 'thai_name';
                $cols[] = 'thai_lastname';
            }
            $users = $db->query('SELECT ' . implode(', ', $cols) . ' FROM user WHERE email IS NOT NULL AND email != ""')->getResultArray();
            foreach ($users as $u) {
                $email = trim((string) ($u['email'] ?? ''));
                if ($email === '') {
                    continue;
                }
                $keyEn = trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
                if ($keyEn !== '') {
                    $this->nameToEmailMap[$keyEn] = $email;
                }
                if (isset($u['thai_name'], $u['thai_lastname'])) {
                    $keyTh = trim(($u['thai_name'] ?? '') . ' ' . ($u['thai_lastname'] ?? ''));
                    if ($keyTh !== '') {
                        $this->nameToEmailMap[$keyTh] = $email;
                    }
                }
            }
        }

        if ($this->tableExists($db, 'student_user')) {
            $cols = ['email', 'gf_name', 'gl_name'];
            if ($db->fieldExists('tf_name', 'student_user') && $db->fieldExists('tl_name', 'student_user')) {
                $cols[] = 'tf_name';
                $cols[] = 'tl_name';
            }
            if ($db->fieldExists('th_name', 'student_user') && $db->fieldExists('thai_lastname', 'student_user')) {
                $cols[] = 'th_name';
                $cols[] = 'thai_lastname';
            }
            $students = $db->query('SELECT ' . implode(', ', $cols) . ' FROM student_user WHERE email IS NOT NULL AND email != ""')->getResultArray();
            foreach ($students as $s) {
                $email = trim((string) ($s['email'] ?? ''));
                if ($email === '') {
                    continue;
                }
                $keyEn = trim(($s['gf_name'] ?? '') . ' ' . ($s['gl_name'] ?? ''));
                if ($keyEn !== '') {
                    $this->nameToEmailMap[$keyEn] = $email;
                }
                if (isset($s['tf_name'], $s['tl_name'])) {
                    $keyTh = trim(($s['tf_name'] ?? '') . ' ' . ($s['tl_name'] ?? ''));
                    if ($keyTh !== '') {
                        $this->nameToEmailMap[$keyTh] = $email;
                    }
                }
                if (isset($s['th_name'], $s['thai_lastname'])) {
                    $keyTh2 = trim(($s['th_name'] ?? '') . ' ' . ($s['thai_lastname'] ?? ''));
                    if ($keyTh2 !== '') {
                        $this->nameToEmailMap[$keyTh2] = $email;
                    }
                }
            }
        }
    }

    private function getSourceDb(): ?object
    {
        $name = CLI::getOption('source-db');
        if ($name === null || $name === true || $name === '') {
            return null;
        }
        $config = new \Config\Database();
        $config->default['database'] = $name;
        return \Config\Database::connect($config->default);
    }

    /**
     * When source DB has edoctag (idtag, gf_name, gl_name, email), build idtag => email and add names to nameToEmailMap.
     */
    private function buildIdtagToEmailFromSource($sourceDb): void
    {
        if (!$this->tableExists($sourceDb, 'edoctag')) {
            return;
        }
        $hasEmail = $sourceDb->fieldExists('email', 'edoctag');
        $tags = $sourceDb->query('SELECT idtag, gf_name, gl_name' . ($hasEmail ? ', email' : '') . ' FROM edoctag')->getResultArray();
        foreach ($tags as $t) {
            $idtag = (int) ($t['idtag'] ?? 0);
            $email = $hasEmail && !empty(trim((string) ($t['email'] ?? ''))) ? trim($t['email']) : null;
            $key = trim(($t['gf_name'] ?? '') . ' ' . ($t['gl_name'] ?? ''));
            if ($email !== null) {
                $this->idtagToEmailMap[$idtag] = $email;
                if ($key !== '') {
                    $this->nameToEmailMap[$key] = $email;
                }
            } elseif ($key !== '' && isset($this->nameToEmailMap[$key])) {
                $this->idtagToEmailMap[$idtag] = $this->nameToEmailMap[$key];
            }
        }
    }

    private function loadEdocUserEmails($db): void
    {
        $this->edocUserEmails = [];

        if ($this->tableExists($db, 'user_system_access') && $this->tableExists($db, 'systems')) {
            $sys = $db->query("SELECT id FROM systems WHERE slug = 'edoc' AND is_active = 1 LIMIT 1")->getRowArray();
            if (!empty($sys)) {
                $sysId = (int) $sys['id'];
                $hasEmail = $db->fieldExists('user_email', 'user_system_access');
                if ($hasEmail) {
                    $rows = $db->query("SELECT user_email FROM user_system_access WHERE system_id = ? AND user_email IS NOT NULL AND user_email != ''", [$sysId])->getResultArray();
                    foreach ($rows as $r) {
                        $this->edocUserEmails[] = trim($r['user_email']);
                    }
                } else {
                    $rows = $db->query("SELECT user_uid FROM user_system_access WHERE system_id = ?", [$sysId])->getResultArray();
                    foreach ($rows as $r) {
                        $uid = (int) $r['user_uid'];
                        $u = $db->query('SELECT email FROM user WHERE uid = ?', [$uid])->getRowArray();
                        if (!empty($u['email'])) {
                            $this->edocUserEmails[] = trim($u['email']);
                        }
                    }
                }
            }
        }

        if (empty($this->edocUserEmails) && $this->tableExists($db, 'user') && $db->fieldExists('edoc', 'user')) {
            $rows = $db->query("SELECT email FROM user WHERE edoc = 1 AND email IS NOT NULL AND email != ''")->getResultArray();
            foreach ($rows as $r) {
                $this->edocUserEmails[] = trim($r['email']);
            }
        }
    }

    private function uniques(array $list): array
    {
        return array_values(array_unique(array_filter($list)));
    }

    /**
     * Enrich edoctitle: set volume_id and doc_year from regisdate/datedoc if missing.
     */
    private function enrichEdoctitle($db): void
    {
        if (!$db->fieldExists('volume_id', 'edoctitle') || !$db->fieldExists('doc_year', 'edoctitle')) {
            return;
        }

        $volumesByYear = [];
        $docs = $db->query('SELECT iddoc, volume_id, doc_year, regisdate, datedoc FROM edoctitle')->getResultArray();
        $volumeModel = new \App\Models\Edoc\EdocVolumeModel();

        foreach ($docs as $doc) {
            $iddoc = (int) $doc['iddoc'];
            $vid = isset($doc['volume_id']) ? (int) $doc['volume_id'] : null;
            $year = isset($doc['doc_year']) ? (int) $doc['doc_year'] : null;

            if ($vid !== null && $vid > 0 && $year !== null && $year > 0) {
                continue;
            }

            $year = $year > 0 ? $year : $this->extractYear($doc['regisdate'] ?? $doc['datedoc'] ?? null);
            if ($year === null || $year < 2400) {
                continue;
            }

            if (!isset($volumesByYear[$year])) {
                $vols = $volumeModel->getByYear($year);
                if (empty($vols) && !$this->dryRun) {
                    $volumeModel->createYearVolumes($year);
                    $vols = $volumeModel->getByYear($year);
                }
                $volumesByYear[$year] = $vols;
            }
            $vols = $volumesByYear[$year];
            $volId = $vols[0]['id'] ?? null;
            if ($volId === null) {
                continue;
            }

            if ($this->dryRun) {
                continue;
            }
            $db->table('edoctitle')->where('iddoc', $iddoc)->update([
                'volume_id' => $volId,
                'doc_year'  => $year,
            ]);
        }
    }

    private function extractYear($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $y = (int) $value;
            return $y > 2000 ? $y : null;
        }
        $date = is_string($value) ? date_parse($value) : null;
        if ($date && $date['year']) {
            return (int) $date['year'];
        }
        if (preg_match('/\b(25\d{2})\b/', (string) $value, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/\b(20\d{2})\b/', (string) $value, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    private function migrateParticipantsToDocumentTags($db): void
    {
        $this->unmatchedLogPath = WRITEPATH . 'logs/edoc_migration_unmatched_' . date('Y-m-d_His') . '.log';
        if (!is_dir(WRITEPATH . 'logs')) {
            @mkdir(WRITEPATH . 'logs', 0755, true);
        }

        $docTagModel = new \App\Models\Edoc\EdocDocumentTagModel();
        $docs = $db->query('SELECT iddoc, participant FROM edoctitle WHERE participant IS NOT NULL AND participant != ""')->getResultArray();
        $edocEmails = $this->uniques($this->edocUserEmails);
        $inserted = 0;
        $unmatched = [];

        foreach ($docs as $row) {
            $documentId = (int) $row['iddoc'];
            $participant = (string) $row['participant'];
            $names = array_map('trim', explode(',', $participant));
            $names = array_filter($names);

            $emailsToSet = [];
            $sourceTable = 'user';

            foreach ($names as $name) {
                if ($name === 'ทุกคน') {
                    foreach ($edocEmails as $email) {
                        $emailsToSet[] = ['email' => $email, 'source' => $sourceTable];
                    }
                    continue;
                }
                $email = $this->nameToEmailMap[$name] ?? null;
                if ($email !== null) {
                    $emailsToSet[] = ['email' => $email, 'source' => $this->detectSourceForEmail($db, $email)];
                } elseif (strpos($name, '@') !== false) {
                    // Already an email (e.g. after previous migration or mixed data)
                    $emailsToSet[] = ['email' => trim($name), 'source' => $this->detectSourceForEmail($db, trim($name))];
                } else {
                    $unmatched[] = "doc_id={$documentId} name=\"{$name}\"";
                }
            }

            $emailsToSet = $this->dedupeEmails($emailsToSet);
            if (empty($emailsToSet)) {
                continue;
            }

            if (!$this->dryRun) {
                $docTagModel->setDocumentTags($documentId, $emailsToSet);
                $newParticipant = implode(',', array_column($emailsToSet, 'email'));
                $db->table('edoctitle')->where('iddoc', $documentId)->update(['participant' => $newParticipant]);
            }
            $inserted++;
        }

        if (!empty($unmatched)) {
            $logContent = implode("\n", $unmatched) . "\n";
            if (!$this->dryRun && $this->unmatchedLogPath !== '') {
                file_put_contents($this->unmatchedLogPath, $logContent);
            }
            CLI::write('  Unmatched names: ' . count($unmatched) . ($this->unmatchedLogPath ? ' (see ' . $this->unmatchedLogPath . ')' : ''), 'yellow');
        }
        CLI::write("  Processed {$inserted} documents with participants.", 'green');
    }

    private function detectSourceForEmail($db, string $email): string
    {
        if ($this->tableExists($db, 'user')) {
            $r = $db->table('user')->where('email', $email)->limit(1)->get()->getRowArray();
            if (!empty($r)) {
                return 'user';
            }
        }
        if ($this->tableExists($db, 'student_user')) {
            $r = $db->table('student_user')->where('email', $email)->limit(1)->get()->getRowArray();
            if (!empty($r)) {
                return 'student_user';
            }
        }
        return 'user';
    }

    private function dedupeEmails(array $list): array
    {
        $seen = [];
        $out = [];
        foreach ($list as $item) {
            $email = is_array($item) ? ($item['email'] ?? $item) : $item;
            $email = strtolower(trim($email));
            if ($email !== '' && !isset($seen[$email])) {
                $seen[$email] = true;
                $out[] = is_array($item) ? $item : ['email' => $email, 'source' => 'user'];
            }
        }
        return $out;
    }

    private function migrateTagGroups($sourceDb, $targetDb): void
    {
        if (!$this->tableExists($targetDb, 'edoc_tag_groups')) {
            return;
        }
        $rows = $sourceDb->query('SELECT group_id, group_name, idtag FROM edoctaggroups ORDER BY group_id, idtag')->getResultArray();
        $byGroup = [];
        foreach ($rows as $r) {
            $gid = $r['group_id'] ?? $r['group_name'];
            $byGroup[$gid]['name'] = $r['group_name'] ?? 'Group ' . $gid;
            $byGroup[$gid]['idtags'][] = (int) ($r['idtag'] ?? 0);
        }

        $tagGroupModel = new \App\Models\Edoc\TagGroupModel();
        $count = 0;
        foreach ($byGroup as $g) {
            $emails = [];
            foreach ($g['idtags'] as $idtag) {
                if (isset($this->idtagToEmailMap[$idtag])) {
                    $emails[] = $this->idtagToEmailMap[$idtag];
                }
            }
            $emails = array_values(array_unique($emails));
            if ($this->dryRun) {
                $count++;
                continue;
            }
            $tagGroupModel->saveGroup($g['name'], $emails, null);
            $count++;
        }
        CLI::write("  Migrated {$count} tag groups.", 'green');
    }

    private function migrateDocumentViews($sourceDb, $targetDb): void
    {
        if (!$this->tableExists($targetDb, 'document_views')) {
            return;
        }
        $views = $sourceDb->query('SELECT view_id, iddoc, idtag, view_time, ip_address FROM documentviews')->getResultArray();
        $uidByEmail = [];
        if ($this->tableExists($targetDb, 'user')) {
            $users = $targetDb->query('SELECT uid, email FROM user')->getResultArray();
            foreach ($users as $u) {
                $uidByEmail[strtolower(trim($u['email']))] = (int) $u['uid'];
            }
        }

        $inserted = 0;
        foreach ($views as $v) {
            $documentId = (int) $v['iddoc'];
            $idtag = (int) ($v['idtag'] ?? 0);
            $email = $this->idtagToEmailMap[$idtag] ?? null;
            if ($email === null) {
                continue;
            }
            $userId = $uidByEmail[strtolower($email)] ?? null;
            if ($userId === null) {
                continue;
            }
            if ($this->dryRun) {
                $inserted++;
                continue;
            }
            $targetDb->table('document_views')->insert([
                'document_id' => $documentId,
                'user_id'     => $userId,
                'ip_address'  => $v['ip_address'] ?? null,
                'viewed_at'   => $v['view_time'] ?? date('Y-m-d H:i:s'),
            ]);
            $inserted++;
        }
        CLI::write("  Migrated {$inserted} document views.", 'green');
    }

    /**
     * Print validation report: table counts (edoctitle, edoc_document_tags, edoc_tag_groups, document_views).
     */
    private function runValidationReport($db): void
    {
        CLI::write('=== Validation report ===', 'cyan');
        $titles = $db->table('edoctitle')->countAllResults();
        CLI::write("  edoctitle: {$titles}");

        if ($this->tableExists($db, 'edoc_document_tags')) {
            $tags = $db->table('edoc_document_tags')->countAllResults();
            CLI::write("  edoc_document_tags: {$tags}");
        }
        if ($this->tableExists($db, 'edoc_tag_groups')) {
            $groups = $db->table('edoc_tag_groups')->countAllResults();
            CLI::write("  edoc_tag_groups: {$groups}");
        }
        if ($this->tableExists($db, 'document_views')) {
            $views = $db->table('document_views')->countAllResults();
            CLI::write("  document_views: {$views}");
        }
        CLI::write('=== End report ===', 'cyan');
    }
}
