<?php

namespace App\Controllers;

use App\Models\CertificateModel;
use App\Models\CertRequestModel;
use App\Models\CertTemplateModel;
use App\Models\StudentUserModel;
use App\Models\UserModel;

class CertVerify extends BaseController
{
    protected CertificateModel $certificateModel;
    protected CertRequestModel $requestModel;
    protected CertTemplateModel $templateModel;
    protected StudentUserModel $studentModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->certificateModel = new CertificateModel();
        $this->requestModel = new CertRequestModel();
        $this->templateModel = new CertTemplateModel();
        $this->studentModel = new StudentUserModel();
        $this->userModel = new UserModel();
    }

    public function verify(string $token)
    {
        $certificate = $this->certificateModel->findByToken($token);

        if (!$certificate) {
            return view('cert_verify/invalid', [
                'page_title' => 'ใบรับรองไม่ถูกต้อง',
                'message'    => 'ไม่พบข้อมูลใบรับรอง',
            ]);
        }

        $request = $this->requestModel->find($certificate['request_id']);
        $template = $this->templateModel->find($request['template_id'] ?? null);
        $signer = $certificate['signed_by'] ? $this->userModel->find($certificate['signed_by']) : null;

        $data = [
            'page_title'  => 'ตรวจสอบใบรับรอง',
            'certificate' => $certificate,
            'request'     => $request,
            'template'    => $template,
            'signer'      => $signer,
            'is_valid'    => $certificate['is_revoked'] == 0,
        ];

        return view('cert_verify/verify', $data);
    }

    public function checkHash()
    {
        $file = $this->request->getFile('pdf');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['valid' => false, 'message' => 'ไม่พบไฟล์']);
        }

        $hash = hash_file('sha256', $file->getTempName());

        $cert = $this->certificateModel->where('pdf_hash', $hash)->first();

        if (!$cert) {
            return $this->response->setJSON(['valid' => false, 'message' => 'ไฟล์ไม่ตรงกับระบบ หรือถูกแก้ไข']);
        }

        return $this->response->setJSON([
            'valid'         => true,
            'certificate_no'=> $cert['certificate_no'],
            'issued_date'   => $cert['issued_date'],
            'is_revoked'    => $cert['is_revoked'] == 1,
        ]);
    }
}
