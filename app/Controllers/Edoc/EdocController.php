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

    private function normalizeProfileImageUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return base_url(ltrim($path, '/'));
    }

    private function getUserMetaByEmails(array $emails): array
    {
        $emails = array_values(array_unique(array_filter(array_map(static function ($email) {
            return strtolower(trim((string) $email));
        }, $emails))));

        if (empty($emails)) {
            return [];
        }

        $userModel = new \App\Models\UserModel();
        $builder = $userModel->select('email, tf_name, tl_name, gf_name, gl_name, profile_image, profile_picture');
        $builder->groupStart();
        foreach ($emails as $index => $email) {
            if ($index === 0) {
                $builder->where('LOWER(email)', $email);
            } else {
                $builder->orWhere('LOWER(email)', $email);
            }
        }
        $builder->groupEnd();
        $rows = $builder->findAll();

        $meta = [];
        foreach ($rows as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '') {
                continue;
            }

            $thaiName = trim((string) (($row['tf_name'] ?? '') . ' ' . ($row['tl_name'] ?? '')));
            $engName = trim((string) (($row['gf_name'] ?? '') . ' ' . ($row['gl_name'] ?? '')));
            $meta[$email] = [
                'name' => $thaiName !== '' ? $thaiName : ($engName !== '' ? $engName : $email),
                'image' => $this->normalizeProfileImageUrl($row['profile_image'] ?? $row['profile_picture'] ?? ''),
            ];
        }

        return $meta;
    }

    private function buildParticipantChips(?string $participantRaw, array $emailToName = [], array $emailToMeta = []): array
    {
        $chips = [];
        if ($participantRaw === '' || $participantRaw === null) {
            return $chips;
        }

        $parts = array_map('trim', explode(',', (string) $participantRaw));
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if ($part === 'ทุกคน') {
                $chips[] = ['email' => 'ทุกคน', 'name' => 'ทุกคน', 'image' => ''];
                continue;
            }

            $key = strtolower($part);
            $meta = $emailToMeta[$key] ?? [];
            $chips[] = [
                'email' => $part,
                'name'  => $meta['name'] ?? ($emailToName[$key] ?? $part),
                'image' => $meta['image'] ?? '',
            ];
        }

        return $chips;
    }

    private function buildOwnerChip(?string $ownerRaw, array $emailToMeta = []): array
    {
        $ownerRaw = trim((string) $ownerRaw);
        if ($ownerRaw === '') {
            return ['label' => '', 'image' => ''];
        }

        $key = strtolower($ownerRaw);
        if (isset($emailToMeta[$key])) {
            return [
                'label' => $emailToMeta[$key]['name'] ?? $ownerRaw,
                'image' => $emailToMeta[$key]['image'] ?? '',
            ];
        }

        return ['label' => $ownerRaw, 'image' => ''];
    }

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

            // ตรวจสอบชื่อไทย — ถ้าไม่มีจะแสดง popup ให้กรอก (ไม่ redirect ออก)
            $needsThaiName = (empty($user['tf_name']) || empty($user['tl_name']));

            $papers = $this->edoctitleModel->getsummaryPaper();

            // Get available years for volume filter (มาตรฐานปี พ.ศ.)
            $availableYears = $this->volumeModel->getAvailableYears();
            $currentYear = (int) date('Y') + 543; // ค.ศ. → พ.ศ.
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
                'needsThaiName'  => $needsThaiName,
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

            $participantRaw = $result['participant'] ?? '';
            $parts = $participantRaw !== '' && $participantRaw !== null ? array_map('trim', explode(',', $participantRaw)) : [];
            $emails = array_filter($parts, static function ($p) {
                return $p !== '' && $p !== 'ทุกคน' && strpos($p, '@') !== false;
            });
            $ownerRaw = trim((string) ($result['owner'] ?? ''));
            if ($ownerRaw !== '' && strpos($ownerRaw, '@') !== false) {
                $emails[] = $ownerRaw;
            }
            $emailToName = $this->docTagModel->getDisplayNamesByEmails(array_map('strtolower', $emails));
            $emailToMeta = $this->getUserMetaByEmails($emails);
            $result['participant_chips'] = $this->buildParticipantChips($participantRaw, $emailToName, $emailToMeta);
            $result['owner_chip'] = $this->buildOwnerChip($ownerRaw, $emailToMeta);

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

            $viewers = $this->documentViews->select('document_views.*, user.tf_name, user.tl_name')
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
        $ownerName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
        if ($ownerName === ' ') {
            $ownerName = trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? ''));
        }
        $ownerName = trim($ownerName);

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

        // ใช้ชื่อและอีเมลจาก user โดยตรง: owner/participant ใน edoctitle (ไม่ใช้ edoc_document_tags)
        $builder->groupStart();
        if ($userEmail !== '') {
            $builder->orWhere('edoctitle.owner', $userEmail);
            $builder->orLike('edoctitle.participant', $userEmail, 'both');
        }
        if ($ownerName !== '') {
            $builder->orWhere('edoctitle.owner', $ownerName);
            $builder->orLike('edoctitle.participant', $ownerName, 'both');
        }
        $builder->orLike('edoctitle.participant', 'ทุกคน', 'both');
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

        // Advanced filters
        if (!empty($request['doctype'])) {
            $types = is_array($request['doctype']) ? $request['doctype'] : [$request['doctype']];
            $types = array_filter(array_map('trim', $types));
            if (!empty($types)) {
                $builder->whereIn('edoctitle.doctype', $types);
            }
        }
        if (!empty($request['date_from'])) {
            $builder->where('edoctitle.datedoc >=', $request['date_from']);
        }
        if (!empty($request['date_to'])) {
            $builder->where('edoctitle.datedoc <=', $request['date_to']);
        }
        if (!empty($request['filter_owner'])) {
            $filterOwner = trim($request['filter_owner']);
            if (strpos($filterOwner, '@') !== false) {
                $builder->where('edoctitle.owner', $filterOwner);
            } else {
                $builder->like('edoctitle.owner', $filterOwner);
            }
        }
        if (!empty($request['filter_officeiddoc'])) {
            $builder->like('edoctitle.officeiddoc', $request['filter_officeiddoc']);
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

        $allEmails = [];
        foreach ($results as $row) {
            $p = $row['participant'] ?? '';
            if ($p !== '' && $p !== null) {
                $parts = array_map('trim', explode(',', (string) $p));
                foreach ($parts as $part) {
                    if ($part !== '' && $part !== 'ทุกคน' && strpos($part, '@') !== false) {
                        $allEmails[] = strtolower($part);
                    }
                }
            }
            $owner = trim((string) ($row['owner'] ?? ''));
            if ($owner !== '' && strpos($owner, '@') !== false) {
                $allEmails[] = strtolower($owner);
            }
        }
        $allEmails = array_unique($allEmails);
        $emailToName = $this->docTagModel->getDisplayNamesByEmails(array_values($allEmails));
        $emailToMeta = $this->getUserMetaByEmails($allEmails);

        $data = array_map(function ($row) use ($emailToName, $emailToMeta) {
            $idLink = "<a href='#' onclick=\"info('{$row['iddoc']}')\">";
            $participantRaw = $row['participant'] ?? '';
            $participantChips = $this->buildParticipantChips($participantRaw, $emailToName, $emailToMeta);
            $ownerChip = $this->buildOwnerChip($row['owner'] ?? '', $emailToMeta);
            return [
                'iddoc' => $row['iddoc'],
                'officeiddoc' => $idLink . $row['officeiddoc'] . '</a>',
                'title' => $idLink . $row['title'] . '</a>',
                'doctype' => $row['doctype'],
                'participant' => (string)$row['participant'],
                'participant_chips' => $participantChips,
                'owner' => $row['owner'],
                'owner_chip' => $ownerChip,
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

    /**
     * บันทึกชื่อ-นามสกุลภาษาไทย (AJAX จาก popup ในหน้า E-Document)
     */
    public function updateThaiName()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $tfName = trim($this->request->getPost('tf_name') ?? '');
        $tlName = trim($this->request->getPost('tl_name') ?? '');

        if ($tfName === '' || $tlName === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'กรุณากรอกชื่อและนามสกุลภาษาไทย']);
        }

        // ตรวจสอบว่าเป็นอักษรไทย (อนุญาต ก-๙, ช่องว่าง, .-/)
        if (!preg_match('/^[\p{Thai}\s.\-\/]+$/u', $tfName) || !preg_match('/^[\p{Thai}\s.\-\/]+$/u', $tlName)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'กรุณากรอกเป็นภาษาไทยเท่านั้น']);
        }

        try {
            $userId = $this->edocUser['uid'];
            $userModel = new \App\Models\UserModel();
            $userModel->update($userId, [
                'tf_name' => $tfName,
                'tl_name' => $tlName,
            ]);

            log_message('info', '[EdocController::updateThaiName] Updated Thai name for uid=' . $userId . ' tf_name=' . $tfName . ' tl_name=' . $tlName);

            return $this->response->setJSON(['status' => 'success', 'message' => 'บันทึกชื่อ-นามสกุลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            log_message('error', '[EdocController::updateThaiName] Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด กรุณาลองใหม่']);
        }
    }

    /**
     * Get volumes for a given year (for user advanced search dropdown)
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
            log_message('error', '[EdocController::getVolumes] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
