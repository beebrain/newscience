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

        $cvEntryModel = new CvEntryModel();
        $eduCount     = self::upsertEntries($cvEntryModel, $educationSection, $education);
        $empCount     = self::upsertEntries($cvEntryModel, $employmentSection, $employment);

        // ผลงานตีพิมพ์จาก ORCID → บันทึกที่ Research Record (แหล่งความจริง) แล้วดึงกลับมาผูกใน CV
        $worksResult  = self::pushOrcidWorksToResearchRecord($personnelId, $works);
        $worksCount   = $worksResult['count'];
        $worksWarning = $worksResult['message'] ?? null;

        ResearchRecordCvSyncMerge::finalizeCvSectionsForPerson($personnelId);

        $hasAnyData = $education !== [] || $employment !== [] || $works !== [];
        $msg        = $hasAnyData
            ? 'นำเข้าจาก ORCID เรียบร้อยแล้ว'
            : 'ไม่พบรายการที่เปิดเผยใน ORCID สำหรับประเภทที่เลือก';
        if ($worksWarning !== null) {
            $msg .= ' — ' . $worksWarning;
        }

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
     * ผลักผลงานตีพิมพ์จาก ORCID ไปบันทึกใน Research Record (dedup-safe) แล้วดึงกลับมาผูกใน CV
     *
     * @param list<array<string,mixed>> $works
     *
     * @return array{count:int,message?:string}
     */
    private static function pushOrcidWorksToResearchRecord(int $personnelId, array $works): array
    {
        if ($works === []) {
            return ['count' => 0];
        }

        $person = (new PersonnelModel())->find($personnelId);
        $email  = $person !== null ? ResearchRecordCvPull::canonicalEmailForPerson($person) : '';
        if ($email === '') {
            return ['count' => 0, 'message' => 'ไม่พบอีเมลสำหรับบันทึกผลงานในระบบวิจัย จึงข้ามส่วนผลงานตีพิมพ์'];
        }
        $name = PersonnelModel::resolvePublicDisplayNameTh($person);

        $payload = [];
        foreach ($works as $w) {
            $meta    = is_array($w['orcid_meta'] ?? null) ? $w['orcid_meta'] : [];
            $doi     = strtolower(trim(str_replace([' ', '\\'], '', (string) ($meta['doi'] ?? ''))));
            $putCode = trim((string) ($w['put_code'] ?? ''));
            $year    = ! empty($w['start_date']) ? (int) substr((string) $w['start_date'], 0, 4) : 0;

            $source = trim((string) ($w['organization'] ?? ''));
            if ($source === '') {
                $source = trim((string) ($w['description'] ?? ''));
            }
            if ($source === '') {
                $source = 'ไม่ระบุแหล่งตีพิมพ์';
            }

            if ($putCode !== '') {
                $key = 'orcid:' . $putCode;
            } elseif ($doi !== '') {
                $key = 'h:' . sha1($doi);
            } else {
                $key = 'orcidwork:' . sha1((string) ($w['title'] ?? '') . '|' . $year);
            }

            $payload[] = [
                'external_key'      => $key,
                'ns_publication_id' => null,
                'rr_publication_id' => null,
                'title'             => (string) ($w['title'] ?? ''),
                'publication_year'  => $year ?: null,
                'publication_type'  => self::orcidWorkTypeToRr((string) ($meta['work_type'] ?? '')),
                'source'            => mb_substr($source, 0, 500),
                'doi'               => $doi !== '' ? $doi : null,
                'sync_origin'       => 'orcid',
                'content_hash'      => null,
                'ref_url'           => $meta['url'] ?? null,
                'contributors'      => [[
                    'email'         => $email,
                    'name'          => $name !== '' ? $name : $email,
                    'order'         => 1,
                    'corresponding' => 1,
                    'affiliation'   => 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
                ]],
            ];
        }

        $push = ResearchRecordCvSyncClient::pushPublicationsSyncBundle($email, $payload);
        if (! ($push['success'] ?? false)) {
            return ['count' => 0, 'message' => 'บันทึกผลงานตีพิมพ์ไปยังระบบวิจัยไม่สำเร็จ: ' . (string) ($push['message'] ?? $push['error'] ?? '')];
        }

        // ดึงกลับมาผูกเป็นรายการผลงานใน CV (อิงผู้แต่ง)
        $bundle = ResearchRecordCvSyncClient::fetchPublicationsSyncBundle($email);
        if (($bundle['success'] ?? false) && ! empty($bundle['publications']) && is_array($bundle['publications'])) {
            ResearchRecordCvSyncMerge::applyPublicationsToCvEntries($personnelId, $bundle['publications'], []);
        }

        $stats = $push['stats'] ?? [];
        $count = (int) (($stats['inserted'] ?? 0) + ($stats['updated'] ?? 0));

        return ['count' => $count > 0 ? $count : count($payload)];
    }

    private static function orcidWorkTypeToRr(string $workType): string
    {
        $w = strtolower(trim($workType));
        if ($w === '') {
            return 'journal';
        }
        if (str_contains($w, 'conference') || str_contains($w, 'proceeding')) {
            return 'proceedings';
        }
        if (str_contains($w, 'book') || str_contains($w, 'chapter')) {
            return 'book';
        }
        if (str_contains($w, 'thesis') || str_contains($w, 'dissertation')) {
            return 'thesis';
        }
        if (str_contains($w, 'report')) {
            return 'report';
        }
        if (str_contains($w, 'journal') || str_contains($w, 'article') || str_contains($w, 'preprint')) {
            return 'journal';
        }

        return 'other';
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
