<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Models\ProgramDownloadModel;
use App\Models\ProgramActivityModel;
use App\Models\ProgramActivityImageModel;
use App\Models\ProgramFacilityModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\NewsModel;

class ProgramSpaController extends BaseController
{
    /**
     * Resolve program and page by program ID.
     * @return array{program: array|null, page: array|null, program_id: int|null}
     */
    protected function resolveById(int $id): array
    {
        if ($id <= 0) {
            return ['program' => null, 'page' => null, 'program_id' => null];
        }
        $programModel = new ProgramModel();
        $program = $programModel->find($id);
        if (!$program || ($program['status'] ?? '') !== 'active') {
            return ['program' => null, 'page' => null, 'program_id' => null];
        }
        $programPageModel = new ProgramPageModel();
        $page = $programPageModel->findByProgramId($id);
        $page = $page ?: $this->minimalPageFromProgram($program);
        return ['program' => $program, 'page' => $page, 'program_id' => $id];
    }

    /**
     * Build minimal program_pages-like array from program row (when no program_pages row)
     */
    protected function minimalPageFromProgram(array $program): array
    {
        return [
            'program_id' => $program['id'],
            'slug' => '',
            'philosophy' => '',
            'objectives' => '',
            'hero_image' => $program['image'] ?? '',
            'theme_color' => '#1e40af',
            'text_color' => null,
            'background_color' => null,
            'elos_json' => '',
            'curriculum_json' => '',
            'curriculum_structure' => '',
            'contact_info' => '',
        ];
    }

    /**
     * Redirect /program/{id} to new SPA flow: /p/{id}
     * GET /program/{id} -> redirect to /p/{id}
     */
    public function showByProgramId($id)
    {
        $id = (int) $id;
        $notFound = function () {
            return $this->response->setStatusCode(404)->setBody(view('errors/html/error_404', [
                'message' => lang('Errors.sorryCannotFind'),
            ]));
        };
        if ($id <= 0) {
            return $notFound();
        }
        $programModel = new ProgramModel();
        $program = $programModel->find($id);
        if (!$program || ($program['status'] ?? '') !== 'active') {
            return $notFound();
        }
        return redirect()->to(base_url('p/' . $id))->setStatusCode(302);
    }

    /**
     * System Check page: /p/{id}
     */
    public function index($id)
    {
        $id = (int) $id;
        $data = ['id' => $id];
        return view('program_spa/check', $data);
    }

    /**
     * Main SPA page: /p/{id}/main (opened in new tab after check passes)
     */
    public function main($id)
    {
        $id = (int) $id;
        $data = ['id' => $id];
        return view('program_spa/main', $data);
    }

    /**
     * Check system (tables + program data). JSON: {status, message, spa_url}
     * GET /p/{id}/check
     */
    public function checkSystem($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Program ID is required',
                'spa_url' => '',
            ]);
        }

        $db = \Config\Database::connect();
        $requiredTables = ['programs', 'program_pages', 'program_downloads', 'program_activities', 'program_facilities', 'news'];
        foreach ($requiredTables as $table) {
            if (!$db->tableExists($table)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Table missing: ' . $table,
                    'spa_url' => '',
                ]);
            }
        }

        $resolved = $this->resolveById($id);
        if ($resolved['program_id'] === null || !$resolved['program']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Program not found (ID: ' . $id . ')',
                'spa_url' => '',
            ]);
        }

        $spaUrl = base_url('p/' . $id . '/main');
        return $this->response->setJSON([
            'status'  => 'ok',
            'message' => 'System ready',
            'spa_url' => $spaUrl,
        ]);
    }

    /**
     * Get all data for SPA (program + page + staff + documents + news + activities + facilities).
     * GET /p/{id}/data
     */
    public function getData($id)
    {
        $id = (int) $id;
        $resolved = $this->resolveById($id);
        if ($resolved['program_id'] === null || !$resolved['program'] || !$resolved['page']) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Program not found',
            ]);
        }

        $id = $resolved['program_id'];
        $program = $resolved['program'];
        $page = $resolved['page'];

        if (($program['status'] ?? '') !== 'active') {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Program not found',
            ]);
        }

        $programDownloadModel = new ProgramDownloadModel();
        $personnelModel = new PersonnelModel();
        $personnelProgramModel = new PersonnelProgramModel();
        $newsModel = new NewsModel();
        $activityModel = new ProgramActivityModel();
        $activityImageModel = new ProgramActivityImageModel();
        $facilityModel = new ProgramFacilityModel();

        $levelLabels = [
            'bachelor'  => 'ปริญญาตรี',
            'master'    => 'ปริญญาโท',
            'doctorate' => 'ปริญญาเอก',
        ];
        $level = $program['level'] ?? 'bachelor';
        $levelLabel = $levelLabels[$level] ?? $level;

        $heroImage = trim($page['hero_image'] ?? '');
        if ($heroImage === '') {
            $heroImage = trim($program['image'] ?? '');
        }
        $heroImageUrl = '';
        if ($heroImage !== '') {
            $heroImageUrl = strpos($heroImage, 'http') === 0
                ? $heroImage
                : base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $heroImage), '/'));
        }

        $elos = [];
        $elosRaw = $page['elos_json'] ?? '';
        if ($elosRaw !== '') {
            $decoded = json_decode($elosRaw, true);
            if (is_array($decoded)) {
                $elos = $decoded;
            }
        }

        $curriculum = [];
        $curriculumRaw = $page['curriculum_json'] ?? '';
        if ($curriculumRaw !== '') {
            $decoded = json_decode($curriculumRaw, true);
            if (is_array($decoded)) {
                $curriculum = $decoded;
            }
        }

        $staff = [];
        $ppRows = $personnelProgramModel->getByProgramId($id);
        if (!empty($ppRows)) {
            $personnelIds = array_map(fn($r) => (int) $r['personnel_id'], $ppRows);
            $roleMap = [];
            foreach ($ppRows as $row) {
                $roleMap[(int) $row['personnel_id']] = $row['role_in_curriculum'] ?? '';
            }
            $rows = $personnelModel->getActiveByIdsWithUser($personnelIds);
            foreach ($rows as $p) {
                $pid = (int) ($p['id'] ?? 0);
                $img = trim($p['image'] ?? '');
                $imageUrl = $img !== '' ? base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $img))) : '';
                if ($img !== '' && strpos($img, 'http') === 0) {
                    $imageUrl = $img;
                }
                $staff[] = [
                    'name'     => trim($p['name'] ?? ''),
                    'position' => trim($p['position'] ?? ''),
                    'role'     => $roleMap[$pid] ?? '',
                    'image'    => $imageUrl,
                ];
            }
            usort($staff, function ($a, $b) {
                $aChair = mb_strpos($a['role'], 'ประธาน') !== false ? 0 : 1;
                $bChair = mb_strpos($b['role'], 'ประธาน') !== false ? 0 : 1;
                return $aChair - $bChair;
            });
        }

        $documents = [];
        $downloads = $programDownloadModel->getByProgramId($id);
        foreach ($downloads as $d) {
            $documents[] = [
                'title'     => $d['title'] ?? '',
                'type'      => strtoupper($d['file_type'] ?? 'PDF'),
                'size'      => $programDownloadModel->getFormattedSize((int) ($d['file_size'] ?? 0)),
                'is_public' => true,
                'url'       => base_url('serve/' . $d['file_path']),
            ];
        }

        $news = [];
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('news_tags') && $db->tableExists('news_news_tags')) {
                $newsRows = $newsModel->getPublishedByTag('program_' . $id, 6, 0);
                foreach ($newsRows as $n) {
                    $img = $this->formatFeaturedImageThumb($n['featured_image'] ?? '');
                    $news[] = [
                        'id'         => (int) ($n['id'] ?? 0),
                        'title'      => $n['title'] ?? '',
                        'title_th'   => $n['title'] ?? '',
                        'excerpt'    => $n['excerpt'] ?? mb_substr(strip_tags($n['content'] ?? ''), 0, 150) . '...',
                        'image_url'  => $img,
                        'thumbnail'  => $img,
                        'date'       => isset($n['published_at']) ? date('d/m/Y', strtotime($n['published_at'])) : '',
                        'created_at' => $n['published_at'] ?? $n['created_at'] ?? '',
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('debug', 'ProgramSpaController::getData news: ' . $e->getMessage());
        }

        $activities = [];
        $activityRows = $activityModel->getPublishedByProgramId($id);
        foreach ($activityRows as $act) {
            $actId = (int) ($act['id'] ?? 0);
            $images = $activityImageModel->getByActivityId($actId);
            $imageList = [];
            foreach ($images as $im) {
                $path = trim($im['image_path'] ?? '');
                $imageList[] = [
                    'url'     => $path !== '' ? (strpos($path, 'http') === 0 ? $path : base_url('serve/' . $path)) : '',
                    'caption' => $im['caption'] ?? '',
                ];
            }
            $activities[] = [
                'id'          => $actId,
                'title'       => $act['title'] ?? '',
                'description' => $act['description'] ?? '',
                'activity_date' => $act['activity_date'] ?? null,
                'location'    => $act['location'] ?? '',
                'images'      => $imageList,
            ];
        }

        $facilities = [];
        $facilityRows = $facilityModel->getPublishedByProgramId($id);
        foreach ($facilityRows as $f) {
            $img = trim($f['image'] ?? '');
            $imgUrl = $img !== '' ? (strpos($img, 'http') === 0 ? $img : base_url('serve/' . $img)) : '';
            $facilities[] = [
                'id'          => (int) ($f['id'] ?? 0),
                'title'       => $f['title'] ?? '',
                'description' => $f['description'] ?? '',
                'image'       => $imgUrl,
                'facility_type' => $f['facility_type'] ?? 'other',
            ];
        }

        $alumni = [];
        $alumniRaw = $page['alumni_messages_json'] ?? '';
        if ($alumniRaw !== '') {
            $decoded = json_decode($alumniRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $a) {
                    $path = trim($a['photo_path'] ?? $a['photo_url'] ?? '');
                    $photoUrl = '';
                    if ($path !== '' && strpos($path, 'http') !== 0) {
                        $photoUrl = base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $path), '/'));
                    } elseif ($path !== '') {
                        $photoUrl = $path;
                    }
                    $alumni[] = [
                        'message'         => $a['message'] ?? '',
                        'position'        => $a['position'] ?? '',
                        'workplace'       => $a['workplace'] ?? '',
                        'graduation_year' => $a['graduation_year'] ?? '',
                        'photo_url'       => $photoUrl,
                    ];
                }
            }
        }

        $data = [
            'id'                   => $id,
            'name_th'              => $program['name_th'] ?? '',
            'name_en'              => $program['name_en'] ?? '',
            'degree_th'             => $program['degree_th'] ?? '',
            'degree_en'             => $program['degree_en'] ?? '',
            'level'                => $levelLabel,
            'credits'              => (int) ($program['credits'] ?? 0) ?: null,
            'duration'             => (int) ($program['duration'] ?? 0) ?: null,
            'hero_image'           => $heroImageUrl,
            'theme_color'          => $page['theme_color'] ?? '#1e40af',
            'text_color'            => !empty($page['text_color']) ? $page['text_color'] : null,
            'background_color'     => !empty($page['background_color']) ? $page['background_color'] : null,
            'philosophy'           => $page['philosophy'] ?? '',
            'vision'               => $page['objectives'] ?? '',
            'graduate_profile'     => $page['graduate_profile'] ?? '',
            'curriculum_structure'  => $page['curriculum_structure'] ?? '',
            'study_plan'           => $page['study_plan'] ?? '',
            'career_prospects'     => $page['career_prospects'] ?? '',
            'tuition_fees'         => $page['tuition_fees'] ?? '',
            'admission_info'       => $page['admission_info'] ?? '',
            'contact_info'         => $page['contact_info'] ?? '',
            'intro_video_url'      => $page['intro_video_url'] ?? '',
            'elos'                 => $elos,
            'curriculum'            => $curriculum,
            'staff'                => $staff,
            'documents'             => $documents,
            'news'                  => $news,
            'activities'           => $activities,
            'facilities'            => $facilities,
            'alumni'                => $alumni,
        ];

        return $this->response->setJSON([
            'success' => true,
            'data'    => $data,
        ]);
    }

    protected function formatFeaturedImageThumb($imagePath)
    {
        if (empty($imagePath) || trim($imagePath) === '') {
            return '';
        }
        helper('program_upload');
        return featured_image_serve_url(trim($imagePath), true);
    }
}
