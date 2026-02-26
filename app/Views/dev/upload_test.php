<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">ทดสอบระบบอัปโหลดไฟล์</h2>
        <span class="badge badge-info">Dev Tool</span>
    </div>

    <div class="card-body">
        <!-- 1. Folder Structure Test -->
        <div class="test-section" style="margin-bottom: 2rem;">
            <h3>1. โครงสร้างโฟลเดอร์</h3>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>โฟลเดอร์</th>
                        <th>Path</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests['folder_structure'] as $folder): ?>
                        <tr>
                            <td><?= esc($folder['name']) ?></td>
                            <td><code style="font-size: 12px;"><?= esc($folder['path']) ?></code></td>
                            <td>
                                <?php if ($folder['status'] === 'success'): ?>
                                    <span class="badge badge-success">✓ OK</span>
                                <?php elseif ($folder['status'] === 'warning'): ?>
                                    <span class="badge badge-warning">⚠ ไม่สามารถเขียนได้</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✗ ไม่พบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 2. Config Paths -->
        <div class="test-section" style="margin-bottom: 2rem;">
            <h3>2. การตั้งค่า Paths</h3>
            <table class="table">
                <tbody>
                    <?php foreach ($tests['config_paths'] as $key => $value): ?>
                        <?php if (is_array($value)): ?>
                            <tr>
                                <td><?= esc($key) ?></td>
                                <td>
                                    <code style="font-size: 12px;"><?= esc($value['value'] ?? $value['bytes'] ?? json_encode($value)) ?></code>
                                    <?php if (isset($value['contains_cert_system'])): ?>
                                        <?= $value['contains_cert_system'] 
                                            ? '<span class="badge badge-success">✓</span>' 
                                            : '<span class="badge badge-danger">✗</span>' ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?= esc($key) ?></td>
                                <td><?= esc($value) ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 3. Temp Cleanup Test -->
        <div class="test-section" style="margin-bottom: 2rem;">
            <h3>3. ทดสอบการล้างไฟล์ Temp</h3>
            <div class="test-results">
                <p><strong>สถานะ:</strong> 
                    <?php if ($tests['temp_cleanup']['all_passed']): ?>
                        <span class="badge badge-success">ผ่านทั้งหมด</span>
                    <?php else: ?>
                        <span class="badge badge-warning">มีปัญหา</span>
                    <?php endif; ?>
                </p>
                <ul>
                    <li>ไฟล์ temp เก่าถูกลบ: <?= $tests['temp_cleanup']['test_results']['old_template_deleted'] ? '✓' : '✗' ?></li>
                    <li>ไฟล์ temp ใหม่ยังอยู่: <?= $tests['temp_cleanup']['test_results']['new_template_kept'] ? '✓' : '✗' ?></li>
                    <li>ไฟล์ import เก่าถูกลบ: <?= $tests['temp_cleanup']['test_results']['old_import_deleted'] ? '✓' : '✗' ?></li>
                </ul>
                <button type="button" class="btn btn-secondary" onclick="runCleanupTest()">
                    ทดสอบ Cleanup อีกครั้ง
                </button>
            </div>
        </div>

        <!-- 4. PDF Upload Test -->
        <div class="test-section" style="margin-bottom: 2rem;">
            <h3>4. ทดสอบอัปโหลด PDF Template</h3>
            <div class="upload-test">
                <input type="file" id="pdfTestFile" accept=".pdf" class="form-control" style="margin-bottom: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="testPdfUpload()">
                    ทดสอบอัปโหลด PDF
                </button>
                <div id="pdfTestResult" style="margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- 5. CSV Upload Test -->
        <div class="test-section" style="margin-bottom: 2rem;">
            <h3>5. ทดสอบอัปโหลด CSV Import</h3>
            <div class="upload-test">
                <input type="file" id="csvTestFile" accept=".csv,.txt" class="form-control" style="margin-bottom: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="testCsvUpload()">
                    ทดสอบอัปโหลด CSV
                </button>
                <div id="csvTestResult" style="margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- 6. Folder Browser -->
        <div class="test-section">
            <h3>6. ดูข้อมูลโฟลเดอร์</h3>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="viewFolder('templates')">Templates</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="viewFolder('certificates')">Certificates</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="viewFolder('temp_templates')">Temp Templates</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="viewFolder('temp_import')">Temp Import</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="viewFolder('signatures')">Signatures</button>
            </div>
            <div id="folderInfo"></div>
        </div>
    </div>
</div>

<script>
async function testPdfUpload() {
    const fileInput = document.getElementById('pdfTestFile');
    const resultDiv = document.getElementById('pdfTestResult');
    
    if (!fileInput.files.length) {
        resultDiv.innerHTML = '<div class="alert alert-warning">กรุณาเลือกไฟล์ PDF</div>';
        return;
    }

    const formData = new FormData();
    formData.append('test_file', fileInput.files[0]);

    resultDiv.innerHTML = '<div class="alert alert-info">กำลังทดสอบ...</div>';

    try {
        const response = await fetch('<?= base_url('dev/upload-test/test-pdf') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>สำเร็จ!</strong><br>
                    ไฟล์: ${data.temp_file}<br>
                    ขนาด: ${data.file_size}<br>
                    MIME: ${data.mime_type}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>ไม่สำเร็จ:</strong> ${data.message}<br>
                    ${data.errors ? data.errors.join('<br>') : ''}
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div class="alert alert-danger">เกิดข้อผิดพลาด: ${error.message}</div>`;
    }
}

async function testCsvUpload() {
    const fileInput = document.getElementById('csvTestFile');
    const resultDiv = document.getElementById('csvTestResult');
    
    if (!fileInput.files.length) {
        resultDiv.innerHTML = '<div class="alert alert-warning">กรุณาเลือกไฟล์ CSV</div>';
        return;
    }

    const formData = new FormData();
    formData.append('test_file', fileInput.files[0]);

    resultDiv.innerHTML = '<div class="alert alert-info">กำลังทดสอบ...</div>';

    try {
        const response = await fetch('<?= base_url('dev/upload-test/test-csv') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            let previewHtml = '<table class="table table-sm"><thead><tr>';
            data.headers.forEach(h => previewHtml += `<th>${h}</th>`);
            previewHtml += '</tr></thead><tbody>';
            
            data.preview.forEach(row => {
                previewHtml += '<tr>';
                data.headers.forEach(h => previewHtml += `<td>${row[h] || ''}</td>`);
                previewHtml += '</tr>';
            });
            previewHtml += '</tbody></table>';
            
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>สำเร็จ!</strong> พบ ${data.total_rows} แถว
                </div>
                <div style="max-height: 200px; overflow: auto;">${previewHtml}</div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>ไม่สำเร็จ:</strong> ${data.message}
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div class="alert alert-danger">เกิดข้อผิดพลาด: ${error.message}</div>`;
    }
}

async function runCleanupTest() {
    try {
        const response = await fetch('<?= base_url('dev/upload-test/cleanup-temp') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        alert(`ล้างไฟล์ temp เสร็จสิ้น: ${data.deleted} ไฟล์ถูกลบ, ${data.failed} ไฟล์ล้มเหลว`);
        location.reload();
    } catch (error) {
        alert('เกิดข้อผิดพลาด: ' + error.message);
    }
}

async function viewFolder(folder) {
    const infoDiv = document.getElementById('folderInfo');
    infoDiv.innerHTML = '<div class="alert alert-info">กำลังโหลด...</div>';

    try {
        const response = await fetch(`<?= base_url('dev/upload-test/folder-info') ?>?folder=${folder}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            let html = `
                <div class="folder-info" style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
                    <p><strong>Path:</strong> <code>${data.path}</code></p>
                    <p><strong>Exists:</strong> ${data.exists ? '✓' : '✗'} | 
                       <strong>Writable:</strong> ${data.writable ? '✓' : '✗'} | 
                       <strong>Files:</strong> ${data.file_count}</p>
            `;
            
            if (data.files.length > 0) {
                html += '<table class="table table-sm"><thead><tr><th>ไฟล์</th><th>ขนาด</th><th>แก้ไขล่าสุด</th></tr></thead><tbody>';
                data.files.forEach(f => {
                    html += `<tr><td>${f.name}</td><td>${f.size}</td><td>${f.modified}</td></tr>`;
                });
                html += '</tbody></table>';
            }
            
            html += '</div>';
            infoDiv.innerHTML = html;
        } else {
            infoDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    } catch (error) {
        infoDiv.innerHTML = `<div class="alert alert-danger">เกิดข้อผิดพลาด: ${error.message}</div>`;
    }
}
</script>

<style>
.test-section {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
}

.test-section h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
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
}
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

.table {
    width: 100%;
    border-collapse: collapse;
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
}
</style>
<?= $this->endSection() ?>
