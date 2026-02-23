<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">ทดสอบการเชื่อมโยงฟิลด์ (Field Mapping Test)</h2>
        <span class="badge badge-info">System Test</span>
    </div>

    <div class="card-body">
        <!-- Test 1: PDF Generator Fields -->
        <div class="test-section">
            <h3>
                1. ฟิลด์ใน CertPdfGenerator
                <?php if ($tests['pdf_generator_fields']['all_passed']): ?>
                    <span class="badge badge-success">✓ PASS</span>
                <?php else: ?>
                    <span class="badge badge-warning">⚠ CHECK</span>
                <?php endif; ?>
            </h3>
            <p>จำนวนฟิลด์ทดสอบ: <?= $tests['pdf_generator_fields']['total_fields'] ?> | 
               ผ่าน: <?= $tests['pdf_generator_fields']['passed'] ?>/<?= $tests['pdf_generator_fields']['total_fields'] ?></p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests['pdf_generator_fields']['results'] as $field => $result): ?>
                        <?php if (!str_starts_with($field, '_')): ?>
                            <tr>
                                <td><code><?= esc($field) ?></code></td>
                                <td><?= esc($result['expected']) ?></td>
                                <td><?= esc($result['actual'] ?? 'null') ?></td>
                                <td>
                                    <?php if ($result['match']): ?>
                                        <span class="badge badge-success">✓</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">✗</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="alert alert-info">
                <strong>Invalid Field Test:</strong> 
                ฟิลด์ที่ไม่มีอยู่จริง (nonexistent_field) ส่งคืน: 
                <?= $tests['pdf_generator_fields']['results']['_invalid_field_test']['is_null'] ? 'null' : 'ค่าอื่น' ?> 
                <?= $tests['pdf_generator_fields']['results']['_invalid_field_test']['correct_behavior'] 
                    ? '<span class="badge badge-success">✓ ถูกต้อง</span>' 
                    : '<span class="badge badge-warning">⚠ ควรเป็น null</span>' ?>
            </div>
        </div>

        <!-- Test 2: Form to Database Mapping -->
        <div class="test-section">
            <h3>
                2. การเชื่อมโยง Form ↔ Database
                <?php if ($tests['form_database_mapping']['all_form_fields_in_db']): ?>
                    <span class="badge badge-success">✓ PASS</span>
                <?php else: ?>
                    <span class="badge badge-danger">✗ FAIL</span>
                <?php endif; ?>
            </h3>
            <p>Form fields: <?= $tests['form_database_mapping']['form_fields_count'] ?> | 
               Database fields: <?= $tests['form_database_mapping']['database_fields_count'] ?></p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Form Field</th>
                        <th>In Form</th>
                        <th>In Database</th>
                        <th>Required</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests['form_database_mapping']['form_to_db_mapping'] as $field => $mapping): ?>
                        <tr>
                            <td><code><?= esc($field) ?></code></td>
                            <td><?= $mapping['in_form'] ? '✓' : '✗' ?></td>
                            <td><?= $mapping['in_database'] ? '✓' : '✗' ?></td>
                            <td><?= $mapping['required_in_form'] ? '<span class="text-danger">Required</span>' : 'Optional' ?></td>
                            <td>
                                <?php if ($mapping['in_database']): ?>
                                    <span class="badge badge-success">OK</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">MISSING</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($tests['form_database_mapping']['database_only_fields'])): ?>
                <div class="alert alert-warning">
                    <strong>ฟิลด์ใน Database ที่ไม่มีใน Form:</strong>
                    <code><?= implode(', ', $tests['form_database_mapping']['database_only_fields']) ?></code>
                </div>
            <?php endif; ?>
        </div>

        <!-- Test 3: Template Field Mapping -->
        <div class="test-section">
            <h3>3. Template Field Mapping (JSON)</h3>
            <p>Total templates: <?= $tests['template_field_mapping']['total_templates'] ?> | 
               With mapping: <?= $tests['template_field_mapping']['templates_with_mapping'] ?> | 
               With coordinates: <?= $tests['template_field_mapping']['templates_with_coords'] ?></p>

            <?php if (!empty($tests['template_field_mapping']['sample_mappings'])): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Has Mapping</th>
                            <th>Mapped Fields</th>
                            <th>Has Coords</th>
                            <th>Signature</th>
                            <th>QR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests['template_field_mapping']['sample_mappings'] as $template): ?>
                            <tr>
                                <td><?= esc($template['template_name']) ?></td>
                                <td><?= $template['has_field_mapping'] ? '✓' : '✗' ?></td>
                                <td>
                                    <?php if (!empty($template['mapped_fields'])): ?>
                                        <code><?= implode(', ', $template['mapped_fields']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $template['has_coordinates'] ? '✓' : '✗' ?></td>
                                <td>
                                    <?php if ($template['signature_coords']['x']): ?>
                                        X:<?= $template['signature_coords']['x'] ?> Y:<?= $template['signature_coords']['y'] ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($template['qr_coords']['x']): ?>
                                        X:<?= $template['qr_coords']['x'] ?> Y:<?= $template['qr_coords']['y'] ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">ไม่พบ Template ในฐานข้อมูล</div>
            <?php endif; ?>

            <div class="alert alert-info">
                <strong>Supported fields in CertPdfGenerator:</strong>
                <code><?= implode(', ', $tests['template_field_mapping']['supported_fields_in_generator']) ?></code>
            </div>
        </div>

        <!-- Test 4: Student Data Fields -->
        <div class="test-section">
            <h3>
                4. ฟิลด์ข้อมูลนักศึกษา
                <?php if ($tests['student_data_fields']['all_required_exist']): ?>
                    <span class="badge badge-success">✓ PASS</span>
                <?php else: ?>
                    <span class="badge badge-danger">✗ FAIL</span>
                <?php endif; ?>
            </h3>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Required Field</th>
                        <th>Exists</th>
                        <th>Alternative Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests['student_data_fields']['field_status'] as $field => $status): ?>
                        <tr>
                            <td><code><?= esc($field) ?></code></td>
                            <td>
                                <?php if ($status['exists']): ?>
                                    <span class="badge badge-success">✓ Found</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✗ Not Found</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($status['actual_name']): ?>
                                    <code><?= esc($status['actual_name']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($tests['student_data_fields']['alternative_fields_available'])): ?>
                <div class="alert alert-info">
                    <strong>Alternative fields available:</strong>
                    <?php foreach ($tests['student_data_fields']['alternative_fields_available'] as $alt => $exists): ?>
                        <span class="badge badge-info"><?= esc($alt) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Test 5: Recipient Data Flow -->
        <div class="test-section">
            <h3>
                5. Data Flow ของ Recipients
                <?php if ($tests['recipient_data_flow']['data_flow_ok']): ?>
                    <span class="badge badge-success">✓ PASS</span>
                <?php else: ?>
                    <span class="badge badge-warning">⚠ CHECK</span>
                <?php endif; ?>
            </h3>
            <p>Sample: <?= $tests['recipient_data_flow']['sample_count'] ?> | 
               Found: <?= $tests['recipient_data_flow']['students_found'] ?> | 
               Not Found: <?= $tests['recipient_data_flow']['students_not_found'] ?></p>

            <?php if (!empty($tests['recipient_data_flow']['sample_data'])): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Recipient ID</th>
                            <th>Student ID</th>
                            <th>Found</th>
                            <th>Data Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests['recipient_data_flow']['sample_data'] as $recipient): ?>
                            <tr>
                                <td><?= $recipient['recipient_id'] ?></td>
                                <td><code><?= esc($recipient['student_id_in_recipient']) ?></code></td>
                                <td>
                                    <?php if ($recipient['student_found']): ?>
                                        <span class="badge badge-success">✓</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">✗</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($recipient['student_data_available']): ?>
                                        <small>
                                            Name: <?= esc($recipient['student_data_available']['th_name']) ?><br>
                                            Last: <?= esc($recipient['student_data_available']['thai_lastname']) ?><br>
                                            Program: <?= esc($recipient['student_data_available']['program_name']) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">No data</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">ไม่พบข้อมูล Recipients ในฐานข้อมูล</div>
            <?php endif; ?>
        </div>

        <!-- Test 6: Live PDF Generation Test -->
        <div class="test-section">
            <h3>6. ทดสอบ PDF Generation จริง</h3>
            <button type="button" class="btn btn-primary" onclick="testPdfGeneration()">
                ทดสอบสร้าง PDF
            </button>
            <div id="pdfTestResult" style="margin-top: 1rem;"></div>
        </div>

        <!-- Summary -->
        <div class="test-section" style="background: #f8f9fa;">
            <h3>สรุปผลการทดสอบ</h3>
            <?php
            $allPass = 
                $tests['pdf_generator_fields']['all_passed'] &&
                $tests['form_database_mapping']['all_form_fields_in_db'] &&
                $tests['student_data_fields']['all_required_exist'];
            ?>
            <?php if ($allPass): ?>
                <div class="alert alert-success">
                    <strong>✓ ทุกการทดสอบผ่าน</strong> - ระบบพร้อมใช้งาน
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>⚠ มีข้อผิดพลาดที่ต้องแก้ไข</strong> - ดูรายละเอียดด้านบน
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function testPdfGeneration() {
    const resultDiv = document.getElementById('pdfTestResult');
    resultDiv.innerHTML = '<div class="alert alert-info">กำลังทดสอบ...</div>';

    try {
        const response = await fetch('<?= base_url('dev/field-test/test-pdf-generation') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="csrf_test_name"]')?.value || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ PDF Generation สำเร็จ</strong><br>
                    Template: ${data.template_used}<br>
                    Path: ${data.generated_path}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>✗ ไม่สำเร็จ:</strong> ${data.message}<br>
                    ${data.template_file_exists !== undefined ? `Template file exists: ${data.template_file_exists ? '✓' : '✗'}` : ''}
                    ${data.trace ? `<pre style="font-size: 11px; margin-top: 10px;">${data.trace}</pre>` : ''}
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    }
}
</script>

<style>
.test-section {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.test-section h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 12px;
}
.badge-success { background: #28a745; color: white; }
.badge-warning { background: #ffc107; color: black; }
.badge-danger { background: #dc3545; color: white; }
.badge-info { background: #17a2b8; color: white; }

.alert {
    padding: 0.75rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.table th, .table td {
    padding: 0.5rem;
    border-bottom: 1px solid #dee2e6;
    text-align: left;
}
.table th {
    background: #f8f9fa;
    font-weight: 600;
}

code {
    background: #f4f4f4;
    padding: 0.125rem 0.25rem;
    border-radius: 3px;
    font-size: 12px;
}

.text-danger { color: #dc3545; }
.text-muted { color: #6b7280; }

.btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
}
.btn-primary { background: #007bff; color: white; }
.btn-primary:hover { background: #0056b3; }

pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
}
</style>

<?= $this->endSection() ?>
