<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;

/**
 * Canonical CV bundle v1 สำหรับ sync newScience ↔ Research Record
 */
class CvBundleCanonical
{
    public const VERSION = 1;

    /**
     * @param array<string,mixed> $meta
     */
    public static function entryExternalKeyFromMetadata(array $meta, string $title, string $organization, ?string $startDate): string
    {
        if (!empty($meta['orcid_put_code'])) {
            return 'p:' . (string) $meta['orcid_put_code'];
        }
        if (!empty($meta['sync_external_key'])) {
            return (string) $meta['sync_external_key'];
        }

        return 'h:' . self::hashSegment(implode('|', [
            self::norm($title),
            self::norm($organization),
            (string) ($startDate ?? ''),
        ]));
    }

    /**
     * @param array<string,mixed> $entry
     */
    public static function entryExternalKey(array $entry): string
    {
        $meta = CvEntryModel::decodeMetadata($entry['metadata'] ?? null);

        return self::entryExternalKeyFromMetadata(
            $meta,
            (string) ($entry['title'] ?? ''),
            (string) ($entry['organization'] ?? ''),
            isset($entry['start_date']) ? (string) $entry['start_date'] : null
        );
    }

    /**
     * @param array<string,mixed> $section
     */
    public static function sectionExternalKey(array $section): string
    {
        return 's:' . self::hashSegment(implode('|', [
            self::norm((string) ($section['type'] ?? '')),
            self::norm((string) ($section['title'] ?? '')),
            (string) ((int) ($section['sort_order'] ?? 0)),
        ]));
    }

    public static function hashBundle(array $bundle): string
    {
        $copy = self::stripVolatileHashFields($bundle);

        $flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        $json = json_encode($copy, $flags);
        if ($json === false) {
            $json = json_encode(['error' => 'encode_failed'], JSON_UNESCAPED_UNICODE);
        }

        return hash('sha256', (string) $json);
    }

    /**
     * @return array<string,mixed>
     */
    public static function buildFromNewScience(int $personnelId, ?string $canonicalEmail): array
    {
        $cvSectionModel = new CvSectionModel();
        if (!$cvSectionModel->db->tableExists('cv_sections')) {
            return self::emptyBundle($canonicalEmail);
        }

        $personnelModel = new PersonnelModel();
        $person         = $personnelModel->find($personnelId);
        $orcidId        = null;
        if ($person !== null && !empty($person['orcid_id'])) {
            $orcidId = (string) $person['orcid_id'];
        }

        $sections = $cvSectionModel->where('personnel_id', $personnelId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $cvEntryModel = new CvEntryModel();
        $outSections  = [];

        foreach ($sections as $section) {
            $sid = (int) $section['id'];
            $entries = [];
            if (CvEntryModel::isTablePresent($cvSectionModel->db)) {
                $entries = $cvEntryModel->where('section_id', $sid)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->findAll();
            }

            $secKey = self::sectionExternalKey($section);
            $entryRows = [];
            foreach ($entries as $e) {
                $meta = CvEntryModel::decodeMetadata($e['metadata'] ?? null);
                $ek   = self::entryExternalKey($e);
                $entryRows[] = [
                    'external_key'       => $ek,
                    'title'              => (string) ($e['title'] ?? ''),
                    'organization'       => $e['organization'] ?? null,
                    'location'           => $e['location'] ?? null,
                    'start_date'         => $e['start_date'] ?? null,
                    'end_date'           => $e['end_date'] ?? null,
                    'is_current'         => (int) ($e['is_current'] ?? 0),
                    'description'        => $e['description'] ?? null,
                    'visible_on_public'  => (int) ($e['visible_on_public'] ?? 1),
                    'metadata'           => $meta,
                    'sort_order'         => (int) ($e['sort_order'] ?? 0),
                    'updated_at'         => $e['updated_at'] ?? null,
                ];
            }

            $outSections[] = [
                'external_key'      => $secKey,
                'type'              => (string) ($section['type'] ?? 'custom'),
                'title'             => (string) ($section['title'] ?? ''),
                'description'       => $section['description'] ?? null,
                'sort_order'        => (int) ($section['sort_order'] ?? 0),
                'visible_on_public' => (int) ($section['visible_on_public'] ?? 1),
                'entries'           => $entryRows,
                'updated_at'        => $section['updated_at'] ?? null,
            ];
        }

        $bundle = [
            'version'   => self::VERSION,
            'email'     => $canonicalEmail ?? '',
            'orcid_id'  => $orcidId,
            'sections'  => $outSections,
            'source'    => 'newscience',
        ];

        $bundle['content_hash'] = self::hashBundle($bundle);

        return $bundle;
    }

    public static function isEducationSectionType(string $type): bool
    {
        return in_array(strtolower(trim($type)), ['education', 'education_structured'], true);
    }

    /**
     * @param list<array<string,mixed>> $sections
     *
     * @return list<array<string,mixed>>
     */
    public static function filterEducationSections(array $sections): array
    {
        $out = [];
        foreach ($sections as $sec) {
            if (is_array($sec) && self::isEducationSectionType((string) ($sec['type'] ?? ''))) {
                $out[] = $sec;
            }
        }

        return $out;
    }

    /**
     * Build bundle for RR push: replace only education sections from NS; keep other RR CV sections.
     *
     * @return array{success:bool,bundle?:array<string,mixed>,education_section_count?:int,skipped?:bool,message?:string,error?:string}
     */
    public static function buildBundleForEducationPushPreservingRrCv(int $personnelId, string $canonicalEmail): array
    {
        $email = CvProfile::normalizeEmail($canonicalEmail);
        if ($email === '') {
            return ['success' => false, 'error' => 'EMAIL_REQUIRED', 'message' => 'missing email'];
        }

        ResearchRecordCvSyncMerge::ensureEducationSectionForPerson($personnelId);

        $nsBundle    = self::buildFromNewScience($personnelId, $email);
        $nsEducation = self::filterEducationSections(is_array($nsBundle['sections'] ?? null) ? $nsBundle['sections'] : []);

        if ($nsEducation === []) {
            $nsEducation = [[
                'external_key'      => self::sectionExternalKey([
                    'type'       => 'education',
                    'title'      => ResearchRecordCvSyncMerge::canonicalEducationSectionTitle(),
                    'sort_order' => ResearchRecordCvSyncMerge::defaultEducationSortOrderForBundle(),
                ]),
                'type'              => 'education',
                'title'             => ResearchRecordCvSyncMerge::canonicalEducationSectionTitle(),
                'description'       => null,
                'sort_order'        => ResearchRecordCvSyncMerge::defaultEducationSortOrderForBundle(),
                'visible_on_public' => 1,
                'entries'           => [],
            ]];
        }

        $educationEntryCount = 0;
        foreach ($nsEducation as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $educationEntryCount += count(is_array($sec['entries'] ?? null) ? $sec['entries'] : []);
        }

        $rr = ResearchRecordCvSyncClient::fetchCvBundle($email);
        if (! ($rr['success'] ?? false) || ! is_array($rr['bundle'] ?? null)) {
            return [
                'success' => false,
                'error'   => $rr['error'] ?? 'FETCH_FAILED',
                'message' => $rr['message'] ?? 'ดึง CV จาก กบศ ไม่สำเร็จ — จึงไม่ส่งประวัติการศึกษา',
            ];
        }

        $rrBundle = self::ensureBundleSectionEntryKeys($rr['bundle']);
        $kept     = [];
        foreach (is_array($rrBundle['sections'] ?? null) ? $rrBundle['sections'] : [] as $sec) {
            if (is_array($sec) && ! self::isEducationSectionType((string) ($sec['type'] ?? ''))) {
                $kept[] = $sec;
            }
        }

        $merged             = $rrBundle;
        $merged['sections'] = array_merge($kept, $nsEducation);
        $merged['email']    = $email;
        $merged['source']   = 'newscience_education_push';
        if (! empty($nsBundle['orcid_id']) && empty($merged['orcid_id'])) {
            $merged['orcid_id'] = $nsBundle['orcid_id'];
        }
        $merged['content_hash'] = self::hashBundle($merged);

        return [
            'success'                 => true,
            'bundle'                  => $merged,
            'education_section_count' => count($nsEducation),
            'education_entry_count'   => $educationEntryCount,
            'education_empty'         => $educationEntryCount === 0,
        ];
    }

    /**
     * เติม external_key ให้ section/entry ใน bundle จากกบศที่ไม่มีคีย์ — ให้ merge (NS + RR) ไม่ทิ้งรายการ
     *
     * @param array<string,mixed> $bundle
     *
     * @return array<string,mixed>
     */
    public static function ensureBundleSectionEntryKeys(array $bundle): array
    {
        $sections = $bundle['sections'] ?? [];
        if (! is_array($sections) || $sections === []) {
            return $bundle;
        }

        $outSections = [];
        $idx         = 0;
        foreach ($sections as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $idx++;
            $sortOrder = (int) ($sec['sort_order'] ?? $idx);
            $secOut    = $sec;
            if (trim((string) ($secOut['external_key'] ?? '')) === '') {
                $secOut['external_key'] = self::sectionExternalKey([
                    'type'       => (string) ($sec['type'] ?? 'custom'),
                    'title'      => (string) ($sec['title'] ?? ''),
                    'sort_order' => $sortOrder,
                ]);
            }

            $entryOut = [];
            $ej       = 0;
            foreach ($sec['entries'] ?? [] as $en) {
                if (! is_array($en)) {
                    continue;
                }
                $ej++;
                $enOut = $en;
                $meta  = $en['metadata'] ?? [];
                if (! is_array($meta)) {
                    $meta = [];
                }
                if (trim((string) ($enOut['external_key'] ?? '')) === '') {
                    $enOut['external_key'] = self::entryExternalKeyFromMetadata(
                        $meta,
                        (string) ($en['title'] ?? ''),
                        (string) ($en['organization'] ?? ''),
                        isset($en['start_date']) ? (string) $en['start_date'] : null
                    );
                }
                $entryOut[] = $enOut;
            }
            $secOut['entries'] = $entryOut;
            $outSections[]     = $secOut;
        }
        $bundle['sections'] = $outSections;
        $bundle['content_hash'] = self::hashBundle($bundle);

        return $bundle;
    }

    /**
     * @return array<string,mixed>
     */
    public static function emptyBundle(?string $email): array
    {
        $bundle = [
            'version'  => self::VERSION,
            'email'    => $email ?? '',
            'orcid_id' => null,
            'sections' => [],
            'source'   => 'newscience',
        ];
        $bundle['content_hash'] = self::hashBundle($bundle);

        return $bundle;
    }

    private static function norm(string $s): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    }

    /**
     * Timestamp fields help compare freshness but should not make content hashes change.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private static function stripVolatileHashFields($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            if (in_array((string) $key, ['created_at', 'updated_at', 'retrieved_at', 'source'], true)) {
                continue;
            }
            $out[$key] = self::stripVolatileHashFields($item);
        }

        return $out;
    }

    private static function hashSegment(string $s): string
    {
        return substr(hash('sha256', $s), 0, 40);
    }
}
