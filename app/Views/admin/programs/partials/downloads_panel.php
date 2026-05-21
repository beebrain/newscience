<?php
/**
 * แผงจัดการเอกสารดาวน์โหลดหลักสูตร
 *
 * @var array $program
 * @var array<int, array<string, mixed>> $downloads
 * @var \App\Models\ProgramDownloadModel $programDownloadModel
 * @var string $downloadsContext 'edit' | 'standalone'
 */
helper('program_upload');
$programId = (int) ($program['id'] ?? 0);
$downloadsContext = ($downloadsContext ?? '') === 'edit' ? 'edit' : 'standalone';
$csrfTokenName = csrf_token();
$csrfHash = csrf_hash();
?>
<div class="downloads-panel">
<p class="admin-upload-hint" style="font-size: 0.875rem; color: var(--color-gray-600); margin: 0 0 1rem;">รองรับ PDF, Word, Excel, PowerPoint, Zip, รูป, MP4, MP3, TXT — ระบบตรวจประเภทจากนามสกุลไฟล์อัตโนมัติ (สูงสุด 10MB)</p>

<form action="<?= base_url('program-admin/upload-download/' . $programId) ?>" method="post" enctype="multipart/form-data" class="admin-upload-panel" style="margin-bottom: 2rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="from_tab" value="<?= esc($downloadsContext) ?>">
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

<div id="downloads-flash" class="downloads-flash" role="status" aria-live="polite" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:6px; font-size:0.875rem;"></div>

<?php if ($downloads !== []): ?>
    <h3 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 600;">ไฟล์ที่อัปโหลดแล้ว</h3>
    <div class="downloads-list" id="downloads-list">
        <?php foreach ($downloads as $download): ?>
            <?php
            $dlId   = (int) ($download['id'] ?? 0);
            $dlUrl  = \App\Models\ProgramDownloadModel::serveUrlForPath((string) ($download['file_path'] ?? ''));
            $dlType = strtoupper((string) ($download['file_type'] ?? ''));
            ?>
            <div class="download-item" id="download-item-<?= $dlId ?>" data-download-id="<?= $dlId ?>" style="padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px; margin-bottom: 0.75rem;">
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
                        <button type="button"
                                class="btn btn-danger btn-sm btn-delete-download"
                                data-id="<?= $dlId ?>"
                                data-url="<?= esc(base_url('program-admin/delete-download/' . $dlId), 'attr') ?>"
                                data-from-tab="<?= esc($downloadsContext) ?>">ลบ</button>
                    </div>
                </div>
                <div id="download-edit-<?= $dlId ?>" class="download-edit-panel" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-gray-200);">
                    <form method="post" action="<?= base_url('program-admin/update-download/' . $dlId) ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="from_tab" value="<?= esc($downloadsContext) ?>">
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
    <div class="empty-state" id="downloads-empty" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
        <p>ยังไม่มีไฟล์ดาวน์โหลด</p>
    </div>
<?php endif; ?>

<style>
.downloads-panel .btn-danger,
.downloads-panel .btn-danger.btn-sm {
    background: #dc2626;
    color: #fff;
    border: none;
}
.downloads-panel .btn-danger:hover {
    background: #b91c1c;
}
.downloads-flash.is-success {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.downloads-flash.is-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
</style>

<script>
(function () {
    var CSRF_NAME = <?= json_encode($csrfTokenName) ?>;
    var CSRF_HASH = <?= json_encode($csrfHash) ?>;

    window.toggleDownloadEdit = function (id) {
        var el = document.getElementById('download-edit-' + id);
        if (!el) return;
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    };

    function showDownloadsFlash(message, isError) {
        var box = document.getElementById('downloads-flash');
        if (!box) return;
        box.textContent = message;
        box.className = 'downloads-flash ' + (isError ? 'is-error' : 'is-success');
        box.style.display = 'block';
    }

    function postDeleteDownload(btn) {
        var url = btn.getAttribute('data-url');
        var id = btn.getAttribute('data-id');
        if (!url || !id) return;

        var fd = new FormData();
        fd.append(CSRF_NAME, CSRF_HASH);
        fd.append('from_tab', btn.getAttribute('data-from-tab') || '');

        btn.disabled = true;
        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (!res.ok || !res.body || !res.body.success) {
                    throw new Error((res.body && res.body.message) || 'ลบไม่สำเร็จ');
                }
                var row = document.getElementById('download-item-' + id);
                if (row) row.remove();
                var list = document.getElementById('downloads-list');
                if (list && !list.querySelector('.download-item')) {
                    list.innerHTML = '<div class="empty-state" style="text-align:center;padding:2rem;color:var(--color-gray-500);"><p>ยังไม่มีไฟล์ดาวน์โหลด</p></div>';
                }
                showDownloadsFlash(res.body.message || 'ลบไฟล์เรียบร้อยแล้ว', false);
            })
            .catch(function (err) {
                showDownloadsFlash(err.message || 'ลบไม่สำเร็จ', true);
                btn.disabled = false;
            });
    }

    document.querySelectorAll('.btn-delete-download').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var run = function () {
                if (!window.confirm('ลบเอกสารนี้หรือไม่? การลบไม่สามารถกู้คืนได้')) return;
                postDeleteDownload(btn);
            };
            if (typeof window.swalConfirm === 'function') {
                window.swalConfirm({
                    title: 'ลบเอกสารนี้หรือไม่?',
                    text: 'การลบไม่สามารถกู้คืนได้',
                    confirmText: 'ลบ',
                    cancelText: 'ยกเลิก'
                }).then(function (ok) { if (ok) postDeleteDownload(btn); });
            } else {
                run();
            }
        });
    });
})();
</script>
</div>
