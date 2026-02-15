<?php

namespace App\Controllers\StudentAdmin;

use App\Controllers\BaseController;
use App\Models\BarcodeEventModel;
use App\Models\BarcodeModel;
use App\Models\BarcodeEventEligibleModel;
use App\Models\StudentUserModel;
use Config\N8n;
use Config\BarcodeExtractApi;

class BarcodeEvents extends BaseController
{
    protected $barcodeEventModel;
    protected $barcodeModel;
    protected $eligibleModel;
    protected $studentUserModel;

    public function __construct()
    {
        $this->barcodeEventModel = new BarcodeEventModel();
        $this->barcodeModel = new BarcodeModel();
        $this->eligibleModel = new BarcodeEventEligibleModel();
        $this->studentUserModel = new StudentUserModel();
    }

    /**
     * Resolve creator IDs for new/update: admin → user_uid, club → student_user_id
     */
    private function getCreatorIds(): array
    {
        $studentUserId = null;
        $userUid = null;
        if (session()->get('student_logged_in') && session()->get('student_role') === 'club') {
            $studentUserId = (int) session()->get('student_id');
        }
        if (session()->get('admin_logged_in')) {
            $userUid = (int) session()->get('admin_id');
        }
        return ['created_by_student_user_id' => $studentUserId, 'created_by_user_uid' => $userUid];
    }

    /**
     * Parse barcode input: JSON (barcodes array or data[].code) or newline/comma-separated list.
     * @return string[]
     */
    private function parseBarcodeInput(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        $raw = trim($raw);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            if (isset($decoded['barcodes']) && is_array($decoded['barcodes'])) {
                $codes = $decoded['barcodes'];
            } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
                $codes = [];
                foreach ($decoded['data'] as $item) {
                    $codes[] = is_array($item) ? ($item['code'] ?? '') : (string) $item;
                }
            } else {
                $codes = [];
            }
        } else {
            $codes = preg_split('/[\r\n,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        }
        return array_values(array_filter(array_map(function ($c) {
            return is_string($c) ? trim($c) : (string) $c;
        }, $codes)));
    }

    /**
     * Extract barcode codes from API JSON response. Expects { barcodes: [...] } or { data: [ { code: "..." }, ... ] }.
     * @return string[]
     */
    private function codesFromApiJson(array $decoded): array
    {
        $codes = [];
        if (isset($decoded['barcodes']) && is_array($decoded['barcodes'])) {
            $codes = $decoded['barcodes'];
        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
            foreach ($decoded['data'] as $item) {
                $codes[] = is_array($item) ? ($item['code'] ?? '') : (string) $item;
            }
        }
        return array_values(array_filter(array_map(function ($c) {
            return is_string($c) ? trim($c) : (string) $c;
        }, $codes)));
    }

    /**
     * Call external API to extract barcodes from uploaded file. API returns JSON.
     * @return array{success: bool, codes: string[], error?: string}
     */
    private function callBarcodeExtractApi($file): array
    {
        if (!$file || !$file->isValid()) {
            return ['success' => false, 'codes' => [], 'error' => 'ไม่มีไฟล์หรือไฟล์ไม่ถูกต้อง'];
        }
        $config = config(BarcodeExtractApi::class);
        if (!$config->isConfigured()) {
            return ['success' => false, 'codes' => [], 'error' => 'ยังไม่ได้ตั้งค่า API ถอดบาร์โค้ด (BARCODE_EXTRACT_API_URL ใน .env)'];
        }
        $path = $file->getTempName();
        $name = $file->getClientName() ?: 'upload';
        $mime = $file->getClientMimeType() ?: 'application/octet-stream';
        $curlFile = new \CURLFile($path, $mime, $name);
        $multipart = [$config->fileFieldName => $curlFile];
        $headers = [];
        if ($config->apiKey !== '') {
            $headers['Authorization'] = 'Bearer ' . $config->apiKey;
            if (strpos($config->apiKey, ' ') !== false) {
                $headers['Authorization'] = $config->apiKey;
            }
        }
        if (strpos($config->url, 'ngrok') !== false) {
            $headers['ngrok-skip-browser-warning'] = 'true';
        }
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->request('POST', $config->url, [
                'multipart' => $multipart,
                'headers'   => $headers,
                'timeout'   => 60,
            ]);
            $body = $response->getBody();
            $decoded = json_decode($body, true);
            if (!is_array($decoded)) {
                return ['success' => false, 'codes' => [], 'error' => 'API ส่งกลับไม่ใช่ JSON'];
            }
            $codes = $this->codesFromApiJson($decoded);
            return ['success' => true, 'codes' => $codes];
        } catch (\Throwable $e) {
            return ['success' => false, 'codes' => [], 'error' => 'เรียก API ไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /**
     * Parse uploaded file locally (TXT/CSV/JSON). Used when API is not configured.
     * @return array{success: bool, codes: string[], error?: string}
     */
    private function parseBarcodeFile($file): array
    {
        if (!$file || !$file->isValid()) {
            return ['success' => false, 'codes' => [], 'error' => 'ไม่มีไฟล์หรือไฟล์ไม่ถูกต้อง'];
        }
        $ext = strtolower($file->getClientExtension());
        $content = $file->getString();
        $codes = [];
        if ($ext === 'json') {
            $decoded = json_decode($content, true);
            if (!is_array($decoded)) {
                return ['success' => false, 'codes' => [], 'error' => 'JSON ไม่ถูกต้อง'];
            }
            $codes = $this->codesFromApiJson($decoded);
        } elseif ($ext === 'csv') {
            $lines = preg_split('/\r\n|\r|\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $cols = str_getcsv($line);
                $codes[] = isset($cols[0]) ? trim($cols[0]) : '';
            }
        } else {
            $lines = preg_split('/\r\n|\r|\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $codes[] = trim($line);
            }
        }
        $codes = array_values(array_filter(array_map(function ($c) {
            return is_string($c) ? trim($c) : (string) $c;
        }, $codes)));
        return ['success' => true, 'codes' => $codes];
    }

    public function index()
    {
        $events = $this->barcodeEventModel->getAllOrdered();
        $withCounts = [];
        foreach ($events as $e) {
            $row = $this->barcodeEventModel->getWithCounts((int) $e['id']);
            if ($row !== null) {
                $withCounts[] = $row;
            }
        }
        $data = [
            'page_title' => 'จัดการบาร์โค้ด (Barcode Events)',
            'events' => $withCounts,
        ];
        return view('student_admin/barcode_events/index', $data);
    }

    public function create()
    {
        $data = ['page_title' => 'สร้าง Event แจกบาร์โค้ด'];
        return view('student_admin/barcode_events/create', $data);
    }

    public function store()
    {
        $rules = [
            'title' => 'required|min_length[1]|max_length[500]',
            'event_date' => 'required|valid_date',
            'status' => 'required|in_list[draft,active,closed]',
        ];
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $ids = $this->getCreatorIds();
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'event_date' => $this->request->getPost('event_date'),
            'status' => $this->request->getPost('status'),
            'created_by_student_user_id' => $ids['created_by_student_user_id'] ?: null,
            'created_by_user_uid' => $ids['created_by_user_uid'] ?: null,
        ];
        $id = $this->barcodeEventModel->insert($data);
        if (!$id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'สร้างไม่สำเร็จ']);
            }
            return redirect()->back()->withInput()->with('error', 'สร้างไม่สำเร็จ');
        }
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'id' => $id, 'message' => 'สร้าง Event สำเร็จ']);
        }
        return redirect()->to(base_url('student-admin/barcode-events/' . $id))->with('success', 'สร้าง Event สำเร็จ — สามารถแนบไฟล์บาร์โค้ดได้ด้านล่าง');
    }

    public function show($id)
    {
        $event = $this->barcodeEventModel->getWithCounts((int) $id);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $barcodes = $this->barcodeModel->getByEvent((int) $id, false);
        $eligibles = $this->eligibleModel->getEligiblesWithStudents((int) $id);
        $barcodePrefill = session()->getFlashdata('barcode_prefill');
        $data = [
            'page_title' => $event['title'],
            'event' => $event,
            'barcodes' => $barcodes,
            'eligibles' => $eligibles,
            'barcode_prefill' => is_array($barcodePrefill) ? $barcodePrefill : [],
        ];
        return view('student_admin/barcode_events/show', $data);
    }

    public function edit($id)
    {
        $event = $this->barcodeEventModel->find($id);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $data = ['page_title' => 'แก้ไข Event', 'event' => $event];
        return view('student_admin/barcode_events/edit', $data);
    }

    public function update($id)
    {
        $event = $this->barcodeEventModel->find($id);
        if (!$event) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'ไม่พบ Event']);
            }
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $rules = [
            'title' => 'required|min_length[1]|max_length[500]',
            'event_date' => 'required|valid_date',
            'status' => 'required|in_list[draft,active,closed]',
        ];
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->barcodeEventModel->update($id, [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'event_date' => $this->request->getPost('event_date'),
            'status' => $this->request->getPost('status'),
        ]);
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'บันทึกแล้ว']);
        }
        return redirect()->to(base_url('student-admin/barcode-events'))->with('success', 'บันทึกแล้ว');
    }

    public function delete($id)
    {
        if (!$this->barcodeEventModel->find($id)) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $this->barcodeEventModel->delete($id);
        return redirect()->to(base_url('student-admin/barcode-events'))->with('success', 'ลบ Event แล้ว');
    }

    /**
     * Import barcodes: POST JSON or fetch from N8n
     */
    public function import($id)
    {
        $event = $this->barcodeEventModel->find($id);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $codes = $this->parseBarcodeInput($this->request->getPost('json_barcodes'));
        if (empty($codes)) {
            $n8n = config(N8n::class);
            if ($n8n->enabled && $n8n->baseUrl !== '' && $n8n->webhookPath !== '') {
                $url = rtrim($n8n->baseUrl, '/') . '/' . ltrim($n8n->webhookPath, '/');
                $client = \Config\Services::curlrequest();
                $opts = ['timeout' => 10];
                if ($n8n->apiKey !== '') {
                    $opts['headers'] = ['Authorization' => 'Bearer ' . $n8n->apiKey];
                }
                try {
                    $response = $client->get($url, $opts);
                    if ($response->getStatusCode() === 200) {
                        $body = $response->getBody();
                        $decoded = json_decode($body, true);
                        if (isset($decoded['barcodes']) && is_array($decoded['barcodes'])) {
                            $codes = $decoded['barcodes'];
                        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
                            foreach ($decoded['data'] as $item) {
                                $codes[] = is_array($item) ? ($item['code'] ?? '') : (string) $item;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    return redirect()->to(base_url('student-admin/barcode-events/' . $id))
                        ->with('error', 'เรียก N8n ไม่สำเร็จ: ' . $e->getMessage());
                }
            }
        }
        $codes = array_values(array_filter(array_map(function ($c) {
            return is_string($c) ? trim($c) : (string) $c;
        }, $codes)));
        $result = $this->barcodeModel->bulkInsertCodes((int) $id, $codes);
        $msg = 'นำเข้าได้ ' . $result['inserted'] . ' รายการ';
        if ($result['skipped'] > 0) {
            $msg .= ', ข้าม (ซ้ำ) ' . $result['skipped'];
        }
        if (!empty($result['errors'])) {
            $msg .= '. ข้อผิดพลาด: ' . implode('; ', array_slice($result['errors'], 0, 5));
        }
        return redirect()->to(base_url('student-admin/barcode-events/' . $id))->with('success', $msg);
    }

    /**
     * Upload file and extract barcodes (via external API if configured, else local parse). Redirect to show with codes for review.
     */
    public function parseFileUpload($id)
    {
        $event = $this->barcodeEventModel->find($id);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $file = $this->request->getFile('barcode_file');
        $config = config(BarcodeExtractApi::class);
        $result = $config->isConfigured()
            ? $this->callBarcodeExtractApi($file)
            : $this->parseBarcodeFile($file);
        if (!$result['success']) {
            return redirect()->to(base_url('student-admin/barcode-events/' . $id))->with('error', $result['error'] ?? 'อ่านไฟล์ไม่สำเร็จ');
        }
        session()->setFlashdata('barcode_prefill', $result['codes']);
        return redirect()->to(base_url('student-admin/barcode-events/' . $id))->with('success', 'ได้ ' . count($result['codes']) . ' รหัส — ตรวจสอบด้านล่างแล้วกดนำเข้า');
    }

    /**
     * Delete a single barcode from the event (must not be assigned, or allow anyway per plan)
     */
    public function deleteBarcode($eventId, $barcodeId)
    {
        $event = $this->barcodeEventModel->find($eventId);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        if ($this->barcodeModel->deleteBarcode((int) $barcodeId, (int) $eventId)) {
            return redirect()->back()->with('success', 'ลบบาร์โค้ดแล้ว');
        }
        return redirect()->back()->with('error', 'ลบไม่สำเร็จหรือบาร์โค้ดไม่ใช่ของ Event นี้');
    }

    /**
     * Unassign barcode from student (set back to unassigned)
     */
    public function unassign($eventId, $barcodeId)
    {
        $event = $this->barcodeEventModel->find($eventId);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        $barcode = $this->barcodeModel->find($barcodeId);
        if (!$barcode || (int) $barcode['barcode_event_id'] !== (int) $eventId) {
            return redirect()->back()->with('error', 'บาร์โค้ดไม่ถูกต้อง');
        }
        $this->barcodeModel->unassignFromStudent((int) $barcodeId);
        return redirect()->back()->with('success', 'ยกเลิกการผูกบาร์โค้ดแล้ว');
    }

    /**
     * Add eligible student to event (by student_user_id or by emails)
     */
    public function addEligible($id)
    {
        $event = $this->barcodeEventModel->getWithCounts((int) $id);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        if ((int) ($event['barcode_total'] ?? 0) === 0) {
            return redirect()->back()->with('error', 'กรุณาเพิ่มบาร์โค้ดใน Event นี้ก่อน จึงจะเพิ่มผู้มีสิทธิ์ได้');
        }
        $by = $this->request->getPost('by');

        if ($by === 'email') {
            $emailsRaw = $this->request->getPost('emails');
            $emails = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $emailsRaw ?? '', -1, PREG_SPLIT_NO_EMPTY)));
            $added = 0;
            $notFound = [];
            $existingIds = $this->eligibleModel->getEligibleStudentIds((int) $id);
            foreach ($emails as $email) {
                $email = filter_var($email, FILTER_VALIDATE_EMAIL) ?: $email;
                if ($email === '') {
                    continue;
                }
                $student = $this->studentUserModel->findByEmail($email);
                if (!$student) {
                    $notFound[] = $email;
                    continue;
                }
                $sid = (int) $student['id'];
                if (in_array($sid, $existingIds, true)) {
                    continue;
                }
                $this->eligibleModel->addEligible((int) $id, $sid);
                $existingIds[] = $sid;
                $added++;
            }
            $msg = 'เพิ่มจาก Email ได้ ' . $added . ' คน';
            if (!empty($notFound)) {
                $msg .= '. ไม่พบในระบบ: ' . implode(', ', array_slice($notFound, 0, 10));
                if (count($notFound) > 10) {
                    $msg .= ' …';
                }
            }
            return redirect()->back()->with($added > 0 ? 'success' : 'error', $msg);
        }

        $studentUserId = (int) $this->request->getPost('student_user_id');
        if (!$studentUserId) {
            return redirect()->back()->with('error', 'กรุณาเลือกนักศึกษาหรือกรอก Email');
        }
        $this->eligibleModel->addEligible((int) $id, $studentUserId);
        return redirect()->back()->with('success', 'เพิ่มผู้มีสิทธิ์แล้ว');
    }

    /**
     * Remove eligible and unassign any barcode for this event assigned to this student
     */
    public function removeEligible($id, $studentUserId)
    {
        $eventId = (int) $id;
        $sid = (int) $studentUserId;
        $barcodes = $this->barcodeModel->where('barcode_event_id', $eventId)->where('student_user_id', $sid)->findAll();
        foreach ($barcodes as $b) {
            $this->barcodeModel->unassignFromStudent((int) $b['id']);
        }
        $this->eligibleModel->removeEligible($eventId, $sid);
        return redirect()->back()->with('success', 'ลบออกจากรายการมีสิทธิ์แล้ว' . (count($barcodes) > 0 ? ' และยกเลิกการผูกบาร์โค้ดแล้ว' : ''));
    }

    /**
     * Page to manage eligibles (add form with student autocomplete)
     */
    public function eligibles($id)
    {
        $event = $this->barcodeEventModel->getWithCounts((int) $id);
        if (!$event) {
            return redirect()->to(base_url('student-admin/barcode-events'))->with('error', 'ไม่พบ Event');
        }
        if ((int) ($event['barcode_total'] ?? 0) === 0) {
            return redirect()->to(base_url('student-admin/barcode-events/' . $id))->with('error', 'กรุณาเพิ่มบาร์โค้ดใน Event นี้ก่อน จึงจะเพิ่มผู้มีสิทธิ์ได้');
        }
        $eligibles = $this->eligibleModel->getEligiblesWithStudents((int) $id);
        $students = $this->studentUserModel->getListForDropdown();
        $existingIds = array_column($eligibles, 'student_user_id');
        $data = [
            'page_title' => 'ผู้มีสิทธิ์รับบาร์โค้ด - ' . $event['title'],
            'event' => $event,
            'eligibles' => $eligibles,
            'students' => $students,
            'existing_ids' => $existingIds,
        ];
        return view('student_admin/barcode_events/eligibles', $data);
    }
}
