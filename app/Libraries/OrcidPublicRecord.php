<?php

namespace App\Libraries;

use Config\Services;

/**
 * อ่านข้อมูลสาธารณะจาก ORCID Public API v3.0 (ไม่ต้อง OAuth)
 *
 * @see https://info.orcid.org/documentation/api-tutorials/
 */
class OrcidPublicRecord
{
    public const ID_PATTERN = '/^[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{3}[0-9X]$/i';

    public static function isValidId(string $id): bool
    {
        return (bool) preg_match(self::ID_PATTERN, trim($id));
    }

    public static function normalizeId(string $id): string
    {
        return strtoupper(trim($id));
    }

    /**
     * @return array{success:bool,http_code?:int,message?:string,data?:array}
     */
    public static function fetchRecord(string $orcidId): array
    {
        $orcidId = self::normalizeId($orcidId);
        if (!self::isValidId($orcidId)) {
            return ['success' => false, 'message' => 'รูปแบบ ORCID iD ไม่ถูกต้อง'];
        }

        $url = 'https://pub.orcid.org/v3.0/' . rawurlencode($orcidId);

        try {
            $client = Services::curlrequest([
                'timeout'     => 30,
                'http_errors' => false,
            ]);
            $response = $client->get($url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ORCID fetch: ' . $e->getMessage());

            return ['success' => false, 'message' => 'เชื่อมต่อ ORCID ไม่สำเร็จ'];
        }

        $code = (int) $response->getStatusCode();
        $raw  = (string) $response->getBody();
        if ($code === 404) {
            return ['success' => false, 'http_code' => $code, 'message' => 'ไม่พบ ORCID iD นี้'];
        }
        if ($code < 200 || $code >= 300) {
            return ['success' => false, 'http_code' => $code, 'message' => 'ORCID API ตอบกลับผิดปกติ (HTTP ' . $code . ')'];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['success' => false, 'message' => 'อ่านข้อมูล ORCID ไม่ได้'];
        }

        return ['success' => true, 'http_code' => $code, 'data' => $data];
    }

    /**
     * @return array{education: list<array<string,mixed>>, employment: list<array<string,mixed>>}
     */
    public static function extractEducationAndEmployment(array $record): array
    {
        $education  = [];
        $employment = [];

        $eduGroups = $record['activities-summary']['educations']['affiliation-group'] ?? [];
        foreach ($eduGroups as $group) {
            foreach ($group['summaries'] ?? [] as $wrap) {
                $sum = $wrap['education-summary'] ?? null;
                if (is_array($sum)) {
                    $mapped = self::mapAffiliationSummary($sum, 'education');
                    if ($mapped !== null) {
                        $education[] = $mapped;
                    }
                }
            }
        }

        $empGroups = $record['activities-summary']['employments']['affiliation-group'] ?? [];
        foreach ($empGroups as $group) {
            foreach ($group['summaries'] ?? [] as $wrap) {
                $sum = $wrap['employment-summary'] ?? null;
                if (is_array($sum)) {
                    $mapped = self::mapAffiliationSummary($sum, 'employment');
                    if ($mapped !== null) {
                        $employment[] = $mapped;
                    }
                }
            }
        }

        return ['education' => $education, 'employment' => $employment];
    }

    /**
     * ดึงรายการ works จาก activities-summary (หนึ่งรายการต่อ work group — เลือกสรุปที่เหมาะสมที่สุด)
     *
     * @return list<array<string,mixed>>
     */
    public static function extractWorks(array $record): array
    {
        $groups = $record['activities-summary']['works']['group'] ?? [];
        if (!is_array($groups) || $groups === []) {
            return [];
        }

        $out = [];
        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $summaries = $group['work-summary'] ?? [];
            if ($summaries === []) {
                continue;
            }
            if (isset($summaries['put-code'])) {
                $summaries = [$summaries];
            }
            if (!is_array($summaries)) {
                continue;
            }
            $picked = self::pickPrimaryWorkSummary($summaries);
            if ($picked === null) {
                continue;
            }
            $mapped = self::mapWorkSummary($picked);
            if ($mapped !== null) {
                $out[] = $mapped;
            }
        }

        return $out;
    }

    /**
     * @param list<array<string,mixed>> $summaries
     *
     * @return array<string,mixed>|null
     */
    private static function pickPrimaryWorkSummary(array $summaries): ?array
    {
        $best     = null;
        $bestKey  = '';

        foreach ($summaries as $s) {
            if (!is_array($s)) {
                continue;
            }
            $doi = self::extractDoiFromWorkSummary($s);
            $vis = (string) ($s['visibility'] ?? '');
            $sc  = 0;
            if ($vis === 'PUBLIC') {
                $sc += 100;
            }
            if ($doi !== '') {
                $sc += 50;
            }
            $lmRaw = $s['last-modified-date'] ?? null;
            $lm    = is_array($lmRaw) ? self::scalarValue($lmRaw['value'] ?? $lmRaw) : self::scalarValue($lmRaw);
            $ts    = $lm !== '' && strtotime($lm) !== false ? strtotime($lm) : 0;
            $key   = sprintf('%010d-%010d', $sc, $ts);

            if ($best === null || $key > $bestKey) {
                $best    = $s;
                $bestKey = $key;
            }
        }

        return $best;
    }

    /**
     * @param array<string,mixed> $summary
     */
    private static function extractDoiFromWorkSummary(array $summary): string
    {
        foreach (self::normalizeExternalIds($summary) as $ext) {
            if (!is_array($ext)) {
                continue;
            }
            $type = strtolower((string) ($ext['external-id-type'] ?? ''));
            if ($type !== 'doi') {
                continue;
            }
            $val = self::scalarValue($ext['external-id-value'] ?? null);
            if ($val === '') {
                $val = self::scalarValue($ext['external-id-normalized'] ?? null);
            }
            $val = trim($val);
            if ($val !== '') {
                return $val;
            }
        }

        return '';
    }

    /**
     * @param array<string,mixed> $summary
     *
     * @return list<array<string,mixed>>
     */
    private static function normalizeExternalIds(array $summary): array
    {
        $raw = $summary['external-ids']['external-id'] ?? [];
        if ($raw === []) {
            return [];
        }
        if (isset($raw['external-id-type'])) {
            return [$raw];
        }
        if (!is_array($raw)) {
            return [];
        }

        return $raw;
    }

    /**
     * @param array<string,mixed> $summary
     *
     * @return array<string,mixed>|null
     */
    private static function mapWorkSummary(array $summary): ?array
    {
        $titleNode = $summary['title'] ?? [];
        $title     = '';
        if (is_array($titleNode)) {
            $title = self::scalarValue($titleNode['title']['value'] ?? $titleNode['title'] ?? $titleNode['value'] ?? '');
            if ($title === '' && isset($titleNode['translated-title']) && is_array($titleNode['translated-title'])) {
                $title = self::scalarValue($titleNode['translated-title']['value'] ?? $titleNode['translated-title']);
            }
        }
        $title = trim($title);
        if ($title === '') {
            return null;
        }

        $journal   = self::deepTitleValue($summary['journal-title'] ?? null);
        $publisher = self::deepTitleValue($summary['publisher'] ?? null);
        $org       = $journal !== '' ? $journal : $publisher;

        $pubDate = $summary['publication-date'] ?? null;
        $start   = self::formatMysqlDate($pubDate);

        $putCode = $summary['put-code'] ?? $summary['put_code'] ?? null;
        $type    = (string) ($summary['type'] ?? '');

        $urlFromSummary = '';
        if (isset($summary['url']) && is_array($summary['url'])) {
            $urlFromSummary = self::scalarValue($summary['url']['value'] ?? $summary['url']);
        }

        $doi    = self::extractDoiFromWorkSummary($summary);
        $doiUrl = $doi !== '' ? 'https://doi.org/' . rawurlencode($doi) : '';

        $url = $urlFromSummary !== '' ? $urlFromSummary : $doiUrl;

        $shortDesc = '';
        if (isset($summary['short-description']) && is_array($summary['short-description'])) {
            $shortDesc = self::scalarValue($summary['short-description']['value'] ?? $summary['short-description']);
        }

        $descParts = array_filter([
            $type !== '' ? 'ประเภท: ' . $type : '',
            $shortDesc !== '' ? $shortDesc : '',
        ]);
        $description = implode("\n", $descParts);

        $dedupe = sha1($title . '|' . $org . '|' . ($start ?? '') . '|' . $type);

        return [
            'put_code'     => $putCode,
            'title'        => $title,
            'organization' => $org,
            'location'     => null,
            'start_date'   => $start,
            'end_date'     => null,
            'department'   => '',
            'description'  => $description !== '' ? $description : null,
            'is_current'   => 0,
            'orcid_meta'   => array_filter([
                'orcid_activity' => 'work',
                'work_type'      => $type !== '' ? $type : null,
                'doi'            => $doi !== '' ? $doi : null,
                'url'            => $url !== '' ? $url : null,
                'orcid_dedupe_key' => $putCode === null || $putCode === '' ? $dedupe : null,
            ]),
        ];
    }

    /**
     * @param mixed $node
     */
    private static function deepTitleValue($node): string
    {
        if ($node === null) {
            return '';
        }
        if (is_string($node) || is_numeric($node)) {
            return trim((string) $node);
        }
        if (!is_array($node)) {
            return '';
        }
        if (isset($node['value'])) {
            return trim((string) $node['value']);
        }

        return '';
    }

    /**
     * @param array<string,mixed> $sum
     *
     * @return array<string,mixed>|null
     */
    private static function mapAffiliationSummary(array $sum, string $kind): ?array
    {
        $org  = $sum['organization'] ?? [];
        $name = self::scalarValue($org['name'] ?? '');
        if ($name === '' && ($sum['role-title'] ?? '') === '') {
            return null;
        }

        $addr       = $org['address'] ?? [];
        $city       = self::scalarValue($addr['city'] ?? '');
        $region     = self::scalarValue($addr['region'] ?? '');
        $country    = self::scalarValue($addr['country'] ?? '');
        $locParts   = array_filter([$city, $region, $country], static fn ($x) => $x !== '');
        $location   = implode(', ', $locParts);
        $roleTitle  = self::scalarValue($sum['role-title'] ?? '');
        $department = self::scalarValue($sum['department-name'] ?? '');
        $putCode    = $sum['put-code'] ?? $sum['put_code'] ?? null;
        $start      = $sum['start-date'] ?? null;
        $end        = $sum['end-date'] ?? null;

        $title = $roleTitle !== '' ? $roleTitle : ($kind === 'education' ? 'การศึกษา' : 'ตำแหน่ง');

        return [
            'put_code'       => $putCode,
            'title'          => $title,
            'role_title'     => $roleTitle,
            'organization'   => $name,
            'location'       => $location,
            'department'     => $department,
            'start_date'     => self::formatMysqlDate($start),
            'end_date'       => self::formatMysqlDate($end),
        ];
    }

    /**
     * @param mixed $v
     */
    private static function scalarValue($v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_string($v) || is_numeric($v)) {
            return trim((string) $v);
        }
        if (is_array($v) && isset($v['value'])) {
            return trim((string) $v['value']);
        }

        return '';
    }

    /**
     * @param mixed $input
     */
    public static function formatMysqlDate($input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }
        if (is_numeric($input)) {
            return sprintf('%04d-01-01', (int) $input);
        }
        if (is_string($input) && preg_match('/^\d{4}-\d{2}-\d{2}/', $input)) {
            return substr($input, 0, 10);
        }
        if (is_array($input)) {
            $y = self::scalarValue($input['year'] ?? null);
            if ($y === '') {
                return null;
            }
            $m = self::scalarValue($input['month'] ?? null) ?: '01';
            $d = self::scalarValue($input['day'] ?? null) ?: '01';

            return sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
        }

        return null;
    }
}
