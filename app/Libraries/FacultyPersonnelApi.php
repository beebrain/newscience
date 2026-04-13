<?php

namespace App\Libraries;

use Config\ResearchApi;

/**
 * Fetches faculty personnel from the external Research Record API.
 * Used by FacultyPersonnelController (JSON response) and Pages::personnel() (view data).
 *
 * ฝั่ง newScience ที่ต้องจับคู่กับแถว personnel ควรใช้ **อีเมล** จากแต่ละแถวเป็นตัวเชื่อมหลัก
 * (ไม่ใช้ uid/login เป็นตัวตัดสินว่าเป็นคนเดียวกัน) — ดู skill login-email-primary-key
 */
class FacultyPersonnelApi
{
    /** ครั้งล่าสุดที่เรียก fetch() แล้วไม่สำเร็จ (ให้ Controller/CLI อ่านข้อความจากกบศ) */
    private static ?array $lastFetchFailure = null;

    /**
     * @return array{http_code: int, query: array<string, scalar>, decoded: ?array<string, mixed>, body_snippet: string}|null
     */
    public static function getLastFetchFailure(): ?array
    {
        return self::$lastFetchFailure;
    }

    /**
     * แปลง error จาก JSON กบศเป็นข้อความภาษาไทยสำหรับผู้ดูแลระบบ
     *
     * @param array<string, mixed>|null $decoded
     *
     * @return array{code: string, message_th: string, message_en: string}
     */
    public static function explainFacultyApiBody(?array $decoded): array
    {
        $code = is_array($decoded) ? trim((string) ($decoded['error'] ?? '')) : '';
        $en   = is_array($decoded) ? trim((string) ($decoded['message'] ?? '')) : '';

        if ($code === 'FACULTY_NOT_FOUND') {
            return [
                'code'       => $code,
                'message_th' => 'ระบบกบศไม่พบคณะที่ส่งไป (FACULTY_NOT_FOUND) — ตั้ง RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE ใน .env ให้ตรงกับรหัสคณะใน Research Record',
                'message_en' => $en,
            ];
        }

        if ($code === 'MISSING_PARAMETER' || str_contains(strtolower($en), 'faculty')) {
            return [
                'code'       => $code !== '' ? $code : 'MISSING_PARAMETER',
                'message_th' => 'กบศต้องการรหัสคณะ (faculty_id หรือ faculty_code) — ตั้ง RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE ใน .env'
                    . ($en !== '' ? ' — ' . $en : ''),
                'message_en' => $en,
            ];
        }

        return [
            'code'       => $code !== '' ? $code : 'EXTERNAL_API_ERROR',
            'message_th' => $en !== '' ? ('กบศตอบ: ' . $en) : 'เรียก API faculty-personnel ไม่สำเร็จ',
            'message_en' => $en,
        ];
    }

    /**
     * ดึงรายการบุคลากรจาก payload หลายรูปแบบ (บางเวอร์ชัน API ใช้คีย์อื่นนอกจาก personnel)
     *
     * @param array<string, mixed> $data
     *
     * @return list<array<string, mixed>>
     */
    public static function normalizePersonnelListFromPayload(array $data): array
    {
        foreach (['personnel', 'staff', 'teachers', 'items', 'rows', 'members'] as $key) {
            if (! empty($data[$key]) && is_array($data[$key])) {
                return array_values(array_filter($data[$key], static fn ($row) => is_array($row)));
            }
        }

        if (! empty($data['data']) && is_array($data['data'])) {
            $inner = $data['data'];
            foreach (['personnel', 'staff', 'teachers', 'items', 'rows'] as $key) {
                if (! empty($inner[$key]) && is_array($inner[$key])) {
                    return array_values(array_filter($inner[$key], static fn ($row) => is_array($row)));
                }
            }
            if ($inner !== [] && array_is_list($inner)) {
                return array_values(array_filter($inner, static fn ($row) => is_array($row)));
            }
        }

        return [];
    }

    /**
     * Fetch faculty personnel from the external API.
     *
     * @return array|null Decoded response with 'success', 'faculty', 'personnel', 'total', 'retrieved_at'; null if not configured or request failed
     */
    public static function fetch(): ?array
    {
        self::$lastFetchFailure = null;

        $researchApi = config(ResearchApi::class);

        if (! $researchApi->isConfigured()) {
            return null;
        }

        $query = [];
        if ($researchApi->facultyId !== null) {
            $query['faculty_id'] = $researchApi->facultyId;
        } else {
            $query['faculty_code'] = $researchApi->facultyCode;
        }

        $url = $researchApi->baseUrl . '/api/public/faculty-personnel?' . http_build_query($query);

        try {
            $response = HttpTransport::get($url, ['timeout' => 10], [
                'headers' => [
                    'X-API-KEY' => $researchApi->apiKey,
                    'Accept'    => 'application/json',
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'FacultyPersonnelApi::fetch HTTP error: ' . $e->getMessage());
            self::$lastFetchFailure = [
                'http_code'    => 0,
                'query'        => $query,
                'decoded'      => null,
                'body_snippet' => $e->getMessage(),
            ];

            return null;
        }

        $statusCode = $response->getStatusCode();
        $body       = $response->getBody();
        $data       = json_decode($body, true);

        $failure = [
            'http_code'    => $statusCode,
            'query'        => $query,
            'decoded'      => is_array($data) ? $data : null,
            'body_snippet' => mb_substr(trim($body), 0, 500),
        ];

        if ($statusCode >= 200 && $statusCode < 300 && is_array($data) && ! empty($data['success'])) {
            $data['personnel'] = self::normalizePersonnelListFromPayload($data);

            return $data;
        }

        self::$lastFetchFailure = $failure;
        if (is_array($data) && ! empty($data['error'])) {
            log_message(
                'warning',
                'FacultyPersonnelApi::fetch ' . (string) ($data['error'] ?? '') . ': ' . (string) ($data['message'] ?? '')
            );
        }

        return null;
    }

    /**
     * ดึงแบบมีรายละเอียดสำหรับ CLI / debug — ไม่ throw
     *
     * @return array{ok: bool, http_code: int, url: string, message: string, data: ?array<string, mixed>, body_preview: string}
     */
    public static function fetchWithDiagnostics(): array
    {
        $researchApi = config(ResearchApi::class);
        $out         = [
            'ok'           => false,
            'http_code'    => 0,
            'url'          => '',
            'message'      => '',
            'data'         => null,
            'body_preview' => '',
        ];

        if (! $researchApi->syncConfigured()) {
            $out['message'] = 'ตั้ง RESEARCH_API_BASE_URL และ RESEARCH_API_KEY ไม่ครบ';

            return $out;
        }
        if (! $researchApi->isConfigured()) {
            if (! $researchApi->syncConfigured()) {
                $out['message'] = 'ตั้ง RESEARCH_API_BASE_URL และ RESEARCH_API_KEY ไม่ครบ';
            } else {
                $out['message'] = 'ตั้ง RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE ใน .env';
            }

            return $out;
        }

        $query = [];
        if ($researchApi->facultyId !== null) {
            $query['faculty_id'] = $researchApi->facultyId;
        } else {
            $query['faculty_code'] = $researchApi->facultyCode;
        }

        $out['url'] = $researchApi->baseUrl . '/api/public/faculty-personnel?' . http_build_query($query);

        try {
            $response = HttpTransport::get($out['url'], ['timeout' => 15], [
                'headers' => [
                    'X-API-KEY' => $researchApi->apiKey,
                    'Accept'    => 'application/json',
                ],
            ]);
        } catch (\Throwable $e) {
            $out['message'] = 'HTTP: ' . $e->getMessage();

            return $out;
        }

        $out['http_code']    = (int) $response->getStatusCode();
        $body                = (string) $response->getBody();
        $out['body_preview'] = mb_substr(trim($body), 0, 400);

        $data = json_decode($body, true);
        if (! is_array($data)) {
            $out['message'] = 'ตอบกลับไม่ใช่ JSON (HTTP ' . $out['http_code'] . ')';

            return $out;
        }

        $out['data'] = $data;
        if (empty($data['success'])) {
            $expl             = self::explainFacultyApiBody($data);
            $out['message']   = $expl['message_th'];
            $out['api_error'] = $expl['code'];

            return $out;
        }

        $list            = self::normalizePersonnelListFromPayload($data);
        $data['personnel'] = $list;
        $out['data']      = $data;
        if ($list === []) {
            $out['message'] = 'สำเร็จแต่ไม่พบรายการบุคลากรในคีย์ที่รองรับ (personnel/staff/...)';

            return $out;
        }

        $out['ok']      = true;
        $out['message'] = 'OK, แถวบุคลากร ' . count($list) . ' รายการ';

        return $out;
    }

    /**
     * Fetch and group personnel by curriculum.
     *
     * @return array{groups: array<string, array>, faculty: array|null, total: int} ['groups' => [ 'หลักสูตร X' => [ person, ... ], ... ], 'faculty' => ..., 'total' => N ]
     */
    public static function fetchGroupedByCurriculum(): array
    {
        $data = self::fetch();

        $result = [
            'groups' => [],
            'faculty' => null,
            'total'   => 0,
        ];

        if ($data === null || empty($data['personnel']) || ! is_array($data['personnel'])) {
            return $result;
        }

        $result['faculty'] = $data['faculty'] ?? null;
        $result['total']  = (int) ($data['total'] ?? 0);

        foreach ($data['personnel'] as $person) {
            $curriculum = isset($person['curriculum']) && (string) $person['curriculum'] !== ''
                ? trim((string) $person['curriculum'])
                : 'อื่นๆ';

            if (! isset($result['groups'][$curriculum])) {
                $result['groups'][$curriculum] = [];
            }
            $result['groups'][$curriculum][] = $person;
        }

        return $result;
    }
}
