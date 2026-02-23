<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CertPdfGenerator;
use App\Libraries\CertQrGenerator;
use App\Models\CertEventModel;
use App\Models\CertEventRecipientModel;
use App\Models\CertTemplateModel;
use App\Models\CertificateModel;
use App\Models\StudentUserModel;
use App\Models\UserModel;
use Config\Certificate as CertificateConfig;

/**
 * CertEvents Controller - จัดการกิจกรรม/หัวข้ออบรมที่จะออก Certificate
 */
class CertEvents extends BaseController
{
    protected CertEventModel $eventModel;
    protected CertEventRecipientModel $recipientModel;
    protected CertTemplateModel $templateModel;
    protected CertificateModel $certificateModel;
    protected StudentUserModel $studentModel;
    protected UserModel $userModel;
    protected CertificateConfig $certConfig;

    public function __construct()
    {
        $this->eventModel = new CertEventModel();
        $this->recipientModel = new CertEventRecipientModel();
        $this->templateModel = new CertTemplateModel();
        $this->certificateModel = new CertificateModel();
        $this->studentModel = new StudentUserModel();
        $this->userModel = new UserModel();
        $this->certConfig = config(CertificateConfig::class);
    }

    /**
     * รายการกิจกรรมทั้งหมด
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $events = $this->eventModel->getAllWithStats($status, 50);

        // Load templates and signers for modal
        $templates = $this->templateModel->getActiveTemplates();
        $signers = $this->userModel->where('active', 1)
            ->whereIn('role', ['super_admin', 'faculty_admin', 'admin'])
            ->findAll();

        return view('admin/cert_events/index', [
            'page_title' => 'จัดการกิจกรรมใบรับรอง',
            'events'     => $events,
            'filter_status' => $status,
            'templates'  => $templates,
            'signers'    => $signers,
        ]);
    }

    /**
     * ฟอร์มสร้างกิจกรรมใหม่
     */
    public function create()
    {
        $templates = $this->templateModel->getActiveTemplates();
        $signers = $this->userModel->where('active', 1)
            ->whereIn('role', ['super_admin', 'faculty_admin', 'admin'])
            ->findAll();

        return view('admin/cert_events/create', [
            'page_title' => 'สร้างกิจกรรมใบรับรอง',
            'templates'  => $templates,
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
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'event_date'  => $this->request->getPost('event_date'),
            'template_id' => (int) $this->request->getPost('template_id'),
            'signer_id'   => (int) $this->request->getPost('signer_id') ?: null,
            'status'      => $this->request->getPost('status') ?? 'draft',
            'created_by'  => session()->get('admin_id'),
        ];

        $eventId = $this->eventModel->insert($payload);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'สร้างกิจกรรมเรียบร้อย',
                'event_id' => $eventId,
                'redirect' => base_url('admin/cert-events/' . $eventId)
            ]);
        }

        return redirect()->to(base_url('admin/cert-events/' . $eventId))
            ->with('success', 'สร้างกิจกรรมเรียบร้อย');
    }

    /**
     * แสดงรายละเอียดกิจกรรม + รายชื่อผู้รับ
     */
    public function show(int $id)
    {
        $event = $this->eventModel->getWithDetails($id);
        if (!$event) {
            return redirect()->to(base_url('admin/cert-events'))->with('error', 'ไม่พบกิจกรรม');
        }

        $recipients = $this->recipientModel->getByEvent($id);
        $students = $this->studentModel->where('status', 'active')->findAll();

        return view('admin/cert_events/show', [
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
            return redirect()->to(base_url('admin/cert-events'))->with('error', 'ไม่พบกิจกรรม');
        }

        // AJAX request - return JSON
        if ($this->request->isAJAX() || $this->request->getGet('ajax')) {
            return $this->response->setJSON([
                'success' => true,
                'event'   => $event
            ]);
        }

        $templates = $this->templateModel->getActiveTemplates();
        $signers = $this->userModel->where('active', 1)
            ->whereIn('role', ['super_admin', 'faculty_admin', 'admin'])
            ->findAll();

        return view('admin/cert_events/edit', [
            'page_title' => 'แก้ไขกิจกรรม: ' . $event['title'],
            'event'      => $event,
            'templates'  => $templates,
            'signers'    => $signers,
        ]);
    }

    /**
     * อัพเดทกิจกรรม - รองรับทั้ง HTML form และ AJAX
     */
    public function update(int $id)
    {
        $event = $this->eventModel->find($id);
        if (!$event) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบกิจกรรม']);
            }
            return redirect()->to(base_url('admin/cert-events'))->with('error', 'ไม่พบกิจกรรม');
        }

        if (!$this->validate($this->rules(false))) {
            $errors = $this->validator->getErrors();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $errors]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'event_date'  => $this->request->getPost('event_date'),
            'template_id' => (int) $this->request->getPost('template_id'),
            'signer_id'   => (int) $this->request->getPost('signer_id') ?: null,
            'status'      => $this->request->getPost('status') ?? $event['status'],
        ];

        $this->eventModel->update($id, $payload);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'อัพเดทกิจกรรมเรียบร้อย'
            ]);
        }

        return redirect()->to(base_url('admin/cert-events/' . $id))
            ->with('success', 'อัพเดทกิจกรรมเรียบร้อย');
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

        return redirect()->to(base_url('admin/cert-events'))
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

        if (!$this->validate([
            'recipient_name' => 'required|min_length[2]',
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
            'recipient_email' => $this->request->getPost('recipient_email') ?: ($student['email'] ?? null),
            'recipient_id_no' => $this->request->getPost('recipient_id_no') ?: ($student['login_uid'] ?? null),
            'extra_data'      => json_encode([
                'program' => $this->request->getPost('program') ?? ($student['program_id'] ?? null),
                'note'    => $this->request->getPost('note'),
            ]),
            'status'          => 'pending',
        ];

        $this->recipientModel->insert($payload);

        return redirect()->to(base_url('admin/cert-events/' . $eventId))
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

        if ($recipient['status'] === 'issued') {
            return redirect()->back()->with('error', 'ไม่สามารถลบผู้ที่ออก Certificate แล้ว');
        }

        $this->recipientModel->delete($recipientId);

        return redirect()->to(base_url('admin/cert-events/' . $recipient['event_id']))
            ->with('success', 'ลบผู้รับเรียบร้อย');
    }

    /**
     * ออก Certificate ให้ผู้รับทั้งหมดที่ยัง pending
     */
    public function issueCertificates(int $eventId)
    {
        $event = $this->eventModel->getWithDetails($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }

        if ($event['status'] === 'draft') {
            return redirect()->back()->with('error', 'กรุณาเปลี่ยนสถานะกิจกรรมเป็น Open ก่อนออก Certificate');
        }

        $recipients = $this->recipientModel->getPendingByEvent($eventId);
        if (empty($recipients)) {
            return redirect()->back()->with('error', 'ไม่มีผู้รับที่รอการออก Certificate');
        }

        $pdfGenerator = new CertPdfGenerator();
        $successCount = 0;
        $failCount = 0;

        // โหลดข้อมูล template สำหรับสร้าง PDF
        $templateModel = new \App\Models\CertTemplateModel();
        $template = $templateModel->find($event['template_id']);
        if (!$template) {
            return redirect()->back()->with('error', 'ไม่พบเทมเพลตที่กำหนดไว้');
        }

        foreach ($recipients as $recipient) {
            try {
                // สร้าง certificate record
                $certificateData = $this->createCertificateRecord($recipient, $event);

                // สร้าง PDF
                $studentData = $this->buildStudentData($recipient, $event);
                $requestData = [
                    'request_number' => $certificateData['certificate_no'],
                    'purpose'        => $event['title'],
                ];

                $signaturePath = $this->getSignerSignature($event['signer_id']);
                $pdfPath = $pdfGenerator->generate(
                    $requestData,
                    $template,
                    $studentData,
                    $certificateData['verification_token'],
                    $signaturePath
                );

                if ($pdfPath) {
                    // อัพเดท certificate ด้วย pdf_path
                    $this->certificateModel->update($certificateData['id'], [
                        'pdf_path' => $pdfPath,
                    ]);

                    // อัพเดท recipient
                    $this->recipientModel->markAsIssued($recipient['id'], $certificateData['id']);
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

        // อัพเดทสถานะกิจกรรมเป็น issued ถ้ามีการออกสำเร็จ
        if ($successCount > 0) {
            $this->eventModel->updateStatus($eventId, 'issued');
        }

        $message = "ออก Certificate สำเร็จ {$successCount} รายการ";
        if ($failCount > 0) {
            $message .= ", ไม่สำเร็จ {$failCount} รายการ";
        }

        return redirect()->to(base_url('admin/cert-events/' . $eventId))
            ->with($failCount > 0 ? 'error' : 'success', $message);
    }

    /**
     * หน้า Import รายชื่อจาก CSV/Excel
     */
    public function importForm(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->to(base_url('admin/cert-events'))->with('error', 'ไม่พบกิจกรรม');
        }

        return view('admin/cert_events/import', [
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
            return redirect()->to(base_url('admin/cert-events'))->with('error', 'ไม่พบกิจกรรม');
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
            $idNo = $data['student_id'] ?? $data['รหัสนักศึกษา'] ?? null;
            $email = $data['email'] ?? $data['อีเมล'] ?? null;

            if ($idNo || $email) {
                $studentQuery = $this->studentModel;
                if ($idNo) {
                    $studentQuery->where('login_uid', $idNo);
                } elseif ($email) {
                    $studentQuery->where('email', $email);
                }
                $student = $studentQuery->first();
                if ($student) {
                    $studentId = $student['id'];
                }
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

        return redirect()->to(base_url('admin/cert-events/' . $eventId))
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

        $recipients = $this->recipientModel->getByEvent($eventId);

        $filename = 'recipients_' . $eventId . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['ชื่อ', 'อีเมล', 'รหัสนักศึกษา', 'สถานะ', 'เลขที่ Certificate', 'วันที่ออก']);

        foreach ($recipients as $recipient) {
            fputcsv($output, [
                $recipient['recipient_name'],
                $recipient['recipient_email'],
                $recipient['recipient_id_no'],
                $recipient['status'],
                $recipient['certificate_no'] ?? '-',
                $recipient['issued_date'] ?? '-',
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
            'pdf_path'           => '', // จะอัพเดทหลังสร้าง PDF
            'pdf_hash'           => '', // จะอัพเดทหลังสร้าง PDF
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
            'th_name'        => $recipient['recipient_name'],
            'thai_lastname'  => '', // ชื่อเต็มอยู่ใน th_name แล้ว
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
     * Validation rules
     */
    protected function rules(bool $isCreate = true): array
    {
        return [
            'title'       => 'required|min_length[3]',
            'template_id' => 'required|integer',
            'event_date'  => 'permit_empty|valid_date',
            'signer_id'   => 'permit_empty|integer',
            'status'      => 'permit_empty|in_list[draft,open,issued,closed]',
        ];
    }
}
