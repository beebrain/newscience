<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Shared mapping between NS CV research entries, publication catalog, and RR sync payloads.
 */
final class PublicationResearchFields
{
    /** @var list<string> */
    public const BIBLIO_KEYS = [
        'abstract',
        'publication_month',
        'volume',
        'pages',
        'isbn',
        'keywords',
        'notes',
        'ref_url',
    ];

    public static function normalizeYearToCe(int|string|null $yearRaw): ?int
    {
        if ($yearRaw === null || $yearRaw === '') {
            return null;
        }

        $year = (int) $yearRaw;
        if ($year >= 2443) {
            return $year - 543;
        }
        if ($year >= 1900 && $year <= 2100) {
            return $year;
        }
        if ($year < 1900) {
            return $year;
        }

        return $year - 543;
    }

    public static function yearBeFromCe(?int $ce): ?int
    {
        if ($ce === null || $ce <= 0) {
            return null;
        }

        return $ce + 543;
    }

    /**
     * @return array<string,mixed>
     */
    public static function extractBibliographicFromMetadata(array $meta): array
    {
        $out = [];
        foreach (self::BIBLIO_KEYS as $key) {
            if (! array_key_exists($key, $meta)) {
                continue;
            }
            $val = $meta[$key];
            if ($val === null || $val === '') {
                continue;
            }
            $out[$key] = is_string($val) ? trim($val) : $val;
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $existingMeta
     *
     * @return array<string,mixed>
     */
    public static function mergeResearchMetadataFromPost(array $post, array $existingMeta = []): array
    {
        $meta = $existingMeta;

        $yearBeRaw = trim((string) ($post['publication_year_be'] ?? ''));
        if ($yearBeRaw !== '' && ctype_digit($yearBeRaw)) {
            $meta['publication_year_be'] = (int) $yearBeRaw;
        } else {
            unset($meta['publication_year_be']);
        }

        foreach (self::BIBLIO_KEYS as $key) {
            if (! array_key_exists($key, $post)) {
                continue;
            }
            $val = trim((string) $post[$key]);
            if ($val !== '') {
                $meta[$key] = mb_substr($val, 0, self::maxLength($key));
            } else {
                unset($meta[$key]);
            }
        }

        $monthRaw = trim((string) ($post['publication_month'] ?? ''));
        if ($monthRaw !== '' && ctype_digit($monthRaw)) {
            $month = (int) $monthRaw;
            if ($month >= 1 && $month <= 12) {
                $meta['publication_month'] = $month;
            }
        } elseif ($monthRaw === '') {
            unset($meta['publication_month']);
        }

        $authors = self::contributorsFromPost($post['publication_authors'] ?? null);
        if ($authors !== []) {
            $meta['publication_authors'] = $authors;
        } else {
            unset($meta['publication_authors']);
        }

        return $meta;
    }

    /** ปี พ.ศ. ที่รับในฟอร์มหน้า publication */
    public const PUBLICATION_YEAR_BE_MIN = 2400;

    public const PUBLICATION_YEAR_BE_MAX = 2700;

    /**
     * รหัสฟิลด์ POST => id ของ input ในหน้า publication (สำหรับ client validation)
     *
     * @return array<string, string>
     */
    public static function publicationPageFieldElementIds(): array
    {
        return [
            'entry_title'         => 'cv-p-title',
            'organization'        => 'cv-p-org',
            'publication_type'    => 'cv-p-pubtype',
            'publication_year_be' => 'cv-p-year-be',
        ];
    }

    /**
     * ตรวจฟอร์มหน้าเพิ่ม/แก้ผลงานตีพิมพ์ — ข้อความตรงกับที่ saveCvEntry ใช้
     *
     * @param array<string,mixed> $post
     *
     * @return list<array{field:string,message:string}>
     */
    public static function publicationPageFieldErrors(array $post): array
    {
        $errors = [];

        $title = trim((string) ($post['entry_title'] ?? ''));
        if ($title === '') {
            $errors[] = ['field' => 'entry_title', 'message' => 'กรุณากรอกชื่อรายการ'];
        } elseif (mb_strlen($title) > 500) {
            $errors[] = ['field' => 'entry_title', 'message' => 'ชื่อรายการยาวเกิน 500 ตัวอักษร'];
        }

        foreach (self::researchFieldErrors($post) as $row) {
            $errors[] = $row;
        }

        $yearBe = trim((string) ($post['publication_year_be'] ?? ''));
        $start  = trim((string) ($post['start_date'] ?? ''));
        if ($yearBe !== '' && $start === '') {
            if (! ctype_digit($yearBe)) {
                $errors[] = ['field' => 'publication_year_be', 'message' => 'ปีที่เผยแพร่ (พ.ศ.) ต้องเป็นตัวเลข'];
            } else {
                $y = (int) $yearBe;
                if ($y < self::PUBLICATION_YEAR_BE_MIN || $y > self::PUBLICATION_YEAR_BE_MAX) {
                    $errors[] = [
                        'field'   => 'publication_year_be',
                        'message' => 'ปีที่เผยแพร่ (พ.ศ.) ต้องอยู่ระหว่าง '
                            . self::PUBLICATION_YEAR_BE_MIN . '–' . self::PUBLICATION_YEAR_BE_MAX,
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string,mixed> $post
     *
     * @return list<array{field:string,message:string}>
     */
    public static function researchFieldErrors(array $post): array
    {
        $errors = [];

        $ptype = trim((string) ($post['publication_type'] ?? ''));
        if ($ptype === '') {
            $errors[] = ['field' => 'publication_type', 'message' => 'กรุณาเลือกประเภทผลงานเผยแพร่'];
        } elseif (! RrPublicationType::isValidPublicationTypeCode($ptype)) {
            $errors[] = ['field' => 'publication_type', 'message' => 'ประเภทผลงานเผยแพร่ไม่ถูกต้อง'];
        }

        $source = trim((string) ($post['organization'] ?? ''));
        if ($source === '') {
            $errors[] = ['field' => 'organization', 'message' => 'กรุณากรอกแหล่งเผยแพร่ (source)'];
        }

        $yearBe = trim((string) ($post['publication_year_be'] ?? ''));
        $start  = trim((string) ($post['start_date'] ?? ''));
        if ($yearBe === '' && $start === '') {
            $errors[] = ['field' => 'publication_year_be', 'message' => 'กรุณาระบุปีที่เผยแพร่ (พ.ศ.) หรือวันเริ่ม'];
        }

        return $errors;
    }

    /**
     * @param array<string,mixed> $post
     */
    public static function validateResearchSave(array $post): ?string
    {
        foreach (self::researchFieldErrors($post) as $row) {
            return $row['message'];
        }

        return null;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function contributorsFromPost(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = trim($raw);
            if ($raw === '') {
                return [];
            }
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? self::normalizeContributors($decoded) : [];
        }

        return is_array($raw) ? self::normalizeContributors($raw) : [];
    }

    /**
     * @param list<array<string,mixed>> $rows
     *
     * @return list<array<string,mixed>>
     */
    public static function normalizeContributors(array $rows): array
    {
        $out = [];
        $order = 1;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            $email = CvProfile::normalizeEmail((string) ($row['email'] ?? ''));
            if ($name === '' && $email === '') {
                continue;
            }
            $out[] = [
                'name'          => $name !== '' ? mb_substr($name, 0, 255) : null,
                'email'         => $email !== '' ? $email : null,
                'affiliation'   => trim((string) ($row['affiliation'] ?? '')) ?: null,
                'corresponding' => ! empty($row['corresponding']) ? 1 : 0,
                'order'         => isset($row['order']) && (int) $row['order'] > 0 ? (int) $row['order'] : $order,
            ];
            $order++;
        }

        return self::dedupeContributors($out);
    }

    /**
     * Merge duplicate authors (same email, or same name when email empty).
     *
     * @param list<array<string,mixed>> $rows
     *
     * @return list<array<string,mixed>>
     */
    public static function dedupeContributors(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        /** @var array<string, array<string,mixed>> $byEmail */
        $byEmail = [];
        /** @var array<string, array<string,mixed>> $byName */
        $byName  = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $email = CvProfile::normalizeEmail((string) ($row['email'] ?? ''));
            if ($email !== '') {
                $byEmail[$email] = isset($byEmail[$email])
                    ? self::mergeContributorRows($byEmail[$email], $row)
                    : $row;

                continue;
            }

            $nameKey = self::contributorNameKey((string) ($row['name'] ?? ''));
            if ($nameKey === '') {
                continue;
            }
            $byName[$nameKey] = isset($byName[$nameKey])
                ? self::mergeContributorRows($byName[$nameKey], $row)
                : $row;
        }

        $merged = array_merge(array_values($byEmail), array_values($byName));
        $order  = 1;
        foreach ($merged as &$row) {
            $row['order'] = $order++;
        }
        unset($row);

        return $merged;
    }

    /**
     * @param array<string,mixed> $a
     * @param array<string,mixed> $b
     *
     * @return array<string,mixed>
     */
    private static function mergeContributorRows(array $a, array $b): array
    {
        $nameA = trim((string) ($a['name'] ?? ''));
        $nameB = trim((string) ($b['name'] ?? ''));
        $name  = $nameB;
        if ($nameA !== '' && ($nameB === '' || mb_strlen($nameA) >= mb_strlen($nameB))) {
            $name = $nameA;
        }

        $affA = trim((string) ($a['affiliation'] ?? ''));
        $affB = trim((string) ($b['affiliation'] ?? ''));
        $aff  = $affB !== '' ? $affB : $affA;
        if ($affA !== '' && ($affB === '' || mb_strlen($affA) >= mb_strlen($affB))) {
            $aff = $affA;
        }

        $email = CvProfile::normalizeEmail((string) ($a['email'] ?? ''));
        if ($email === '') {
            $email = CvProfile::normalizeEmail((string) ($b['email'] ?? ''));
        }

        return [
            'name'          => $name !== '' ? mb_substr($name, 0, 255) : null,
            'email'         => $email !== '' ? $email : null,
            'affiliation'   => $aff !== '' ? $aff : null,
            'corresponding' => (! empty($a['corresponding']) || ! empty($b['corresponding'])) ? 1 : 0,
            'order'         => (int) ($a['order'] ?? $b['order'] ?? 0),
        ];
    }

    private static function contributorNameKey(string $name): string
    {
        $name = mb_strtolower(trim($name));

        return $name === '' ? '' : (string) preg_replace('/\s+/u', ' ', $name);
    }

    /**
     * @param array<string,mixed> $meta
     * @param list<array<string,mixed>>|null $catalogContributors
     *
     * @return list<array<string,mixed>>
     */
    public static function contributorsFromMetadataOrCatalog(array $meta, ?array $catalogContributors = null): array
    {
        $fromMeta = $meta['publication_authors'] ?? null;
        if (is_array($fromMeta) && $fromMeta !== []) {
            return self::normalizeContributors($fromMeta);
        }

        if (is_array($catalogContributors) && $catalogContributors !== []) {
            return self::normalizeContributors($catalogContributors);
        }

        return [];
    }

    /**
     * @param array<string,mixed> $entry
     * @param array<string,mixed> $meta
     * @param list<array<string,mixed>> $contributors
     *
     * @return array<string,mixed>
     */
    public static function buildPublicationPayloadFromEntry(array $entry, array $meta, array $contributors): array
    {
        $biblio = self::extractBibliographicFromMetadata($meta);
        $yearCe = null;
        if (! empty($meta['publication_year_be'])) {
            $yearCe = self::normalizeYearToCe($meta['publication_year_be']);
        }
        if ($yearCe === null && ! empty($entry['start_date']) && preg_match('/^(\d{4})/', (string) $entry['start_date'], $m)) {
            $yearCe = (int) $m[1];
        }

        $url = trim((string) ($meta['url'] ?? $meta['legacy_url'] ?? ''));
        if ($url === '' && ! empty($biblio['ref_url'])) {
            $url = (string) $biblio['ref_url'];
        }

        $payload = [
            'title'            => (string) ($entry['title'] ?? ''),
            'publication_year' => $yearCe,
            'publication_type' => $meta['rr_publication_type'] ?? null,
            'source'           => $entry['organization'] ?? null,
            'doi'              => $meta['doi'] ?? null,
            'contributors'     => $contributors,
        ];

        foreach ($biblio as $key => $val) {
            $payload[$key] = $val;
        }

        if ($url !== '') {
            $payload['ref_url'] = mb_substr($url, 0, 2048);
        }

        return $payload;
    }

    /**
     * @param list<array<string,mixed>> $contributors
     */
    public static function formatContributorsDisplay(array $contributors): string
    {
        if ($contributors === []) {
            return '';
        }

        $parts = [];
        foreach ($contributors as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['display_name'] ?? $row['name'] ?? ''));
            $email = trim((string) ($row['contributor_email_norm'] ?? $row['email'] ?? ''));
            $aff = trim((string) ($row['affiliation'] ?? ''));
            $label = $name !== '' ? $name : $email;
            if ($label === '') {
                continue;
            }
            if (! empty($row['corresponding'])) {
                $label .= '*';
            }
            if ($aff !== '' && $name !== '') {
                $label .= ' (' . $aff . ')';
            }
            $parts[] = $label;
        }

        return $parts !== [] ? implode('; ', $parts) : '';
    }

    /**
     * @param array<string,mixed> $pub
     *
     * @return array<string,mixed>
     */
    public static function encodeBibliographicMetadata(array $pub): array
    {
        $meta = [
            'rr_publication_id' => isset($pub['rr_publication_id']) && (int) $pub['rr_publication_id'] > 0
                ? (int) $pub['rr_publication_id']
                : null,
            'source_payload' => $pub['sync_origin'] ?? PublicationCatalog::ORIGIN_RR,
        ];

        foreach (self::BIBLIO_KEYS as $key) {
            if (! array_key_exists($key, $pub)) {
                continue;
            }
            $val = $pub[$key];
            if ($val === null || $val === '') {
                continue;
            }
            $meta[$key] = is_string($val) ? trim($val) : $val;
        }

        if (isset($pub['publication_month']) && $pub['publication_month'] !== '' && $pub['publication_month'] !== null) {
            $meta['publication_month'] = (int) $pub['publication_month'];
        }

        if (isset($pub['publication_year']) && $pub['publication_year'] !== '' && $pub['publication_year'] !== null) {
            $ce = (int) $pub['publication_year'];
            if ($ce > 0) {
                $meta['publication_year_be'] = self::yearBeFromCe($ce);
            }
        }

        if (! empty($pub['contributors']) && is_array($pub['contributors'])) {
            $meta['publication_authors'] = self::normalizeContributors($pub['contributors']);
        }

        return $meta;
    }

    /**
     * @param array<string,mixed> $row publications table row
     *
     * @return array<string,mixed>
     */
    public static function decodeBibliographicFromPublicationRow(array $row): array
    {
        $meta = [];
        $raw = $row['metadata'] ?? null;
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $out = self::extractBibliographicFromMetadata($meta);
        foreach (self::BIBLIO_KEYS as $key) {
            if (! isset($out[$key]) && isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                $out[$key] = $row[$key];
            }
        }

        if (! isset($out['publication_month']) && isset($meta['publication_month'])) {
            $out['publication_month'] = (int) $meta['publication_month'];
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $biblio
     */
    public static function applyBibliographicToSyncPayload(array &$payload, array $biblio): void
    {
        foreach (self::BIBLIO_KEYS as $key) {
            if (! array_key_exists($key, $biblio)) {
                continue;
            }
            $val = $biblio[$key];
            if ($val === null || $val === '') {
                continue;
            }
            $payload[$key] = $val;
        }
        if (isset($biblio['publication_month']) && $biblio['publication_month'] !== '') {
            $payload['publication_month'] = (int) $biblio['publication_month'];
        }
    }

    /**
     * @param array<string,mixed> $pub
     *
     * @return array<string,mixed>
     */
    public static function bibliographicForContentHash(array $pub): array
    {
        $out = [];
        foreach (self::BIBLIO_KEYS as $key) {
            if (! array_key_exists($key, $pub)) {
                continue;
            }
            $val = $pub[$key];
            if ($val === null || $val === '') {
                continue;
            }
            $out[$key] = is_string($val) ? mb_strtolower(trim($val)) : $val;
        }
        if (isset($pub['publication_month']) && $pub['publication_month'] !== '' && $pub['publication_month'] !== null) {
            $out['publication_month'] = (int) $pub['publication_month'];
        }

        return $out;
    }

    private static function maxLength(string $key): int
    {
        return match ($key) {
            'abstract', 'keywords', 'notes' => 20000,
            'ref_url' => 2048,
            default => 500,
        };
    }
}
