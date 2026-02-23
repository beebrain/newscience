<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">เพิ่มเทมเพลตใบรับรอง (PDF)</h2>
    </div>

    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <!-- Step 1: Upload PDF -->
        <div id="upload-section" class="form-section">
            <h3>ขั้นตอนที่ 1: อัพโหลด PDF Template</h3>
            <div class="form-group">
                <label>เลือกไฟล์ PDF <span class="text-danger">*</span></label>
                <input type="file" id="pdf-upload" class="form-control" accept="application/pdf">
                <small class="form-text text-muted">รองรับไฟล์ PDF ขนาดไม่เกิน 8MB</small>
            </div>
            <button type="button" id="btn-analyze" class="btn btn-secondary">
                <span class="btn-text">วิเคราะห์ PDF</span>
                <span class="btn-loading" style="display: none;">กำลังประมวลผล...</span>
            </button>
        </div>

        <!-- Step 2: PDF Preview & Text Analysis -->
        <div id="preview-section" style="display: none; margin-top: 2rem;">
            <h3>ขั้นตอนที่ 2: ตรวจสอบข้อความใน PDF</h3>

            <div class="pdf-info" style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <strong>ไฟล์:</strong> <span id="pdf-filename"></span>
                <input type="hidden" name="temp_file" id="temp-file">
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Text Preview -->
                <div class="text-preview-box">
                    <h4>ข้อความที่ดึงได้จาก PDF:</h4>
                    <textarea id="pdf-text-preview" readonly style="width: 100%; height: 300px; font-family: monospace; font-size: 12px;"></textarea>
                </div>

                <!-- Suggested Fields -->
                <div class="suggested-fields-box">
                    <h4>ฟิลด์ที่ตรวจพบ:</h4>
                    <div id="suggested-fields" style="background: #f8f9fa; padding: 1rem; border-radius: 4px; min-height: 300px;">
                        <!-- Dynamically populated -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Template Form -->
        <form id="template-form" method="post" enctype="multipart/form-data" action="<?= base_url('admin/cert-templates/store') ?>" class="form-grid" style="margin-top: 2rem; display: none;">
            <?= csrf_field() ?>
            <input type="hidden" name="temp_file" id="form-temp-file">

            <h3>ขั้นตอนที่ 3: กำหนดค่าเทมเพลต</h3>

            <div class="form-group">
                <label>ชื่อเทมเพลต (ไทย) <span class="text-danger">*</span></label>
                <input type="text" name="name_th" class="form-control" value="<?= esc(old('name_th')) ?>" required>
            </div>

            <div class="form-group">
                <label>ชื่อเทมเพลต (อังกฤษ)</label>
                <input type="text" name="name_en" class="form-control" value="<?= esc(old('name_en')) ?>">
            </div>

            <div class="form-group">
                <label>ระดับการออกใบรับรอง</label>
                <select name="level" class="form-control" required>
                    <option value="program" <?= old('level') === 'program' ? 'selected' : '' ?>>ระดับหลักสูตร (ประธานหลักสูตรลงนาม)</option>
                    <option value="faculty" <?= old('level') === 'faculty' ? 'selected' : '' ?>>ระดับคณะ (คณบดีลงนาม)</option>
                </select>
            </div>

            <div class="form-group">
                <label>สถานะ</label>
                <select name="status" class="form-control" required>
                    <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                </select>
            </div>

            <h4 style="margin-top: 1.5rem;">ตำแหน่งลายเซ็นและ QR Code</h4>
            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap: 1rem;">
                <?php
                    $defaults = [
                        'signature_x' => ['label' => 'ลายเซ็น X', 'val' => '300'],
                        'signature_y' => ['label' => 'ลายเซ็น Y', 'val' => '600'],
                        'qr_x' => ['label' => 'QR X', 'val' => '450'],
                        'qr_y' => ['label' => 'QR Y', 'val' => '700'],
                        'qr_size' => ['label' => 'QR ขนาด', 'val' => '60'],
                    ];
                ?>
                <?php foreach ($defaults as $field => $info): ?>
                    <div class="form-group">
                        <label><?= $info['label'] ?></label>
                        <input type="number" step="0.01" name="<?= $field ?>" class="form-control" value="<?= esc(old($field, $info['val'])) ?>" required>
                    </div>
                <?php endforeach; ?>
            </div>

            <h4 style="margin-top: 1.5rem;">Field Mapping (ตำแหน่งข้อมูลใน PDF)</h4>
            <div class="form-group">
                <label>กำหนดตำแหน่งฟิลด์ (JSON)</label>
                <textarea name="field_mapping" id="field-mapping-json" class="form-control" rows="12"><?= esc(old('field_mapping')) ?></textarea>
                <small class="form-text text-muted">
                    กำหนดตำแหน่งแต่ละฟิลด์ใน PDF (พิกัด X,Y เริ่มจากซ้ายบน)<br>
                    ตัวอย่าง: {"student_name":{"x":100,"y":200,"font_size":16},"date":{"x":100,"y":250,"font_size":14}}
                </small>
            </div>

            <!-- Field Builder UI -->
            <div class="field-builder" style="margin-top: 1rem;">
                <h5>สร้างฟิลด์อัตโนมัติ:</h5>
                <div id="field-list" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                    <!-- Dynamic fields -->
                </div>
                <div class="add-field-row" style="display: flex; gap: 0.5rem;">
                    <input type="text" id="new-field-name" placeholder="ชื่อฟิลด์ (เช่น student_name)" class="form-control">
                    <input type="number" id="new-field-x" placeholder="X" class="form-control" style="width: 80px;">
                    <input type="number" id="new-field-y" placeholder="Y" class="form-control" style="width: 80px;">
                    <input type="number" id="new-field-size" placeholder="Size" class="form-control" style="width: 80px;" value="14">
                    <button type="button" id="btn-add-field" class="btn btn-sm btn-secondary">เพิ่ม</button>
                </div>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="<?= base_url('admin/cert-templates') ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">บันทึกเทมเพลต</button>
            </div>
        </form>
    </div>
</div>

<style>
.field-tag {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.field-tag button {
    background: none;
    border: none;
    cursor: pointer;
    color: #dc3545;
}
.suggestion-item {
    background: #d4edda;
    padding: 0.5rem;
    margin: 0.25rem 0;
    border-radius: 4px;
    border-left: 3px solid #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAnalyze = document.getElementById('btn-analyze');
    const pdfUpload = document.getElementById('pdf-upload');
    const previewSection = document.getElementById('preview-section');
    const templateForm = document.getElementById('template-form');
    let fieldMapping = {};

    // Analyze PDF
    btnAnalyze.addEventListener('click', async function() {
        const file = pdfUpload.files[0];
        if (!file) {
            alert('กรุณาเลือกไฟล์ PDF');
            return;
        }

        btnAnalyze.querySelector('.btn-text').style.display = 'none';
        btnAnalyze.querySelector('.btn-loading').style.display = 'inline';
        btnAnalyze.disabled = true;

        const formData = new FormData();
        formData.append('pdf_file', file);

        try {
            const response = await fetch('<?= base_url('admin/cert-templates/preview-upload') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show preview
                document.getElementById('pdf-filename').textContent = data.filename;
                document.getElementById('temp-file').value = data.temp_file;
                document.getElementById('form-temp-file').value = data.temp_file;
                document.getElementById('pdf-text-preview').value = data.text_preview;

                // Show suggestions
                const suggestionsDiv = document.getElementById('suggested-fields');
                suggestionsDiv.innerHTML = '';
                if (Object.keys(data.suggestions).length > 0) {
                    for (const [field, info] of Object.entries(data.suggestions)) {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.innerHTML = `<strong>${field}</strong> - พบคำว่า "${info.label}"`;
                        suggestionsDiv.appendChild(div);
                    }
                } else {
                    suggestionsDiv.innerHTML = '<p style="color: #666;">ไม่พบฟิลด์อัตโนมัติ กรุณากำหนดเอง</p>';
                }

                // Set field mapping
                fieldMapping = data.field_mapping;
                document.getElementById('field-mapping-json').value = JSON.stringify(fieldMapping, null, 2);
                renderFieldList();

                // Show sections
                previewSection.style.display = 'block';
                templateForm.style.display = 'block';
            } else {
                alert(data.error || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการอัพโหลด');
        } finally {
            btnAnalyze.querySelector('.btn-text').style.display = 'inline';
            btnAnalyze.querySelector('.btn-loading').style.display = 'none';
            btnAnalyze.disabled = false;
        }
    });

    // Add field
    document.getElementById('btn-add-field').addEventListener('click', function() {
        const name = document.getElementById('new-field-name').value.trim();
        const x = parseFloat(document.getElementById('new-field-x').value) || 100;
        const y = parseFloat(document.getElementById('new-field-y').value) || 100;
        const size = parseFloat(document.getElementById('new-field-size').value) || 14;

        if (!name) {
            alert('กรุณาระบุชื่อฟิลด์');
            return;
        }

        fieldMapping[name] = { x, y, font_size: size };
        document.getElementById('field-mapping-json').value = JSON.stringify(fieldMapping, null, 2);
        renderFieldList();

        // Clear inputs
        document.getElementById('new-field-name').value = '';
        document.getElementById('new-field-x').value = '';
        document.getElementById('new-field-y').value = '';
    });

    // Render field list
    function renderFieldList() {
        const container = document.getElementById('field-list');
        container.innerHTML = '';

        for (const [name, config] of Object.entries(fieldMapping)) {
            const tag = document.createElement('span');
            tag.className = 'field-tag';
            tag.innerHTML = `${name} (x:${config.x}, y:${config.y}) <button type="button" data-field="${name}">&times;</button>`;
            container.appendChild(tag);
        }

        // Add delete handlers
        container.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                const field = this.dataset.field;
                delete fieldMapping[field];
                document.getElementById('field-mapping-json').value = JSON.stringify(fieldMapping, null, 2);
                renderFieldList();
            });
        });
    }

    // JSON textarea change
    document.getElementById('field-mapping-json').addEventListener('change', function() {
        try {
            fieldMapping = JSON.parse(this.value);
            renderFieldList();
        } catch (e) {
            // Invalid JSON, ignore
        }
    });
});
</script>
<?= $this->endSection() ?>
