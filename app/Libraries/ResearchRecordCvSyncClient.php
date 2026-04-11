<?php

namespace App\Libraries;

use Config\ResearchApi;
use Config\ResearchRecordSync;

/**
 * HTTP client เรียก Research Record sync API — ใช้หลักสำหรับดึงข้อมูลจาก RR ลง newScience (GET bundle / publications)
 */
class ResearchRecordCvSyncClient
{
    /**
     * @return array{success:bool,bundle?:array,error?:string,message?:string}
     */
    public static function fetchCvBundle(string $canonicalEmail): array
    {
        return self::getJson('cv-bundle-by-email', $canonicalEmail);
    }

    /**
     * @param array<string,mixed> $bundle
     *
     * @return array{success:bool,bundle?:array,error?:string,message?:string}
     */
    public static function pushCvBundle(string $canonicalEmail, array $bundle): array
    {
        $researchApi = config(ResearchApi::class);
        if ($researchApi->baseUrl === '') {
            return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'ตั้ง RESEARCH_API_BASE_URL ใน .env'];
        }
        if ($researchApi->apiKey === '') {
            return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'ตั้ง RESEARCH_API_KEY ใน .env'];
        }

        $url = self::buildUrl('cv-bundle-by-email', $canonicalEmail);

        try {
            $response = HttpTransport::post($url, ['timeout' => 60], [
                'headers' => [
                    'X-API-KEY'    => $researchApi->apiKey,
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(['bundle' => $bundle], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ResearchRecordCvSyncClient::pushCvBundle ' . $e->getMessage());

            return ['success' => false, 'error' => 'REQUEST_FAILED', 'message' => $e->getMessage()];
        }

        return self::decodeResponse($response);
    }

    /**
     * @return array{success:bool,publications?:list<array>,content_hash?:string,error?:string,message?:string}
     */
    public static function fetchPublicationsSyncBundle(string $canonicalEmail): array
    {
        $r = self::getJson('publications-sync-bundle-by-email', $canonicalEmail);
        if (!$r['success']) {
            return $r;
        }
        $d = $r['data'] ?? [];

        return [
            'success'      => true,
            'publications' => $d['publications'] ?? [],
            'content_hash' => $d['content_hash'] ?? '',
            'retrieved_at' => $d['retrieved_at'] ?? null,
        ];
    }

    /**
     * @return array{success:bool,data?:array,bundle?:array,error?:string,message?:string}
     */
    private static function getJson(string $path, string $canonicalEmail): array
    {
        $researchApi = config(ResearchApi::class);
        if ($researchApi->baseUrl === '') {
            return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'ตั้ง RESEARCH_API_BASE_URL ใน .env'];
        }
        if ($researchApi->apiKey === '') {
            return ['success' => false, 'error' => 'NOT_CONFIGURED', 'message' => 'ตั้ง RESEARCH_API_KEY ใน .env'];
        }

        $url = self::buildUrl($path, $canonicalEmail);

        try {
            $response = HttpTransport::get($url, ['timeout' => 45], [
                'headers' => [
                    'X-API-KEY' => $researchApi->apiKey,
                    'Accept'    => 'application/json',
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ResearchRecordCvSyncClient::getJson ' . $e->getMessage());

            return ['success' => false, 'error' => 'REQUEST_FAILED', 'message' => $e->getMessage()];
        }

        return self::decodeResponse($response);
    }

    private static function buildUrl(string $path, string $canonicalEmail): string
    {
        $researchApi = config(ResearchApi::class);
        $syncCfg     = config(ResearchRecordSync::class);
        $email       = CvProfile::normalizeEmail($canonicalEmail);

        $q = ['email' => $email];
        if ($syncCfg->hmacEnabled()) {
            $exp       = time() + $syncCfg->hmacTtlSeconds;
            $q['exp']  = (string) $exp;
            $q['sig']  = ResearchSyncHmac::sign($email, $exp);
        }

        return $researchApi->baseUrl . '/api/public/' . $path . '?' . http_build_query($q);
    }

    /**
     * @return array<string,mixed>
     */
    private static function decodeResponse($response): array
    {
        $code = (int) $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if ($code >= 200 && $code < 300 && is_array($data) && !empty($data['success'])) {
            if (isset($data['bundle'])) {
                return ['success' => true, 'bundle' => $data['bundle']];
            }

            return ['success' => true, 'data' => $data];
        }

        if ($code >= 200 && $code < 300 && is_array($data)) {
            return ['success' => false, 'error' => $data['error'] ?? 'API_ERROR', 'message' => (string) ($data['message'] ?? json_encode($data))];
        }

        $msg = is_array($data) ? ($data['message'] ?? $data['error'] ?? 'HTTP ' . $code) : $body;

        return ['success' => false, 'error' => 'API_ERROR', 'message' => (string) $msg];
    }
}
