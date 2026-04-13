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

            return null;
        }

        $statusCode = $response->getStatusCode();
        $body       = $response->getBody();
        $data       = json_decode($body, true);

        if ($statusCode >= 200 && $statusCode < 300 && is_array($data) && ! empty($data['success'])) {
            $data['personnel'] = self::normalizePersonnelListFromPayload($data);

            return $data;
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
            $out['message'] = 'ตั้งค่า API ไม่ครบสำหรับ ' . \Config\ResearchApi::FACULTY_NAME_TH . ' (RESEARCH_API_BASE_URL / RESEARCH_API_KEY)';

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
            $out['message'] = 'API ส่ง success=false หรือไม่มี success (HTTP ' . $out['http_code'] . ')';

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
