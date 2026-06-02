<?php

namespace App\Libraries;

use App\Models\CvSyncLogModel;

/**
 * ซิงค์ให้ NS กับ กบศ ใกล้เคียงกัน: ดึง CV แบบเสริม (NS หลัก) → ดึงผลงานจาก RR (pull-only) → ส่งประวัติการศึกษา
 *
 * ข้อจำกัด: การลบฝั่งเดียวอาจกลับมาหลัง pull; แก้/เพิ่มเนื้อหาผลงานทำที่ฟอร์ม กบศ ไม่ push จากฟอร์ม NS
 */
class CvResearchRecordReconcile
{
    public const TRIGGER_UI = 'reconcile_all_ui';

    /**
     * @return array{
     *   success:bool,
     *   message?:string,
     *   error?:string,
     *   pull?:array,
     *   publications?:array,
     *   education?:array,
     *   partial?:bool
     * }
     */
    public static function runFull(int $personnelId, string $canonicalEmail, string $trigger = self::TRIGGER_UI): array
    {
        $email = CvProfile::normalizeEmail($canonicalEmail);
        if ($email === '') {
            return ['success' => false, 'message' => 'ไม่มีอีเมลสำหรับซิงค์', 'error' => 'EMAIL_REQUIRED'];
        }

        $pull = ResearchRecordCvPull::run($personnelId, $email, $trigger);
        if (! ($pull['success'] ?? false)) {
            return [
                'success' => false,
                'message' => $pull['message'] ?? 'ดึงจาก กบศ ไม่สำเร็จ',
                'error'   => $pull['error'] ?? 'PULL_FAILED',
                'pull'    => $pull,
            ];
        }

        $pub = ResearchRecordCvPull::pullPublicationsOnly($personnelId, $email, $trigger);
        if (! ($pub['success'] ?? false)) {
            self::logReconcile($personnelId, $trigger, false, $pull, $pub, null);

            return [
                'success'      => false,
                'message'      => ($pull['message'] ?? 'ดึง CV แล้ว') . ' แต่ดึงผลงานไม่สำเร็จ: ' . ($pub['message'] ?? ''),
                'error'        => $pub['error'] ?? 'PUB_PULL_FAILED',
                'partial'      => true,
                'pull'         => $pull,
                'publications' => $pub,
            ];
        }

        $edu = self::pushEducationToResearchRecord($personnelId, $email, $trigger);
        if (! ($edu['success'] ?? false)) {
            self::logReconcile($personnelId, $trigger, false, $pull, $pub, $edu);

            return [
                'success'  => false,
                'message'  => 'ดึงและซิงค์ผลงานแล้ว แต่ส่งประวัติการศึกษาไป กบศ ไม่สำเร็จ: ' . ($edu['message'] ?? ''),
                'error'    => $edu['error'] ?? 'EDU_PUSH_FAILED',
                'partial'  => true,
                'pull'     => $pull,
                'publications' => $pub,
                'education' => $edu,
            ];
        }

        self::logReconcile($personnelId, $trigger, true, $pull, $pub, $edu);

        return [
            'success'      => true,
            'message'      => self::formatSuccessMessage($pull, $pub, $edu),
            'pull'         => $pull,
            'publications' => $pub,
            'education'    => $edu,
        ];
    }

    /**
     * @return array{success:bool,message?:string,error?:string,education_empty?:bool,skipped?:bool}
     */
    public static function pushEducationToResearchRecord(int $personnelId, string $canonicalEmail, string $trigger = 'education_push'): array
    {
        $email = CvProfile::normalizeEmail($canonicalEmail);
        if ($email === '') {
            return ['success' => false, 'message' => 'ไม่มีอีเมล', 'error' => 'EMAIL_REQUIRED'];
        }

        $eduBuild = CvBundleCanonical::buildBundleForEducationPushPreservingRrCv($personnelId, $email);
        if (! ($eduBuild['success'] ?? false)) {
            return [
                'success' => false,
                'message' => $eduBuild['message'] ?? 'เตรียมส่งประวัติการศึกษาไม่สำเร็จ',
                'error'   => 'EDU_BUILD_FAILED',
            ];
        }

        $bundle = $eduBuild['bundle'] ?? null;
        if (! is_array($bundle)) {
            return [
                'success'          => true,
                'message'          => 'ไม่มี bundle การศึกษาให้ส่ง',
                'skipped'          => true,
                'education_empty'  => ! empty($eduBuild['education_empty']),
            ];
        }

        $rr = ResearchRecordCvSyncClient::pushCvBundle($email, $bundle);
        if (! ($rr['success'] ?? false)) {
            return [
                'success' => false,
                'message' => $rr['message'] ?? 'ส่งประวัติการศึกษาไป กบศ ไม่สำเร็จ',
                'error'   => $rr['error'] ?? 'PUSH_FAILED',
            ];
        }

        return [
            'success'           => true,
            'message'           => 'ส่งประวัติการศึกษาแล้ว',
            'education_empty'   => ! empty($eduBuild['education_empty']),
            'education_sections' => $eduBuild['education_section_count'] ?? 0,
            'education_entries'  => $eduBuild['education_entry_count'] ?? 0,
        ];
    }

    /**
     * @param array<string,mixed> $pull
     * @param array<string,mixed> $pub
     * @param array<string,mixed>|null $edu
     */
    private static function formatSuccessMessage(array $pull, array $pub, ?array $edu): string
    {
        $parts = ['ซิงค์ให้ตรงกันแล้ว'];
        $parts[] = $pull['message'] ?? 'ดึง CV จาก กบศ แล้ว';

        if ($pub['success'] ?? false) {
            $stats = $pub['publications_stats'] ?? [];
            $parts[] = sprintf(
                'ผลงาน: ดึงจาก กบศ (เพิ่ม %d, อัปเดต %d)',
                (int) ($stats['inserted'] ?? 0),
                (int) ($stats['updated'] ?? 0)
            );
        }

        if (is_array($edu) && ($edu['success'] ?? false) && empty($edu['skipped'])) {
            $parts[] = ! empty($edu['education_empty'])
                ? 'ประวัติการศึกษา (หัวข้อว่างบน กบศ)'
                : 'ประวัติการศึกษาส่งแล้ว';
        }

        return implode(' · ', $parts);
    }

    /**
     * @param array<string,mixed> $pull
     * @param array<string,mixed> $pub
     * @param array<string,mixed>|null $edu
     */
    private static function logReconcile(int $personnelId, string $trigger, bool $ok, array $pull, array $pub, ?array $edu): void
    {
        $log = new CvSyncLogModel();
        if (! $log->db->tableExists('cv_sync_log')) {
            return;
        }

        $log->insert([
            'personnel_id'    => $personnelId,
            'direction'       => 'reconcile_all_ns_rr',
            'ns_content_hash' => null,
            'rr_content_hash' => null,
            'decisions_json'  => json_encode([
                'trigger'      => $trigger,
                'ok'           => $ok,
                'pull'         => [
                    'publications_stats' => $pull['publications_stats'] ?? null,
                ],
                'publications' => [
                    'publications_stats' => $pub['publications_stats'] ?? null,
                    'trigger'            => $pub['trigger'] ?? null,
                ],
                'education'    => $edu,
            ], JSON_UNESCAPED_UNICODE),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
