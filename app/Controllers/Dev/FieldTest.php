<?php

namespace App\Controllers\Dev;

use App\Controllers\BaseController;
use App\Models\CertEventModel;
use App\Models\CertTemplateModel;
use App\Models\CertEventRecipientModel;
use App\Models\CertificateModel;
use App\Libraries\CertPdfGenerator;
use App\Libraries\PdfTextExtractor;

/**
 * Field Mapping Test Controller - ตรวจสอบการเชื่อมโยงฟิลด์ทั้งระบบ
 * 
 * URL: /dev/field-test
 */
class FieldTest extends BaseController
{
    protected CertEventModel $eventModel;
    protected CertTemplateModel $templateModel;
    protected CertEventRecipientModel $recipientModel;
    protected CertificateModel $certificateModel;

    public function __construct()
    {
        $this->eventModel = new CertEventModel();
        $this->templateModel = new CertTemplateModel();
        $this->recipientModel = new CertEventRecipientModel();
        $this->certificateModel = new CertificateModel();
    }

    /**
     * หน้าทดสอบการเชื่อมโยงฟิลด์ทั้งหมด
     */
    public function index()
    {
        $tests = [
            'pdf_generator_fields' => $this->testPdfGeneratorFields(),
            'form_database_mapping' => $this->testFormDatabaseMapping(),
            'template_field_mapping' => $this->testTemplateFieldMapping(),
            'student_data_fields' => $this->testStudentDataFields(),
            'recipient_data_flow' => $this->testRecipientDataFlow(),
        ];

        return view('dev/field_test', [
            'page_title' => 'ทดสอบการเชื่อมโยงฟิลด์ (Field Mapping Test)',
            'tests' => $tests,
        ]);
    }

    /**
     * ทดสอบฟิลด์ใน CertPdfGenerator
     */
    protected function testPdfGeneratorFields(): array
    {
        $generator = new CertPdfGenerator();

        // ใช้ reflection เพื่อดึง getFieldValue method
        $reflection = new \ReflectionMethod($generator, 'getFieldValue');
        $reflection->setAccessible(true);

        $testStudent = [
            'th_name' => 'สมชาย',
            'thai_lastname' => 'ใจดี',
            'login_uid' => '123456789',
            'program_name' => 'วิทยาการคอมพิวเตอร์',
        ];

        $testRequest = [
            'request_number' => 'CERT-2024-0001',
            'purpose' => 'ทดสอบระบบ',
        ];

        $expectedFields = [
            'student_name' => 'สมชาย ใจดี',
            'student_id' => '123456789',
            'program_name' => 'วิทยาการคอมพิวเตอร์',
            'request_number' => 'CERT-2024-0001',
            'purpose' => 'ทดสอบระบบ',
            'date' => date('d/m/Y'),
        ];

        $results = [];
        foreach ($expectedFields as $field => $expected) {
            $actual = $reflection->invoke($generator, $field, $testStudent, $testRequest);
            $results[$field] = [
                'expected' => $expected,
                'actual' => $actual,
                'match' => $actual === $expected || ($field === 'date' && strlen($actual) > 0),
                'is_null' => $actual === null,
            ];
        }

        // ทดสอบฟิลด์ที่ไม่มีอยู่จริง
        $invalidField = $reflection->invoke($generator, 'nonexistent_field', $testStudent, $testRequest);
        $results['_invalid_field_test'] = [
            'field' => 'nonexistent_field',
            'result' => $invalidField,
            'is_null' => $invalidField === null,
            'correct_behavior' => $invalidField === null,
        ];

        $allMatch = !array_filter($results, fn($r) => isset($r['match']) && !$r['match']);

        return [
            'total_fields' => count($expectedFields),
            'passed' => count(array_filter($results, fn($r) => ($r['match'] ?? $r['correct_behavior'] ?? true))),
            'results' => $results,
            'all_passed' => $allMatch,
        ];
    }

    /**
     * ทดสอบการเชื่อมโยงระหว่าง Form และ Database
     */
    protected function testFormDatabaseMapping(): array
    {
        $formFields = [
            'title' => ['required' => true, 'type' => 'text'],
            'description' => ['required' => false, 'type' => 'textarea'],
            'event_date' => ['required' => false, 'type' => 'date'],
            'template_id' => ['required' => true, 'type' => 'select'],
            'signer_id' => ['required' => false, 'type' => 'select'],
            'status' => ['required' => true, 'type' => 'select'],
        ];

        $dbFields = $this->eventModel->allowedFields;

        // ตรวจสอบว่าทุกฟิลด์ใน form มีใน database
        $formToDb = [];
        foreach ($formFields as $field => $config) {
            $formToDb[$field] = [
                'in_form' => true,
                'in_database' => in_array($field, $dbFields),
                'required_in_form' => $config['required'],
                'type' => $config['type'],
            ];
        }

        // ตรวจสอบฟิลด์ใน database ที่ไม่มีใน form
        $dbOnlyFields = array_filter($dbFields, fn($f) => !isset($formFields[$f]) && $f !== 'created_by');

        return [
            'form_fields_count' => count($formFields),
            'database_fields_count' => count($dbFields),
            'form_to_db_mapping' => $formToDb,
            'database_only_fields' => $dbOnlyFields,
            'all_form_fields_in_db' => !array_filter($formToDb, fn($m) => !$m['in_database']),
        ];
    }

    /**
     * ทดสอบ Template Field Mapping (JSON structure)
     */
    protected function testTemplateFieldMapping(): array
    {
        // ดึง template ตัวอย่าง (ถ้ามี)
        $templates = $this->templateModel->getActiveTemplates();

        $results = [];
        $extractor = new PdfTextExtractor();
        $suggestedFields = $extractor->suggestFields('ทดสอบ ชื่อ นามสกุล รหัสนักศึกษา หลักสูตร');

        foreach ($templates as $template) {
            $mapping = json_decode($template['field_mapping'] ?? '{}', true);

            $results[] = [
                'template_id' => $template['id'],
                'template_name' => $template['name_th'],
                'has_field_mapping' => !empty($mapping),
                'mapped_fields' => array_keys($mapping),
                'has_coordinates' => !empty($template['signature_x']) && !empty($template['qr_x']),
                'signature_coords' => [
                    'x' => $template['signature_x'] ?? null,
                    'y' => $template['signature_y'] ?? null,
                ],
                'qr_coords' => [
                    'x' => $template['qr_x'] ?? null,
                    'y' => $template['qr_y'] ?? null,
                    'size' => $template['qr_size'] ?? null,
                ],
            ];
        }

        // ตรวจสอบว่า fields ใน mapping มีใน CertPdfGenerator หรือไม่
        $generator = new CertPdfGenerator();
        $reflection = new \ReflectionMethod($generator, 'getFieldValue');
        $reflection->setAccessible(true);

        $supportedFields = ['student_name', 'student_id', 'program_name', 'request_number', 'purpose', 'date', 'date_thai'];

        return [
            'total_templates' => count($templates),
            'templates_with_mapping' => count(array_filter($results, fn($r) => $r['has_field_mapping'])),
            'templates_with_coords' => count(array_filter($results, fn($r) => $r['has_coordinates'])),
            'supported_fields_in_generator' => $supportedFields,
            'sample_mappings' => $results,
        ];
    }

    /**
     * ทดสอบ Student Data Fields
     */
    protected function testStudentDataFields(): array
    {
        $db = \Config\Database::connect();

        // ดึงโครงสร้างตาราง student_user
        $fields = $db->getFieldData('student_user');
        $fieldNames = array_map(fn($f) => $f->name, $fields);

        // ฟิลด์ที่ CertPdfGenerator ใช้
        $requiredFields = ['th_name', 'thai_lastname', 'login_uid', 'program_id']; // program_id ใช้สำหรับ resolve program_name

        $fieldStatus = [];
        foreach ($requiredFields as $field) {
            $fieldStatus[$field] = [
                'exists' => in_array($field, $fieldNames),
                'actual_name' => $this->findActualFieldName($fieldNames, $field),
            ];
        }

        // หาฟิลด์ที่อาจใช้แทนได้
        $alternativeFields = [
            'name_th' => in_array('name_th', $fieldNames),
            'first_name' => in_array('first_name', $fieldNames),
            'fname' => in_array('fname', $fieldNames),
            'student_id' => in_array('student_id', $fieldNames),
            'id' => in_array('id', $fieldNames),
        ];

        return [
            'required_fields' => $requiredFields,
            'field_status' => $fieldStatus,
            'alternative_fields_available' => array_filter($alternativeFields),
            'all_required_exist' => !array_filter($fieldStatus, fn($s) => !$s['exists']),
        ];
    }

    /**
     * ทดสอบ Data Flow ของ Recipients
     */
    protected function testRecipientDataFlow(): array
    {
        // ดึง recipients ตัวอย่าง (ถ้ามี)
        $db = \Config\Database::connect();
        $builder = $db->table('cert_event_recipients');
        $sampleRecipients = $builder->limit(3)->get()->getResultArray();

        $results = [];
        foreach ($sampleRecipients as $recipient) {
            // ดึงข้อมูล student ที่เชื่อมโยง
            $studentData = null;
            if (!empty($recipient['student_id'])) {
                $studentBuilder = $db->table('student_user');
                $studentData = $studentBuilder->where('login_uid', $recipient['student_id'])
                    ->orWhere('id', $recipient['student_id'])
                    ->get()
                    ->getRowArray();
            }

            $results[] = [
                'recipient_id' => $recipient['id'],
                'event_id' => $recipient['event_id'],
                'student_id_in_recipient' => $recipient['student_id'] ?? null,
                'student_found' => !empty($studentData),
                'student_data_available' => $studentData ? [
                    'th_name' => $studentData['th_name'] ?? 'NOT FOUND',
                    'thai_lastname' => $studentData['thai_lastname'] ?? 'NOT FOUND',
                    'program_name' => $studentData['program_name'] ?? 'NOT FOUND',
                ] : null,
            ];
        }

        $foundCount = count(array_filter($results, fn($r) => $r['student_found']));

        return [
            'sample_count' => count($sampleRecipients),
            'students_found' => $foundCount,
            'students_not_found' => count($sampleRecipients) - $foundCount,
            'sample_data' => $results,
            'data_flow_ok' => $foundCount === count($sampleRecipients) || count($sampleRecipients) === 0,
        ];
    }

    /**
     * AJAX: ทดสอบ PDF Generation ด้วยข้อมูลจำลอง
     */
    public function testPdfGeneration()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        // ดึง template แรกที่มี
        $template = $this->templateModel->where('status', 'active')->first();

        if (!$template) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบ Template ที่ใช้งานได้'
            ]);
        }

        // ตรวจสอบว่า template file มีอยู่จริง
        $templatePath = ROOTPATH . 'writable/' . $template['template_file'];
        if (!file_exists($templatePath)) {
            // ลอง path อื่น
            $templatePath = FCPATH . $template['template_file'];
        }

        $generator = new CertPdfGenerator();

        // ข้อมูลจำลอง
        $student = [
            'th_name' => 'ทดสอบ',
            'thai_lastname' => 'ระบบ',
            'login_uid' => 'TEST001',
            'program_name' => 'วิทยาการคอมพิวเตอร์',
        ];

        $request = [
            'request_number' => 'TEST-001',
            'purpose' => 'ทดสอบการสร้าง PDF',
        ];

        try {
            $pdfPath = $generator->generate(
                $request,
                $template,
                $student,
                'TEST_VERIFY_TOKEN_' . time()
            );

            if ($pdfPath) {
                // ลบไฟล์ทดสอบ
                $fullPath = FCPATH . $pdfPath;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'PDF Generation สำเร็จ',
                    'template_used' => $template['name_th'],
                    'template_file_exists' => file_exists($templatePath),
                    'generated_path' => $pdfPath,
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PDF Generation ล้มเหลว',
                    'template_file_exists' => file_exists($templatePath),
                    'template_path_checked' => $templatePath,
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * หาชื่อฟิลด์ที่ใกล้เคียง
     */
    protected function findActualFieldName(array $fieldNames, string $target): ?string
    {
        // หาฟิลด์ที่คล้ายกัน
        $similar = array_filter(
            $fieldNames,
            fn($f) =>
            stripos($f, $target) !== false ||
                stripos($target, $f) !== false ||
                levenshtein($f, $target) <= 3
        );

        return !empty($similar) ? reset($similar) : null;
    }
}
