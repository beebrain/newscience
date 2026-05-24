<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\AiCv;

/**
 * แปลงข้อความหรือ URL ไฟล์ (หลังอัปโหลด) เป็น JSON ผลงาน CV
 *
 * **n8n (AI_CV_N8N_URL):** POST JSON `{"url":"https://…/index.php/cv-ai/file/…"}` แบบ Research Record extract-article-v2
 * หรือ `{"text":"…"}` สำหรับข้อความ/BibTeX
 */
final class AiPublicationParser
{
    /**
     * แปลง JSON ดิบจาก n8n เป็น publication เดียว (ลำดับ parse ตาม Research Record normalizeAIResponsePayload)
     *
     * @return array{success:bool,publication?:array<string,mixed>,message?:string,error?:string}
     */
    public static function parseN8nResponse(array $decoded): array
    {
        $pub = self::extractPublicationArrayFromN8nResponse($decoded);
        if ($pub === null) {
            return ['success' => false, 'error' => 'NO_PUBLICATION', 'message' => 'ไม่พบอ็อบเจ็กต์ผลงานใน JSON (คาดหวัง title / title_th / output)'];
        }

        return self::normalizePublicationFromRrLikeArray($pub);
    }

    /**
     * @return array{success:bool,publication?:array<string,mixed>,message?:string,error?:string}
     */
    public static function normalizePublicationFromRrLikeArray(array $pub): array
    {
        $title = trim((string) ($pub['title'] ?? ''));
        if ($title === '') {
            $title = trim((string) ($pub['title_th'] ?? ''));
        }
        if ($title === '') {
            $title = trim((string) ($pub['title_en'] ?? ''));
        }
        if ($title === '') {
            return ['success' => false, 'error' => 'NO_TITLE', 'message' => 'ไม่มีชื่อผลงานใน JSON'];
        }

        $org = trim((string) ($pub['organization'] ?? ''));
        if ($org === '') {
            $org = trim((string) (
                $pub['journal'] ?? $pub['journalname'] ?? $pub['journal_name'] ?? ''
                ?: $pub['conference_name_th'] ?? $pub['conference_name_en'] ?? ''
                ?: $pub['publisher_th'] ?? $pub['publisher_en'] ?? $pub['publisher'] ?? ''
                ?: $pub['book_title_th'] ?? $pub['book_title_en'] ?? ''
                ?: $pub['venue'] ?? ''
            ));
        }
        if ($org === '') {
            $sourceRaw = trim((string) ($pub['source'] ?? ''));
            if ($sourceRaw !== '' && $sourceRaw !== 'ai_extraction') {
                $org = $sourceRaw;
            }
        }

        $yearRaw = $pub['publication_year'] ?? $pub['year'] ?? $pub['year_en'] ?? $pub['year_th'] ?? null;
        $y       = is_numeric($yearRaw) ? (int) $yearRaw : null;
        if ($y !== null && $y >= 2400) {
            $y -= 543;
        }
        if ($y !== null && ($y < 1900 || $y > 2100)) {
            $y = null;
        }

        $month = self::resolvePublicationMonth($pub);

        $doiNorm = PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? ''));
        $ptype   = self::normalizeAiPublicationType((string) ($pub['publication_type'] ?? $pub['type'] ?? ''));

        $loc = trim((string) ($pub['location'] ?? $pub['place'] ?? $pub['city'] ?? ''));
        if ($loc === '') {
            $loc = trim((string) (
                $pub['conference_location_th'] ?? $pub['conference_location_en'] ?? ''
                ?: $pub['conference_place'] ?? $pub['conference_location'] ?? $pub['country'] ?? ''
            ));
        }

        $url = trim((string) ($pub['url'] ?? $pub['link'] ?? $pub['article_url'] ?? $pub['source_url'] ?? $pub['file_link'] ?? ''));
        if ($url === '' && $doiNorm !== '') {
            $url = 'https://doi.org/' . $doiNorm;
        }

        $rrIdRaw = $pub['rr_publication_id'] ?? $pub['publication_id'] ?? null;
        if ($rrIdRaw === null && isset($pub['id']) && is_numeric($pub['id']) && (int) $pub['id'] > 0) {
            $rrIdRaw = $pub['id'];
        }
        $rrId = is_numeric($rrIdRaw) && (int) $rrIdRaw > 0 ? (int) $rrIdRaw : null;

        $desc = trim((string) ($pub['description'] ?? $pub['abstract'] ?? $pub['abstract_th'] ?? $pub['abstract_en'] ?? $pub['notes'] ?? $pub['extra_info'] ?? ''));
        $authors = self::extractStructuredContributors($pub);
        if ($authors === []) {
            $authorsLine = self::formatAuthorsLine($pub);
            if ($authorsLine !== '' && $desc !== '') {
                $desc = $authorsLine . "\n\n" . $desc;
            } elseif ($authorsLine !== '') {
                $desc = $authorsLine;
            }
        }
        $desc = self::appendBibliographicDetails($pub, $desc);

        [$startDate, $endDate] = self::resolvePublicationDates($y, $month);

        $out = [
            'title'             => mb_substr($title, 0, 500),
            'organization'      => mb_substr($org, 0, 500) ?: null,
            'location'          => mb_substr($loc, 0, 500) ?: null,
            'year'              => $y,
            'month'             => $month,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'doi'               => $doiNorm !== '' ? $doiNorm : null,
            'url'               => $url !== '' ? mb_substr($url, 0, 2048) : null,
            'publication_type'  => $ptype !== '' ? $ptype : null,
            'rr_publication_id' => $rrId,
            'description'       => mb_substr($desc, 0, 20000) ?: null,
            'authors'           => $authors,
        ];

        return ['success' => true, 'publication' => $out];
    }

    /**
     * @param array<string, mixed> $pub
     *
     * @return list<array<string,mixed>>
     */
    private static function extractStructuredContributors(array $pub): array
    {
        $rows = [];

        $authorsTh = $pub['authors_th'] ?? null;
        $authorsEn = $pub['authors_en'] ?? null;
        if (is_array($authorsTh) || is_array($authorsEn)) {
            $thList = is_array($authorsTh) ? $authorsTh : [];
            $enList = is_array($authorsEn) ? $authorsEn : [];
            $max    = max(count($thList), count($enList));
            for ($i = 0; $i < $max; $i++) {
                $th = isset($thList[$i]) ? trim((string) $thList[$i]) : '';
                $en = isset($enList[$i]) ? trim((string) $enList[$i]) : '';
                $name = $th !== '' ? $th : $en;
                if ($name === '') {
                    continue;
                }
                if ($th !== '' && $en !== '') {
                    $name = $th . ' (' . $en . ')';
                }
                $rows[] = ['name' => $name, 'order' => count($rows) + 1];
            }
        }

        $hasThEnRows = $rows !== [];
        $authors     = $pub['authors'] ?? $pub['author'] ?? null;
        if (is_string($authors)) {
            if (! $hasThEnRows) {
                foreach (preg_split('/[;,]\s*/u', $authors) ?: [] as $name) {
                    $name = trim($name);
                    if ($name !== '') {
                        $rows[] = ['name' => $name, 'order' => count($rows) + 1];
                    }
                }
            }
        } elseif (is_array($authors)) {
            foreach ($authors as $author) {
                if (is_string($author)) {
                    if ($hasThEnRows) {
                        continue;
                    }
                    $name = trim($author);
                    if ($name !== '') {
                        $rows[] = ['name' => $name, 'order' => count($rows) + 1];
                    }
                    continue;
                }
                if (! is_array($author)) {
                    continue;
                }

                $name = trim((string) ($author['name'] ?? $author['full_name'] ?? $author['display_name'] ?? $author['thai_name'] ?? $author['english_name'] ?? ''));
                $email = trim((string) ($author['email'] ?? $author['author_email'] ?? $author['mail'] ?? ''));
                $affiliation = trim((string) ($author['affiliation'] ?? $author['organization'] ?? $author['department'] ?? ''));
                if ($name === '' && $email === '') {
                    continue;
                }
                $rows[] = [
                    'name'          => $name,
                    'email'         => $email,
                    'affiliation'   => $affiliation,
                    'corresponding' => (int) ($author['corresponding'] ?? $author['is_corresponding'] ?? 0),
                    'order'         => (int) ($author['order'] ?? count($rows) + 1),
                ];
            }
        }

        return PublicationResearchFields::normalizeContributors($rows);
    }

    /**
     * @param array<string, mixed> $pub
     */
    private static function formatAuthorsLine(array $pub): string
    {
        $authorsTh = $pub['authors_th'] ?? null;
        $authorsEn = $pub['authors_en'] ?? null;
        if (is_array($authorsTh) || is_array($authorsEn)) {
            $thList = is_array($authorsTh) ? $authorsTh : [];
            $enList = is_array($authorsEn) ? $authorsEn : [];
            $max    = max(count($thList), count($enList));
            $parts  = [];
            for ($i = 0; $i < $max; $i++) {
                $th = isset($thList[$i]) ? trim((string) $thList[$i]) : '';
                $en = isset($enList[$i]) ? trim((string) $enList[$i]) : '';
                if ($th !== '' && $en !== '') {
                    $parts[] = $th . ' (' . $en . ')';
                } elseif ($th !== '') {
                    $parts[] = $th;
                } elseif ($en !== '') {
                    $parts[] = $en;
                }
            }

            return $parts !== [] ? 'ผู้แต่ง: ' . mb_substr(implode('; ', $parts), 0, 2000) : '';
        }

        $authors = $pub['authors'] ?? $pub['author'] ?? null;
        if (is_string($authors)) {
            $authors = trim($authors);

            return $authors !== '' ? 'ผู้แต่ง: ' . mb_substr($authors, 0, 2000) : '';
        }
        if (! is_array($authors) || $authors === []) {
            return '';
        }
        $parts = [];
        foreach ($authors as $a) {
            if (is_string($a) && trim($a) !== '') {
                $parts[] = trim($a);
            } elseif (is_array($a)) {
                $name = trim((string) ($a['name'] ?? $a['full_name'] ?? $a['display_name'] ?? $a['thai_name'] ?? $a['english_name'] ?? ''));
                if ($name !== '') {
                    $parts[] = $name;
                }
            }
        }

        return $parts !== [] ? 'ผู้แต่ง: ' . mb_substr(implode('; ', $parts), 0, 2000) : '';
    }

    /**
     * @param array<string, mixed> $pub
     */
    private static function appendBibliographicDetails(array $pub, string $desc): string
    {
        $lines = [];
        $volume = trim((string) ($pub['volume'] ?? ''));
        if ($volume !== '') {
            $lines[] = 'เล่ม (Volume): ' . $volume;
        }
        $issue = trim((string) ($pub['issue'] ?? ''));
        if ($issue !== '') {
            $lines[] = 'ฉบับ (Issue): ' . $issue;
        }
        $pages = trim((string) ($pub['pages'] ?? ''));
        if ($pages !== '') {
            $lines[] = 'หน้า: ' . $pages;
        }
        $isbn = trim((string) ($pub['isbn'] ?? ''));
        if ($isbn !== '') {
            $lines[] = 'ISBN: ' . $isbn;
        }
        $kwLine = self::formatKeywordsLine($pub);
        if ($kwLine !== '') {
            $lines[] = $kwLine;
        }
        if ($lines === []) {
            return $desc;
        }
        $block = implode("\n", $lines);

        return $desc !== '' ? $desc . "\n\n" . $block : $block;
    }

    /**
     * @param array<string, mixed> $pub
     */
    private static function formatKeywordsLine(array $pub): string
    {
        $kw = $pub['keywords'] ?? $pub['keywords_th'] ?? $pub['keywords_en'] ?? null;
        if (is_string($kw)) {
            $kw = trim($kw);

            return $kw !== '' ? 'คำสำคัญ: ' . mb_substr($kw, 0, 1000) : '';
        }
        if (! is_array($kw) || $kw === []) {
            return '';
        }
        $parts = [];
        foreach ($kw as $item) {
            if (is_string($item) && trim($item) !== '') {
                $parts[] = trim($item);
            }
        }

        return $parts !== [] ? 'คำสำคัญ: ' . mb_substr(implode(', ', $parts), 0, 1000) : '';
    }

    /**
     * @param array<string, mixed> $pub
     */
    private static function resolvePublicationMonth(array $pub): ?int
    {
        $candidates = [
            $pub['month_en'] ?? null,
            $pub['month_th'] ?? null,
            $pub['month'] ?? null,
            $pub['publication_month'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }
            $raw = trim((string) $candidate);
            if ($raw === '') {
                continue;
            }
            if (preg_match('/^\d{1,2}$/', $raw)) {
                $m = (int) $raw;
                if ($m >= 1 && $m <= 12) {
                    return $m;
                }
            }
            $mapped = self::mapMonthNameToNumber(mb_strtolower($raw));
            if ($mapped !== null) {
                return $mapped;
            }
        }

        return null;
    }

    private static function mapMonthNameToNumber(string $lower): ?int
    {
        static $map = [
            'january' => 1, 'jan' => 1, 'february' => 2, 'feb' => 2, 'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4, 'may' => 5, 'june' => 6, 'jun' => 6, 'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8, 'september' => 9, 'sep' => 9, 'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11, 'december' => 12, 'dec' => 12,
            'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'ก.พ.' => 2, 'มีนาคม' => 3, 'มี.ค.' => 3,
            'เมษายน' => 4, 'เม.ย.' => 4, 'พฤษภาคม' => 5, 'พ.ค.' => 5, 'มิถุนายน' => 6, 'มิ.ย.' => 6,
            'กรกฎาคม' => 7, 'ก.ค.' => 7, 'สิงหาคม' => 8, 'ส.ค.' => 8, 'กันยายน' => 9, 'ก.ย.' => 9,
            'ตุลาคม' => 10, 'ต.ค.' => 10, 'พฤศจิกายน' => 11, 'พ.ย.' => 11, 'ธันวาคม' => 12, 'ธ.ค.' => 12,
        ];

        return $map[$lower] ?? null;
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private static function resolvePublicationDates(?int $year, ?int $month): array
    {
        if ($year === null) {
            return [null, null];
        }
        if ($month !== null && $month >= 1 && $month <= 12) {
            $start = sprintf('%04d-%02d-01', $year, $month);
            $end   = date('Y-m-t', strtotime($start));

            return [$start, $end];
        }

        return [sprintf('%04d-01-01', $year), sprintf('%04d-12-31', $year)];
    }

    private static function normalizeAiPublicationType(string $ptype): string
    {
        $ptype = strtolower(trim($ptype));
        if ($ptype === '') {
            return '';
        }
        if ($ptype === 'proceedings') {
            $ptype = 'conference';
        }
        if (! RrPublicationType::isValidPublicationTypeCode($ptype)) {
            return '';
        }

        return $ptype;
    }

    /**
     * @return array{success:bool,publication?:array<string,mixed>,message?:string,error?:string}
     */
    public static function parseFromUrl(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return ['success' => false, 'error' => 'EMPTY', 'message' => 'ไม่มี URL สำหรับวิเคราะห์'];
        }
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'BAD_URL', 'message' => 'URL ไม่ถูกต้อง'];
        }
        $blocked = self::validateUrlReachableByN8n($url);
        if ($blocked !== null) {
            return ['success' => false, 'error' => 'BAD_URL', 'message' => $blocked];
        }

        $cfg = config(AiCv::class);
        if (! $cfg->isReady()) {
            return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'ยังไม่ได้ตั้งค่า AI (AI_CV_N8N_URL)'];
        }
        if ($cfg->usesN8n()) {
            return self::parseViaN8nWebhook($cfg, ['url' => $url]);
        }

        return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'การวิเคราะห์จาก URL ต้องตั้ง AI_CV_N8N_URL'];
    }

    /**
     * @return array{success:bool,publication?:array<string,mixed>,message?:string,error?:string}
     */
    public static function parseFromText(string $rawText): array
    {
        $cfg = config(AiCv::class);
        if (! $cfg->isReady()) {
            return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'ยังไม่ได้ตั้งค่า AI (ตั้ง AI_CV_N8N_URL หรือ AI_CV_API_URL + AI_CV_API_KEY)'];
        }

        $text = trim($rawText);
        if ($text === '') {
            return ['success' => false, 'error' => 'EMPTY', 'message' => 'กรุณาวางข้อความหรืออัปโหลดไฟล์'];
        }
        if (mb_strlen($text) > $cfg->maxInputChars) {
            return ['success' => false, 'error' => 'TOO_LONG', 'message' => 'ข้อความยาวเกิน ' . $cfg->maxInputChars . ' ตัวอักษร'];
        }

        if ($cfg->usesN8n()) {
            return self::parseViaN8nWebhook($cfg, ['text' => $text]);
        }

        return self::parseViaOpenAiCompatible($cfg, $text);
    }

    /** @deprecated use parseFromText */
    public static function parseSinglePublication(string $rawText): array
    {
        return self::parseFromText($rawText);
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array{success:bool,publication?:array<string,mixed>,message?:string,error?:string}
     */
    private static function parseViaN8nWebhook(AiCv $cfg, array $payload): array
    {
        $url  = $cfg->n8nUrl;
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
        if ($cfg->n8nBearerToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $cfg->n8nBearerToken;
        }

        $timeout = max($cfg->timeoutSeconds, 90);

        try {
            $resp = HttpTransport::post(
                $url,
                ['timeout' => $timeout, 'http_errors' => false],
                ['headers' => $headers, 'body' => $body]
            );
        } catch (\Throwable $e) {
            log_message('error', 'AiPublicationParser n8n: ' . $e->getMessage());

            return ['success' => false, 'error' => 'REQUEST_FAILED', 'message' => 'เรียก n8n ไม่สำเร็จ'];
        }

        $code = (int) $resp->getStatusCode();
        $raw  = (string) $resp->getBody();
        if ($code < 200 || $code >= 300) {
            log_message('error', 'AiPublicationParser n8n HTTP ' . $code . ' ' . mb_substr($raw, 0, 500));

            return ['success' => false, 'error' => 'API_ERROR', 'message' => 'n8n ตอบผิดพลาด (HTTP ' . $code . ')'];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['success' => false, 'error' => 'BAD_RESPONSE', 'message' => 'รูปแบบ JSON จาก n8n ไม่ถูกต้อง'];
        }

        return self::parseN8nResponse($decoded);
    }

    /**
     * ลำดับเดียวกับ Research Record publication-ai.js normalizeAIResponsePayload
     *
     * @param array<string,mixed> $decoded
     *
     * @return array<string,mixed>|null
     */
    private static function extractPublicationArrayFromN8nResponse(array $decoded): ?array
    {
        if ($decoded !== [] && array_keys($decoded) === range(0, count($decoded) - 1)) {
            $first = $decoded[0];

            return is_array($first) ? $first : null;
        }

        if (isset($decoded['output'])) {
            $out = $decoded['output'];
            if (is_array($out) && $out !== [] && array_keys($out) === range(0, count($out) - 1)) {
                return is_array($out[0]) ? $out[0] : null;
            }
            if (is_array($out)) {
                return $out;
            }
        }

        if (isset($decoded['publications']) && is_array($decoded['publications'])) {
            $list = $decoded['publications'];
            if ($list !== [] && isset($list[0]) && is_array($list[0])) {
                return $list[0];
            }
        }
        if (isset($decoded['publication']) && is_array($decoded['publication'])) {
            return $decoded['publication'];
        }
        if (isset($decoded['data'])) {
            $d = $decoded['data'];
            if (is_string($d)) {
                $inner = json_decode($d, true);
                if (is_array($inner)) {
                    return self::extractPublicationArrayFromN8nResponse($inner);
                }

                return null;
            }
            if (is_array($d)) {
                if ($d !== [] && array_keys($d) === range(0, count($d) - 1) && isset($d[0]) && is_array($d[0])) {
                    return $d[0];
                }
                if (isset($d['title']) || isset($d['title_th']) || isset($d['title_en']) || ($d['source'] ?? '') === 'ai_extraction') {
                    return $d;
                }
            }
        }
        if (isset($decoded['title']) || isset($decoded['title_th']) || isset($decoded['title_en']) || ($decoded['source'] ?? '') === 'ai_extraction') {
            return $decoded;
        }

        return null;
    }

    private static function validateUrlReachableByN8n(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return 'ไม่สามารถใช้ URL localhost ได้ — AI ไม่เข้าถึงได้จากภายนอก กรุณาอัปโหลดไฟล์แทน';
        }

        return null;
    }

    /**
     * @return array{success:bool,publication?:array<string,mixed>,message?:string,error?:string}
     */
    private static function parseViaOpenAiCompatible(AiCv $cfg, string $text): array
    {
        $system = <<<'PROMPT'
You extract ONE academic publication from user text (Thai or English, BibTeX, APA-style line, or messy paste).
Reply with ONLY a single JSON object, no markdown fences, no commentary. Keys:
- title (string, required)
- organization (string, journal or publisher name, may be empty)
- year (integer 1900-2100 or null)
- doi (string, normalized bare DOI without https://doi.org/ prefix, or empty string)
- publication_type (string: one of journal, conference, book, book_chapter, patent, thesis, other — or empty if unknown)
- description (string, short optional abstract or notes, may be empty)
If multiple works appear, extract only the first/clearest one.
PROMPT;

        $url = $cfg->apiUrl . '/chat/completions';
        $body = json_encode([
            'model'       => $cfg->model,
            'temperature' => 0.2,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $text],
            ],
        ], JSON_UNESCAPED_UNICODE);

        try {
            $resp = HttpTransport::post(
                $url,
                ['timeout' => $cfg->timeoutSeconds, 'http_errors' => false],
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $cfg->apiKey,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'body' => $body,
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'AiPublicationParser: ' . $e->getMessage());

            return ['success' => false, 'error' => 'REQUEST_FAILED', 'message' => 'เรียก AI ไม่สำเร็จ'];
        }

        $code = (int) $resp->getStatusCode();
        $raw  = (string) $resp->getBody();
        if ($code < 200 || $code >= 300) {
            log_message('error', 'AiPublicationParser HTTP ' . $code . ' ' . mb_substr($raw, 0, 500));

            return ['success' => false, 'error' => 'API_ERROR', 'message' => 'ผู้ให้บริการ AI ตอบผิดพลาด'];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['success' => false, 'error' => 'BAD_RESPONSE', 'message' => 'รูปแบบตอบกลับไม่ถูกต้อง'];
        }
        $content = $decoded['choices'][0]['message']['content'] ?? '';
        if (! is_string($content)) {
            return ['success' => false, 'error' => 'BAD_RESPONSE', 'message' => 'ไม่มีเนื้อหาจาก AI'];
        }

        $content = trim($content);
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/m', $content, $m)) {
            $content = trim($m[1]);
        }

        $pub = json_decode($content, true);
        if (! is_array($pub)) {
            return ['success' => false, 'error' => 'NOT_JSON', 'message' => 'AI ไม่ได้ส่ง JSON ที่อ่านได้'];
        }

        return self::normalizePublicationFromRrLikeArray($pub);
    }
}
