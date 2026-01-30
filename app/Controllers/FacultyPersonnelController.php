<?php

namespace App\Controllers;

use App\Libraries\FacultyPersonnelApi;
use Config\ResearchApi;

/**
 * Controller for fetching faculty personnel from the external Research Record API
 * (คณะวิทยาศาสตร์และเทคโนโลยี) – see doc_api.rd
 *
 * Requires in .env:
 *   RESEARCH_API_BASE_URL  = base URL of research record app (no trailing slash)
 *   RESEARCH_API_KEY       = API key (default: URU_RESEARCH)
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
                        'message' => 'Research API is not configured. Set RESEARCH_API_BASE_URL and RESEARCH_API_FACULTY_ID or RESEARCH_API_FACULTY_CODE in .env',
                    ]);
            }
            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'error'   => 'EXTERNAL_API_ERROR',
                    'message' => 'Could not fetch faculty personnel from external API',
                ]);
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
