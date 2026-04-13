<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\CvSyncLogModel;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * ดึง CV + ผลงานจาก Research Record ลง newScience (ใช้ร่วมกับ pull-all, auto บนหน้าแก้ไข CV, manual)
 */
class ResearchRecordCvPull
{
    public const TRIGGER_MANUAL = 'manual';

    public const TRIGGER_AUTO_EMPTY = 'auto_empty';

    public const TRIGGER_AUTO_STALE = 'auto_stale';

    public const DIRECTION_PULL = 'pull_all_rr_to_ns';

    public static function canonicalEmailForPerson(array $person): string
    {
        if (! empty($person['user_email'])) {
            return CvProfile::normalizeEmail((string) $person['user_email']);
        }
        if (! empty($person['email'])) {
            return CvProfile::normalizeEmail((string) $person['email']);
        }

        return CvProfile::normalizeEmail((string) session()->get('admin_email'));
    }

    /**
     * ยังไม่เคยดึงจาก RR สำเร็จ (ไม่มีแถว pull ใน cv_sync_log) และใน NS ไม่มี CV จริงๆ
     * (ไม่มี section หรือทุก section ไม่มี entry)
     *
     * ถ้าเคยดึงจาก RR แล้ว แม้ผู้ใช้จะลบหัวข้อทั้งหมด จะไม่ถือว่าเป็น "initial" — จะไม่ดึงอัตโนมัติจนกว่าจะ stale ตามเวลา
     * หรือผู้ใช้กดดึงเอง
     */
    public static function needsInitialPull(int $personnelId): bool
    {
        if (self::lastSuccessfulRrPullAt($personnelId) !== null) {
            return false;
        }

        $sectionModel = new CvSectionModel();
        if (! $sectionModel->db->tableExists('cv_sections')) {
            return false;
        }

        $sections = $sectionModel->where('personnel_id', $personnelId)->findAll();
        if ($sections === []) {
            return true;
        }

        if (! CvEntryModel::isTablePresent($sectionModel->db)) {
            return false;
        }

        $entryModel = new CvEntryModel();
        foreach ($sections as $sec) {
            $n = $entryModel->where('section_id', (int) $sec['id'])->countAllResults();
            if ($n > 0) {
                return false;
            }
        }

        return true;
    }

    public static function lastSuccessfulRrPullAt(int $personnelId): ?DateTimeImmutable
    {
        $log = new CvSyncLogModel();
        if (! $log->db->tableExists('cv_sync_log')) {
            return null;
        }

        $row = $log->where('personnel_id', $personnelId)
            ->where('direction', self::DIRECTION_PULL)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($row === null || empty($row['created_at'])) {
            return null;
        }

        try {
            return new DateTimeImmutable((string) $row['created_at']);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function isStale(?DateTimeInterface $last, int $maxAgeDays): bool
    {
        if ($last === null) {
            return true;
        }

        $days = max(1, $maxAgeDays);
        $cut   = (new DateTimeImmutable('now'))->modify('-' . $days . ' days');

        return $last < $cut;
    }

    /**
     * @return string|false trigger constant หรือ false ถ้าไม่ต้อง auto pull
     */
    public static function shouldAutoPull(int $personnelId, int $maxAgeDays): string|false
    {
        if (self::needsInitialPull($personnelId)) {
            return self::TRIGGER_AUTO_EMPTY;
        }

        $last = self::lastSuccessfulRrPullAt($personnelId);
        if (self::isStale($last, $maxAgeDays)) {
            return self::TRIGGER_AUTO_STALE;
        }

        return false;
    }

    /**
     * @return array{success:bool,message?:string,error?:string,publications_saved?:int,publications_stats?:array{inserted:int,updated:int,skipped_unchanged:int}}
     */
    public static function run(int $personnelId, string $canonicalEmail, string $trigger): array
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('cv_sections') || ! CvEntryModel::isTablePresent($db)) {
            return [
                'success' => false,
                'message' => 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_sections และ cv_entries)',
                'error'   => 'SCHEMA_MISSING',
            ];
        }

        $nsBundle = CvBundleCanonical::buildFromNewScience($personnelId, $canonicalEmail);
        $rr       = ResearchRecordCvSyncClient::fetchCvBundle($canonicalEmail);
        if (! $rr['success'] || empty($rr['bundle'])) {
            return [
                'success' => false,
                'message' => $rr['message'] ?? 'ดึงจาก RR ไม่สำเร็จ',
                'error'   => $rr['error'] ?? 'FETCH_FAILED',
            ];
        }

        try {
            ResearchRecordCvSyncMerge::replaceNewScienceCvFromBundle($personnelId, $rr['bundle']);

            $pubStats = ['inserted' => 0, 'updated' => 0, 'skipped_unchanged' => 0];
            $pubRes   = ResearchRecordCvSyncClient::fetchPublicationsSyncBundle($canonicalEmail);
            if ($pubRes['success'] && ! empty($pubRes['publications']) && is_array($pubRes['publications'])) {
                $pubStats = ResearchRecordCvSyncMerge::applyPublicationsToCvEntries(
                    $personnelId,
                    $pubRes['publications'],
                    []
                );
            }

            $log = new CvSyncLogModel();
            if ($log->db->tableExists('cv_sync_log')) {
                $log->insert([
                    'personnel_id'    => $personnelId,
                    'direction'       => self::DIRECTION_PULL,
                    'ns_content_hash' => $nsBundle['content_hash'] ?? null,
                    'rr_content_hash' => $rr['bundle']['content_hash'] ?? null,
                    'decisions_json'  => json_encode([
                        'trigger'            => $trigger,
                        'publications_stats' => $pubStats,
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            $pubChanged = $pubStats['inserted'] + $pubStats['updated'];
            $msg        = 'แทนที่ CV บน newScience ด้วยข้อมูลจาก Research Record แล้ว';
            if ($pubChanged > 0) {
                $msg .= sprintf(
                    ' (ผลงาน: เพิ่ม %d, อัปเดต %d',
                    $pubStats['inserted'],
                    $pubStats['updated']
                );
                if ($pubStats['skipped_unchanged'] > 0) {
                    $msg .= sprintf(', ข้ามที่ข้อมูลเท่าเดิม %d', $pubStats['skipped_unchanged']);
                }
                $msg .= ')';
            } elseif ($pubStats['skipped_unchanged'] > 0) {
                $msg .= sprintf(' (ผลงาน %d รายการตรงกับที่ดึงไว้แล้ว — ไม่บันทึกซ้ำ)', $pubStats['skipped_unchanged']);
            }

            return [
                'success'            => true,
                'message'            => $msg,
                'publications_saved' => $pubChanged,
                'publications_stats' => $pubStats,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'ResearchRecordCvPull::run ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error'   => 'EXCEPTION',
            ];
        }
    }
}
