<?php

namespace App\Libraries;

/**
 * คีย์ผลงานให้สอดคล้องกับ ResearchRecord
 * {@see \App\Controllers\CvSyncApiController::getPublicationsSyncBundleByEmail}
 * — external_key เป็น pub:h:{sha256(doi)[0:40]} หรือ pub:id:{id}
 */
final class PublicationIdentity
{
    /**
     * Normalize DOI สำหรับเก็บและจับคู่: trim, lower, ตัด prefix URL ทั่วไป
     * (กบศมักเก็บ bare DOI; ผู้ใช้อาจวาง https://doi.org/...)
     */
    public static function normalizeDoi(?string $doi): string
    {
        $s = trim((string) $doi);
        if ($s === '') {
            return '';
        }
        $lower = strtolower($s);
        foreach (['https://doi.org/', 'http://doi.org/', 'https://dx.doi.org/', 'http://dx.doi.org/'] as $pfx) {
            if (str_starts_with($lower, $pfx)) {
                $s = trim(substr($s, strlen($pfx)));

                break;
            }
        }
        if (str_starts_with(strtolower($s), 'doi:')) {
            $s = trim(substr($s, 4));
        }

        return strtolower(trim($s));
    }

    /**
     * สูตรเดียวกับกบศเมื่อมี DOI (หลัง normalize แล้ว)
     */
    public static function publicationExternalKeyFromNormalizedDoi(string $normalizedDoi): ?string
    {
        if ($normalizedDoi === '') {
            return null;
        }

        return 'pub:h:' . substr(hash('sha256', $normalizedDoi), 0, 40);
    }

    public static function publicationExternalKeyFromDoi(?string $doi): ?string
    {
        $n = self::normalizeDoi($doi);

        return self::publicationExternalKeyFromNormalizedDoi($n);
    }

    public static function publicationExternalKeyFromRrId(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        return 'pub:id:' . $id;
    }

    /**
     * สร้าง external_key แบบเดียวกับ payload จากกบศ (ใช้ trim+lower ของค่า doi ดิบ — ไม่ strip URL)
     * สำหรับเทียบกับคีย์ใน JSON จาก API โดยตรง
     */
    public static function publicationExternalKeyFromRrApiShape(?string $rawDoi, int $rrPublicationId): string
    {
        $doi = trim((string) $rawDoi);
        if ($doi !== '') {
            return 'pub:h:' . substr(hash('sha256', strtolower($doi)), 0, 40);
        }
        if ($rrPublicationId > 0) {
            return 'pub:id:' . $rrPublicationId;
        }

        return '';
    }

    /**
     * @param array<string,mixed> $meta cv_entries.metadata decoded
     */
    public static function normalizedDoiFromMetadata(array $meta): string
    {
        return self::normalizeDoi((string) ($meta['doi'] ?? ''));
    }

    /**
     * @param array<string,mixed> $meta
     */
    public static function syncExternalKeyFromMetadata(array $meta): string
    {
        $ek = trim((string) ($meta['sync_external_key'] ?? ''));
        if ($ek !== '') {
            return $ek;
        }
        $doi = self::normalizedDoiFromMetadata($meta);
        $pk  = self::publicationExternalKeyFromNormalizedDoi($doi);
        if ($pk !== null) {
            return $pk;
        }
        $rid = (int) ($meta['rr_publication_id'] ?? 0);

        return $rid > 0 ? (string) self::publicationExternalKeyFromRrId($rid) : '';
    }
}
