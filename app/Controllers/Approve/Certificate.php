<?php

namespace App\Controllers\Approve;

use App\Controllers\BaseController;
use App\Models\CertApprovalModel;
use App\Models\CertRequestModel;
use App\Models\CertSignerModel;
use App\Models\CertTemplateModel;
use App\Models\PersonnelProgramModel;
use App\Models\ProgramModel;
use Config\Certificate as CertificateConfig;

class Certificate extends BaseController
{
    protected CertRequestModel $requestModel;
    protected CertTemplateModel $templateModel;
    protected CertApprovalModel $approvalModel;
    protected CertSignerModel $signerModel;
    protected PersonnelProgramModel $personnelProgramModel;
    protected ProgramModel $programModel;
    protected CertificateConfig $certConfig;

    public function __construct()
    {
        $this->requestModel = new CertRequestModel();
        $this->templateModel = new CertTemplateModel();
        $this->approvalModel = new CertApprovalModel();
        $this->signerModel = new CertSignerModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->programModel = new ProgramModel();
        $this->certConfig = config(CertificateConfig::class);
    }

    public function index()
    {
        $userUid = (int) session()->get('admin_id');
        $userRole = session()->get('admin_role');

        $pending = $this->getPendingForApprover($userUid, $userRole);

        return view('approve/certificates/index', [
            'page_title' => 'รออนุมัติใบรับรอง',
            'requests'   => $pending,
        ]);
    }

    public function show(int $id)
    {
        $userUid = (int) session()->get('admin_id');
        $userRole = session()->get('admin_role');

        $request = $this->requestModel
            ->select('cert_requests.*, cert_templates.name_th as template_name, cert_templates.level as template_level, student_user.th_name as student_name, student_user.thai_lastname as student_lastname')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->join('student_user', 'student_user.id = cert_requests.student_id', 'left')
            ->where('cert_requests.id', $id)
            ->first();

        if (!$request) {
            return redirect()->to(base_url('approve/certificates'))->with('error', 'ไม่พบคำขอ');
        }

        if (!$this->canApprove($request, $userUid, $userRole)) {
            return redirect()->to(base_url('approve/certificates'))->with('error', 'คุณไม่มีสิทธิ์อนุมัติคำขอนี้');
        }

        $timeline = $this->approvalModel->getTimeline($id);

        return view('approve/certificates/show', [
            'page_title' => 'อนุมัติคำขอ #' . $request['request_number'],
            'request'    => $request,
            'timeline'   => $timeline,
            'can_approve' => $request['status'] === CertRequestModel::STATUS_VERIFIED,
        ]);
    }

    public function approve(int $id)
    {
        $password = $this->request->getPost('password');
        if (empty($password)) {
            return redirect()->back()->with('error', 'กรุณายืนยันรหัสผ่านเพื่ออนุมัติ');
        }

        $userUid = (int) session()->get('admin_id');
        $userRole = session()->get('admin_role');

        $request = $this->requestModel->find($id);
        if (!$request || $request['status'] !== CertRequestModel::STATUS_VERIFIED) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติคำขอนี้ได้');
        }

        if (!$this->canApprove($request, $userUid, $userRole)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์อนุมัติคำขอนี้');
        }

        $this->requestModel->update($id, [
            'status'      => CertRequestModel::STATUS_APPROVED,
            'approved_by' => $userUid,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->approvalModel->log($id, 'approve', $userUid, $this->getActorRole($request, $userUid), 'อนุมัติคำขอ');

        return redirect()->to(base_url('approve/certificates/' . $id))->with('success', 'อนุมัติคำขอเรียบร้อย รอสร้าง PDF');
    }

    public function reject(int $id)
    {
        $reason = $this->request->getPost('reason');
        if (empty($reason)) {
            return redirect()->back()->with('error', 'กรุณาระบุเหตุผล');
        }

        $userUid = (int) session()->get('admin_id');
        $userRole = session()->get('admin_role');

        $request = $this->requestModel->find($id);
        if (!$request) {
            return redirect()->back()->with('error', 'ไม่พบคำขอ');
        }

        if (!$this->canApprove($request, $userUid, $userRole)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ปฏิเสธคำขอนี้');
        }

        $this->requestModel->update($id, [
            'status'          => CertRequestModel::STATUS_REJECTED,
            'rejected_reason' => $reason,
        ]);

        $this->approvalModel->log($id, 'reject', $userUid, $this->getActorRole($request, $userUid), $reason);

        return redirect()->to(base_url('approve/certificates/' . $id))->with('success', 'ปฏิเสธคำขอเรียบร้อย');
    }

    public function history()
    {
        $userUid = (int) session()->get('admin_id');

        $approved = $this->requestModel
            ->select('cert_requests.*, cert_templates.name_th as template_name, student_user.th_name as student_name, student_user.thai_lastname as student_lastname')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->join('student_user', 'student_user.id = cert_requests.student_id', 'left')
            ->where('cert_requests.approved_by', $userUid)
            ->orderBy('cert_requests.approved_at', 'DESC')
            ->findAll(50);

        return view('approve/certificates/history', [
            'page_title' => 'ประวัติการอนุมัติ',
            'requests'   => $approved,
        ]);
    }

    protected function getPendingForApprover(int $userUid, string $userRole): array
    {
        $builder = $this->requestModel
            ->select('cert_requests.*, cert_templates.name_th as template_name, student_user.th_name as student_name, student_user.thai_lastname as student_lastname')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->join('student_user', 'student_user.id = cert_requests.student_id', 'left')
            ->where('cert_requests.status', CertRequestModel::STATUS_VERIFIED);

        if ($userRole === 'super_admin' || $userRole === 'admin') {
            return $builder->orderBy('cert_requests.created_at', 'ASC')->findAll();
        }

        $chairPrograms = $this->personnelProgramModel
            ->where('personnel_id', $userUid)
            ->like('role_in_curriculum', 'ประธาน')
            ->findAll();

        $programIds = array_column($chairPrograms, 'program_id');

        if (!empty($programIds)) {
            $builder->groupStart()
                ->whereIn('cert_requests.program_id', $programIds)
                ->orWhere('cert_requests.level', 'faculty')
                ->groupEnd();
        } else {
            $builder->where('cert_requests.level', 'faculty');
        }

        return $builder->orderBy('cert_requests.created_at', 'ASC')->findAll();
    }

    protected function canApprove(array $request, int $userUid, string $userRole): bool
    {
        if ($userRole === 'super_admin' || $userRole === 'admin') {
            return true;
        }

        if ($request['level'] === 'faculty') {
            $deanSigner = $this->signerModel
                ->where('user_uid', $userUid)
                ->where('signer_role', 'dean')
                ->where('is_active', 1)
                ->first();
            return !empty($deanSigner);
        }

        if ($request['level'] === 'program' && !empty($request['program_id'])) {
            $hasChairRole = $this->personnelProgramModel
                ->where('personnel_id', $userUid)
                ->where('program_id', $request['program_id'])
                ->like('role_in_curriculum', 'ประธาน')
                ->first();
            return !empty($hasChairRole);
        }

        return false;
    }

    protected function getActorRole(array $request, int $userUid): string
    {
        if ($request['level'] === 'faculty') {
            return 'dean';
        }
        return 'program_chair';
    }
}
