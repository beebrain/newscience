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
        $copy = $bundle;
        unset($copy['retrieved_at'], $copy['source']);

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
            $entries = $cvEntryModel->where('section_id', $sid)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

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

    private static function hashSegment(string $s): string
    {
        return substr(hash('sha256', $s), 0, 40);
    }
}
