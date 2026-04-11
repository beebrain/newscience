<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\PersonnelModel;

/**
 * เปรียบเทียบ bundle และเขียนกลับ newScience
 */
class ResearchRecordCvSyncMerge
{
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
                    'title'             => (string) ($pick['title'] ?? ''),
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
                'title'             => (string) ($base['title'] ?? ''),
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
     * @param list<array<string,mixed>> $publications
     * @param array<string,string>      $decisions pub key => rr|skip
     */
    public static function applyPublicationsToCvEntries(int $personnelId, array $publications, array $decisions): int
    {
        if ($publications === []) {
            return 0;
        }

        $cvSectionModel = new CvSectionModel();
        if (!$cvSectionModel->db->tableExists('cv_sections')) {
            return 0;
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
                'title'             => 'ผลงานตีพิมพ์ (จาก Research Record)',
                'description'       => null,
                'sort_order'        => $cvSectionModel->nextSortOrder($personnelId),
                'is_default'        => 0,
                'visible_on_public' => 1,
            ]);
            $section = $cvSectionModel->find((int) $cvSectionModel->getInsertID());
        }

        $sectionId    = (int) ($section['id'] ?? 0);
        $cvEntryModel = new CvEntryModel();
        $count        = 0;

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

            $existing = null;
            foreach ($cvEntryModel->where('section_id', $sectionId)->findAll() as $row) {
                $m = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
                if (($m['rr_publication_id'] ?? null) == ($pub['rr_publication_id'] ?? null)
                    && ($pub['rr_publication_id'] ?? 0) > 0) {
                    $existing = $row;
                    break;
                }
                if (($m['sync_external_key'] ?? '') === $key) {
                    $existing = $row;
                    break;
                }
            }

            $meta = [
                'source'              => 'research_record',
                'sync_external_key'   => $key,
                'rr_publication_id'     => $pub['rr_publication_id'] ?? null,
                'doi'                   => $pub['doi'] ?? null,
            ];
            $title = mb_substr((string) ($pub['title'] ?? ''), 0, 500);
            $year  = $pub['publication_year'] ?? null;
            $desc  = trim(implode("\n", array_filter([
                ($pub['publication_type'] ?? '') !== '' ? 'ประเภท: ' . $pub['publication_type'] : '',
                ($pub['source'] ?? '') !== '' ? 'แหล่ง: ' . $pub['source'] : '',
            ])));

            $rowData = [
                'section_id'        => $sectionId,
                'title'             => $title !== '' ? $title : 'ผลงาน',
                'organization'      => $pub['source'] ?? null,
                'location'          => null,
                'start_date'        => $year ? sprintf('%04d-01-01', (int) $year) : null,
                'end_date'          => null,
                'is_current'        => 0,
                'description'       => $desc !== '' ? $desc : null,
                'metadata'          => json_encode(array_filter($meta), JSON_UNESCAPED_UNICODE),
                'visible_on_public' => 1,
            ];

            if ($existing !== null) {
                $rowData['sort_order'] = (int) ($existing['sort_order'] ?? 0);
                $cvEntryModel->update((int) $existing['id'], $rowData);
            } else {
                $rowData['sort_order'] = $cvEntryModel->nextSortOrder($sectionId);
                $cvEntryModel->insert($rowData);
            }
            $count++;
        }

        return $count;
    }

    /**
     * @param array<string,mixed> $bundle
     */
    public static function replaceNewScienceCvFromBundle(int $personnelId, array $bundle): void
    {
        $db           = \Config\Database::connect();
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

        return trim(($e['title'] ?? '') . ' | ' . ($e['organization'] ?? '') . ' | ' . (string) ($e['start_date'] ?? ''));
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
