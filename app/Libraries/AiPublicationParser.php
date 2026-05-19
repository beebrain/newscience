<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\AiCv;

/**
 * แปลงข้อความหรือ URL ไฟล์ (หลังอัปโหลด) เป็น JSON ผลงาน CV
 *
 * **n8n (AI_CV_N8N_URL):** POST JSON `{"url":"https://…/uploads/cv_ai/…"}` แบบ Research Record extract-article-v2
 * หรือ `{"text":"…"}` สำหรับข้อความ/BibTeX
 */
final class AiPublicationParser
{
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
            $org = trim((string) ($pub['journal'] ?? $pub['journalname'] ?? $pub['journal_name'] ?? $pub['publisher'] ?? $pub['venue'] ?? $pub['source'] ?? $pub['conference_name_th'] ?? $pub['conference_name_en'] ?? ''));
        }

        $yearRaw = $pub['publication_year'] ?? $pub['year'] ?? $pub['year_en'] ?? $pub['year_th'] ?? null;
        $y       = is_numeric($yearRaw) ? (int) $yearRaw : null;
        if ($y !== null && ($y < 1900 || $y > 2100)) {
            $y = null;
        }

        $doiNorm = PublicationIdentity::normalizeDoi((string) ($pub['doi'] ?? ''));
        $ptype   = trim((string) ($pub['publication_type'] ?? $pub['type'] ?? ''));
        if ($ptype !== '' && ! RrPublicationType::isValidPublicationTypeCode($ptype)) {
            $ptype = '';
        }

        $desc = trim((string) ($pub['description'] ?? $pub['abstract'] ?? $pub['abstract_th'] ?? $pub['abstract_en'] ?? $pub['notes'] ?? $pub['extra_info'] ?? ''));

        $out = [
            'title'            => mb_substr($title, 0, 500),
            'organization'     => mb_substr($org, 0, 500) ?: null,
            'year'             => $y,
            'doi'              => $doiNorm !== '' ? $doiNorm : null,
            'publication_type' => $ptype !== '' ? $ptype : null,
            'description'      => mb_substr($desc, 0, 20000) ?: null,
        ];

        return ['success' => true, 'publication' => $out];
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

        $pub = self::extractPublicationArrayFromN8nResponse($decoded);
        if ($pub === null) {
            return ['success' => false, 'error' => 'NO_PUBLICATION', 'message' => 'ไม่พบอ็อบเจ็กต์ผลงานใน JSON (คาดหวัง title / title_th / output)'];
        }

        return self::normalizePublicationFromRrLikeArray($pub);
    }

    /**
     * @param array<string,mixed> $decoded
     *
     * @return array<string,mixed>|null
     */
    private static function extractPublicationArrayFromN8nResponse(array $decoded): ?array
    {
        if (isset($decoded['output'])) {
            $out = $decoded['output'];
            if (is_array($out) && $out !== [] && array_keys($out) === range(0, count($out) - 1)) {
                return is_array($out[0]) ? $out[0] : null;
            }
            if (is_array($out)) {
                return $out;
            }
        }

        if ($decoded !== [] && array_keys($decoded) === range(0, count($decoded) - 1)) {
            $first = $decoded[0];

            return is_array($first) ? $first : null;
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
                if (isset($d['title']) || isset($d['title_th']) || isset($d['title_en'])) {
                    return $d;
                }
            }
        }
        if (isset($decoded['title']) || isset($decoded['title_th']) || isset($decoded['title_en']) || ($decoded['source'] ?? '') === 'ai_extraction') {
            return $decoded;
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
