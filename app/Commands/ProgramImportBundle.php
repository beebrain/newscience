<?php

namespace App\Commands;

use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Services\ProgramContentBundleService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * นำเข้า content bundle JSON ต่อ programId โดยตรงจาก CLI (ไม่ผ่าน staging token)
 *
 * ตรงกับ logic ของ Dashboard::importContentBundleCommit:
 *   parse → validate shape → convert → merge กับแถวปัจจุบัน → updateOrCreate
 *
 * ด้วยความที่ CLI bypass auth/UI ไปตรง ๆ จึง guard 2 ชั้น:
 *   1) environment ต้องไม่เป็น production (ยกเว้นใส่ --force)
 *   2) program_id ใน file ต้องตรงกับ argument
 *
 * Usage:
 *   php spark program:import-bundle 12 /path/to/bundle.json
 *   php spark program:import-bundle 12 /path/to/bundle.json --dry-run
 *   php spark program:import-bundle 12 /path/to/bundle.json --force   (อนุญาตบน production)
 */
class ProgramImportBundle extends BaseCommand
{
    protected $group       = 'Program';
    protected $name        = 'program:import-bundle';
    protected $description = 'นำเข้า content bundle JSON เข้า program_pages ต่อ programId (CLI)';
    protected $usage       = 'program:import-bundle [programId] [path.json] [--dry-run] [--force]';
    protected $arguments   = [
        'programId' => 'รหัสหลักสูตร (ต้องตรงกับ program_id ในไฟล์)',
        'path'      => 'path ไปไฟล์ JSON bundle',
    ];
    protected $options = [
        '--dry-run' => 'ตรวจ + แสดงผลลัพธ์ ไม่บันทึกจริง',
        '--force'   => 'อนุญาตให้รันบน environment = production',
    ];

    public function run(array $params)
    {
        $env    = ENVIRONMENT;
        $force  = CLI::getOption('force') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        if ($env === 'production' && ! $force) {
            CLI::error('ปฏิเสธ: รันบน production ต้องใส่ --force');

            return;
        }

        $programId = (int) ($params[0] ?? CLI::getSegment(2) ?? 0);
        $path      = (string) ($params[1] ?? CLI::getSegment(3) ?? '');

        if ($programId <= 0 || $path === '') {
            CLI::error('ต้องระบุ programId และ path — ดู: php spark help program:import-bundle');

            return;
        }
        if (! is_file($path)) {
            CLI::error('ไม่พบไฟล์: ' . $path);

            return;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            CLI::error('อ่านไฟล์ไม่ได้หรือไฟล์ว่าง: ' . $path);

            return;
        }

        $svc = new ProgramContentBundleService();
        helper(['career_cards', 'tuition_fees', 'overview_lists']);

        $p = $svc->parseBundleJsonString($raw);
        if (! empty($p['errors'])) {
            $this->printErrors('parse', $p['errors']);

            return;
        }
        if ($p['program_id'] !== $programId) {
            CLI::error(sprintf('program_id ในไฟล์ (%s) ไม่ตรงกับ argument (%d)', var_export($p['program_id'], true), $programId));

            return;
        }

        $basicConv = $svc->basicToUpdateRow($p['basic']);
        $pageIn    = $p['content'] + $p['settings'];
        $pageConv  = $svc->pageBundleToUpdateRow($pageIn);
        $errors    = array_merge($basicConv['errors'], $pageConv['errors']);
        if (! empty($errors)) {
            $this->printErrors('convert', $errors);

            return;
        }
        if (empty($basicConv['update']) && empty($pageConv['update'])) {
            CLI::error('ไม่มี field ใน bundle สำหรับนำเข้า');

            return;
        }

        $programModel = new ProgramModel();
        $program      = $programModel->find($programId);
        if (! $program) {
            CLI::error('ไม่พบหลักสูตร id=' . $programId);

            return;
        }

        $pageModel = new ProgramPageModel();
        $existing  = $pageModel->findByProgramId($programId) ?? [];

        $pageMerged = ['program_id' => $programId];
        foreach ($pageModel->allowedFields as $field) {
            if (in_array($field, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if (array_key_exists($field, $pageConv['update'])) {
                $pageMerged[$field] = $pageConv['update'][$field];
            } elseif (array_key_exists($field, $existing)) {
                $pageMerged[$field] = $existing[$field];
            }
        }

        CLI::write('basic fields: ' . count($basicConv['update']) . ' — ' . implode(', ', array_keys($basicConv['update'])), 'yellow');
        CLI::write('page fields: ' . count($pageConv['update']) . ' — ' . implode(', ', array_keys($pageConv['update'])), 'yellow');
        if ($p['legacy']) {
            CLI::write('[legacy format {program, page}] แปลงเป็น 3 namespace แล้ว', 'light_cyan');
        }

        if ($dryRun) {
            CLI::write('[DRY-RUN] ไม่บันทึก', 'yellow');

            return;
        }

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            if (! empty($basicConv['update'])) {
                $programModel->update($programId, $basicConv['update']);
            }
            if (count($pageMerged) > 1) {
                $pageModel->updateOrCreate(['program_id' => $programId], $pageMerged);
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            CLI::error('บันทึกไม่สำเร็จ: ' . $e->getMessage());

            return;
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            CLI::error('บันทึกไม่สำเร็จ (transaction failed)');

            return;
        }

        $programAfter = $programModel->find($programId);
        $pageAfter    = $pageModel->findByProgramId($programId);
        $jsonAfter    = json_encode($svc->buildBundleFromDatabase($programId, $programAfter, $pageAfter), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($jsonAfter !== false) {
            $svc->writeSnapshotToUploads($programId, ProgramContentBundleService::SNAPSHOT_LATEST, $jsonAfter);
        }

        CLI::write('นำเข้าและบันทึกเนื้อหาเรียบร้อย program_id=' . $programId, 'green');
    }

    /**
     * @param list<string> $errors
     */
    private function printErrors(string $phase, array $errors): void
    {
        CLI::error('พบปัญหาใน ' . $phase . ' (' . count($errors) . ')');
        foreach ($errors as $e) {
            CLI::write('  - ' . $e, 'red');
        }
    }
}
