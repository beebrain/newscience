<?php

namespace App\Controllers;

use App\Libraries\FacultyPersonnelApi;
use Config\ResearchApi;

/**
 * Controller for fetching faculty personnel from the external Research Record API
 * — see doc_api.rd
 *
 * Requires in .env:
 *   RESEARCH_API_BASE_URL  = base URL of research record app (no trailing slash)
 *   RESEARCH_API_KEY       = API key (required; ต้องตรงกับฝั่ง Research Record)
 *   RESEARCH_API_FACULTY_ID or RESEARCH_API_FACULTY_CODE = faculty identifier
 */
class FacultyPersonnelController extends BaseController
{
    protected ResearchApi $researchApi;

    public function __construct()
    {
        $this->researchApi = config(ResearchApi::class);
    }

    /**
     * GET /personnel-api/faculty
     * Fetches personnel (Dean, Chairs, Teachers) for the configured faculty from the external API.
     * Returns JSON.
     */
    public function index()
    {
        $data = FacultyPersonnelApi::fetch();

        if ($data === null) {
            if (! $this->researchApi->isConfigured()) {
                return $this->response
                    ->setStatusCode(503)
                    ->setJSON([
                        'success' => false,
                        'error'   => 'NOT_CONFIGURED',
                        'message' => 'ยังไม่ได้ตั้งค่า API กบศ — ตั้ง RESEARCH_API_BASE_URL, RESEARCH_API_KEY และอย่างใดอย่างหนึ่งของ RESEARCH_API_FACULTY_ID / RESEARCH_API_FACULTY_CODE ใน .env',
                    ]);
            }

            $fail    = FacultyPersonnelApi::getLastFetchFailure();
            $decoded = is_array($fail['decoded'] ?? null) ? $fail['decoded'] : null;
            $expl    = FacultyPersonnelApi::explainFacultyApiBody($decoded);
            $apiErr  = $expl['code'];

            $status = 502;
            if ($apiErr === 'FACULTY_NOT_FOUND' || $apiErr === 'MISSING_PARAMETER') {
                $status = 404;
            }

            $payload = [
                'success'       => false,
                'error'         => $apiErr !== '' ? $apiErr : 'EXTERNAL_API_ERROR',
                'message'       => $expl['message_th'],
                'request_query' => is_array($fail['query'] ?? null) ? $fail['query'] : null,
                'http_code'     => $fail['http_code'] ?? null,
            ];
            if ($expl['message_en'] !== '') {
                $payload['message_en'] = $expl['message_en'];
            }

            return $this->response->setStatusCode($status)->setJSON($payload);
        }

        return $this->response->setJSON($data);
    }

    /**
     * GET /personnel-api/faculty/status
     * Returns whether the Research API is configured (for health check / admin).
     */
    public function status()
    {
        return $this->response->setJSON([
            'configured' => $this->researchApi->isConfigured(),
            'base_url'   => $this->researchApi->baseUrl ?: null,
            'has_faculty_id'   => $this->researchApi->facultyId !== null,
            'has_faculty_code' => $this->researchApi->facultyCode !== null && $this->researchApi->facultyCode !== '',
        ]);
    }
}
