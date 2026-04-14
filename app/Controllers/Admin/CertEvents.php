<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CertOrganizerAccess;
use App\Libraries\CertPdfGenerator;
use App\Models\CertEventModel;
use App\Models\CertEventRecipientModel;
use App\Models\CertificateModel;
use App\Models\StudentUserModel;
use App\Models\UserModel;
use App\Services\CertificateEmailService;
use Config\Certificate as CertificateConfig;

/**
 * CertEvents Controller - จัดการกิจกรรม/หัวข้ออบรมที่จะออก Certificate
 */
class CertEvents extends BaseController
{
    protected CertEventModel $eventModel;
    protected CertEventRecipientModel $recipientModel;
    protected CertificateModel $certificateModel;
    protected StudentUserModel $studentModel;
    protected UserModel $userModel;
    protected CertificateConfig $certConfig;

    /** @var string URL segment หลัง base_url เช่น admin/cert-events */
    protected string $routePrefix = 'admin/cert-events';

    /** @var string view folder เช่น admin/cert_events */
    protected string $viewPrefix = 'admin/cert_events';

    /** Dashboard organizer: บังคับรายการเฉพาะกิจกรรมที่ตนเป็นผู้สร้าง */
    protected bool $scopeIndexToCreatorOnly = false;

    public function __construct()
    {
        $this->eventModel = new CertEventModel();
        $this->recipientModel = new CertEventRecipientModel();
        $this->certificateModel = new CertificateModel();
        $this->studentModel = new StudentUserModel();
        $this->userModel = new UserModel();
        $this->certConfig = config(CertificateConfig::class);

        if (! CertOrganizerAccess::currentMayOrganize()) {
            $path = trim((string) uri_string(), '/');
            $to    = str_starts_with($path, 'dashboard/') ? base_url('dashboard') : base_url('admin');
            redirect()->to($to)
                ->with(
                    'error',
                    'ไม่สามารถเข้าถึงระบบใบรับรองได้: สงวนสำหรับบุคลากรที่สังกัดคณะวิทยาศาสตร์และเทคโนโลยีเท่านั้น (ตรวจสอบคอลัมน์คณะในโปรไฟล์หลังล็อกอิน Portal หรือติดต่อผู้ดูแลระบบ)'
                )
                ->send();
            exit;
        }
    }

    protected function certUrl(string $path = ''): string
    {
        return base_url($this->routePrefix . ($path !== '' ? '/' . $path : ''));
    }

    protected function certView(string $name): string
    {
        return $this->viewPrefix . '/' . $name;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderCert(string $name, array $data = []): string
    {
        $data['cert_base'] = rtrim($this->certUrl(), '/');

        return view($this->certView($name), $data);
    }

    /**
     * รายการกิจกรรมทั้งหมด
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $createdByGet = $this->request->getGet('created_by');
        $createdBy    = ($createdByGet !== null && $createdByGet !== '') ? (int) $createdByGet : null;
        if ($createdBy !== null && $createdBy <= 0) {
            $createdBy = null;
        }
        if ($this->scopeIndexToCreatorOnly) {
            $createdBy = (int) session()->get('admin_id');
        }

        $events = $this->eventModel->getAllWithStats($status, 50, $createdBy);

        $signers = $this->userModel->where('active', 1)
            ->whereIn('role', ['super_admin', 'faculty_admin', 'admin'])
            ->findAll();

        return $this->renderCert('index', [
            'page_title'    => 'จัดการกิจกรรมใบรับรอง',
            'events'        => $events,
            'filter_status' => $status,
            'filter_creator'=> $createdBy,
            'signers'       => $signers,
        ]);
    }

    /**
     * รายงานใบรับรองที่ออกแล้วทั้งระบบ (ระดับคณะ / ผู้ดูแล)
     */
    public function issuedReport()
    {
        $rows = $this->recipientModel->getIssuedReportRows(800);

        return $this->renderCert('issued_report', [
            'page_title' => 'รายงานใบรับรองที่ออกแล้ว',
            'rows'       => $rows,
        ]);
    }

    /**
     * ฟอร์มสร้างกิจกรรมใหม่
     */
    public function create()
    {
        $signers = $this->userModel->where('active', 1)
            ->whereIn('role', ['super_admin', 'faculty_admin', 'admin'])
            ->findAll();

        return $this->renderCert('create', [
            'page_title' => 'สร้างกิจกรรมใบรับรอง',
            'signers'    => $signers,
        ]);
    }

    /**
     * บันทึกกิจกรรมใหม่ - รองรับทั้ง HTML form และ AJAX
     */
    public function store()
    {
        if (!$this->validate($this->rules())) {
            $errors = $this->validator->getErrors();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $errors]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload = [
            'title'        => $this->request->getPost('title'),
            'description'  => $this->request->getPost('description'),
            'event_date'   => $this->request->getPost('event_date'),
            'template_id'  => null,
            'signer_id'    => (int) $this->request->getPost('signer_id') ?: null,
            'status'       => $this->request->getPost('status') ?? 'draft',
            'created_by'   => session()->get('admin_id'),
        ];

        $layoutErr = $this->mergeLayoutJsonIntoPayload($payload);
        if ($layoutErr !== null) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => ['layout_json' => $layoutErr]]);
            }

            return redirect()->back()->withInput()->with('errors', ['layout_json' => $layoutErr]);
        }

        $eventId = $this->eventModel->insert($payload);

        if ($eventId) {
            $bg = $this->applyBackgroundUpload((int) $eventId);
            if (isset($bg['_error'])) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => $bg['_error']]);
                }

                return redirect()->back()->withInput()->with('error', $bg['_error']);
            }
            if ($bg !== []) {
                $this->eventModel->update((int) $eventId, $bg);
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'สร้างกิจกรรมเรียบร้อย',
                'event_id' => $eventId,
                'redirect' => $this->certUrl((string) $eventId)
            ]);
        }

        return redirect()->to($this->certUrl((string) $eventId))
            ->with('success', 'สร้างกิจกรรมเรียบร้อย');
    }

    /**
     * แสดงรายละเอียดกิจกรรม + รายชื่อผู้รับ
     */
    public function show(int $id)
    {
        $event = $this->eventModel->getWithDetails($id);
        if (!$event) {
            return redirect()->to($this->certUrl())->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->to($this->certUrl())->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        $recipients = $this->recipientModel->getByEvent($id);
        $students = $this->studentModel->where('status', 'active')->findAll();

        return $this->renderCert('show', [
            'page_title' => $event['title'],
            'event'      => $event,
            'recipients' => $recipients,
            'students'   => $students,
        ]);
    }

    /**
     * ฟอร์มแก้ไขกิจกรรม - รองรับทั้ง HTML และ AJAX
     */
    public function edit(int $id)
    {
        $event = $this->eventModel->find($id);
        if (!$event) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบกิจกรรม']);
            }
            return redirect()->to($this->certUrl())->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้']);
            }

            return redirect()->to($this->certUrl())->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        // AJAX request - return JSON
        if ($this->request->isAJAX() || $this->request->getGet('ajax')) {
            return $this->response->setJSON([
                'success' => true,
                'event'   => $event
            ]);
        }

        $signers = $this->userModel->where('active', 1)
            ->whereIn('role', ['super_admin', 'faculty_admin', 'admin'])
            ->findAll();

        return $this->renderCert('edit', [
            'page_title' => 'แก้ไขกิจกรรม: ' . $event['title'],
            'event'      => $event,
            'signers'    => $signers,
        ]);
    }

    /**
     * อัปเดตกิจกรรม - รองรับทั้ง HTML form และ AJAX
     */
    public function update(int $id)
    {
        $event = $this->eventModel->find($id);
        if (!$event) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบกิจกรรม']);
            }
            return redirect()->to($this->certUrl())->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้']);
            }

            return redirect()->to($this->certUrl())->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        if (!$this->validate($this->rules(false))) {
            $errors = $this->validator->getErrors();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $errors]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload = [
            'title'        => $this->request->getPost('title'),
            'description'  => $this->request->getPost('description'),
            'event_date'   => $this->request->getPost('event_date'),
            'template_id'  => null,
            'signer_id'    => (int) $this->request->getPost('signer_id') ?: null,
            'status'       => $this->request->getPost('status') ?? $event['status'],
        ];

        $layoutErr = $this->mergeLayoutJsonIntoPayload($payload);
        if ($layoutErr !== null) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => ['layout_json' => $layoutErr]]);
            }

            return redirect()->back()->withInput()->with('errors', ['layout_json' => $layoutErr]);
        }

        $bg = $this->applyBackgroundUpload($id);
        if (isset($bg['_error'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $bg['_error']]);
            }

            return redirect()->back()->withInput()->with('error', $bg['_error']);
        }
        if ($bg !== []) {
            $payload = array_merge($payload, $bg);
        }

        $this->eventModel->update($id, $payload);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'อัปเดตกิจกรรมเรียบร้อย'
            ]);
        }

        return redirect()->to($this->certUrl((string) $id))
            ->with('success', 'อัปเดตกิจกรรมเรียบร้อย');
    }

    /**
     * ลบกิจกรรม (เฉพาะที่ยังไม่มีการออก Certificate) - รองรับทั้ง HTML และ AJAX
     */
    public function delete(int $id)
    {
        $event = $this->eventModel->find($id);
        if (!$event) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบกิจกรรม']);
            }
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้']);
            }

            return redirect()->back()->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        // ตรวจสอบว่ามีการออก Certificate แล้วหรือไม่
        $issuedCount = $this->recipientModel->countByStatus($id, 'issued');
        if ($issuedCount > 0) {
            $message = 'ไม่สามารถลบกิจกรรมที่มีการออก Certificate แล้ว';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->with('error', $message);
        }

        $this->eventModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'ลบกิจกรรมเรียบร้อย'
            ]);
        }

        return redirect()->to($this->certUrl())
            ->with('success', 'ลบกิจกรรมเรียบร้อย');
    }

    /**
     * เพิ่มผู้รับรายชื่อเดียว
     */
    public function addRecipient(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->back()->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        if (! $this->validate([
            'recipient_name'  => 'required|min_length[2]',
            'recipient_email' => 'required|valid_email|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $studentId = $this->request->getPost('student_id');
        $student = null;
        if ($studentId) {
            $student = $this->studentModel->find($studentId);
        }

        $payload = [
            'event_id'        => $eventId,
            'student_id'      => $studentId ?: null,
            'recipient_name'  => $this->request->getPost('recipient_name'),
            'recipient_email' => CertOrganizerAccess::normalizeEmail((string) $this->request->getPost('recipient_email'))
                ?: CertOrganizerAccess::normalizeEmail((string) ($student['email'] ?? '')),
            'recipient_id_no' => $this->request->getPost('recipient_id_no') ?: ($student['login_uid'] ?? null),
            'extra_data'      => json_encode([
                'program' => $this->request->getPost('program') ?? ($student['program_id'] ?? null),
                'note'    => $this->request->getPost('note'),
            ]),
            'status'          => 'pending',
        ];

        $this->recipientModel->insert($payload);

        return redirect()->to($this->certUrl((string) $eventId))
            ->with('success', 'เพิ่มผู้รับเรียบร้อย');
    }

    /**
     * ลบผู้รับที่ยังไม่ได้ออก Certificate
     */
    public function removeRecipient(int $recipientId)
    {
        $recipient = $this->recipientModel->find($recipientId);
        if (!$recipient) {
            return redirect()->back()->with('error', 'ไม่พบผู้รับ');
        }

        $event = $this->eventModel->find((int) $recipient['event_id']);
        if (! $event || ! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->back()->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        if ($recipient['status'] === 'issued') {
            return redirect()->back()->with('error', 'ไม่สามารถลบผู้ที่ออก Certificate แล้ว');
        }

        $this->recipientModel->delete($recipientId);

        return redirect()->to($this->certUrl((string) $recipient['event_id']))
            ->with('success', 'ลบผู้รับเรียบร้อย');
    }

    /**
     * ออก Certificate ให้ผู้รับทั้งหมดที่ยัง pending
     */
    public function issueCertificates(int $eventId)
    {
        $event = $this->eventModel->getWithDetails($eventId);
        if (! $event) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->back()->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        if ($event['status'] === 'draft') {
            return redirect()->back()->with('error', 'กรุณาเปลี่ยนสถานะกิจกรรมเป็น Open ก่อนออก Certificate');
        }

        $recipients = $this->recipientModel->getPendingByEvent($eventId);
        if (empty($recipients)) {
            return redirect()->back()->with('error', 'ไม่มีผู้รับที่รอการออก Certificate');
        }

        foreach ($recipients as $recipient) {
            $em = CertOrganizerAccess::normalizeEmail((string) ($recipient['recipient_email'] ?? ''));
            if ($em === '' || ! filter_var($em, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->with(
                    'error',
                    'ผู้รับทุกคนต้องมีอีเมลถูกต้องก่อนออกใบรับรอง (แก้ไขรายชื่อที่ยังไม่มีอีเมล)'
                );
            }
        }

        if (trim((string) ($event['background_file'] ?? '')) === '' || trim((string) ($event['background_kind'] ?? '')) === '') {
            return redirect()->back()->with(
                'error',
                'กรุณาอัปโหลดไฟล์ใบรับรอง (รูป JPG/PNG หรือ PDF) ของกิจกรรมนี้ที่หน้าแก้ไขก่อนออกใบ'
            );
        }

        $pdfGenerator = new CertPdfGenerator();
        $mailService  = new CertificateEmailService();
        $successCount = 0;
        $failCount    = 0;
        $emailFail    = 0;

        $template = $this->resolveTemplateForPdf($event);
        if ($template === null) {
            return redirect()->back()->with('error', 'ไม่สามารถเตรียมข้อมูล overlay สำหรับสร้าง PDF ได้');
        }

        foreach ($recipients as $recipient) {
            try {
                $certificateData = $this->createCertificateRecord($recipient, $event);

                $studentData = $this->buildStudentData($recipient, $event);
                $requestData = [
                    'request_number' => $certificateData['certificate_no'],
                    'purpose'        => $event['title'],
                ];

                $signaturePath = $this->getSignerSignature($event['signer_id']);
                $pdfPath       = $pdfGenerator->generate(
                    $requestData,
                    $template,
                    $studentData,
                    $certificateData['verification_token'],
                    $signaturePath,
                    $event
                );

                if ($pdfPath) {
                    $absPdf = $mailService->resolvePdfAbsolutePath($pdfPath, $this->certConfig);
                    $hash   = $absPdf ? $pdfGenerator->hashFile($absPdf) : '';

                    $this->certificateModel->update($certificateData['id'], [
                        'pdf_path' => $pdfPath,
                        'pdf_hash' => $hash,
                    ]);

                    $this->recipientModel->markAsIssued($recipient['id'], $certificateData['id']);

                    $verifyUrl = $certificateData['verification_token']
                        ? base_url('verify/' . $certificateData['verification_token'])
                        : null;
                    if ($absPdf) {
                        $send = $mailService->sendIssuedCertificate($recipient, $event, $absPdf, $verifyUrl);
                        $mailService->persistSendResult((int) $recipient['id'], $send['ok'], $send['ok'] ? null : ($send['error'] ?? 'unknown'));
                        if (! $send['ok']) {
                            $emailFail++;
                        }
                    } else {
                        $mailService->persistSendResult((int) $recipient['id'], false, 'ไม่พบไฟล์ PDF บนดิสก์');
                        $emailFail++;
                    }

                    $successCount++;
                } else {
                    $this->recipientModel->markAsFailed($recipient['id'], 'ไม่สามารถสร้าง PDF ได้');
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->recipientModel->markAsFailed($recipient['id'], $e->getMessage());
                $failCount++;
            }
        }

        if ($successCount > 0) {
            $this->eventModel->updateStatus($eventId, 'issued');
        }

        $message = "ออก Certificate สำเร็จ {$successCount} รายการ";
        if ($failCount > 0) {
            $message .= ", ไม่สำเร็จ {$failCount} รายการ";
        }
        if ($emailFail > 0) {
            $message .= " (ส่งอีเมลไม่สำเร็จ {$emailFail} รายการ — ตรวจสอบคอนฟิก SMTP และคอลัมน์ email_error)";
        }

        return redirect()->to($this->certUrl((string) $eventId))
            ->with($failCount > 0 ? 'error' : 'success', $message);
    }

    /**
     * หน้า Import รายชื่อจาก CSV/Excel
     */
    public function importForm(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->to($this->certUrl())->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->to($this->certUrl())->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        return $this->renderCert('import', [
            'page_title' => 'นำเข้ารายชื่อ: ' . $event['title'],
            'event'      => $event,
        ]);
    }

    /**
     * ประมวลผลไฟล์ Import
     */
    public function processImport(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->to($this->certUrl())->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->to($this->certUrl())->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'กรุณาเลือกไฟล์');
        }

        // อ่านไฟล์ CSV
        $handle = fopen($file->getTempName(), 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'ไม่สามารถอ่านไฟล์ได้');
        }

        // อ่าน header
        $headers = fgetcsv($handle);
        if (!$headers) {
            return redirect()->back()->with('error', 'ไฟล์ว่างเปล่า');
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);

            $name = $data['name'] ?? $data['ชื่อ'] ?? null;
            if (empty($name)) {
                $skipped++;
                continue;
            }

            // ค้นหา student จากรหัสนักศึกษาหรืออีเมล
            $studentId = null;
            $student   = null;
            $idNo = $data['student_id'] ?? $data['รหัสนักศึกษา'] ?? null;
            $emailRaw = $data['email'] ?? $data['อีเมล'] ?? null;
            $email    = is_string($emailRaw) ? CertOrganizerAccess::normalizeEmail($emailRaw) : null;
            if ($email === '') {
                $email = null;
            }

            if ($idNo) {
                $student = (new StudentUserModel())->where('login_uid', $idNo)->first();
            } elseif ($email) {
                $student = (new StudentUserModel())->where('email', $email)->first();
            } else {
                $student = null;
            }
            if ($student) {
                $studentId = $student['id'];
            }

            $payload = [
                'event_id'        => $eventId,
                'student_id'      => $studentId,
                'recipient_name'  => $name,
                'recipient_email' => $email,
                'recipient_id_no' => $idNo,
                'extra_data'      => json_encode([
                    'program'   => $data['program'] ?? $data['หลักสูตร'] ?? null,
                    'faculty'   => $data['faculty'] ?? $data['คณะ'] ?? null,
                    'grade'     => $data['grade'] ?? $data['เกรด'] ?? null,
                    'note'      => $data['note'] ?? $data['หมายเหตุ'] ?? null,
                ]),
                'status'          => 'pending',
            ];

            $this->recipientModel->insert($payload);
            $imported++;
        }

        fclose($handle);

        return redirect()->to($this->certUrl((string) $eventId))
            ->with('success', "นำเข้า {$imported} รายชื่อสำเร็จ" . ($skipped > 0 ? " (ข้าม {$skipped} รายการ)" : ''));
    }

    /**
     * ดาวน์โหลดรายชื่อเป็น CSV
     */
    public function exportRecipients(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }
        if (! CertOrganizerAccess::mayAccessEvent($event)) {
            return redirect()->back()->with('error', 'ไม่มีสิทธิ์เข้าถึงกิจกรรมนี้');
        }

        $recipients = $this->recipientModel->getByEvent($eventId);

        $filename = 'recipients_' . $eventId . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['ชื่อ', 'อีเมล', 'รหัสนักศึกษา', 'สถานะ', 'เลขที่ Certificate', 'วันที่ออก', 'ส่งอีเมล', 'ข้อผิดพลาดอีเมล']);

        foreach ($recipients as $recipient) {
            fputcsv($output, [
                $recipient['recipient_name'],
                $recipient['recipient_email'],
                $recipient['recipient_id_no'],
                $recipient['status'],
                $recipient['certificate_no'] ?? '-',
                $recipient['issued_date'] ?? '-',
                $recipient['email_sent_at'] ?? '-',
                $recipient['email_error'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * สร้าง certificate record ในฐานข้อมูล
     */
    protected function createCertificateRecord(array $recipient, array $event): array
    {
        $certificateNo = $this->generateCertificateNumber();
        $token = bin2hex(random_bytes($this->certConfig->verificationTokenBytes));

        $data = [
            'request_id'         => 0, // ไม่ผูกกับ cert_requests ในระบบใหม่
            'certificate_no'     => $certificateNo,
            'pdf_path'           => '', // จะอัปเดตหลังสร้าง PDF
            'pdf_hash'           => '', // จะอัปเดตหลังสร้าง PDF
            'verification_token' => $token,
            'student_snapshot'   => json_encode([
                'name'      => $recipient['recipient_name'],
                'email'     => $recipient['recipient_email'],
                'id_no'     => $recipient['recipient_id_no'],
                'event'     => $event['title'],
                'event_date' => $event['event_date'],
            ]),
            'signed_by'          => $event['signer_id'],
            'signed_at'          => date('Y-m-d H:i:s'),
            'issued_date'        => date('Y-m-d'),
            'expiry_date'        => null,
        ];

        $id = $this->certificateModel->insert($data);
        $data['id'] = $id;

        return $data;
    }

    /**
     * สร้างข้อมูล student สำหรับ PDF generator
     */
    protected function buildStudentData(array $recipient, array $event): array
    {
        $extraData = json_decode($recipient['extra_data'] ?? '{}', true);

        return [
            'tf_name'        => $recipient['recipient_name'],
            'tl_name'        => '',
            'login_uid'      => $recipient['recipient_id_no'] ?? '',
            'email'          => $recipient['recipient_email'] ?? '',
            'program_name'   => $extraData['program'] ?? '',
            'program_id'     => $extraData['program'] ?? null,
        ];
    }

    /**
     * ดึง path รูปลายเซ็นของผู้ลงนาม
     */
    protected function getSignerSignature(?int $signerId): ?string
    {
        if (!$signerId) {
            return null;
        }

        $signer = $this->userModel->find($signerId);
        if (!$signer || empty($signer['signature_image'])) {
            return null;
        }

        return FCPATH . 'uploads/signatures/' . $signer['signature_image'];
    }

    /**
     * สร้างเลขที่ Certificate
     */
    protected function generateCertificateNumber(): string
    {
        $prefix = $this->certConfig->certificateNumberPrefix . '-' . date('Y');

        $latest = $this->certificateModel
            ->like('certificate_no', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $running = 1;
        if ($latest) {
            $parts = explode('-', $latest['certificate_no']);
            $running = isset($parts[2]) ? ((int) $parts[2]) + 1 : $running;
        }

        return sprintf('%s-%0' . $this->certConfig->runningDigits . 'd', $prefix, $running);
    }

    /**
     * รวม layout_json จาก POST เข้า payload (ถ้ามี)
     *
     * @param array<string, mixed> $payload
     */
    protected function mergeLayoutJsonIntoPayload(array &$payload): ?string
    {
        $raw = $this->request->getPost('layout_json');
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }
        json_decode($raw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'layout_json ต้องเป็น JSON ที่ถูกต้อง';
        }
        $payload['layout_json'] = $raw;

        return null;
    }

    /**
     * @return array<string, string>|array{_error: string}
     */
    protected function applyBackgroundUpload(int $eventId): array
    {
        $file = $this->request->getFile('background_file');
        if (! $file || ! $file->isValid() || (int) $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        $ext = strtolower((string) $file->getExtension());
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        $kind = in_array($ext, ['jpg', 'png'], true) ? 'image' : ($ext === 'pdf' ? 'pdf' : null);
        if ($kind === null) {
            return ['_error' => 'รองรับเฉพาะไฟล์พื้นหลัง PDF, JPG หรือ PNG'];
        }

        if ($file->getSize() > $this->certConfig->maxTemplateSize) {
            return ['_error' => 'ไฟล์พื้นหลังใหญ่เกินกำหนด'];
        }

        $baseDir = rtrim($this->certConfig->eventBackgroundUploadPath, '/\\') . DIRECTORY_SEPARATOR . date('Y');
        if (! is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $newName = 'evt' . $eventId . '_' . time() . '.' . $ext;
        if (! $file->move($baseDir, $newName)) {
            return ['_error' => 'อัปโหลดไฟล์พื้นหลังไม่สำเร็จ'];
        }

        $rel = 'writable/uploads/cert_system/event_backgrounds/' . date('Y') . '/' . $newName;

        return [
            'background_file' => $rel,
            'background_kind' => $kind,
        ];
    }

    /**
     * Validation rules
     */
    protected function rules(bool $isCreate = true): array
    {
        return [
            'title'        => 'required|min_length[3]',
            'event_date'   => 'permit_empty|valid_date',
            'signer_id'    => 'permit_empty|integer',
            'status'       => 'permit_empty|in_list[draft,open,issued,closed]',
        ];
    }

    /**
     * แถว overlay สำหรับ CertPdfGenerator — ใช้เฉพาะค่า default จาก Config (ไฟล์พื้นหลังมาจากกิจกรรม)
     *
     * @return array<string, mixed>|null
     */
    protected function resolveTemplateForPdf(array $event): ?array
    {
        $defaults = json_decode($this->certConfig->eventCertificateDefaultLayoutJson, true);
        if (! is_array($defaults)) {
            $defaults = [];
        }
        $fieldMapping = $defaults['field_mapping'] ?? [
            'student_name' => ['x' => 90, 'y' => 145, 'font_size' => 22],
            'purpose'      => ['x' => 90, 'y' => 168, 'font_size' => 14],
        ];

        return [
            'id'            => 0,
            'template_file' => '',
            'field_mapping' => json_encode($fieldMapping, JSON_UNESCAPED_UNICODE),
            'signature_x'   => (float) ($defaults['signature_x'] ?? 150),
            'signature_y'   => (float) ($defaults['signature_y'] ?? 200),
            'qr_x'          => (float) ($defaults['qr_x'] ?? 18),
            'qr_y'          => (float) ($defaults['qr_y'] ?? 262),
            'qr_size'       => (float) ($defaults['qr_size'] ?? 22),
        ];
    }
}
