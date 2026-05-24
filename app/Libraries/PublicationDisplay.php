<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\CvEntryModel;

/**
 * Enrich CV section entries with publication catalog data for display.
 */
final class PublicationDisplay
{
    /**
     * @param list<array<string,mixed>> $sections
     *
     * @return list<array<string,mixed>>
     */
    public static function enrichSections(int $personnelId, array $sections): array
    {
        if (! PublicationCatalog::isReady() || $sections === []) {
            return $sections;
        }

        $catalogByKey = self::loadCatalogByExternalKey($personnelId);
        $contributorsByPubId = self::loadContributorsByPublicationId(array_values(array_filter(array_map(
            static fn (array $row): int => (int) ($row['id'] ?? 0),
            $catalogByKey
        ))));

        foreach ($sections as &$section) {
            $type = (string) ($section['type'] ?? '');
            if (! in_array($type, ['research', 'articles'], true)) {
                continue;
            }

            $entries = $section['entries'] ?? [];
            if (! is_array($entries)) {
                continue;
            }

            foreach ($entries as &$entry) {
                $meta = $entry['metadata_array'] ?? CvEntryModel::decodeMetadata($entry['metadata'] ?? null);
                $key = PublicationIdentity::syncExternalKeyFromMetadata(is_array($meta) ? $meta : []);
                if ($key === '' && is_array($meta) && ! empty($meta['doi'])) {
                    $key = 'pub:h:' . substr(hash('sha256', PublicationIdentity::normalizeDoi((string) $meta['doi'])), 0, 40);
                }

                $catalog = $key !== '' ? ($catalogByKey[$key] ?? null) : null;
                $pubId = is_array($catalog) ? (int) ($catalog['id'] ?? 0) : 0;
                $catalogContributors = $pubId > 0 ? ($contributorsByPubId[$pubId] ?? []) : [];

                $authors = PublicationResearchFields::contributorsFromMetadataOrCatalog(
                    is_array($meta) ? $meta : [],
                    $catalogContributors
                );

                $biblio = is_array($catalog)
                    ? PublicationResearchFields::decodeBibliographicFromPublicationRow($catalog)
                    : PublicationResearchFields::extractBibliographicFromMetadata(is_array($meta) ? $meta : []);

                $entry['publication_catalog'] = $catalog;
                $entry['publication_contributors'] = $authors;
                $entry['publication_biblio'] = $biblio;
                $entry['publication_contributors_display'] = PublicationResearchFields::formatContributorsDisplay(
                    $authors !== [] ? $authors : $catalogContributors
                );
            }
            unset($entry);

            $section['entries'] = $entries;
        }
        unset($section);

        return $sections;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private static function loadCatalogByExternalKey(int $personnelId): array
    {
        $out = [];
        foreach (PublicationCatalog::publicationRowsForPersonnel($personnelId) as $row) {
            $key = trim((string) ($row['sync_external_key'] ?? ''));
            if ($key !== '') {
                $out[$key] = $row;
            }
        }

        return $out;
    }

    /**
     * @param list<int> $publicationIds
     *
     * @return array<int,list<array<string,mixed>>>
     */
    private static function loadContributorsByPublicationId(array $publicationIds): array
    {
        if ($publicationIds === []) {
            return [];
        }

        $db = \Config\Database::connect();
        $rows = $db->table('publication_contributors')
            ->whereIn('publication_id', $publicationIds)
            ->orderBy('author_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $row) {
            $pid = (int) ($row['publication_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $out[$pid][] = [
                'name'          => $row['display_name'] ?? null,
                'email'         => $row['contributor_email_norm'] ?? null,
                'affiliation'   => $row['affiliation'] ?? null,
                'corresponding' => (int) ($row['corresponding'] ?? 0),
                'order'         => (int) ($row['author_order'] ?? 0),
                'display_name'  => $row['display_name'] ?? null,
                'contributor_email_norm' => $row['contributor_email_norm'] ?? null,
            ];
        }

        return $out;
    }
}
