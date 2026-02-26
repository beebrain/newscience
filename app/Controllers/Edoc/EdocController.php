<?php

namespace App\Controllers\Edoc;

use App\Models\Edoc\DocumentViewModel;
use App\Models\Edoc\EdoctagModel;
use App\Models\Edoc\EdoctitleModel;
use App\Models\Edoc\EdocDocumentTagModel;
use App\Models\Edoc\EdocVolumeModel;

class EdocController extends EdocBaseController
{
    protected $edoctagModel;
    protected $edoctitleModel;
    protected $documentViews;
    protected $docTagModel;
    protected $volumeModel;

    public function __construct()
    {
        $this->edoctagModel = new EdoctagModel();
        $this->edoctitleModel = new EdoctitleModel();
        $this->documentViews = new DocumentViewModel();
        $this->docTagModel = new EdocDocumentTagModel();
        $this->volumeModel = new EdocVolumeModel();
    }

    public function index()
    {
        return $this->showAllDoc();
    }

    public function showAllDoc()
    {
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($this->edocUser['uid']);

            if (empty($user['thai_name']) || empty($user['thai_lastname'])) {
                return redirect()->to(base_url('dashboard'))
                    ->with('error', 'กรุณากรอกข้อมูลชื่อ-นามสกุลก่อนใช้งาน E-Document');
            }

            $papers = $this->edoctitleModel->getsummaryPaper();

            // Get available years for volume filter
            $availableYears = $this->volumeModel->getAvailableYears();
            $currentYear = (int) date('Y');
            if (!in_array($currentYear, $availableYears)) {
                array_unshift($availableYears, $currentYear);
            }

            $data = [
                'infoUser'       => $user,
                'papers'         => $papers,
                'edocUser'       => $this->edocUser,
                'isEdocAdmin'    => $this->isEdocAdmin,
                'availableYears' => $availableYears,
                'currentYear'    => $currentYear,
            ];

            return view('edoc/documents/showEdoc', $data);
        } catch (\Exception $e) {
            log_message('error', '[EdocController::showAllDoc] Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while loading documents.');
        }
    }

    public function getDocInfo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
        }

        try {
            $userId = $this->edocUser['uid'];
            if (empty($userId)) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => 'Authentication required',
                    'redirect' => base_url('admin/login')
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

            $this->documentViews->recordView($iddoc, $userId);

            $viewStats = $this->documentViews->getDocumentViewStats($iddoc);
            $result['view_statistics'] = $viewStats;

            return $this->response->setJSON([
                'status' => 'success',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[EdocController::getDocInfo] Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while processing your request'
            ]);
        }
    }

    public function getAllViewers()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        try {
            $iddoc = $this->request->getPost('iddoc');

            if (!$iddoc) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Document ID is required']);
            }

            $viewers = $this->documentViews->select('document_views.*, user.thai_name, user.thai_lastname')
                ->join('user', 'user.uid = document_views.user_id', 'left')
                ->where('document_id', $iddoc)
                ->orderBy('viewed_at', 'DESC')
                ->find();

            return $this->response->setJSON([
                'status' => 'success',
                'viewers' => $viewers
            ]);
        } catch (\Exception $e) {
            log_message('error', '[EdocController::getAllViewers] Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to retrieve viewing history']);
        }
    }

    public function getDoc()
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($this->edocUser['uid']);
        $request = $this->request->getVar();

        $userEmail = strtolower(trim($user['email'] ?? ''));
        $owner = trim($user["thai_name"] ?? '') . " " . trim($user["thai_lastname"] ?? '');

        $columnsOrder = [
            0 => 'iddoc',
            1 => 'officeiddoc',
            2 => 'title',
            3 => 'doctype',
            4 => 'owner',
            5 => 'participant',
            6 => 'datedoc',
            7 => 'order',
        ];

        $builder = $this->edoctitleModel->builder();
        $builder->select(['edoctitle.iddoc', 'edoctitle.officeiddoc', 'edoctitle.datedoc', 'edoctitle.title', 'edoctitle.doctype', 'edoctitle.owner', 'edoctitle.participant', 'edoctitle.fileaddress', 'edoctitle.pages', 'edoctitle.order']);

        // Filter: show docs where user is tagged by email OR is owner OR tagged as "ทุกคน" (legacy)
        $taggedDocIds = $this->docTagModel->getDocumentIdsByEmail($userEmail);

        $builder->groupStart();
        if (!empty($taggedDocIds)) {
            $builder->whereIn('edoctitle.iddoc', $taggedDocIds);
            $builder->orWhere('owner', $owner);
        } else {
            $builder->where('owner', $owner);
        }
        // Legacy fallback: also match by name in participant field
        $builder->orLike('participant', $owner);
        $builder->orLike('participant', 'ทุกคน');
        $builder->groupEnd();

        // Volume/year filter
        $volumeId = $this->request->getPost('volume_id') ?? $this->request->getGet('volume_id');
        $docYear = $this->request->getPost('doc_year') ?? $this->request->getGet('doc_year');
        if (!empty($volumeId)) {
            $builder->where('edoctitle.volume_id', (int) $volumeId);
        }
        if (!empty($docYear)) {
            $builder->where('edoctitle.doc_year', (int) $docYear);
        }

        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $builder->groupStart();
            foreach ($columnsOrder as $column) {
                $builder->orLike($column, $searchValue);
            }
            $builder->groupEnd();
        }

        if (!empty($request['columnSearch'])) {
            foreach ($request['columnSearch'] as $columnSearch) {
                $columnName = $columnsOrder[$columnSearch['column']];
                $builder->like($columnName, $columnSearch['search']);
            }
        }

        $totalData = $builder->countAllResults(false);
        $totalFiltered = $totalData;

        if (!empty($request['order'])) {
            foreach ($request['order'] as $order) {
                $columnName = $columnsOrder[$order['column']] ?? 'iddoc';
                $direction = $order['dir'] ?? 'desc';
                $builder->orderBy($columnName, $direction);
            }
        } else {
            $builder->orderBy('iddoc', 'DESC');
        }

        if (!empty($request['length']) && $request['length'] != -1) {
            $builder->limit($request['length'], $request['start']);
        }

        $results = $builder->get()->getResultArray();

        $data = array_map(function ($row) {
            $idLink = "<a href='#' onclick=\"info('{$row['iddoc']}')\">";
            return [
                'iddoc' => $row['iddoc'],
                'officeiddoc' => $idLink . $row['officeiddoc'] . '</a>',
                'title' => $idLink . $row['title'] . '</a>',
                'doctype' => $row['doctype'],
                'participant' => (string)$row['participant'],
                'owner' => $row['owner'],
                'order' => $row['order'],
                'datedoc' => $row['datedoc']
            ];
        }, $results);

        return $this->response->setJSON([
            'draw' => intval($request['draw']),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'records' => $data
        ]);
    }

    public function viewPDF($id)
    {
        try {
            $docInfo = $this->edoctitleModel->find($id);
            if (!$docInfo && is_numeric($id)) {
                $docInfo = $this->edoctitleModel->find((int) $id);
            }
            if (!$docInfo) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ไม่พบเอกสาร id: ' . $id);
            }

            $parsed = $this->parseFileAddressForRead($docInfo['fileaddress'] ?? '');
            $fileList = $parsed['list'];

            if (empty($fileList)) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('No files found for this document');
            }

            $requestedFile = $this->request->getGet('subfile');
            $targetFile = '';

            if ($requestedFile) {
                if (in_array($requestedFile, $fileList)) {
                    $targetFile = $requestedFile;
                } else {
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not authorized');
                }
            } else {
                $targetFile = $fileList[0];
            }

            $targetFileForUrl = $targetFile;

            $targetFile = str_replace('\\', '/', trim($targetFile));
            if (strpos($targetFile, '..') !== false) {
                $targetFile = basename($targetFile);
            }
            $targetFileSafe = ltrim($targetFile, '/');
            $targetBasename = basename($targetFile);

            $basePaths = [
                $this->getEdocDocumentPath(),
                WRITEPATH . 'uploads/',
                WRITEPATH . 'uploads/documents/',
                ROOTPATH . 'EdocDocument/',
                FCPATH . 'EdocDocument/',
            ];
            $filePath = null;

            foreach ($basePaths as $base) {
                foreach ([$targetFileSafe, $targetBasename] as $name) {
                    if ($name === '') continue;
                    $candidate = $base . $name;
                    if (file_exists($candidate) && is_file($candidate)) {
                        $filePath = $candidate;
                        break 2;
                    }
                }
            }

            log_message('debug', '[viewPDF] Target file: ' . $targetFileSafe . ' (basename: ' . $targetBasename . ')');
            log_message('debug', '[viewPDF] Resolved path: ' . ($filePath ?: 'NOT FOUND'));

            if (!$filePath || !file_exists($filePath)) {
                $triedPaths = array_map(fn($b) => $b . $targetBasename, $basePaths);
                log_message('error', '[viewPDF] File not found: ' . $targetFileSafe . ' Tried: ' . implode('; ', $triedPaths));
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                    'ไฟล์นี้ไม่มีอยู่: ' . $targetBasename
                );
            }

            if ($this->request->getGet('file') === 'true') {
                $mimeType = mime_content_type($filePath);
                return $this->response
                    ->setHeader('Content-Type', $mimeType)
                    ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
                    ->setBody(file_get_contents($filePath));
            }

            return view('edoc/documents/pdfviewer', [
                'pdf_url' => base_url('index.php/edoc/viewPDF/' . $id . '?file=true' . ($targetFileForUrl ? '&subfile=' . urlencode($targetFileForUrl) : '')),
                'title' => $docInfo['title']
            ]);
        } catch (\Exception $e) {
            log_message('error', '[EdocController::viewPDF] Error: ' . $e->getMessage());
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
}
