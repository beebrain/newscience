<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;

/**
 * นำเข้า CV จาก ORCID Public API ลง cv_sections / cv_entries
 */
class OrcidCvImport
{
    /**
     * @param list<string> $scopes education, employment, works
     *
     * @return array{success:bool,message:string,education_count?:int,employment_count?:int,works_count?:int,orcid_id?:string,scopes?:list<string>}
     */
    public static function import(int $personnelId, string $orcidRaw, array $scopes): array
    {
        if ($personnelId <= 0) {
            return ['success' => false, 'message' => 'ข้อมูลบุคลากรไม่สมบูรณ์'];
        }

        $cvSectionModel = new CvSectionModel();
        if (! $cvSectionModel->db->tableExists('cv_sections')) {
            return ['success' => false, 'message' => 'ระบบ CV ยังไม่พร้อม — รัน php spark migrate (ต้องมีตาราง cv_sections)'];
        }
        if (! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return ['success' => false, 'message' => 'ระบบ CV ยังไม่ครบ — รัน php spark migrate (ต้องมีตาราง cv_entries)'];
        }

        if (! OrcidPublicRecord::isValidId($orcidRaw)) {
            return ['success' => false, 'message' => 'รูปแบบ ORCID iD ไม่ถูกต้อง (เช่น 0000-0002-1825-0097)'];
        }

        $scopes = self::normalizeScopes($scopes);
        if ($scopes === []) {
            return ['success' => false, 'message' => 'เลือกอย่างน้อยหนึ่งประเภทการนำเข้า'];
        }

        $orcidId = OrcidPublicRecord::normalizeId($orcidRaw);

        $personnelModel = new PersonnelModel();
        if ($personnelModel->db->fieldExists('orcid_id', 'personnel')) {
            $personnelModel->update($personnelId, ['orcid_id' => $orcidId]);
        }

        $fetched = OrcidPublicRecord::fetchRecord($orcidId);
        if (empty($fetched['success']) || empty($fetched['data']) || ! is_array($fetched['data'])) {
            return [
                'success' => false,
                'message' => $fetched['message'] ?? 'ดึงข้อมูล ORCID ไม่สำเร็จ',
            ];
        }

        $record = $fetched['data'];

        $aff = ['education' => [], 'employment' => []];
        if (in_array('education', $scopes, true) || in_array('employment', $scopes, true)) {
            $aff = OrcidPublicRecord::extractEducationAndEmployment($record);
        }
        $education  = in_array('education', $scopes, true) ? ($aff['education'] ?? []) : [];
        $employment = in_array('employment', $scopes, true) ? ($aff['employment'] ?? []) : [];
        $works      = in_array('works', $scopes, true) ? OrcidPublicRecord::extractWorks($record) : [];

        $educationSection  = self::ensureCvSection($cvSectionModel, $personnelId, 'education', ResearchRecordCvSyncMerge::canonicalEducationSectionTitle(), $education !== []);
        $employmentSection = self::ensureCvSection($cvSectionModel, $personnelId, 'work', 'ประสบการณ์การทำงาน', $employment !== []);
        $worksSection      = self::ensureCvSectionForWorks($cvSectionModel, $personnelId, $works !== []);

        $cvEntryModel = new CvEntryModel();
        $eduCount     = self::upsertEntries($cvEntryModel, $educationSection, $education);
        $empCount     = self::upsertEntries($cvEntryModel, $employmentSection, $employment);
        $worksCount   = self::upsertEntries($cvEntryModel, $worksSection, $works);

        ResearchRecordCvSyncMerge::finalizeCvSectionsForPerson($personnelId);

        $hasAnyData = $education !== [] || $employment !== [] || $works !== [];
        $msg        = $hasAnyData
            ? 'นำเข้าจาก ORCID เรียบร้อยแล้ว'
            : 'ไม่พบรายการที่เปิดเผยใน ORCID สำหรับประเภทที่เลือก';

        return [
            'success'          => true,
            'message'          => $msg,
            'education_count'  => $eduCount,
            'employment_count' => $empCount,
            'works_count'      => $worksCount,
            'orcid_id'         => $orcidId,
            'scopes'           => $scopes,
        ];
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<string>
     */
    public static function normalizeScopes(array $scopes): array
    {
        $allowed = ['education', 'employment', 'works'];
        if ($scopes === []) {
            return $allowed;
        }

        $parts = array_map('strtolower', array_map('trim', $scopes));

        return array_values(array_intersect($allowed, $parts));
    }

    /**
     * @param list<array<string,mixed>> $items
     */
    private static function upsertEntries(CvEntryModel $cvEntryModel, ?array $section, array $items): int
    {
        if ($section === null || $items === []) {
            return 0;
        }

        $sectionId = (int) ($section['id'] ?? 0);
        if ($sectionId <= 0) {
            return 0;
        }

        $count = 0;
        foreach ($items as $item) {
            $putCode  = $item['put_code'] ?? null;
            $existing = null;
            if ($putCode !== null && $putCode !== '') {
                $existing = self::findEntryByOrcidPutCode($cvEntryModel, $sectionId, (string) $putCode);
            }
            if ($existing === null) {
                $extraMeta = isset($item['orcid_meta']) && is_array($item['orcid_meta']) ? $item['orcid_meta'] : [];
                $dedupe    = isset($extraMeta['orcid_dedupe_key']) ? (string) $extraMeta['orcid_dedupe_key'] : '';
                if ($dedupe !== '') {
                    $existing = self::findEntryByOrcidDedupeKey($cvEntryModel, $sectionId, $dedupe);
                }
            }

            $start = $item['start_date'] ?? null;
            $end   = $item['end_date'] ?? null;
            $isCurrent = array_key_exists('is_current', $item)
                ? ((int) (bool) $item['is_current'])
                : (($end === null || $end === '') ? 1 : 0);

            $prev = $existing !== null ? CvEntryModel::decodeMetadata($existing['metadata'] ?? null) : [];
            $base = [
                'orcid_put_code' => $putCode,
                'source'         => 'orcid',
                'synced_at'      => date('Y-m-d H:i:s'),
            ];
            $extra = isset($item['orcid_meta']) && is_array($item['orcid_meta']) ? $item['orcid_meta'] : [];
            $meta  = array_merge($prev, $base, $extra);
            $meta  = array_filter($meta, static fn ($v) => $v !== null && $v !== '');

            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                $title = 'รายการจาก ORCID';
            }

            $desc = $item['description'] ?? $item['department'] ?? '';

            $entryData = [
                'section_id'        => $sectionId,
                'title'             => mb_substr($title, 0, 500),
                'organization'      => self::nullableString((string) ($item['organization'] ?? '')),
                'location'          => self::nullableString((string) ($item['location'] ?? '')),
                'start_date'        => $start ?: null,
                'end_date'          => $end ?: null,
                'is_current'        => $isCurrent,
                'description'       => self::nullableString((string) $desc),
                'metadata'          => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'visible_on_public' => 1,
            ];

            if ($existing !== null) {
                $entryData['sort_order'] = (int) ($existing['sort_order'] ?? $cvEntryModel->nextSortOrder($sectionId));
                $cvEntryModel->update((int) $existing['id'], $entryData);
            } else {
                $entryData['sort_order'] = $cvEntryModel->nextSortOrder($sectionId);
                $cvEntryModel->insert($entryData);
            }
            $count++;
        }

        return $count;
    }

    private static function nullableString(string $s): ?string
    {
        $s = trim($s);

        return $s === '' ? null : $s;
    }

    private static function findEntryByOrcidPutCode(CvEntryModel $cvEntryModel, int $sectionId, string $putCode): ?array
    {
        $rows = $cvEntryModel->where('section_id', $sectionId)->findAll();
        foreach ($rows as $row) {
            $meta = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
            if (isset($meta['orcid_put_code']) && (string) $meta['orcid_put_code'] === (string) $putCode) {
                return $row;
            }
        }

        return null;
    }

    private static function findEntryByOrcidDedupeKey(CvEntryModel $cvEntryModel, int $sectionId, string $dedupeKey): ?array
    {
        $rows = $cvEntryModel->where('section_id', $sectionId)->findAll();
        foreach ($rows as $row) {
            $meta = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
            if (isset($meta['orcid_dedupe_key']) && (string) $meta['orcid_dedupe_key'] === $dedupeKey) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return array<string,mixed>|null
     */
    private static function ensureCvSectionForWorks(CvSectionModel $cvSectionModel, int $personnelId, bool $needed): ?array
    {
        if (! $needed) {
            return null;
        }

        $section = $cvSectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();

        if ($section !== null) {
            return $section;
        }

        $cvSectionModel->insert([
            'personnel_id'      => $personnelId,
            'type'              => 'research',
            'title'             => ResearchRecordCvSyncMerge::canonicalPublicationSectionTitle(),
            'description'       => null,
            'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
            'is_default'        => 0,
            'visible_on_public' => 1,
        ]);

        $newId = (int) $cvSectionModel->getInsertID();

        return $cvSectionModel->find($newId);
    }

    /**
     * @return array<string,mixed>|null
     */
    private static function ensureCvSection(CvSectionModel $cvSectionModel, int $personnelId, string $type, string $defaultTitle, bool $needed): ?array
    {
        if (! $needed) {
            return null;
        }

        if ($type === 'work') {
            $section = $cvSectionModel->where('personnel_id', $personnelId)
                ->groupStart()
                ->where('type', 'work')
                ->orWhere('type', 'experience')
                ->groupEnd()
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->first();
        } else {
            $section = $cvSectionModel->where('personnel_id', $personnelId)
                ->where('type', $type)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->first();
        }

        if ($section !== null) {
            return $section;
        }

        $cvSectionModel->insert([
            'personnel_id'      => $personnelId,
            'type'              => $type === 'work' ? 'work' : $type,
            'title'             => $defaultTitle,
            'description'       => null,
            'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
            'is_default'        => 0,
            'visible_on_public' => 1,
        ]);

        $newId = (int) $cvSectionModel->getInsertID();

        return $cvSectionModel->find($newId);
    }
}
