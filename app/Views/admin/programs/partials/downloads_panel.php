<?php
/**
 * แผงจัดการเอกสารดาวน์โหลดหลักสูตร
 *
 * @var array $program
 * @var array<int, array<string, mixed>> $downloads
 * @var \App\Models\ProgramDownloadModel $programDownloadModel
 */
helper('program_upload');
$programId = (int) ($program['id'] ?? 0);
?>
<p class="admin-upload-hint" style="font-size: 0.875rem; color: var(--color-gray-600); margin: 0 0 1rem;">รองรับ PDF, Word, Excel, PowerPoint, Zip, รูป, MP4, MP3, TXT — ระบบตรวจประเภทจากนามสกุลไฟล์อัตโนมัติ (สูงสุด 10MB)</p>

<form action="<?= base_url('program-admin/upload-download/' . $programId) ?>" method="post" enctype="multipart/form-data" class="admin-upload-panel" style="margin-bottom: 2rem;">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="dl-title-new" class="form-label">ชื่อเอกสาร *</label>
        <input type="text" id="dl-title-new" name="title" class="form-control" required maxlength="255" placeholder="เช่น คู่มือนักศึกษา" value="<?= esc(old('title', '')) ?>">
    </div>
    <div class="form-group">
        <label for="dl-file-new" class="form-label">ไฟล์ *</label>
        <input type="file" id="dl-file-new" name="file" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">อัปโหลดไฟล์</button>
</form>

<?php if ($downloads !== []): ?>
    <h3 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 600;">ไฟล์ที่อัปโหลดแล้ว</h3>
    <div class="downloads-list">
        <?php foreach ($downloads as $download): ?>
            <?php
            $dlId   = (int) ($download['id'] ?? 0);
            $dlUrl  = \App\Models\ProgramDownloadModel::serveUrlForPath((string) ($download['file_path'] ?? ''));
            $dlType = strtoupper((string) ($download['file_type'] ?? ''));
            ?>
            <div class="download-item" style="padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <div style="font-weight: 600;"><?= esc($download['title']) ?></div>
                        <div style="font-size: 0.875rem; color: var(--color-gray-600); margin-top: 0.25rem;">
                            <?= esc($dlType) ?> • <?= esc($programDownloadModel->getFormattedSize((int) ($download['file_size'] ?? 0))) ?>
                            <?php if (! empty($download['created_at'])): ?>
                                • <?= esc(date('d/m/Y H:i', strtotime((string) $download['created_at']))) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="<?= esc($dlUrl, 'attr') ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer">เปิดไฟล์</a>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleDownloadEdit(<?= $dlId ?>)">แก้ไข</button>
                        <form method="post" action="<?= base_url('program-admin/delete-download/' . $dlId) ?>" style="display:inline" onsubmit="return confirm('ลบเอกสารนี้หรือไม่? การลบไม่สามารถกู้คืนได้');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                        </form>
                    </div>
                </div>
                <div id="download-edit-<?= $dlId ?>" class="download-edit-panel" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-gray-200);">
                    <form method="post" action="<?= base_url('program-admin/update-download/' . $dlId) ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label" for="dl-title-<?= $dlId ?>">ชื่อเอกสาร *</label>
                            <input type="text" class="form-control" id="dl-title-<?= $dlId ?>" name="title" required maxlength="255" value="<?= esc($download['title']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="dl-file-<?= $dlId ?>">เปลี่ยนไฟล์ (ไม่บังคับ)</label>
                            <input type="file" class="form-control" id="dl-file-<?= $dlId ?>" name="file">
                            <small class="form-hint">เว้นว่างถ้าแก้เฉพาะชื่อ — ประเภทไฟล์จะอัปเดตจากไฟล์ใหม่</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">บันทึก</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDownloadEdit(<?= $dlId ?>)">ยกเลิก</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
        <p>ยังไม่มีไฟล์ดาวน์โหลด</p>
    </div>
<?php endif; ?>

<script>
function toggleDownloadEdit(id) {
    var el = document.getElementById('download-edit-' + id);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
