<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>จัดการตารางคุมสอบ</h2>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">นำเข้าและจัดการตารางคุมสอบ (JSON File Based)</p>
            </div>
            <div style="margin-left: auto;">
                <a href="<?= base_url('admin/exam/upload') ?>" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    นำเข้าตารางสอบ
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: var(--color-success); color: white; border-radius: 6px;">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: var(--color-danger); color: white; border-radius: 6px;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="news-table-wrap">
            <table class="table" role="table">
                <thead>
                    <tr>
                        <th>ภาคการศึกษา</th>
                        <th>ประเภท</th>
                        <th>ไฟล์</th>
                        <th>สถานะ</th>
                        <th>แก้ไขล่าสุด</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($files)): ?>
                        <tr>
                            <td colspan="6" class="empty-state" style="text-align: center; padding: 3rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 64px; height: 64px; opacity: 0.5; margin-bottom: 1rem;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <p>ยังไม่มีไฟล์ตารางสอบ</p>
                                <a href="<?= base_url('admin/exam/upload') ?>" class="btn btn-primary" style="margin-top: 1rem;">นำเข้าตารางสอบ</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 500;"><?= esc($file['semester_label']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $file['exam_type'] === 'midterm' ? 'badge-warning' : 'badge-success' ?>">
                                        <?= $file['exam_type'] === 'midterm' ? 'กลางภาค' : 'ปลายภาค' ?>
                                    </span>
                                </td>
                                <td>
                                    <code style="font-size: 0.8rem;"><?= esc($file['name']) ?></code>
                                </td>
                                <td>
                                    <?php if ($file['is_published']): ?>
                                        <span class="badge badge-success">เผยแพร่แล้ว</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--color-gray-200);">ร่าง</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.875rem; color: var(--color-gray-600);">
                                    <?= date('d/m/Y H:i', $file['modified']) ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?= base_url("admin/exam/preview/{$file['semester_no']}/{$file['year']}/{$file['exam_type']}") ?>" class="btn btn-sm btn-primary">ตรวจสอบ</a>
                                        <?php if (!$file['is_published']): ?>
                                            <button onclick="publishFile('<?= $file['semester_no'] ?>', '<?= $file['year'] ?>', '<?= $file['exam_type'] ?>')" class="btn btn-sm btn-success">เผยแพร่</button>
                                        <?php endif; ?>
                                        <button onclick="deleteFile('<?= $file['semester_no'] ?>', '<?= $file['year'] ?>', '<?= $file['exam_type'] ?>')" class="btn btn-sm" style="background: var(--color-gray-200);">ลบ</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background: var(--color-success);
    color: white;
}

.badge-warning {
    background: var(--color-warning);
    color: #333;
}

.action-btns {
    display: flex;
    gap: 0.25rem;
}
</style>

<script>
function publishFile(semesterNo, year, examType) {
    if (!confirm('ยืนยันการเผยแพร่ตารางสอบ?')) return;
    
    fetch(`<?= base_url('admin/exam/publish/') ?>${semesterNo}/${year}/${examType}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(err => {
        console.error(err);
        alert('เกิดข้อผิดพลาด');
    });
}

function deleteFile(semesterNo, year, examType) {
    if (!confirm('ยืนยันการลบไฟล์?')) return;
    
    fetch(`<?= base_url('admin/exam/delete/') ?>${semesterNo}/${year}/${examType}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        location.reload();
    })
    .catch(err => {
        console.error(err);
        location.reload();
    });
}
</script>

<?= $this->endSection() ?>
