<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CertTemplateModel;
use App\Services\FileUploadService;
use Config\Certificate as CertificateConfig;

class CertTemplates extends BaseController
{
    protected CertTemplateModel $templateModel;
    protected CertificateConfig $certConfig;
    protected FileUploadService $uploadService;

    public function __construct()
    {
        $this->templateModel = new CertTemplateModel();
        $this->certConfig = config(CertificateConfig::class);
        $this->uploadService = new FileUploadService();
    }

    public function index()
    {
        $data = [
            'page_title' => 'จัดการเทมเพลตใบรับรอง',
            'templates'  => $this->templateModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('admin/cert_templates/index', $data);
    }

    public function create()
    {
        return view('admin/cert_templates/create_enhanced', [
            'page_title' => 'สร้างเทมเพลตใบรับรอง (PDF)',
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ตรวจสอบไฟล์จาก AJAX upload หรือ form upload
        $tempFile = $this->request->getPost('temp_file');
        if ($tempFile) {
            $templateFilePath = $this->moveTempFile($tempFile);
        } else {
            $templateFilePath = $this->handleTemplateUpload();
        }

        if (!$templateFilePath) {
            return redirect()->back()->withInput()->with('error', 'กรุณาอัปโหลดไฟล์ PDF');
        }

        $fieldMapping = $this->normalizeFieldMapping($this->request->getPost('field_mapping'));
        if ($fieldMapping === false) {
            return redirect()->back()->withInput()->with('error', 'Field Mapping ต้องเป็น JSON ที่ถูกต้อง');
        }

        $payload = $this->collectPayload($templateFilePath, $fieldMapping);
        $payload['created_by'] = session()->get('admin_id');

        $this->templateModel->insert($payload);

        return redirect()->to(base_url('admin/cert-templates'))->with('success', 'บันทึกเทมเพลตสำเร็จ');
    }

    public function edit(int $id)
    {
        $template = $this->templateModel->find($id);
        if (!$template) {
            return redirect()->to(base_url('admin/cert-templates'))->with('error', 'ไม่พบเทมเพลต');
        }

        $template['field_mapping_pretty'] = $this->prettyFieldMapping($template['field_mapping'] ?? null);

        return view('admin/cert_templates/edit', [
            'page_title' => 'แก้ไขเทมเพลต',
            'template'   => $template,
        ]);
    }

    public function update(int $id)
    {
        $template = $this->templateModel->find($id);
        if (!$template) {
            return redirect()->to(base_url('admin/cert-templates'))->with('error', 'ไม่พบเทมเพลต');
        }

        if (!$this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newFilePath = $this->handleTemplateUpload($template['template_file']);

        $fieldMapping = $this->normalizeFieldMapping($this->request->getPost('field_mapping'));
        if ($fieldMapping === false) {
            return redirect()->back()->withInput()->with('error', 'Field Mapping ต้องเป็น JSON ที่ถูกต้อง');
        }

        $payload = $this->collectPayload($newFilePath ?? $template['template_file'], $fieldMapping);

        $this->templateModel->update($id, $payload);

        return redirect()->to(base_url('admin/cert-templates'))->with('success', 'อัปเดตเทมเพลตสำเร็จ');
    }

    public function delete(int $id)
    {
        $template = $this->templateModel->find($id);
        if (!$template) {
            return redirect()->back()->with('error', 'ไม่พบเทมเพลต');
        }

        $this->deleteTemplateFile($template['template_file']);
        $this->templateModel->delete($id);

        return redirect()->to(base_url('admin/cert-templates'))->with('success', 'ลบเทมเพลตสำเร็จ');
    }

    protected function rules(bool $isCreate = true): array
    {
        $rules = [
            'name_th' => 'required|min_length[3]',
            'level'   => 'required|in_list[program,faculty]',
            'status'  => 'required|in_list[active,inactive]',
            'signature_x' => 'required|decimal',
            'signature_y' => 'required|decimal',
            'qr_x'        => 'required|decimal',
            'qr_y'        => 'required|decimal',
            'qr_size'     => 'required|decimal',
        ];

        if ($isCreate) {
            $rules['template_file'] = 'uploaded[template_file]|max_size[template_file,8192]|ext_in[template_file,pdf]';
        } else {
            $file = $this->request->getFile('template_file');
            if ($file && $file->isValid()) {
                $rules['template_file'] = 'max_size[template_file,8192]|ext_in[template_file,pdf]';
            }
        }

        return $rules;
    }

    protected function handleTemplateUpload(?string $existingPath = null): ?string
    {
        $file = $this->request->getFile('template_file');
        if (!$file || !$file->isValid()) {
            return null;
        }

        // ใช้ FileUploadService สำหรับจัดการ
        $savedPath = $this->uploadService->savePdfTemplate($file);

        if (!$savedPath) {
            return null;
        }

        // ลบไฟล์เก่าถ้ามี
        if ($existingPath && !str_contains($savedPath, $existingPath)) {
            $this->deleteTemplateFile($existingPath);
        }

        return $savedPath;
    }

    protected function moveTempFile(string $tempName): ?string
    {
        return $this->uploadService->moveTempFile($tempName, 'templates');
    }

    protected function deleteTemplateFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        $baseName = basename($path);
        $year = date('Y');
        $fullPath = $this->certConfig->templateUploadPath . $year . '/' . $baseName;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    protected function normalizeFieldMapping(?string $json)
    {
        $json = trim((string) $json);
        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }

    protected function collectPayload(?string $templateFilePath, $fieldMapping): array
    {
        return [
            'name_th'   => $this->request->getPost('name_th'),
            'name_en'   => $this->request->getPost('name_en'),
            'level'     => $this->request->getPost('level'),
            'template_file' => $templateFilePath,
            'field_mapping' => $fieldMapping,
            'signature_x'   => (float) $this->request->getPost('signature_x'),
            'signature_y'   => (float) $this->request->getPost('signature_y'),
            'qr_x'          => (float) $this->request->getPost('qr_x'),
            'qr_y'          => (float) $this->request->getPost('qr_y'),
            'qr_size'       => (float) $this->request->getPost('qr_size'),
            'status'        => $this->request->getPost('status'),
        ];
    }

    protected function prettyFieldMapping(?string $json): ?string
    {
        if (!$json) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $json;
        }

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
