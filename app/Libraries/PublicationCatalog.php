<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;
use App\Models\CvSectionPublicationModel;
use App\Models\PublicationContributorModel;
use App\Models\PublicationModel;
use App\Models\PublicationSyncStateModel;

class PublicationCatalog
{
    public const ORIGIN_RR = 'research_record';
    public const ORIGIN_NS = 'newscience';

    /**
     * @param list<array<string,mixed>> $publications
     *
     * @return array{inserted:int,updated:int,linked:int,skipped_unchanged:int,contributors_synced:int}
     */
    public static function syncFromRrPayload(int $personnelId, string $canonicalEmail, array $publications): array
    {
        $stats = [
            'inserted'           => 0,
            'updated'            => 0,
            'linked'             => 0,
            'skipped_unchanged'  => 0,
            'contributors_synced' => 0,
        ];

        if (! self::isReady()) {
            return $stats;
        }

        $section = self::ensureResearchSection($personnelId);
        if ($section === null) {
            return $stats;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($publications as $pub) {
            if (! is_array($pub)) {
                continue;
            }

            $key = self::externalKeyFromPayload($pub);
            if ($key === '') {
                continue;
            }

            $existing = self::findPublication($pub, $key);
            $hash     = self::contentHash($pub);
            $rowData  = self::publicationRowData($pub, $key, $hash);
            $pubModel = new PublicationModel();

            if ($existing === null) {
                $pubModel->insert($rowData);
                $publicationId = (int) $pubModel->getInsertID();
                $stats['inserted']++;
            } else {
                $publicationId = (int) $existing['id'];
                if (($existing['content_hash'] ?? '') === $hash) {
                    $stats['skipped_unchanged']++;
                } else {
                    $pubModel->update($publicationId, $rowData);
                    $stats['updated']++;
                }
            }

            $stats['contributors_synced'] += self::syncContributors(
                $publicationId,
                $personnelId,
                $pub['contributors'] ?? [],
                self::ORIGIN_RR,
                false
            );
            if (self::linkSectionPublication((int) $section['id'], $publicationId)) {
                $stats['linked']++;
            }
            self::propagateToLinkedCoAuthors($personnelId, $pub, $publicationId);
            self::upsertSyncState($key, $publicationId, (int) ($pub['rr_publication_id'] ?? 0), $hash, self::ORIGIN_RR, 'rr_to_ns');
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new \RuntimeException('Publication catalog transaction failed');
        }

        return $stats;
    }

    /**
     * Mirror a locally saved research cv_entry into the catalog so automatic
     * NS -> RR push has a canonical row to send.
     *
     * @return array{publication_id:int,inserted:bool,updated:bool}|null
     */
    public static function syncFromCvEntry(int $personnelId, array $section, array $entry): ?array
    {
        if (! self::isReady()) {
            return null;
        }
        if (! in_array((string) ($section['type'] ?? ''), ['research', 'articles'], true)) {
            return null;
        }

        $meta = CvEntryModel::decodeMetadata($entry['metadata'] ?? null);
        $key  = PublicationIdentity::syncExternalKeyFromMetadata($meta);
        if ($key === '') {
            $key = 'ns:h:' . substr(hash('sha256', implode('|', [
                self::normalizeText((string) ($entry['title'] ?? '')),
                (string) ($entry['start_date'] ?? ''),
                self::normalizeText((string) ($entry['organization'] ?? '')),
            ])), 0, 40);
            $meta['sync_external_key'] = $key;
        }

        $ownerEmail = self::emailForPersonnel($personnelId);
        $contributors = PublicationResearchFields::contributorsFromMetadataOrCatalog($meta);
        if ($contributors === [] && $ownerEmail !== '') {
            $contributors = [[
                'email' => $ownerEmail,
                'name'  => null,
                'order' => 1,
            ]];
        }

        $pub = PublicationResearchFields::buildPublicationPayloadFromEntry($entry, $meta, $contributors);
        $pub['external_key']      = $key;
        $pub['rr_publication_id'] = (int) ($meta['rr_publication_id'] ?? 0);
        $hash = self::contentHash($pub);

        $existing = self::findPublication($pub, $key);
        $data     = self::publicationRowData($pub, $key, $hash, self::ORIGIN_NS);
        $model    = new PublicationModel();
        $inserted = false;
        $updated  = false;

        if ($existing === null) {
            $model->insert($data);
            $publicationId = (int) $model->getInsertID();
            $inserted = true;
        } else {
            $publicationId = (int) $existing['id'];
            if (($existing['content_hash'] ?? '') !== $hash) {
                $model->update($publicationId, $data);
                $updated = true;
            }
        }

        self::syncContributors($publicationId, $personnelId, $contributors, self::ORIGIN_NS, true);
        self::linkSectionPublication((int) $section['id'], $publicationId, (int) ($entry['sort_order'] ?? 0), (int) ($entry['visible_on_public'] ?? 1));
        self::propagateToLinkedCoAuthors($personnelId, $pub, $publicationId);
        self::upsertSyncState($key, $publicationId, (int) ($meta['rr_publication_id'] ?? 0), $hash, self::ORIGIN_NS, 'ns_local');

        return ['publication_id' => $publicationId, 'inserted' => $inserted, 'updated' => $updated];
    }

    public static function isReady(): bool
    {
        $db = \Config\Database::connect();

        return $db->tableExists('publications')
            && $db->tableExists('publication_contributors')
            && $db->tableExists('cv_section_publications')
            && $db->tableExists('publication_sync_state');
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function publicationRowsForPersonnel(int $personnelId): array
    {
        if (! self::isReady()) {
            return [];
        }

        return self::publicationRowsVisibleToPersonnel(\Config\Database::connect(), $personnelId);
    }

    /**
     * @param array<string,mixed> $meta
     *
     * @return list<array<string,mixed>>
     */
    public static function lookupContributorsForMetadata(array $meta): array
    {
        $authors = PublicationResearchFields::contributorsFromMetadataOrCatalog($meta);
        if ($authors !== [] || ! self::isReady()) {
            return $authors;
        }

        $key = PublicationIdentity::syncExternalKeyFromMetadata($meta);
        if ($key === '') {
            return [];
        }

        $row = (new PublicationModel())->where('sync_external_key', $key)->first();
        if ($row === null) {
            return [];
        }

        $db = \Config\Database::connect();
        $rows = $db->table('publication_contributors')
            ->where('publication_id', (int) $row['id'])
            ->orderBy('author_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return PublicationResearchFields::normalizeContributors(array_map(static fn (array $c): array => [
            'name'          => $c['display_name'] ?? null,
            'email'         => $c['contributor_email_norm'] ?? null,
            'affiliation'   => $c['affiliation'] ?? null,
            'corresponding' => (int) ($c['corresponding'] ?? 0),
            'order'         => (int) ($c['author_order'] ?? 0),
        ], $rows));
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function buildPayloadForPersonnel(int $personnelId): array
    {
        if (! self::isReady()) {
            return [];
        }

        $db = \Config\Database::connect();
        $rows = self::publicationRowsVisibleToPersonnel($db, $personnelId);

        $out = [];
        foreach ($rows as $row) {
            $publicationId = (int) $row['id'];
            $contributors = $db->table('publication_contributors')
                ->where('publication_id', $publicationId)
                ->orderBy('author_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            $biblio = PublicationResearchFields::decodeBibliographicFromPublicationRow($row);
            $payload = [
                'external_key'      => (string) ($row['sync_external_key'] ?? ''),
                'ns_publication_id' => $publicationId,
                'rr_publication_id' => isset($row['rr_publication_id']) ? (int) $row['rr_publication_id'] : null,
                'title'             => (string) ($row['title'] ?? ''),
                'publication_year'  => $row['publication_year'] ?? null,
                'publication_type'  => $row['publication_type'] ?? null,
                'source'            => $row['source'] ?? null,
                'doi'               => $row['doi_norm'] ?: ($row['doi'] ?? null),
                'sync_origin'       => $row['sync_origin'] ?? self::ORIGIN_NS,
                'content_hash'      => $row['content_hash'] ?? null,
                'contributors'      => array_map(static function (array $c): array {
                    return [
                        'email'         => $c['contributor_email_norm'] ?? null,
                        'name'          => $c['display_name'] ?? null,
                        'order'         => (int) ($c['author_order'] ?? 0),
                        'corresponding' => (int) ($c['corresponding'] ?? 0),
                        'affiliation'   => $c['affiliation'] ?? null,
                        'rr_user_uid'   => $c['rr_user_uid'] ?? null,
                        'rr_faculty_id' => $c['rr_faculty_id'] ?? null,
                    ];
                }, $contributors),
            ];
            PublicationResearchFields::applyBibliographicToSyncPayload($payload, $biblio);
            $out[] = $payload;
        }

        return $out;
    }

    /**
     * Publications linked to this person's CV section or where they are a catalog contributor.
     *
     * @return list<array<string,mixed>>
     */
    private static function publicationRowsVisibleToPersonnel($db, int $personnelId): array
    {
        $rows = $db->table('publications p')
            ->select('p.*')
            ->join('cv_section_publications csp', 'csp.publication_id = p.id', 'inner')
            ->join('cv_sections cs', 'cs.id = csp.section_id', 'inner')
            ->where('cs.personnel_id', $personnelId)
            ->where('p.is_active', 1)
            ->groupBy('p.id')
            ->get()
            ->getResultArray();

        $email = self::emailForPersonnel($personnelId);
        if ($email !== '') {
            $byContributor = $db->table('publications p')
                ->select('p.*')
                ->join('publication_contributors pc', 'pc.publication_id = p.id', 'inner')
                ->where('pc.contributor_email_norm', $email)
                ->where('p.is_active', 1)
                ->groupBy('p.id')
                ->get()
                ->getResultArray();
            $rows = array_merge($rows, $byContributor);
        }

        $byId = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $byId[$id] = $row;
            }
        }

        usort($byId, static function (array $a, array $b): int {
            $ya = (int) ($a['publication_year'] ?? 0);
            $yb = (int) ($b['publication_year'] ?? 0);
            if ($ya !== $yb) {
                return $yb <=> $ya;
            }

            return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
        });

        return array_values($byId);
    }

    private static function ensureResearchSection(int $personnelId): ?array
    {
        $model = new CvSectionModel();
        $section = $model->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->first();
        if ($section !== null) {
            return $section;
        }

        $model->insert([
            'personnel_id'      => $personnelId,
            'type'              => 'research',
            'title'             => ResearchRecordCvSyncMerge::canonicalPublicationSectionTitle(),
            'description'       => null,
            'sort_order'        => $model->nextSortOrder($personnelId),
            'is_default'        => 0,
            'visible_on_public' => 1,
        ]);

        return $model->find((int) $model->getInsertID());
    }

    private static function externalKeyFromPayload(array $pub): string
    {
        $key = trim((string) ($pub['external_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }

        return PublicationIdentity::publicationExternalKeyFromRrApiShape(
            (string) ($pub['doi'] ?? ''),
            (int) ($pub['rr_publication_id'] ?? 0)
        );
    }

    private static function findPublication(array $pub, string $key): ?array
    {
        $model = new PublicationModel();
        $rrId = (int) ($pub['rr_publication_id'] ?? 0);
        if ($rrId > 0) {
            $row = $model->where('rr_publication_id', $rrId)->first();
            if ($row !== null) {
                return $row;
            }
        }

        $row = $model->where('sync_external_key', $key)->first();
        if ($row !== null) {
            return $row;
        }

        $doiNorm = PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? ''));
        if ($doiNorm !== '') {
            return $model->where('doi_norm', $doiNorm)->first();
        }

        return null;
    }

    private static function publicationRowData(array $pub, string $key, string $hash, string $origin = self::ORIGIN_RR): array
    {
        $doiNorm = PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? ''));
        $rrId    = (int) ($pub['rr_publication_id'] ?? 0);

        return [
            'sync_external_key' => $key,
            'rr_publication_id' => $rrId > 0 ? $rrId : null,
            'title'             => mb_substr((string) ($pub['title'] ?? ''), 0, 500),
            'publication_year'  => isset($pub['publication_year']) && $pub['publication_year'] !== '' ? (int) $pub['publication_year'] : null,
            'publication_type'  => trim((string) ($pub['publication_type'] ?? '')) ?: null,
            'source'            => trim((string) ($pub['source'] ?? '')) ?: null,
            'doi'               => trim((string) ($pub['doi'] ?? '')) ?: null,
            'doi_norm'          => $doiNorm !== '' ? $doiNorm : null,
            'sync_origin'       => $origin,
            'last_synced_from'  => $origin,
            'content_hash'      => $hash,
            'metadata'          => json_encode(
                PublicationResearchFields::encodeBibliographicMetadata(array_merge($pub, ['sync_origin' => $origin])),
                JSON_UNESCAPED_UNICODE
            ),
            'is_active'         => 1,
        ];
    }

    private static function syncContributors(
        int $publicationId,
        int $ownerPersonnelId,
        mixed $contributors,
        string $source,
        bool $replaceAll
    ): int {
        if (! is_array($contributors)) {
            return 0;
        }

        $model = new PublicationContributorModel();
        if ($replaceAll) {
            $model->where('publication_id', $publicationId)->delete();
        } else {
            $model->where('publication_id', $publicationId)
                ->where('source', $source)
                ->delete();
        }

        $count = 0;
        foreach ($contributors as $row) {
            if (! is_array($row)) {
                continue;
            }
            $email = CvProfile::normalizeEmail((string) ($row['email'] ?? ''));
            $name  = trim((string) ($row['name'] ?? ''));
            if ($email === '' && $name === '') {
                continue;
            }

            $resolved = self::resolveContributor($email, $ownerPersonnelId);
            $model->insert([
                'publication_id'          => $publicationId,
                'contributor_email_norm' => $email !== '' ? $email : null,
                'contributor_name_key'   => $email === '' ? self::nameKey($name, (string) ($row['affiliation'] ?? '')) : null,
                'display_name'           => $name !== '' ? mb_substr($name, 0, 255) : null,
                'personnel_id'           => $resolved['personnel_id'],
                'contributor_affinity'   => $resolved['affinity'],
                'rr_user_uid'            => trim((string) ($row['rr_user_uid'] ?? '')) ?: null,
                'rr_faculty_id'          => isset($row['rr_faculty_id']) && $row['rr_faculty_id'] !== '' ? (int) $row['rr_faculty_id'] : null,
                'author_order'           => (int) ($row['order'] ?? 0),
                'corresponding'          => (int) ($row['corresponding'] ?? 0),
                'affiliation'            => trim((string) ($row['affiliation'] ?? '')) ?: null,
                'source'                 => $source,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * @return array{personnel_id:int|null,affinity:string}
     */
    private static function resolveContributor(string $email, int $ownerPersonnelId): array
    {
        if ($email === '') {
            return ['personnel_id' => null, 'affinity' => 'name_only'];
        }

        $db = \Config\Database::connect();
        $person = $db->table('personnel')
            ->groupStart()
            ->where('LOWER(TRIM(user_email)) = ' . $db->escape($email), null, false)
            ->orWhere('LOWER(TRIM(email)) = ' . $db->escape($email), null, false)
            ->groupEnd()
            ->get()
            ->getRowArray();
        if (! $person) {
            return [
                'personnel_id' => null,
                'affinity'     => str_ends_with($email, '@live.uru.ac.th') || str_ends_with($email, '@uru.ac.th') ? 'org_unlinked' : 'external',
            ];
        }

        $ownerFaculty = self::facultyIdForPersonnel($ownerPersonnelId);
        $contribFaculty = self::facultyIdForEmail($email);
        $affinity = $ownerFaculty !== null && $contribFaculty !== null && $ownerFaculty !== $contribFaculty
            ? 'org_other_faculty'
            : 'same_faculty';

        return ['personnel_id' => (int) $person['id'], 'affinity' => $affinity];
    }

    private static function facultyIdForPersonnel(int $personnelId): ?int
    {
        $db = \Config\Database::connect();
        $row = $db->table('personnel p')
            ->select('u.faculty_id')
            ->join('user u', 'u.email = p.user_email OR u.email = p.email', 'left', false)
            ->where('p.id', $personnelId)
            ->get()
            ->getRowArray();

        return isset($row['faculty_id']) && $row['faculty_id'] !== null ? (int) $row['faculty_id'] : null;
    }

    private static function facultyIdForEmail(string $email): ?int
    {
        $db = \Config\Database::connect();
        $row = $db->table('user')
            ->select('faculty_id')
            ->where('LOWER(TRIM(email)) = ' . $db->escape($email), null, false)
            ->get()
            ->getRowArray();

        return isset($row['faculty_id']) && $row['faculty_id'] !== null ? (int) $row['faculty_id'] : null;
    }

    private static function emailForPersonnel(int $personnelId): string
    {
        $db = \Config\Database::connect();
        $row = $db->table('personnel')
            ->select('COALESCE(NULLIF(TRIM(user_email), \'\'), NULLIF(TRIM(email), \'\')) AS email', false)
            ->where('id', $personnelId)
            ->get()
            ->getRowArray();

        return CvProfile::normalizeEmail((string) ($row['email'] ?? ''));
    }

    /**
     * When one author syncs from RR, mirror the publication onto other NS personnel
     * listed as co-authors (catalog link + cv_entry) so their public CV shows it too.
     *
     * @param array<string,mixed> $pub RR-shaped publication payload
     */
    private static function propagateToLinkedCoAuthors(int $syncingPersonnelId, array $pub, int $publicationId): void
    {
        $contributors = $pub['contributors'] ?? [];
        if (! is_array($contributors) || $contributors === []) {
            return;
        }

        $seen = [$syncingPersonnelId => true];
        foreach ($contributors as $row) {
            if (! is_array($row)) {
                continue;
            }
            $email = CvProfile::normalizeEmail((string) ($row['email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $resolved = self::resolveContributor($email, $syncingPersonnelId);
            $coPersonnelId = $resolved['personnel_id'];
            if ($coPersonnelId === null || isset($seen[$coPersonnelId])) {
                continue;
            }
            $seen[$coPersonnelId] = true;

            $section = self::ensureResearchSection($coPersonnelId);
            if ($section === null) {
                continue;
            }

            self::linkSectionPublication((int) $section['id'], $publicationId);
            ResearchRecordCvSyncMerge::applyPublicationsToCvEntries($coPersonnelId, [$pub], []);
        }
    }

    private static function linkSectionPublication(int $sectionId, int $publicationId, int $sortOrder = 0, int $visible = 1): bool
    {
        $model = new CvSectionPublicationModel();
        $existing = $model->where('section_id', $sectionId)
            ->where('publication_id', $publicationId)
            ->first();
        if ($existing !== null) {
            return false;
        }

        if ($sortOrder <= 0) {
            $row = $model->builder()
                ->selectMax('sort_order', 'm')
                ->where('section_id', $sectionId)
                ->get()
                ->getRowArray();
            $sortOrder = (int) (($row['m'] ?? 0) + 1);
        }

        $model->insert([
            'section_id'        => $sectionId,
            'publication_id'    => $publicationId,
            'sort_order'        => $sortOrder,
            'visible_on_public' => $visible ? 1 : 0,
        ]);

        return true;
    }

    private static function upsertSyncState(string $key, int $publicationId, int $rrPublicationId, string $hash, string $origin, string $direction): void
    {
        $model = new PublicationSyncStateModel();
        $existing = $model->where('sync_external_key', $key)->first();
        $data = [
            'sync_external_key'   => $key,
            'rr_publication_id'   => $rrPublicationId > 0 ? $rrPublicationId : null,
            'ns_publication_id'   => $publicationId,
            'sync_origin'         => $existing['sync_origin'] ?? $origin,
            'last_synced_from'    => $origin,
            'last_sync_direction' => $direction,
            'last_synced_at'      => date('Y-m-d H:i:s'),
        ];
        if ($origin === self::ORIGIN_RR) {
            $data['content_hash_rr'] = $hash;
        } else {
            $data['content_hash_ns'] = $hash;
        }

        if ($existing === null) {
            $model->insert($data);
        } else {
            $model->update((int) $existing['id'], $data);
        }
    }

    private static function contentHash(array $pub): string
    {
        return hash('sha256', json_encode([
            'title'        => self::normalizeText((string) ($pub['title'] ?? '')),
            'year'         => $pub['publication_year'] ?? null,
            'type'         => $pub['publication_type'] ?? null,
            'source'       => self::normalizeText((string) ($pub['source'] ?? '')),
            'doi'          => PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? '')),
            'contributors' => $pub['contributors'] ?? [],
            'biblio'       => PublicationResearchFields::bibliographicForContentHash($pub),
        ], JSON_UNESCAPED_UNICODE));
    }

    private static function nameKey(string $name, string $affiliation): string
    {
        return hash('sha256', self::normalizeText($name) . '|' . self::normalizeText($affiliation));
    }

    private static function normalizeText(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }
}
