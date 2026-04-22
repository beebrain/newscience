<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ล้างไฟล์ staging ของ bundle import ที่หมดอายุแล้ว
 *
 * ไฟล์เขียนโดย ProgramContentBundleService::writeStagingFile ที่
 *   writable/temp/program_bundle_import/p{programId}_{token}.json
 * โดย `expires` = created + 10 นาที
 *
 * ตัวนี้ควรรันเป็น cron (ทุก 1 ชั่วโมง)
 *
 * Usage:
 *   php spark cleanup:program-bundle-staging           # ลบทุกไฟล์ที่ expired
 *   php spark cleanup:program-bundle-staging --dry-run # แสดงว่าจะลบอะไร ไม่ลบจริง
 *   php spark cleanup:program-bundle-staging --min-age 3600  # ลบเฉพาะไฟล์ที่อายุ > 1 ชม. (นอกเหนือ expired)
 */
class CleanupProgramBundleStaging extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'cleanup:program-bundle-staging';
    protected $description = 'ลบไฟล์ staging ของ bundle import ที่หมดอายุ (writable/temp/program_bundle_import/)';
    protected $usage       = 'cleanup:program-bundle-staging [--dry-run] [--min-age SECONDS]';
    protected $arguments   = [];
    protected $options     = [
        '--dry-run' => 'แสดงว่าจะลบอะไรบ้าง โดยไม่ลบไฟล์',
        '--min-age' => 'อายุไฟล์ขั้นต่ำ (วินาที) ที่จะลบ แม้ไม่ระบุ expires — ดีฟอลต์ 0',
    ];

    public function run(array $params)
    {
        $dryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $minAgeRaw = CLI::getOption('min-age');
        $minAge    = is_numeric($minAgeRaw) ? (int) $minAgeRaw : 0;

        $dir = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import';
        if (! is_dir($dir)) {
            CLI::write('ไม่มีโฟลเดอร์ staging: ' . $dir, 'yellow');

            return;
        }

        $now     = time();
        $removed = 0;
        $kept    = 0;
        $bytes   = 0;

        foreach (glob($dir . DIRECTORY_SEPARATOR . 'p*_*.json') ?: [] as $path) {
            $reason = $this->shouldDelete($path, $now, $minAge);
            if ($reason === null) {
                $kept++;

                continue;
            }
            $size = @filesize($path) ?: 0;
            if ($dryRun) {
                CLI::write(sprintf('[DRY] %s  (%s, %d bytes)', basename($path), $reason, $size), 'light_gray');
            } else {
                if (@unlink($path)) {
                    CLI::write(sprintf('ลบ %s  (%s, %d bytes)', basename($path), $reason, $size), 'green');
                    $removed++;
                    $bytes += $size;
                } else {
                    CLI::write('ลบไม่สำเร็จ: ' . $path, 'red');
                }
            }
        }

        CLI::newLine();
        if ($dryRun) {
            CLI::write(sprintf('เสร็จ (dry-run): เก็บ %d ไฟล์', $kept), 'yellow');
        } else {
            CLI::write(sprintf('เสร็จ: ลบ %d ไฟล์ (%.1f KB) เก็บ %d', $removed, $bytes / 1024, $kept), 'green');
        }
    }

    /**
     * คืน reason สั้น ๆ ว่าลบทำไม หรือ null ถ้าไม่ลบ
     */
    private function shouldDelete(string $path, int $now, int $minAge): ?string
    {
        $mtime = @filemtime($path);
        if ($mtime !== false && $minAge > 0 && ($now - $mtime) >= $minAge) {
            return sprintf('อายุ %d วินาที', $now - $mtime);
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return 'ไฟล์ว่าง/อ่านไม่ได้';
        }
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return 'payload พัง';
        }
        $expires = (int) ($data['expires'] ?? 0);
        if ($expires > 0 && $now > $expires) {
            return sprintf('หมดอายุแล้ว %d วินาที', $now - $expires);
        }

        return null;
    }
}
