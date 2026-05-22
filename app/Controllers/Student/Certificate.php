<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\CertEventRecipientModel;
use App\Models\CertificateModel;
use App\Models\StudentUserModel;
use App\Services\CertificateEmailService;
use Config\Certificate as CertificateConfig;

/**
 * Student Certificate Controller - ดูและดาวน์โหลด Certificate ที่ได้รับ
 * ระบบใหม่: นักศึกษาไม่สามารถขอ Certificate เองได้ แต่จะได้รับจาก Admin ผ่านกิจกรรม
 */
class Certificate extends BaseController
{
    protected CertEventRecipientModel $recipientModel;
    protected CertificateModel $certificateModel;
    protected StudentUserModel $studentModel;
    protected CertificateConfig $certConfig;

    public function __construct()
    {
        $this->recipientModel = new CertEventRecipientModel();
        $this->certificateModel = new CertificateModel();
        $this->studentModel = new StudentUserModel();
        $this->certConfig = config(CertificateConfig::class);
    }

    /**
     * แสดงรายการ Certificate ที่นักศึกษาได้รับทั้งหมด
     */
    public function index()
    {
        $studentId = (int) session()->get('student_id');
        $certificates = $this->recipientModel->getByStudent($studentId);

        return view('student/certificates/index', [
            'page_title'   => 'ประวัติการเข้าอบรม / ใบประกาศ',
            'certificates' => $certificates,
        ]);
    }

    /**
     * แสดงรายละเอียด Certificate
     */
    public function show(int $id)
    {
        $studentId = (int) session()->get('student_id');

        // ดึงข้อมูลจาก recipient table
        $recipient = $this->recipientModel
            ->select('cert_event_recipients.*, 
                cert_events.title as event_title,
                cert_events.event_date,
                certificates.certificate_no,
                certificates.pdf_path,
                certificates.verification_token,
                certificates.issued_date,
                certificates.download_count')
            ->join('cert_events', 'cert_events.id = cert_event_recipients.event_id')
            ->join('certificates', 'certificates.id = cert_event_recipients.certificate_id', 'left')
            ->where('cert_event_recipients.id', $id)
            ->where('cert_event_recipients.student_id', $studentId)
            ->first();

        if (!$recipient) {
            return redirect()->to(base_url('student/certificates'))->with('error', 'ไม่พบใบรับรอง');
        }

        return view('student/certificates/show', [
            'page_title'  => 'รายละเอียดใบรับรอง',
            'recipient'   => $recipient,
        ]);
    }

    /**
     * ดาวน์โหลด Certificate PDF
     */
    public function download(int $id)
    {
        $studentId = (int) session()->get('student_id');

        $recipient = $this->recipientModel
            ->select('cert_event_recipients.*, certificates.pdf_path, certificates.id as cert_id')
            ->join('certificates', 'certificates.id = cert_event_recipients.certificate_id')
            ->where('cert_event_recipients.id', $id)
            ->where('cert_event_recipients.student_id', $studentId)
            ->where('cert_event_recipients.status', 'issued')
            ->first();

        if (!$recipient || empty($recipient['pdf_path'])) {
            return redirect()->back()->with('error', 'ไม่พบไฟล์ใบรับรอง');
        }

        $mailService = new CertificateEmailService();
        $fullPath = $mailService->resolvePdfAbsolutePath((string) $recipient['pdf_path'], $this->certConfig);
        if ($fullPath === null || ! is_file($fullPath)) {
            return redirect()->back()->with('error', 'ไฟล์ถูกลบหรือไม่สามารถเข้าถึงได้');
        }

        // อัปเดต download count
        $this->certificateModel->update($recipient['cert_id'], [
            'download_count'     => (int) $this->certificateModel->find($recipient['cert_id'])['download_count'] + 1,
            'last_downloaded_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->download($fullPath, null)->setFileName(basename($recipient['pdf_path']));
    }

    /**
     * ตรวจสอบ Certificate จาก verify token
     */
    public function verify(string $token)
    {
        // Redirect ไปยังหน้าตรวจสอบสาธารณะ
        return redirect()->to(base_url('verify/' . $token));
    }
}
