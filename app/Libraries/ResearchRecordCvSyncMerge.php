<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;

/**
 * เปรียบเทียบ bundle และเขียนกลับ newScience
 *
 * แผนการดึงจาก RR (ลดการบันทึกซ้ำ):
 * - CV จาก `cv-bundle`: รวมแบบ **NS เป็นหลัก** (`mergedCvBundle` ว่าง) แล้วค่อยเขียนลง DB — เสริมรายการจาก RR
 *   ที่ยังไม่มีใน NS; รายการที่จับคู่ด้วย `external_key` เดียวกันเก็บฝั่ง NS
 * - ผลงานตีพิมพ์: จับคู่ด้วย `metadata.sync_external_key` / `metadata.rr_publication_id`; ถ้า fingerprint
 *   ของข้อมูลจาก RR (`rr_pull_fingerprint`) เท่ากับที่เก็บไว้แล้ว จะข้าม (ไม่อัปเดต DB)
 * - ประวัติ: `cv_sync_log` + content_hash จาก API
 */
class ResearchRecordCvSyncMerge
{
    /**
     * รวมหัวข้อผลงานที่ซ้ำจริง (type + title เดียวกัน) แล้วลบ cv_entries ที่เป็นผลงานเดียวกันซ้ำ
     * กันหน้า CV สาธารณะแสดงบล็อกซ้ำ โดยไม่ยุบหัวข้อ research/articles ที่ชื่อคนละหัวข้อ
     */
    public static function normalizePublicationSectionsForPerson(int $personnelId): void
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('cv_sections') || ! CvEntryModel::isTablePresent($db)) {
            return;
        }

        $cvSectionModel = new CvSectionModel();
        $sections      = $cvSectionModel->where('personnel_id', $personnelId)
            ->whereIn('type', ['research', 'articles'])
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if ($sections === []) {
            return;
        }

        $groups = [];
        foreach ($sections as $section) {
            $groups[self::publicationSectionGroupKey($section)][] = $section;
        }

        $db->transStart();
        try {
            foreach ($groups as $groupSections) {
                $primaryId = (int) ($groupSections[0]['id'] ?? 0);
                if ($primaryId <= 0) {
                    continue;
                }

                for ($i = 1, $n = count($groupSections); $i < $n; $i++) {
                    $dupId = (int) ($groupSections[$i]['id'] ?? 0);
                    if ($dupId <= 0) {
                        continue;
                    }
                    $db->table('cv_entries')->where('section_id', $dupId)->update(['section_id' => $primaryId]);
                    $cvSectionModel->delete($dupId, true);
                }

                self::dedupePublicationEntriesInSection($primaryId);
            }
            $db->transComplete();
            if ($db->transStatus() === false) {
                log_message('error', 'normalizePublicationSectionsForPerson: transaction failed for personnel ' . $personnelId);
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'normalizePublicationSectionsForPerson: ' . $e->getMessage());
        }
    }

    /**
     * ลบรายการซ้ำในหัวข้อผลงานเดียว — จับคู่ตาม rr_publication_id, sync_external_key, หรือชื่อ+วันที่+องค์กร
     */
    public static function dedupePublicationEntriesInSection(int $sectionId): void
    {
        if ($sectionId <= 0) {
            return;
        }

        $cvEntryModel = new CvEntryModel();
        $rows         = $cvEntryModel->where('section_id', $sectionId)->orderBy('id', 'ASC')->findAll();
        if ($rows === []) {
            return;
        }

        $seen = [];
        foreach ($rows as $row) {
            $id   = (int) ($row['id'] ?? 0);
            $meta = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
            $rid  = (int) ($meta['rr_publication_id'] ?? 0);
            $ek   = trim((string) ($meta['sync_external_key'] ?? ''));
            $doi  = strtolower(trim((string) ($meta['doi'] ?? '')));

            if ($rid > 0) {
                $key = 'rid:' . $rid;
            } elseif ($ek !== '') {
                $key = 'ek:' . $ek;
            } else {
                $t   = strtolower(trim((string) ($row['title'] ?? '')));
                $sd  = trim((string) ($row['start_date'] ?? ''));
                $org = strtolower(trim((string) ($row['organization'] ?? '')));
                $key = 'tp:' . hash('sha256', $t . "\x1e" . $sd . "\x1e" . $org . "\x1e" . $doi);
            }

            if (isset($seen[$key])) {
                $cvEntryModel->delete($id, true);
            } else {
                $seen[$key] = true;
            }
        }
    }

    /**
     * @param array<string,mixed> $nsBundle
     * @param array<string,mixed> $rrBundle
     *
     * @return list<array<string,mixed>>
     */
    public static function buildMergeRows(array $nsBundle, array $rrBundle): array
    {
        $nsMap = self::sectionsByKey($nsBundle['sections'] ?? []);
        $rrMap = self::sectionsByKey($rrBundle['sections'] ?? []);
        $keys  = array_values(array_unique(array_merge(array_keys($nsMap), array_keys($rrMap))));
        sort($keys);

        $rows = [];
        foreach ($keys as $sk) {
            $nsSec = $nsMap[$sk] ?? null;
            $rrSec = $rrMap[$sk] ?? null;
            $sectionStatus = self::compareSideStatus($nsSec, $rrSec, 'section');
            $rows[] = [
                'id'          => 'section|' . $sk,
                'kind'        => 'section',
                'section_key' => $sk,
                'title'       => ($nsSec['title'] ?? '') !== '' ? $nsSec['title'] : ($rrSec['title'] ?? ''),
                'summary_ns'  => self::summarizeSection($nsSec),
                'summary_rr'  => self::summarizeSection($rrSec),
                'has_ns'      => $nsSec !== null,
                'has_rr'      => $rrSec !== null,
            ] + $sectionStatus;

            $nsEnt = self::entriesByKey($nsSec['entries'] ?? []);
            $rrEnt = self::entriesByKey($rrSec['entries'] ?? []);
            $eKeys = array_values(array_unique(array_merge(array_keys($nsEnt), array_keys($rrEnt))));
            sort($eKeys);

            foreach ($eKeys as $ek) {
                $ne = $nsEnt[$ek] ?? null;
                $re = $rrEnt[$ek] ?? null;
                $entryStatus = self::compareSideStatus($ne, $re, 'entry');
                $rows[] = [
                    'id'          => 'entry|' . $sk . '|' . $ek,
                    'kind'        => 'entry',
                    'section_key' => $sk,
                    'entry_key'   => $ek,
                    'title'       => ($ne['title'] ?? '') !== '' ? $ne['title'] : ($re['title'] ?? ''),
                    'summary_ns'  => self::summarizeEntry($ne),
                    'summary_rr'  => self::summarizeEntry($re),
                    'has_ns'      => $ne !== null,
                    'has_rr'      => $re !== null,
                ] + $entryStatus;
            }
        }

        return $rows;
    }

    /**
     * @param array<string,string> $decisions id => rr|ns|skip
     * @param array<string,mixed>  $nsBundle
     * @param array<string,mixed>  $rrBundle
     *
     * @return array<string,mixed>
     */
    public static function mergedCvBundle(array $decisions, array $nsBundle, array $rrBundle, string $canonicalEmail): array
    {
        $nsMap = self::sectionsByKey($nsBundle['sections'] ?? []);
        $rrMap = self::sectionsByKey($rrBundle['sections'] ?? []);
        $keys  = array_values(array_unique(array_merge(array_keys($nsMap), array_keys($rrMap))));
        sort($keys);

        $outSections = [];
        $sort          = 0;

        foreach ($keys as $sk) {
            $sid    = 'section|' . $sk;
            $choice = $decisions[$sid] ?? self::defaultSectionChoice($nsMap[$sk] ?? null, $rrMap[$sk] ?? null);
            if ($choice === 'skip') {
                continue;
            }

            $nsSec = $nsMap[$sk] ?? null;
            $rrSec = $rrMap[$sk] ?? null;
            $base  = $choice === 'rr' ? $rrSec : $nsSec;
            if ($base === null) {
                $base = $rrSec ?? $nsSec;
            }
            if ($base === null) {
                continue;
            }

            $sort++;
            $nsEnt = self::entriesByKey($nsSec['entries'] ?? []);
            $rrEnt = self::entriesByKey($rrSec['entries'] ?? []);
            $eKeys = array_values(array_unique(array_merge(array_keys($nsEnt), array_keys($rrEnt))));
            sort($eKeys);

            $entriesOut = [];
            $eo         = 0;
            foreach ($eKeys as $ek) {
                $eid    = 'entry|' . $sk . '|' . $ek;
                $ec     = $decisions[$eid] ?? null;
                if ($ec === null) {
                    $ec = $choice === 'rr' ? 'rr' : 'ns';
                    if (!isset($rrEnt[$ek])) {
                        $ec = 'ns';
                    }
                    if (!isset($nsEnt[$ek])) {
                        $ec = 'rr';
                    }
                }
                if ($ec === 'skip') {
                    continue;
                }

                $pick = $ec === 'rr' ? ($rrEnt[$ek] ?? null) : ($nsEnt[$ek] ?? null);
                if ($pick === null) {
                    $pick = $rrEnt[$ek] ?? $nsEnt[$ek] ?? null;
                }
                if ($pick === null) {
                    continue;
                }
                $eo++;
                $meta = is_array($pick['metadata'] ?? null) ? $pick['metadata'] : [];
                $meta['sync_external_key'] = $ek;
                if ($ec === 'rr') {
                    $entryForHash = [
                        'title'             => self::titleWithFallback($pick, $nsEnt[$ek] ?? null),
                        'organization'      => $pick['organization'] ?? null,
                        'location'          => $pick['location'] ?? null,
                        'start_date'        => $pick['start_date'] ?? null,
                        'end_date'          => $pick['end_date'] ?? null,
                        'is_current'        => (int) ($pick['is_current'] ?? 0),
                        'description'       => $pick['description'] ?? null,
                        'visible_on_public' => (int) ($pick['visible_on_public'] ?? 1),
                        'metadata'          => $meta,
                    ];
                    $meta = self::stampResearchRecordSyncMetadata($meta, $pick, self::sideComparableHash($entryForHash, 'entry'));
                }

                $entriesOut[] = [
                    'external_key'      => $ek,
                    'title'             => self::titleWithFallback(
                        $pick,
                        $ec === 'rr' ? ($nsEnt[$ek] ?? null) : ($rrEnt[$ek] ?? null)
                    ),
                    'organization'      => $pick['organization'] ?? null,
                    'location'          => $pick['location'] ?? null,
                    'start_date'        => $pick['start_date'] ?? null,
                    'end_date'          => $pick['end_date'] ?? null,
                    'is_current'        => (int) ($pick['is_current'] ?? 0),
                    'description'       => $pick['description'] ?? null,
                    'visible_on_public' => (int) ($pick['visible_on_public'] ?? 1),
                    'metadata'          => $meta,
                    'sort_order'        => $eo,
                ];
            }

            $outSections[] = [
                'external_key'      => $sk,
                'type'              => (string) ($base['type'] ?? 'custom'),
                'title'             => self::titleWithFallback($base, $choice === 'rr' ? $nsSec : $rrSec),
                'description'       => $base['description'] ?? null,
                'sort_order'        => $sort,
                'visible_on_public' => (int) ($base['visible_on_public'] ?? 1),
                'entries'           => $entriesOut,
            ];
        }

        $orcidNs = $nsBundle['orcid_id'] ?? null;
        $orcidRr = $rrBundle['orcid_id'] ?? null;
        $orcid   = $decisions['orcid'] ?? null;
        if ($orcid === 'rr') {
            $orcidId = $orcidRr;
        } elseif ($orcid === 'ns') {
            $orcidId = $orcidNs;
        } else {
            $orcidId = $orcidNs ?? $orcidRr;
        }

        $bundle = [
            'version'  => CvBundleCanonical::VERSION,
            'email'    => CvProfile::normalizeEmail($canonicalEmail),
            'orcid_id' => $orcidId,
            'sections' => $outSections,
            'source'   => 'merged',
        ];
        $bundle = self::normalizeSectionsInBundle($bundle);
        $bundle['content_hash'] = CvBundleCanonical::hashBundle($bundle);

        return $bundle;
    }

    /**
     * จัดรูป bundle / DB หลังดึงจากหลายแหล่ง (กบศ, ORCID, กรอกเอง) — หัวข้อต้องเป็นหัวข้อ ไม่ใช่ชื่อผลงาน
     */
    public static function finalizeCvSectionsForPerson(int $personnelId): void
    {
        self::repairMisplacedPublicationSectionsForPerson($personnelId);
        self::dedupeCvSectionsByTypeForPerson($personnelId);
        self::normalizePublicationSectionsForPerson($personnelId);
        self::ensureEducationSectionForPerson($personnelId);
        self::ensurePublicationSectionForPerson($personnelId);
        self::applyCanonicalSectionTitlesForPerson($personnelId);
    }

    /**
     * สร้างหัวข้อการศึกษาให้ทุกคนที่ยังไม่มี (ว่างได้จนกว่าจะมีรายการจาก ORCID/กรอกเอง)
     */
    public static function ensureEducationSectionForPerson(int $personnelId): void
    {
        if ($personnelId <= 0) {
            return;
        }

        $cvSectionModel = new CvSectionModel();
        if (! $cvSectionModel->db->tableExists('cv_sections')) {
            return;
        }

        $section = $cvSectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'education')
            ->orWhere('type', 'education_structured')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();

        if ($section === null) {
            $cvSectionModel->insert([
                'personnel_id'      => $personnelId,
                'type'              => 'education',
                'title'             => self::canonicalEducationSectionTitle(),
                'description'       => null,
                'sort_order'        => self::defaultEducationSortOrder(),
                'is_default'        => 0,
                'visible_on_public' => 1,
            ]);
            $section = $cvSectionModel->find((int) $cvSectionModel->getInsertID());
        } elseif (self::shouldNormalizeSectionTitleToCanonical('education', (string) ($section['title'] ?? ''))) {
            $cvSectionModel->update((int) $section['id'], [
                'title' => mb_substr(self::canonicalEducationSectionTitle(), 0, 255),
                'type'  => 'education',
            ]);
        }

        self::migratePersonnelEducationSummaryToEntries($personnelId, $section);
    }

    /**
     * สร้างหัวข้อการศึกษาให้บุคลากรทุกคน (ใช้ใน migration / บำรุงรักษา)
     */
    public static function ensureEducationSectionForAllPersonnel(): int
    {
        $personnelModel = new PersonnelModel();
        if (! $personnelModel->db->tableExists('personnel')) {
            return 0;
        }

        $count = 0;
        foreach ($personnelModel->select('id')->findAll() as $row) {
            $pid = (int) ($row['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            self::ensureEducationSectionForPerson($pid);
            $count++;
        }

        return $count;
    }

    public static function canonicalEducationSectionTitle(): string
    {
        return 'การศึกษา';
    }

    /**
     * สร้างหัวข้อผลงานตีพิมพ์ให้ทุกคนที่ยังไม่มี (ว่างได้จนกว่าจะมีรายการจาก catalog/กรอกเอง)
     */
    public static function ensurePublicationSectionForPerson(int $personnelId): void
    {
        if ($personnelId <= 0) {
            return;
        }

        $cvSectionModel = new CvSectionModel();
        if (! $cvSectionModel->db->tableExists('cv_sections')) {
            return;
        }

        $section = $cvSectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();

        if ($section === null) {
            $cvSectionModel->insert([
                'personnel_id'      => $personnelId,
                'type'              => 'research',
                'title'             => self::canonicalPublicationSectionTitle(),
                'description'       => null,
                'sort_order'        => self::defaultPublicationSortOrder(),
                'is_default'        => 0,
                'visible_on_public' => 1,
            ]);
            $section = $cvSectionModel->find((int) $cvSectionModel->getInsertID());
        } elseif (self::shouldNormalizeSectionTitleToCanonical('research', (string) ($section['title'] ?? ''))) {
            $cvSectionModel->update((int) $section['id'], [
                'title' => mb_substr(self::canonicalPublicationSectionTitle(), 0, 255),
                'type'  => 'research',
            ]);
        }

        if ($section !== null) {
            self::normalizePublicationSectionsForPerson($personnelId);
        }
    }

    /**
     * สร้างหัวข้อผลงานตีพิมพ์ให้บุคลากรทุกคน (migration / บำรุงรักษา)
     */
    public static function ensurePublicationSectionForAllPersonnel(): int
    {
        $personnelModel = new PersonnelModel();
        if (! $personnelModel->db->tableExists('personnel')) {
            return 0;
        }

        $count = 0;
        foreach ($personnelModel->select('id')->findAll() as $row) {
            $pid = (int) ($row['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            self::ensurePublicationSectionForPerson($pid);
            $count++;
        }

        return $count;
    }

    /**
     * @param array<string,mixed> $section
     */
    public static function isProtectedEducationSection(array $section): bool
    {
        return self::sectionTypeGroupKey((string) ($section['type'] ?? '')) === 'education';
    }

    /**
     * @param array<string,mixed> $section
     */
    public static function isProtectedPublicationSection(array $section): bool
    {
        return self::sectionTypeGroupKey((string) ($section['type'] ?? '')) === 'research';
    }

    /**
     * หัวข้อที่รองรับฟอร์มผลงาน + ปุ่มช่วยเติมด้วย AI (type research/articles หรือ custom ที่ชื่อเป็นหัวข้อผลงาน)
     *
     * @param array<string,mixed> $section
     */
    public static function isPublicationCvSection(array $section): bool
    {
        $type = strtolower(trim((string) ($section['type'] ?? '')));
        if (in_array($type, ['research', 'articles'], true)) {
            return true;
        }

        if (self::isProtectedPublicationSection($section)) {
            return true;
        }

        return $type === 'custom' && self::isPublicationSectionHeading((string) ($section['title'] ?? ''));
    }

    /**
     * หัวข้อหลักที่ลบไม่ได้ (การศึกษา + ผลงานตีพิมพ์)
     *
     * @param array<string,mixed> $section
     */
    public static function isProtectedDefaultCvSection(array $section): bool
    {
        $group = self::sectionTypeGroupKey((string) ($section['type'] ?? ''));

        return $group === 'education' || $group === 'research';
    }

    /**
     * @param array<string,mixed> $section
     */
    public static function protectedSectionDeleteMessage(array $section): string
    {
        if (self::isProtectedPublicationSection($section)) {
            return 'ไม่สามารถลบหัวข้อผลงานตีพิมพ์ได้ — เพิ่มหรือแก้ไขรายการในหัวข้อนี้แทน';
        }

        return 'ไม่สามารถลบหัวข้อการศึกษาได้ — เพิ่มหรือแก้ไขรายการในหัวข้อนี้แทน';
    }

    public static function defaultEducationSortOrderForBundle(): int
    {
        return self::defaultEducationSortOrder();
    }

    private static function defaultEducationSortOrder(): int
    {
        $map = CvProfile::defaultSortOrderByKey();

        return (int) (($map[CvProfile::SECTION_EDUCATION] ?? 1) + 1);
    }

    private static function defaultPublicationSortOrder(): int
    {
        $map = CvProfile::defaultSortOrderByKey();

        return (int) (($map[CvProfile::SECTION_RESEARCH] ?? 2) + 1);
    }

    /**
     * ย้ายข้อความสรุป personnel.education (เก่า) เป็นรายการใต้หัวข้อการศึกษา แล้วล้างฟิลด์สรุป
     *
     * @param array<string,mixed>|null $section
     */
    private static function migratePersonnelEducationSummaryToEntries(int $personnelId, ?array $section): void
    {
        if ($section === null || ! CvEntryModel::isTablePresent((new CvSectionModel())->db)) {
            return;
        }

        $sectionId = (int) ($section['id'] ?? 0);
        if ($sectionId <= 0) {
            return;
        }

        $cvEntryModel = new CvEntryModel();
        if ($cvEntryModel->where('section_id', $sectionId)->countAllResults() > 0) {
            return;
        }

        $personnelModel = new PersonnelModel();
        if (! $personnelModel->db->fieldExists('education', 'personnel')) {
            return;
        }

        $person = $personnelModel->find($personnelId);
        if ($person === null) {
            return;
        }

        $text = trim((string) ($person['education'] ?? ''));
        if ($text === '') {
            return;
        }

        $lines = preg_split('/\R+/u', $text) ?: [];
        $sort  = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $sort++;
            $cvEntryModel->insert([
                'section_id'        => $sectionId,
                'title'             => mb_substr($line, 0, 500),
                'visible_on_public' => 1,
                'sort_order'        => $sort,
                'metadata'          => json_encode(['source' => 'personnel_education_summary'], JSON_UNESCAPED_UNICODE),
            ]);
        }

        $personnelModel->update($personnelId, ['education' => '']);
    }

    /**
     * @param array<string,mixed> $bundle
     *
     * @return array<string,mixed>
     */
    public static function normalizeSectionsInBundle(array $bundle): array
    {
        $bundle = self::normalizePublicationSectionsInBundle($bundle);
        $bundle = self::mergeSectionsByTypeGroupInBundle($bundle);
        $bundle = self::applyCanonicalTitlesInBundle($bundle);
        $bundle = self::ensureEducationSectionInBundle($bundle);

        return $bundle;
    }

    /**
     * @param array<string,mixed> $bundle
     *
     * @return array<string,mixed>
     */
    private static function ensureEducationSectionInBundle(array $bundle): array
    {
        $sections = $bundle['sections'] ?? [];
        if (! is_array($sections)) {
            $sections = [];
        }

        foreach ($sections as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            if (self::sectionTypeGroupKey((string) ($sec['type'] ?? '')) === 'education') {
                return $bundle;
            }
        }

        $sections[] = [
            'external_key'      => CvBundleCanonical::sectionExternalKey([
                'type'       => 'education',
                'title'      => self::canonicalEducationSectionTitle(),
                'sort_order' => self::defaultEducationSortOrder(),
            ]),
            'type'              => 'education',
            'title'             => self::canonicalEducationSectionTitle(),
            'description'       => null,
            'sort_order'        => self::defaultEducationSortOrder(),
            'visible_on_public' => 1,
            'entries'           => [],
        ];

        usort($sections, static fn ($a, $b) => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));
        $bundle['sections'] = $sections;

        return $bundle;
    }

    /**
     * ย้ายชื่อผลงานที่ กบศ ส่งมาเป็นหัวข้อ (ไม่มีรายการย่อย) ไปเป็นรายการใต้หัวข้อ "งานวิจัยที่ตีพิมพ์"
     *
     * @param array<string,mixed> $bundle
     *
     * @return array<string,mixed>
     */
    public static function normalizePublicationSectionsInBundle(array $bundle): array
    {
        $sections = $bundle['sections'] ?? [];
        if (! is_array($sections) || $sections === []) {
            return $bundle;
        }

        $promoted = [];
        $kept     = [];
        foreach ($sections as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            if (self::shouldPromoteSectionTitleToEntry($sec)) {
                $promoted[] = self::entryFromMisplacedSection($sec);

                continue;
            }
            $kept[] = $sec;
        }

        if ($promoted === []) {
            return $bundle;
        }

        $canonical   = self::canonicalPublicationSectionTitle();
        $researchIdx = null;
        foreach ($kept as $i => $sec) {
            $type = strtolower(trim((string) ($sec['type'] ?? '')));
            if (! in_array($type, ['research', 'articles'], true)) {
                continue;
            }
            if (self::isPublicationSectionHeading((string) ($sec['title'] ?? ''))) {
                $researchIdx = $i;

                break;
            }
        }

        if ($researchIdx === null) {
            $sort = 0;
            foreach ($kept as $sec) {
                $sort = max($sort, (int) ($sec['sort_order'] ?? 0));
            }
            $kept[] = [
                'external_key'      => CvBundleCanonical::sectionExternalKey([
                    'type'       => 'research',
                    'title'      => $canonical,
                    'sort_order' => $sort + 1,
                ]),
                'type'              => 'research',
                'title'             => $canonical,
                'description'       => null,
                'sort_order'        => $sort + 1,
                'visible_on_public' => 1,
                'entries'           => $promoted,
            ];
        } else {
            $existing = $kept[$researchIdx]['entries'] ?? [];
            if (! is_array($existing)) {
                $existing = [];
            }
            $kept[$researchIdx]['type']    = 'research';
            $kept[$researchIdx]['entries'] = array_merge($existing, $promoted);
            if (trim((string) ($kept[$researchIdx]['title'] ?? '')) === '') {
                $kept[$researchIdx]['title'] = $canonical;
            }
        }

        $bundle['sections'] = $kept;

        return $bundle;
    }

    /**
     * แก้ข้อมูลใน DB ที่ชื่อผลงานไปอยู่เป็นชื่อหัวข้อ (มักเกิดจาก bundle กบศ)
     */
    public static function repairMisplacedPublicationSectionsForPerson(int $personnelId): void
    {
        $cvSectionModel = new CvSectionModel();
        if (! $cvSectionModel->db->tableExists('cv_sections') || ! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return;
        }

        $cvEntryModel = new CvEntryModel();
        $sections     = $cvSectionModel->where('personnel_id', $personnelId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $misplaced = [];
        $primary   = null;
        foreach ($sections as $sec) {
            $sid = (int) ($sec['id'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            $entryCount = $cvEntryModel->where('section_id', $sid)->countAllResults();
            $shape      = [
                'type'    => $sec['type'] ?? '',
                'title'   => $sec['title'] ?? '',
                'entries' => $entryCount > 0 ? [1] : [],
            ];
            if (self::shouldPromoteSectionTitleToEntry($shape)) {
                $misplaced[] = $sec;

                continue;
            }
            if (
                $primary === null
                && in_array(strtolower(trim((string) ($sec['type'] ?? ''))), ['research', 'articles'], true)
                && self::isPublicationSectionHeading((string) ($sec['title'] ?? ''))
            ) {
                $primary = $sec;
            }
        }

        if ($misplaced === []) {
            return;
        }

        $canonical = self::canonicalPublicationSectionTitle();
        if ($primary === null && count($misplaced) === 1) {
            $only = $misplaced[0];
            $onlyId = (int) ($only['id'] ?? 0);
            if ($onlyId > 0) {
                $title = mb_substr(trim((string) ($only['title'] ?? '')), 0, 500);
                $cvSectionModel->update($onlyId, [
                    'type'  => 'research',
                    'title' => $canonical,
                ]);
                if ($title !== '') {
                    $cvEntryModel->insert([
                        'section_id'        => $onlyId,
                        'title'             => $title,
                        'organization'      => $only['description'] ?? null,
                        'visible_on_public' => (int) ($only['visible_on_public'] ?? 1),
                        'sort_order'        => 1,
                    ]);
                }
            }

            return;
        }

        if ($primary === null) {
            $cvSectionModel->insert([
                'personnel_id'      => $personnelId,
                'type'              => 'research',
                'title'             => $canonical,
                'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
                'is_default'        => 0,
                'visible_on_public' => 1,
            ]);
            $primary = $cvSectionModel->find((int) $cvSectionModel->getInsertID());
        }

        $primaryId = (int) ($primary['id'] ?? 0);
        if ($primaryId <= 0) {
            return;
        }

        foreach ($misplaced as $sec) {
            $sid = (int) ($sec['id'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            $entryTitle = mb_substr(trim((string) ($sec['title'] ?? '')), 0, 500);
            if ($entryTitle === '') {
                continue;
            }
            if ($sid === $primaryId) {
                $cvSectionModel->update($sid, ['type' => 'research', 'title' => $canonical]);
            }
            $cvEntryModel->insert([
                'section_id'        => $primaryId,
                'title'             => $entryTitle,
                'organization'      => $sec['description'] ?? null,
                'visible_on_public' => (int) ($sec['visible_on_public'] ?? 1),
                'sort_order'        => $cvEntryModel->nextSortOrder($primaryId),
            ]);
            if ($sid !== $primaryId) {
                $cvSectionModel->delete($sid, true);
            }
        }
    }

    /**
     * Fingerprint ของรายการผลงานจาก RR — ถ้าเท่ากับที่เก็บใน metadata แล้ว จะไม่อัปเดตแถว (ไม่บันทึกซ้ำ)
     */
    private static function publicationRrFingerprint(array $pub): string
    {
        $parts = [
            (string) ($pub['rr_publication_id'] ?? ''),
            strtolower(trim((string) ($pub['title'] ?? ''))),
            (string) ($pub['publication_year'] ?? ''),
            strtolower(trim((string) ($pub['doi'] ?? ''))),
            (string) ($pub['publication_type'] ?? ''),
            (string) ($pub['source'] ?? ''),
        ];

        return hash('sha256', implode("\x1e", $parts));
    }

    /**
     * @param list<array<string,mixed>> $publications
     * @param array<string,string>      $decisions pub key => rr|skip
     *
     * @return array{inserted:int, updated:int, skipped_unchanged:int}
     */
    public static function applyPublicationsToCvEntries(int $personnelId, array $publications, array $decisions): array
    {
        $stats = ['inserted' => 0, 'updated' => 0, 'skipped_unchanged' => 0];

        $cvSectionModel = new CvSectionModel();
        if (!$cvSectionModel->db->tableExists('cv_sections')) {
            return $stats;
        }
        if (! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return $stats;
        }

        $section = $cvSectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->first();

        if ($section === null) {
            $cvSectionModel->insert([
                'personnel_id'      => $personnelId,
                'type'              => 'research',
                'title'             => self::canonicalPublicationSectionTitle(),
                'description'       => null,
                'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
                'is_default'        => 0,
                'visible_on_public' => 1,
            ]);
            $section = $cvSectionModel->find((int) $cvSectionModel->getInsertID());
        }

        $sectionId    = (int) ($section['id'] ?? 0);
        $cvEntryModel = new CvEntryModel();

        $byRrId    = [];
        $byKey     = [];
        $byDoiNorm = [];
        $allSections = $cvSectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        foreach ($allSections as $secRow) {
            $sid = (int) ($secRow['id'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            foreach ($cvEntryModel->where('section_id', $sid)->findAll() as $row) {
                $m   = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
                $rid = (int) ($m['rr_publication_id'] ?? 0);
                if ($rid > 0 && ! isset($byRrId[$rid])) {
                    $byRrId[$rid] = $row;
                }
                $ek = (string) ($m['sync_external_key'] ?? '');
                if ($ek !== '' && ! isset($byKey[$ek])) {
                    $byKey[$ek] = $row;
                }
                $dn = PublicationIdentity::normalizedDoiFromMetadata($m);
                if ($dn !== '' && ! isset($byDoiNorm[$dn])) {
                    $byDoiNorm[$dn] = $row;
                }
            }
        }

        foreach ($publications as $pub) {
            if (!is_array($pub)) {
                continue;
            }
            $key = (string) ($pub['external_key'] ?? '');
            if ($key === '') {
                continue;
            }
            if (($decisions['pub|' . $key] ?? 'rr') === 'skip') {
                continue;
            }

            $rrPid = (int) ($pub['rr_publication_id'] ?? 0);
            $existing = null;
            if ($rrPid > 0 && isset($byRrId[$rrPid])) {
                $existing = $byRrId[$rrPid];
            } elseif (isset($byKey[$key])) {
                $existing = $byKey[$key];
            } else {
                $pubDoiNorm = PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? ''));
                if ($pubDoiNorm !== '' && isset($byDoiNorm[$pubDoiNorm])) {
                    $existing = $byDoiNorm[$pubDoiNorm];
                }
            }

            $fingerprint = self::publicationRrFingerprint($pub);
            if ($existing !== null) {
                $prevMeta = CvEntryModel::decodeMetadata($existing['metadata'] ?? null);
                if (($prevMeta['rr_pull_fingerprint'] ?? '') === $fingerprint) {
                    $stats['skipped_unchanged']++;

                    continue;
                }
            }

            $pubTypeRaw = trim((string) ($pub['publication_type'] ?? ''));
            $doiNorm    = PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? ''));
            $meta       = array_merge(
                PublicationResearchFields::encodeBibliographicMetadata($pub),
                [
                    'source'              => 'research_record',
                    'sync_external_key'   => $key,
                    'rr_publication_id'   => $pub['rr_publication_id'] ?? null,
                    'doi'                 => $doiNorm !== '' ? $doiNorm : null,
                    'rr_pull_fingerprint' => $fingerprint,
                    'rr_publication_type' => $pubTypeRaw !== '' ? $pubTypeRaw : null,
                ]
            );
            $title = mb_substr((string) ($pub['title'] ?? ''), 0, 500);
            $year  = $pub['publication_year'] ?? null;
            $desc  = trim((string) ($pub['source'] ?? '') !== '' ? 'แหล่ง: ' . trim((string) $pub['source']) : '');

            $rowData = [
                'section_id'        => $sectionId,
                'title'             => $title !== '' ? $title : 'ผลงาน',
                'organization'      => $pub['source'] ?? null,
                'location'          => null,
                'start_date'        => $year ? sprintf('%04d-01-01', (int) $year) : null,
                'end_date'          => null,
                'is_current'        => 0,
                'description'       => $desc !== '' ? $desc : null,
                'visible_on_public' => 1,
            ];
            $meta = self::stampResearchRecordSyncMetadata(
                $existing !== null
                    ? array_merge(CvEntryModel::decodeMetadata($existing['metadata'] ?? null), $meta)
                    : $meta,
                $pub,
                self::sideComparableHash($rowData + ['metadata' => $meta], 'entry')
            );
            $rowData['metadata'] = json_encode(array_filter($meta, static fn ($v) => $v !== null && $v !== ''), JSON_UNESCAPED_UNICODE);

            if ($existing !== null) {
                $rowData['sort_order'] = (int) ($existing['sort_order'] ?? 0);
                $eidUp = (int) $existing['id'];
                $cvEntryModel->update($eidUp, $rowData);
                $stats['updated']++;
                $updatedRow = $cvEntryModel->find($eidUp);
                if (is_array($updatedRow)) {
                    $ridU = (int) ($pub['rr_publication_id'] ?? 0);
                    if ($ridU > 0) {
                        $byRrId[$ridU] = $updatedRow;
                    }
                    $byKey[$key] = $updatedRow;
                    $ndU = PublicationIdentity::normalizedDoiFromMetadata(CvEntryModel::decodeMetadata($updatedRow['metadata'] ?? null));
                    if ($ndU !== '') {
                        $byDoiNorm[$ndU] = $updatedRow;
                    }
                }
            } else {
                $rowData['sort_order'] = $cvEntryModel->nextSortOrder($sectionId);
                $cvEntryModel->insert($rowData);
                $newId = (int) $cvEntryModel->getInsertID();
                $stats['inserted']++;
                $newRow = $cvEntryModel->find($newId);
                if (is_array($newRow)) {
                    $rid = (int) ($pub['rr_publication_id'] ?? 0);
                    if ($rid > 0) {
                        $byRrId[$rid] = $newRow;
                    }
                    $byKey[$key] = $newRow;
                    $nd = PublicationIdentity::normalizedDoiFromMetadata(CvEntryModel::decodeMetadata($newRow['metadata'] ?? null));
                    if ($nd !== '') {
                        $byDoiNorm[$nd] = $newRow;
                    }
                }
            }
        }

        self::finalizeCvSectionsForPerson($personnelId);

        return $stats;
    }

    /**
     * @param array<string,mixed> $bundle
     */
    public static function replaceNewScienceCvFromBundle(int $personnelId, array $bundle): void
    {
        $db           = \Config\Database::connect();
        if (! $db->tableExists('cv_sections') || ! CvEntryModel::isTablePresent($db)) {
            throw new \RuntimeException('CV schema incomplete — run php spark migrate (cv_sections and cv_entries required)');
        }

        $sectionModel = new CvSectionModel();
        $entryModel   = new CvEntryModel();

        $db->transStart();

        $existing = $sectionModel->where('personnel_id', $personnelId)->findAll();
        foreach ($existing as $ex) {
            $entryModel->where('section_id', (int) $ex['id'])->delete();
            $sectionModel->delete((int) $ex['id']);
        }

        $order = 0;
        foreach ($bundle['sections'] ?? [] as $sec) {
            if (!is_array($sec)) {
                continue;
            }
            $order++;
            $sectionModel->insert([
                'personnel_id'      => $personnelId,
                'type'              => (string) ($sec['type'] ?? 'custom'),
                'title'             => mb_substr((string) ($sec['title'] ?? ''), 0, 255),
                'description'       => $sec['description'] ?? null,
                'sort_order'        => (int) ($sec['sort_order'] ?? $order),
                'is_default'        => 0,
                'visible_on_public' => (int) ($sec['visible_on_public'] ?? 1),
            ]);
            $sid = (int) $sectionModel->getInsertID();
            $eo  = 0;
            foreach ($sec['entries'] ?? [] as $en) {
                if (!is_array($en)) {
                    continue;
                }
                $eo++;
                $meta = $en['metadata'] ?? [];
                if (!is_array($meta)) {
                    $meta = [];
                }
                if (!empty($en['external_key'])) {
                    $meta['sync_external_key'] = (string) $en['external_key'];
                }
                $entryModel->insert([
                    'section_id'        => $sid,
                    'title'             => mb_substr((string) ($en['title'] ?? ''), 0, 500),
                    'organization'      => isset($en['organization']) ? mb_substr((string) $en['organization'], 0, 500) : null,
                    'location'          => isset($en['location']) ? mb_substr((string) $en['location'], 0, 500) : null,
                    'start_date'        => $en['start_date'] ?? null,
                    'end_date'          => $en['end_date'] ?? null,
                    'is_current'        => (int) ($en['is_current'] ?? 0),
                    'description'       => $en['description'] ?? null,
                    'metadata'          => $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                    'sort_order'        => (int) ($en['sort_order'] ?? $eo),
                    'visible_on_public' => (int) ($en['visible_on_public'] ?? 1),
                ]);
            }
        }

        if (!empty($bundle['orcid_id'])) {
            $pm = new PersonnelModel();
            if ($pm->db->fieldExists('orcid_id', 'personnel')) {
                $pm->update($personnelId, ['orcid_id' => (string) $bundle['orcid_id']]);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new \RuntimeException('replaceNewScienceCvFromBundle transaction failed');
        }

        self::finalizeCvSectionsForPerson($personnelId);
    }

    /**
     * @param list<array<string,mixed>> $sections
     *
     * @return array<string,array<string,mixed>>
     */
    private static function sectionsByKey(array $sections): array
    {
        $m = [];
        foreach ($sections as $s) {
            if (!is_array($s)) {
                continue;
            }
            $k = (string) ($s['external_key'] ?? '');
            if ($k === '') {
                continue;
            }
            $m[$k] = $s;
        }

        return $m;
    }

    /**
     * @param list<array<string,mixed>> $entries
     *
     * @return array<string,array<string,mixed>>
     */
    private static function entriesByKey(array $entries): array
    {
        $m = [];
        foreach ($entries as $e) {
            if (!is_array($e)) {
                continue;
            }
            $k = (string) ($e['external_key'] ?? '');
            if ($k === '') {
                continue;
            }
            $m[$k] = $e;
        }

        return $m;
    }

    /**
     * Stamp only real user edits in NS. Sync/rebuild flows must not call this.
     *
     * @param array<string,mixed> $meta
     *
     * @return array<string,mixed>
     */
    public static function stampNewScienceContentMetadata(array $meta, ?string $now = null): array
    {
        $meta['ns_content_updated_at'] = self::normalizedSyncTime($now ?? date('Y-m-d H:i:s'));

        return $meta;
    }

    /**
     * @param array<string,mixed> $meta
     * @param array<string,mixed> $rrRow
     *
     * @return array<string,mixed>
     */
    private static function stampResearchRecordSyncMetadata(array $meta, array $rrRow, string $contentHash, ?string $now = null): array
    {
        $rrUpdated = self::rawUpdatedAt($rrRow);
        if ($rrUpdated !== '') {
            $meta['rr_content_updated_at'] = self::normalizedSyncTime($rrUpdated);
        }

        $meta['last_synced_at']   = self::normalizedSyncTime($now ?? date('Y-m-d H:i:s'));
        $meta['last_synced_hash'] = $contentHash;
        $meta['last_synced_from'] = 'research_record';

        return $meta;
    }

    private static function normalizedSyncTime(string $value): string
    {
        $ts = strtotime($value);

        return $ts === false ? $value : date('Y-m-d H:i:s', $ts);
    }

    /**
     * @param array<string,mixed>|null $ns
     * @param array<string,mixed>|null $rr
     *
     * @return array<string,mixed>
     */
    private static function compareSideStatus(?array $ns, ?array $rr, string $kind): array
    {
        $hasNs    = $ns !== null;
        $hasRr    = $rr !== null;
        $presence = $hasNs && $hasRr ? 'both' : ($hasNs ? 'ns_only' : ($hasRr ? 'rr_only' : 'missing'));

        if (! $hasNs || ! $hasRr) {
            $newerSide = $hasNs ? 'ns' : ($hasRr ? 'rr' : 'unknown');

            return [
                'presence'        => $presence,
                'content_status'  => 'unknown',
                'newer_side'      => $newerSide,
                'suggested'       => $hasRr ? 'rr' : ($hasNs ? 'ns' : 'skip'),
                'status_label'    => $hasNs ? 'มีเฉพาะ NS' : ($hasRr ? 'มีเฉพาะ กบศ' : 'ไม่พบข้อมูล'),
                'updated_at_ns'   => $hasNs ? self::sideUpdatedAt($ns, 'ns') : null,
                'updated_at_rr'   => $hasRr ? self::sideUpdatedAt($rr, 'rr') : null,
            ];
        }

        $nsHash    = self::sideComparableHash($ns, $kind);
        $rrHash    = self::sideComparableHash($rr, $kind);
        $same      = $nsHash === $rrHash;
        $newerSide = $same ? 'same' : self::newerSideBySyncMetadata($ns, $rr, $nsHash, $rrHash);

        return [
            'presence'        => $presence,
            'content_status'  => $same ? 'same' : 'differ',
            'newer_side'      => $newerSide,
            'suggested'       => in_array($newerSide, ['ns', 'rr'], true) ? $newerSide : ($same ? 'ns' : 'skip'),
            'status_label'    => self::statusLabel($same, $newerSide),
            'updated_at_ns'   => self::sideUpdatedAt($ns, 'ns'),
            'updated_at_rr'   => self::sideUpdatedAt($rr, 'rr'),
        ];
    }

    /**
     * @param array<string,mixed> $row
     */
    private static function sideComparableHash(array $row, string $kind): string
    {
        $data = $kind === 'section'
            ? [
                'type'              => $row['type'] ?? null,
                'title'             => $row['title'] ?? null,
                'description'       => $row['description'] ?? null,
                'visible_on_public' => (int) ($row['visible_on_public'] ?? 1),
            ]
            : [
                'title'             => $row['title'] ?? null,
                'organization'      => $row['organization'] ?? null,
                'location'          => $row['location'] ?? null,
                'start_date'        => $row['start_date'] ?? null,
                'end_date'          => $row['end_date'] ?? null,
                'is_current'        => (int) ($row['is_current'] ?? 0),
                'description'       => $row['description'] ?? null,
                'visible_on_public' => (int) ($row['visible_on_public'] ?? 1),
                'metadata'          => self::stripComparableVolatile($row['metadata'] ?? []),
            ];

        return hash('sha256', (string) json_encode(self::normalizeComparable($data), JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private static function normalizeComparable($value)
    {
        if (! is_array($value)) {
            return is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value) ?? '') : $value;
        }

        ksort($value);
        foreach ($value as $key => $item) {
            $value[$key] = self::normalizeComparable($item);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private static function stripComparableVolatile($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            if (in_array((string) $key, [
                'sync_external_key',
                'rr_publication_id',
                'rr_pull_fingerprint',
                'ns_content_updated_at',
                'rr_content_updated_at',
                'created_at',
                'updated_at',
                'retrieved_at',
                'last_synced_at',
                'last_synced_hash',
                'last_synced_from',
                'source',
            ], true)) {
                continue;
            }
            $out[$key] = self::stripComparableVolatile($item);
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $ns
     * @param array<string,mixed> $rr
     */
    private static function newerSideBySyncMetadata(array $ns, array $rr, string $nsHash, string $rrHash): string
    {
        $nsMeta     = self::syncMetadata($ns);
        $lastHash   = trim((string) ($nsMeta['last_synced_hash'] ?? ''));
        $lastSynced = self::timestampFromValue($nsMeta['last_synced_at'] ?? null);

        $nsChanged = null;
        if ($lastHash !== '' && hash_equals($lastHash, $nsHash)) {
            $nsChanged = false;
        } elseif ($lastSynced !== null) {
            $nsEditedAt = self::timestampFromValue($nsMeta['ns_content_updated_at'] ?? null);
            $nsChanged = $nsEditedAt !== null ? $nsEditedAt > $lastSynced : null;
        }

        $rrChanged = null;
        if ($lastHash !== '' && hash_equals($lastHash, $rrHash)) {
            $rrChanged = false;
        } elseif ($lastSynced !== null) {
            $rrEditedAt = self::timestampFromValue(self::contentUpdatedAt($rr, 'rr'));
            $rrChanged = $rrEditedAt !== null ? $rrEditedAt > $lastSynced : null;
        }

        if ($nsChanged === true && $rrChanged === true) {
            return 'conflict';
        }
        if ($nsChanged === true && $rrChanged !== true) {
            return 'ns';
        }
        if ($rrChanged === true && $nsChanged !== true) {
            return 'rr';
        }

        $nsTime = self::timestampFromValue(self::contentUpdatedAt($ns, 'ns'));
        $rrTime = self::timestampFromValue(self::contentUpdatedAt($rr, 'rr'));
        if ($lastSynced === null && $nsTime !== null && $rrTime !== null && $nsTime !== $rrTime) {
            return $nsTime > $rrTime ? 'ns' : 'rr';
        }

        return 'unknown';
    }

    /**
     * @param array<string,mixed> $row
     */
    private static function sideUpdatedAt(array $row, string $side): ?string
    {
        $raw = self::contentUpdatedAt($row, $side);
        if ($raw === '') {
            return null;
        }

        $ts = strtotime($raw);

        return $ts === false ? $raw : date('Y-m-d H:i:s', $ts);
    }

    private static function timestampFromValue($value): ?int
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        $ts = strtotime($raw);

        return $ts === false ? null : $ts;
    }

    /**
     * @param array<string,mixed> $row
     */
    private static function contentUpdatedAt(array $row, string $side): string
    {
        $meta = self::syncMetadata($row);
        if ($side === 'ns' && ! empty($meta['ns_content_updated_at'])) {
            return (string) $meta['ns_content_updated_at'];
        }
        if ($side === 'rr' && ! empty($meta['rr_content_updated_at'])) {
            return (string) $meta['rr_content_updated_at'];
        }

        return $side === 'rr' ? self::rawUpdatedAt($row) : '';
    }

    /**
     * @param array<string,mixed> $row
     *
     * @return array<string,mixed>
     */
    private static function syncMetadata(array $row): array
    {
        $meta = $row['metadata'] ?? [];

        return is_array($meta) ? $meta : [];
    }

    /**
     * @param array<string,mixed> $row
     */
    private static function rawUpdatedAt(array $row): string
    {
        foreach (['updated_at', 'updatedAt', 'modified_at', 'modifiedAt', 'last_modified_at', 'lastModifiedAt'] as $key) {
            if (! empty($row[$key])) {
                return (string) $row[$key];
            }
        }

        return '';
    }

    private static function statusLabel(bool $same, string $newerSide): string
    {
        if ($same) {
            return 'ข้อมูลตรงกัน';
        }
        if ($newerSide === 'ns') {
            return 'NS ใหม่กว่า';
        }
        if ($newerSide === 'rr') {
            return 'กบศ ใหม่กว่า';
        }
        if ($newerSide === 'conflict') {
            return 'แก้ทั้งสองฝั่ง';
        }

        return 'ข้อมูลต่างกัน (เลือกเอง)';
    }

    /**
     * @param array<string,mixed>|null $sec
     */
    private static function summarizeSection(?array $sec): string
    {
        if ($sec === null) {
            return '—';
        }
        $n = count($sec['entries'] ?? []);

        return trim(($sec['title'] ?? '') . ' [' . ($sec['type'] ?? '') . '] — ' . $n . ' รายการ');
    }

    /**
     * @param array<string,mixed>|null $e
     */
    private static function summarizeEntry(?array $e): string
    {
        if ($e === null) {
            return '—';
        }
        $meta = $e['metadata'] ?? [];
        $pt   = '';
        if (is_array($meta) && ! empty($meta['rr_publication_type'])) {
            $pt = RrPublicationType::labelTh((string) $meta['rr_publication_type']);
        }

        $base = trim(($e['title'] ?? '') . ' | ' . ($e['organization'] ?? '') . ' | ' . (string) ($e['start_date'] ?? ''));

        return $pt !== '' ? $base . ' | ประเภท: ' . $pt : $base;
    }

    /**
     * @param array<string,mixed>|null $primary
     * @param array<string,mixed>|null $fallback
     */
    public static function canonicalPublicationSectionTitle(): string
    {
        return CvProfile::sectionLabelsTh()[CvProfile::SECTION_RESEARCH] ?? 'งานวิจัยที่ตีพิมพ์';
    }

    public static function canonicalSectionTitleForType(string $type): ?string
    {
        $group = self::sectionTypeGroupKey($type);
        if ($group === null) {
            return null;
        }

        return match ($group) {
            'education' => self::canonicalEducationSectionTitle(),
            'work'      => 'ประสบการณ์การทำงาน',
            'research'  => self::canonicalPublicationSectionTitle(),
            default     => null,
        };
    }

    public static function shouldNormalizeSectionTitleToCanonical(string $type, string $title): bool
    {
        if (self::sectionTypeGroupKey($type) === null) {
            return false;
        }
        if (self::shouldPromoteSectionTitleToEntry(['type' => $type, 'title' => $title, 'entries' => []])) {
            return false;
        }

        $title = trim($title);
        if ($title === '') {
            return true;
        }

        $canonical = self::canonicalSectionTitleForType($type);
        if ($canonical === null) {
            return false;
        }

        $norm = strtolower(preg_replace('/\s+/', ' ', $title));
        if ($norm === strtolower($canonical)) {
            return true;
        }

        $aliases = self::sectionTitleAliasesForType($type);
        if (in_array($norm, $aliases, true)) {
            return true;
        }

        $group = self::sectionTypeGroupKey($type);

        return $group === 'research' && self::isPublicationSectionHeading($title);
    }

    /**
     * @param array<string,mixed> $bundle
     *
     * @return array<string,mixed>
     */
    private static function mergeSectionsByTypeGroupInBundle(array $bundle): array
    {
        $sections = $bundle['sections'] ?? [];
        if (! is_array($sections) || $sections === []) {
            return $bundle;
        }

        $groups = [];
        $kept   = [];
        foreach ($sections as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $gk = self::sectionTypeGroupKey((string) ($sec['type'] ?? ''));
            if ($gk === null) {
                $kept[] = $sec;

                continue;
            }
            $groups[$gk][] = $sec;
        }

        foreach ($groups as $group) {
            usort($group, static fn ($a, $b) => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));
            $primary = $group[0];
            $entries = is_array($primary['entries'] ?? null) ? $primary['entries'] : [];
            for ($i = 1, $n = count($group); $i < $n; $i++) {
                $extra = $group[$i]['entries'] ?? [];
                if (is_array($extra)) {
                    $entries = array_merge($entries, $extra);
                }
            }
            $primary['entries'] = self::dedupeBundleEntries($entries);
            if (self::sectionTypeGroupKey((string) ($primary['type'] ?? '')) === 'research') {
                $primary['type'] = 'research';
            }
            $kept[] = self::applyCanonicalTitleToSection($primary);
        }

        usort($kept, static fn ($a, $b) => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));
        $bundle['sections'] = $kept;

        return $bundle;
    }

    /**
     * @param array<string,mixed> $bundle
     *
     * @return array<string,mixed>
     */
    private static function applyCanonicalTitlesInBundle(array $bundle): array
    {
        $sections = $bundle['sections'] ?? [];
        if (! is_array($sections)) {
            return $bundle;
        }

        foreach ($sections as $i => $sec) {
            if (is_array($sec)) {
                $sections[$i] = self::applyCanonicalTitleToSection($sec);
            }
        }
        $bundle['sections'] = $sections;

        return $bundle;
    }

    /**
     * @param array<string,mixed> $sec
     *
     * @return array<string,mixed>
     */
    private static function applyCanonicalTitleToSection(array $sec): array
    {
        $type = (string) ($sec['type'] ?? '');
        if (self::shouldNormalizeSectionTitleToCanonical($type, (string) ($sec['title'] ?? ''))) {
            $canonical = self::canonicalSectionTitleForType($type);
            if ($canonical !== null) {
                $sec['title'] = $canonical;
            }
        }

        return $sec;
    }

    /**
     * @param list<array<string,mixed>> $entries
     *
     * @return list<array<string,mixed>>
     */
    private static function dedupeBundleEntries(array $entries): array
    {
        $seen = [];
        $out  = [];
        foreach ($entries as $e) {
            if (! is_array($e)) {
                continue;
            }
            $meta = is_array($e['metadata'] ?? null) ? $e['metadata'] : [];
            $k    = (string) ($e['external_key'] ?? '');
            if ($k === '') {
                $k = CvBundleCanonical::entryExternalKeyFromMetadata(
                    $meta,
                    (string) ($e['title'] ?? ''),
                    (string) ($e['organization'] ?? ''),
                    isset($e['start_date']) ? (string) $e['start_date'] : null
                );
            }
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[]    = $e;
        }

        return $out;
    }

    public static function dedupeCvSectionsByTypeForPerson(int $personnelId): void
    {
        $cvSectionModel = new CvSectionModel();
        if (! $cvSectionModel->db->tableExists('cv_sections') || ! CvEntryModel::isTablePresent($cvSectionModel->db)) {
            return;
        }

        $sections = $cvSectionModel->where('personnel_id', $personnelId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $groups = [];
        foreach ($sections as $sec) {
            $gk = self::sectionTypeGroupKey((string) ($sec['type'] ?? ''));
            if ($gk === null) {
                continue;
            }
            $groups[$gk][] = $sec;
        }

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            $cvEntryModel = new CvEntryModel();
            foreach ($groups as $groupSections) {
                if (count($groupSections) < 2) {
                    continue;
                }
                $primaryId = (int) ($groupSections[0]['id'] ?? 0);
                if ($primaryId <= 0) {
                    continue;
                }
                for ($i = 1, $n = count($groupSections); $i < $n; $i++) {
                    $dupId = (int) ($groupSections[$i]['id'] ?? 0);
                    if ($dupId <= 0) {
                        continue;
                    }
                    $db->table('cv_entries')->where('section_id', $dupId)->update(['section_id' => $primaryId]);
                    $cvSectionModel->delete($dupId, true);
                }
                if (self::sectionTypeGroupKey((string) ($groupSections[0]['type'] ?? '')) === 'research') {
                    $cvSectionModel->update($primaryId, ['type' => 'research']);
                }
                self::dedupePublicationEntriesInSection($primaryId);
            }
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'dedupeCvSectionsByTypeForPerson: ' . $e->getMessage());
        }
    }

    public static function applyCanonicalSectionTitlesForPerson(int $personnelId): void
    {
        $cvSectionModel = new CvSectionModel();
        if (! $cvSectionModel->db->tableExists('cv_sections')) {
            return;
        }

        $sections = $cvSectionModel->where('personnel_id', $personnelId)->findAll();
        foreach ($sections as $sec) {
            $sid = (int) ($sec['id'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            $type  = (string) ($sec['type'] ?? '');
            $title = (string) ($sec['title'] ?? '');
            if (! self::shouldNormalizeSectionTitleToCanonical($type, $title)) {
                continue;
            }
            $canonical = self::canonicalSectionTitleForType($type);
            if ($canonical === null) {
                continue;
            }
            $cvSectionModel->update($sid, ['title' => mb_substr($canonical, 0, 255)]);
        }
    }

    private static function sectionTypeGroupKey(string $type): ?string
    {
        $type = strtolower(trim($type));

        return match (true) {
            in_array($type, ['education', 'education_structured'], true) => 'education',
            in_array($type, ['work', 'experience'], true)             => 'work',
            in_array($type, ['research', 'articles'], true)             => 'research',
            default                                                     => null,
        };
    }

    /**
     * @return list<string>
     */
    private static function sectionTitleAliasesForType(string $type): array
    {
        $group = self::sectionTypeGroupKey($type);
        if ($group === null) {
            return [];
        }

        $aliases = match ($group) {
            'education' => [
                'education',
                'การศึกษา',
                'ประวัติการศึกษา',
                'ประวัติการศึกษา (รายการ)',
                'education (structured)',
            ],
            'work' => [
                'work',
                'work experience',
                'employment',
                'ประสบการณ์การทำงาน',
                'ประสบการณ์ทำงาน',
            ],
            'research' => [
                'research',
                'articles',
                'ผลงานตีพิมพ์',
                'ผลงานตีพิมพ์ (จาก กบศ)',
                'publications',
            ],
            default => [],
        };

        foreach ($aliases as $i => $label) {
            $aliases[$i] = strtolower(preg_replace('/\s+/', ' ', trim($label)));
        }

        return array_values(array_unique($aliases));
    }

    /**
     * @param array<string,mixed> $section
     */
    public static function shouldPromoteSectionTitleToEntry(array $section): bool
    {
        $entries = $section['entries'] ?? [];
        if (is_array($entries) && $entries !== []) {
            return false;
        }
        $title = trim((string) ($section['title'] ?? ''));
        if ($title === '' || self::isPublicationSectionHeading($title)) {
            return false;
        }
        $type = strtolower(trim((string) ($section['type'] ?? '')));
        if (in_array($type, ['research', 'articles'], true)) {
            return true;
        }
        if ($type === '' || $type === 'custom') {
            return mb_strlen($title) >= 50;
        }

        return false;
    }

    public static function isPublicationSectionHeading(string $title): bool
    {
        $norm = strtolower(trim(preg_replace('/\s+/', ' ', $title)));
        if ($norm === '') {
            return false;
        }
        $known = [
            strtolower(self::canonicalPublicationSectionTitle()),
            'งานวิจัย',
            'งานวิจัยที่ตีพิมพ์',
            'บทความวิชาการ',
            'ผลงานตีพิมพ์',
            'ผลงานตีพิมพ์ (จาก กบศ)',
        ];
        foreach (CvProfile::sectionLabelsTh() as $label) {
            $known[] = strtolower(trim($label));
        }

        return in_array($norm, array_unique($known), true);
    }

    /**
     * @param array<string,mixed> $section
     *
     * @return array<string,mixed>
     */
    private static function entryFromMisplacedSection(array $section): array
    {
        $title = mb_substr(trim((string) ($section['title'] ?? '')), 0, 500);
        $meta  = $section['metadata'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        return [
            'external_key'      => (string) ($section['external_key'] ?? CvBundleCanonical::entryExternalKeyFromMetadata(
                $meta,
                $title,
                (string) ($section['organization'] ?? ''),
                isset($section['start_date']) ? (string) $section['start_date'] : null
            )),
            'title'             => $title,
            'organization'      => $section['organization'] ?? null,
            'location'          => $section['location'] ?? null,
            'start_date'        => $section['start_date'] ?? null,
            'end_date'          => $section['end_date'] ?? null,
            'is_current'        => (int) ($section['is_current'] ?? 0),
            'description'       => $section['description'] ?? null,
            'visible_on_public' => (int) ($section['visible_on_public'] ?? 1),
            'metadata'          => $meta,
            'sort_order'        => (int) ($section['sort_order'] ?? 0),
        ];
    }

    private static function titleWithFallback(?array $primary, ?array $fallback): string
    {
        $title = (string) ($primary['title'] ?? '');
        if (trim($title) !== '') {
            return $title;
        }

        return (string) ($fallback['title'] ?? '');
    }

    /**
     * @param array<string,mixed> $section
     */
    private static function publicationSectionGroupKey(array $section): string
    {
        $type  = strtolower(trim((string) ($section['type'] ?? '')));
        $title = strtolower(trim((string) preg_replace('/\s+/', ' ', (string) ($section['title'] ?? ''))));

        return $type . '|' . $title;
    }

    /**
     * @param array<string,mixed>|null $ns
     * @param array<string,mixed>|null $rr
     */
    private static function defaultSectionChoice(?array $ns, ?array $rr): string
    {
        if ($ns !== null && $rr === null) {
            return 'ns';
        }
        if ($rr !== null && $ns === null) {
            return 'rr';
        }

        return 'ns';
    }
}
