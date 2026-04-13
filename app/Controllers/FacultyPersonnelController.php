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
                        'message' => 'ยังไม่ได้ตั้งค่า API กบศสำหรับ ' . ResearchApi::FACULTY_NAME_TH . ' — ตั้ง RESEARCH_API_BASE_URL และ RESEARCH_API_KEY ใน .env (และ RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE หากไม่ใช้ค่าเริ่มต้น)',
                        'faculty' => ResearchApi::FACULTY_NAME_TH,
                    ]);
            }

            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'error'   => 'EXTERNAL_API_ERROR',
                    'message' => 'ดึงรายชื่อบุคลากร ' . ResearchApi::FACULTY_NAME_TH . ' จากระบบกบศไม่สำเร็จ — ตรวจ URL, API Key และรหัสคณะ (เช่น request faculty_id / faculty_code ฝั่งกบศ)',
                    'faculty' => ResearchApi::FACULTY_NAME_TH,
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
