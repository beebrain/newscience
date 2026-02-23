<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CertApprovalModel;
use App\Models\CertRequestModel;
use App\Models\CertTemplateModel;
use App\Models\StudentUserModel;
use Config\Certificate as CertificateConfig;

class Certificates extends BaseController
{
    protected CertRequestModel $requestModel;
    protected CertTemplateModel $templateModel;
    protected CertApprovalModel $approvalModel;
    protected StudentUserModel $studentModel;
    protected CertificateConfig $certConfig;

    public function __construct()
    {
        $this->requestModel = new CertRequestModel();
        $this->templateModel = new CertTemplateModel();
        $this->approvalModel = new CertApprovalModel();
        $this->studentModel = new StudentUserModel();
        $this->certConfig = config(CertificateConfig::class);
    }

    public function index()
    {
        $status = $this->request->getGet('status');
        $builder = $this->requestModel
            ->select('cert_requests.*, cert_templates.name_th as template_name, student_user.th_name as student_name, student_user.thai_lastname as student_lastname')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->join('student_user', 'student_user.id = cert_requests.student_id', 'left')
            ->orderBy('cert_requests.id', 'DESC');

        if ($status) {
            $builder->where('cert_requests.status', $status);
        }

        return view('admin/certificates/index', [
            'page_title' => 'จัดการคำขอใบรับรอง',
            'requests'   => $builder->findAll(100),
            'filter_status' => $status,
        ]);
    }

    public function pending()
    {
        $requests = $this->requestModel
            ->select('cert_requests.*, cert_templates.name_th as template_name, student_user.th_name as student_name, student_user.thai_lastname as student_lastname')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->join('student_user', 'student_user.id = cert_requests.student_id', 'left')
            ->whereIn('cert_requests.status', [CertRequestModel::STATUS_PENDING, CertRequestModel::STATUS_VERIFIED])
            ->orderBy('cert_requests.created_at', 'ASC')
            ->findAll();

        return view('admin/certificates/pending', [
            'page_title' => 'คำขอรอตรวจสอบ',
            'requests'   => $requests,
        ]);
    }

    public function show(int $id)
    {
        $request = $this->requestModel
            ->select('cert_requests.*, cert_templates.name_th as template_name, cert_templates.level as template_level, student_user.th_name as student_name, student_user.thai_lastname as student_lastname, student_user.email as student_email, student_user.program_id')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->join('student_user', 'student_user.id = cert_requests.student_id', 'left')
            ->where('cert_requests.id', $id)
            ->first();

        if (!$request) {
            return redirect()->to(base_url('admin/certificates'))->with('error', 'ไม่พบคำขอ');
        }

        $timeline = $this->approvalModel->getTimeline($id);

        return view('admin/certificates/show', [
            'page_title' => 'รายละเอียดคำขอ #' . $request['request_number'],
            'request'    => $request,
            'timeline'   => $timeline,
        ]);
    }

    public function verify(int $id)
    {
        $request = $this->requestModel->find($id);
        if (!$request || $request['status'] !== CertRequestModel::STATUS_PENDING) {
            return redirect()->back()->with('error', 'ไม่สามารถตรวจสอบคำขอนี้ได้');
        }

        $staffId = (int) session()->get('admin_id');

        $this->requestModel->update($id, [
            'status'      => CertRequestModel::STATUS_VERIFIED,
            'verified_by' => $staffId,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        $this->approvalModel->log($id, 'verify', $staffId, 'staff', 'ตรวจสอบเอกสารถูกต้อง');

        return redirect()->to(base_url('admin/certificates/' . $id))->with('success', 'ตรวจสอบคำขอเรียบร้อย');
    }

    public function reject(int $id)
    {
        $reason = $this->request->getPost('reason');
        if (empty($reason)) {
            return redirect()->back()->with('error', 'กรุณาระบุเหตุผลการปฏิเสธ');
        }

        $request = $this->requestModel->find($id);
        if (!$request || !in_array($request['status'], [CertRequestModel::STATUS_PENDING, CertRequestModel::STATUS_VERIFIED])) {
            return redirect()->back()->with('error', 'ไม่สามารถปฏิเสธคำขอนี้ได้');
        }

        $staffId = (int) session()->get('admin_id');

        $this->requestModel->update($id, [
            'status'          => CertRequestModel::STATUS_REJECTED,
            'rejected_reason' => $reason,
        ]);

        $this->approvalModel->log($id, 'reject', $staffId, 'staff', $reason);

        return redirect()->to(base_url('admin/certificates/' . $id))->with('success', 'ปฏิเสธคำขอเรียบร้อย');
    }
}
