<?php

namespace App\Controllers\Edoc;

use App\Models\Edoc\EdoctagModel;
use App\Models\Edoc\TagGroupModel;
use App\Models\Edoc\EdoctitleModel;
use App\Models\Edoc\DocumentViewModel;
use App\Models\Edoc\EdocVolumeModel;
use App\Models\Edoc\EdocDocumentTagModel;

class AdminEdocController extends EdocBaseController
{
    protected $edoctagModel;
    protected $edoctitleModel;
    protected $documentViews;
    protected $tagGroupModel;
    protected $volumeModel;
    protected $docTagModel;

    public function __construct()
    {
        $this->edoctagModel = new EdoctagModel();
        $this->edoctitleModel = new EdoctitleModel();
        $this->documentViews = new DocumentViewModel();
        $this->tagGroupModel = new TagGroupModel();
        $this->volumeModel = new EdocVolumeModel();
        $this->docTagModel = new EdocDocumentTagModel();
    }

    public function index()
    {
        return $this->showAllDoc();
    }

    public function showAllDoc()
    {
        try {
            if (!$this->isEdocAdmin) {
                return redirect()->to(base_url('edoc'))
                    ->with('error', 'คุณไม่มีสิทธิ์ Admin E-Document');
            }

            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($this->edocUser['uid']);

            if (!$user) {
                return redirect()->to(base_url('admin/login'))->with('error', 'User not found.');
            }

            $suggestNames = $this->edoctagModel->getAllData();
            $dataSuggest = [];
            foreach ($suggestNames as $row) {
                if (isset($row['first_name']) && isset($row['last_name'])) {
                    $dataSuggest[] = $row['first_name'] . " " . $row['last_name'];
                }
            }

            // Get available years and volumes
            $availableYears = $this->volumeModel->getAvailableYears();
            $currentYear = (int) date('Y');
            if (!in_array($currentYear, $availableYears)) {
                array_unshift($availableYears, $currentYear);
            }

            $data = [
                'infoUser'       => $user,
                'suggestname'    => $dataSuggest,
                'edocUser'       => $this->edocUser,
                'isEdocAdmin'    => $this->isEdocAdmin,
                'availableYears' => $availableYears,
                'currentYear'    => $currentYear,
            ];

            return view('edoc/documents/admin_documents', $data);
        } catch (\Exception $e) {
            log_message('error', '[AdminEdocController::showAllDoc] Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while loading documents.');
        }
    }

    /**
     * Get document info for admin (AJAX endpoint)
     */
    public function getDocInfo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
        }

        try {
            if (!$this->isEdocAdmin) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Admin access required'
                ]);
            }

            $iddoc = $this->request->getPost('iddoc');
            if (!$iddoc) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Document ID is required'
                ]);
            }

            $result = $this->edoctitleModel->getDocInfo($iddoc);

            if (!$result) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Document not found'
                ]);
            }

            $parsed = $this->parseFileAddressForRead($result['fileaddress'] ?? '');
            $result['fileaddress_first'] = $parsed['first'];
            $result['fileaddress_list'] = $parsed['list'];

            $this->documentViews->recordView($iddoc, $this->edocUser['uid']);

            $viewStats = $this->documentViews->getDocumentViewStats($iddoc);
            $result['view_statistics'] = $viewStats;

            return $this->response->setJSON([
                'status' => 'success',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', '[AdminEdocController::getDocInfo] Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while fetching document details'
            ]);
        }
    }

    public function getDoc()
    {
        $request = $this->request->getVar();

        $columnsOrder = [
            0 => 'iddoc',
            1 => 'officeiddoc',
            2 => 'title',
            3 => 'doctype',
            4 => 'owner',
            5 => 'participant',
            6 => 'datedoc',
            7 => 'pages',
            8 => 'view_count'
        ];

        $builder = $this->edoctitleModel->builder();
        $builder->select([
            'edoctitle.iddoc',
            'edoctitle.officeiddoc',
            'edoctitle.datedoc',
            'edoctitle.title',
            'edoctitle.doctype',
            'edoctitle.owner',
            'edoctitle.participant',
            'edoctitle.fileaddress',
            'edoctitle.pages',
            '(SELECT COUNT(*) FROM document_views WHERE document_id = edoctitle.iddoc) as view_count'
        ]);

        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $builder->groupStart();
            foreach ($columnsOrder as $column) {
                if ($column !== 'view_count') {
                    $builder->orLike($column, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        if (!empty($request['columnSearch'])) {
            foreach ($request['columnSearch'] as $columnSearch) {
                $columnName = $columnsOrder[$columnSearch['column']] ?? '';
                if ($columnName && $columnName !== 'view_count') {
                    $builder->like($columnName, $columnSearch['search']);
                }
            }
        }

        $totalData = $builder->countAllResults(false);
        $totalFiltered = $totalData;

        if (!empty($request['order'])) {
            foreach ($request['order'] as $order) {
                $columnName = $columnsOrder[$order['column']] ?? 'iddoc';
                $direction = $order['dir'] ?? 'desc';
                if ($columnName === 'view_count') {
                    $builder->orderBy('(SELECT COUNT(*) FROM document_views WHERE document_id = edoctitle.iddoc)', $direction);
                } else {
                    $builder->orderBy($columnName, $direction);
                }
            }
        } else {
            $builder->orderBy('iddoc', 'DESC');
        }

        if (!empty($request['length']) && $request['length'] != -1) {
            $builder->limit($request['length'], $request['start']);
        }

        $results = $builder->get()->getResultArray();

        $data = array_map(function ($row) {
            $fileaddressRaw = isset($row['fileaddress']) ? $row['fileaddress'] : '';
            $parsed = $this->parseFileAddressForRead($fileaddressRaw);
            return [
                'iddoc' => $row['iddoc'],
                'officeiddoc' => esc($row['officeiddoc']),
                'title' => esc($row['title']),
                'doctype' => esc($row['doctype']),
                'owner' => esc($row['owner']),
                'participant' => esc($row['participant']),
                'datedoc' => esc($row['datedoc']),
                'pages' => (int)$row['pages'],
                'view_count' => (int)$row['view_count'],
                'fileaddress' => $fileaddressRaw,
                'fileaddress_first' => $parsed['first'],
                'fileaddress_list' => $parsed['list'],
                'edit' => '<div class="doc-action-buttons">' .
                    '<button type="button" class="doc-btn doc-btn-view" onclick="info(\'' . $row['iddoc'] . '\')" title="ดูรายละเอียดและ PDF"><i class="bx bx-show"></i></button>' .
                    '<button type="button" class="doc-btn doc-btn-edit" onclick="edit(\'' . $row['iddoc'] . '\')" title="แก้ไข"><i class="bx bx-edit-alt"></i></button>' .
                    '<button type="button" class="doc-btn doc-btn-stats" onclick="info(\'' . $row['iddoc'] . '\')" title="รายละเอียด / สถิติ"><i class="bx bx-bar-chart-alt-2"></i></button>' .
                    '</div>'
            ];
        }, $results);

        return $this->response->setJSON([
            'draw' => intval($request['draw']),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function saveDoc()
    {
        $data = $this->request->getPost();

        $allname = $data['allname'] ?? null;
        unset($data['allname']);

        // Extract email tags before saving document
        $tagEmails = [];
        if (!empty($data['tag_emails'])) {
            $tagEmails = is_array($data['tag_emails']) ? $data['tag_emails'] : explode(',', $data['tag_emails']);
            $tagEmails = array_map('trim', $tagEmails);
            $tagEmails = array_filter($tagEmails);
        }
        unset($data['tag_emails']);

        if (isset($data['fileaddress'])) {
            $data['fileaddress'] = $this->normalizeFileAddress($data['fileaddress']);
        }

        // Set doc_year from datedoc if available
        if (!empty($data['datedoc'])) {
            $data['doc_year'] = (int) date('Y', strtotime($data['datedoc']));
        }

        $iddoc = null;
        if (empty($data['iddoc'])) {
            $iddoc = $this->edoctitleModel->insertdoc($data);
        } else {
            $iddoc = $data['iddoc'];
            $this->edoctitleModel->updatedoc($data['iddoc'], $data);
        }

        // Save email tags to edoc_document_tags
        if ($iddoc && !empty($tagEmails)) {
            $emailData = [];
            foreach ($tagEmails as $email) {
                $source = $this->detectEmailSource($email);
                $emailData[] = ['email' => $email, 'source' => $source];
            }
            $this->docTagModel->setDocumentTags((int) $iddoc, $emailData);

            // Also update participant field for backward compatibility
            $data['participant'] = implode(',', $tagEmails);
            if ($iddoc) {
                $this->edoctitleModel->updatedoc($iddoc, ['participant' => $data['participant']]);
            }
        }

        $data['mailList'] = [];
        return $this->response->setJSON($data);
    }

    /**
     * Detect if email belongs to user or student_user table
     */
    private function detectEmailSource(string $email): string
    {
        $email = strtolower(trim($email));
        $user = $this->db ?? \Config\Database::connect();
        $db = \Config\Database::connect();

        $exists = $db->table('user')
            ->where('email', $email)
            ->countAllResults();

        return $exists > 0 ? 'user' : 'student_user';
    }

    // --- Tag Group Management Methods ---

    public function getTagGroups()
    {
        try {
            $groups = $this->tagGroupModel->getAll();
            return $this->response->setJSON(['status' => 'success', 'data' => $groups]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function saveTagGroup()
    {
        try {
            $name = $this->request->getPost('name');
            $tags = $this->request->getPost('tags');
            $id = $this->request->getPost('id');

            if (empty($name) || empty($tags)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Name and Tags are required.']);
            }

            if (!is_array($tags)) {
                $tags = explode(',', $tags);
            }
            $tags = array_map('trim', $tags);

            $result = $this->tagGroupModel->saveGroup($name, $tags, $id);

            if ($result) {
                if (is_string($result['tags'])) {
                    $result['tags'] = json_decode($result['tags'], true);
                }
                return $this->response->setJSON(['status' => 'success', 'data' => $result]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save group.']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteTagGroup()
    {
        try {
            $id = $this->request->getPost('id');
            if (empty($id)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'ID is required.']);
            }

            $result = $this->tagGroupModel->deleteGroup($id);
            if ($result) {
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Group not found.']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ================================================================
    // Volume Management
    // ================================================================

    /**
     * Get volumes for a given year (AJAX)
     */
    public function getVolumes()
    {
        try {
            $year = (int) ($this->request->getGet('year') ?? date('Y'));
            $volumes = $this->volumeModel->getVolumeDocCounts($year);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $volumes,
                'year'   => $year,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[AdminEdocController::getVolumes] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create all 5 volumes for a year (AJAX POST)
     */
    public function createYearVolumes()
    {
        try {
            if (!$this->isEdocAdmin) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Admin access required'
                ]);
            }

            $year = (int) $this->request->getPost('year');
            if ($year < 2020 || $year > 2100) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid year'
                ]);
            }

            $volumes = $this->volumeModel->createYearVolumes($year, $this->edocUser['uid'] ?? null);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $volumes,
                'message' => 'สร้างเล่มปี ' . $year . ' สำเร็จ (' . count($volumes) . ' เล่ม)',
            ]);
        } catch (\Exception $e) {
            log_message('error', '[AdminEdocController::createYearVolumes] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Toggle volume active status (AJAX POST)
     */
    public function toggleVolume()
    {
        try {
            if (!$this->isEdocAdmin) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Admin access required'
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $result = $this->volumeModel->toggleActive($id);

            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'อัปเดตสถานะเล่มสำเร็จ' : 'ไม่พบเล่ม',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get available years (AJAX)
     */
    public function getAvailableYears()
    {
        $years = $this->volumeModel->getAvailableYears();
        $currentYear = (int) date('Y');
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }
        return $this->response->setJSON(['status' => 'success', 'data' => $years]);
    }

    // ================================================================
    // Email Tag Suggest
    // ================================================================

    /**
     * Search users/students by email or name for tag suggest (AJAX)
     */
    public function suggestEmails()
    {
        try {
            $query = trim($this->request->getGet('q') ?? '');
            if (strlen($query) < 2) {
                return $this->response->setJSON(['status' => 'success', 'data' => []]);
            }

            $results = $this->docTagModel->searchTaggableEmails($query, 20);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $results,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[AdminEdocController::suggestEmails] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get tags for a specific document (AJAX)
     */
    public function getDocumentTags()
    {
        try {
            $iddoc = (int) $this->request->getGet('iddoc');
            if (!$iddoc) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Document ID required']);
            }

            $tags = $this->docTagModel->getDocumentTags($iddoc);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $tags,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Parse fileaddress สำหรับหน้าอ่านข้อมูล — รองรับทั้ง (1) ชื่อไฟล์เดี่ยว (2) JSON array
     */
    private function parseFileAddressForRead(?string $fileaddress): array
    {
        $out = ['first' => '', 'list' => []];
        if ($fileaddress === null || trim($fileaddress) === '') {
            return $out;
        }
        $raw = trim($fileaddress);
        $list = [];
        $decoded = @json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $list = $decoded;
        } else {
            $parts = array_map('trim', explode(',', $raw));
            foreach ($parts as $p) {
                $p = trim($p, " \"'[]");
                if ($p !== '') {
                    $list[] = $p;
                }
            }
            if (empty($list)) {
                $clean = trim($raw, " \"'[]");
                if ($clean !== '') {
                    $list = [$clean];
                }
            }
        }
        $list = array_map(function ($f) {
            return trim(preg_replace('/["\'\[\]\s]+$/', '', preg_replace('/^["\'\[\]\s]+/', '', (string) $f)));
        }, $list);
        $list = array_values(array_filter($list, function ($f) {
            return $f !== '';
        }));
        $out['list'] = $list;
        $out['first'] = $list[0] ?? '';
        return $out;
    }

    /**
     * Normalize fileaddress to JSON array string
     */
    private function normalizeFileAddress(?string $fileaddress): string
    {
        $parsed = $this->parseFileAddressForRead($fileaddress);
        return json_encode($parsed['list']);
    }
}
