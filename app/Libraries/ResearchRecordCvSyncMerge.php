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
            $rows[] = [
                'id'          => 'section|' . $sk,
                'kind'        => 'section',
                'section_key' => $sk,
                'title'       => ($nsSec['title'] ?? '') !== '' ? $nsSec['title'] : ($rrSec['title'] ?? ''),
                'summary_ns'  => self::summarizeSection($nsSec),
                'summary_rr'  => self::summarizeSection($rrSec),
                'has_ns'      => $nsSec !== null,
                'has_rr'      => $rrSec !== null,
            ];

            $nsEnt = self::entriesByKey($nsSec['entries'] ?? []);
            $rrEnt = self::entriesByKey($rrSec['entries'] ?? []);
            $eKeys = array_values(array_unique(array_merge(array_keys($nsEnt), array_keys($rrEnt))));
            sort($eKeys);

            foreach ($eKeys as $ek) {
                $ne = $nsEnt[$ek] ?? null;
                $re = $rrEnt[$ek] ?? null;
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
                ];
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
        $bundle['content_hash'] = CvBundleCanonical::hashBundle($bundle);

        return $bundle;
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
                'title'             => 'ผลงานตีพิมพ์ (จาก กบศ)',
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
            $meta       = [
                'source'                => 'research_record',
                'sync_external_key'     => $key,
                'rr_publication_id'     => $pub['rr_publication_id'] ?? null,
                'doi'                   => $doiNorm !== '' ? $doiNorm : null,
                'rr_pull_fingerprint'    => $fingerprint,
                'rr_publication_type'   => $pubTypeRaw !== '' ? $pubTypeRaw : null,
            ];
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
                'metadata'          => json_encode(array_filter($meta, static fn ($v) => $v !== null && $v !== ''), JSON_UNESCAPED_UNICODE),
                'visible_on_public' => 1,
            ];

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

        self::normalizePublicationSectionsForPerson($personnelId);

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
