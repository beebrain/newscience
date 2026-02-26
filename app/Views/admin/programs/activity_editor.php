<?= $this->extend($layout ?? 'admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$activity = $activity ?? null;
$isEdit = $activity !== null;
$program = $program ?? [];
$activityId = $isEdit ? (int) $activity['id'] : 0;
?>

<div class="card">
    <div class="card-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">
            <?= $isEdit ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรม' ?> — <?= esc($program['name_th'] ?? $program['name_en']) ?>
        </h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= base_url('program-admin/activities/' . $program['id']) ?>" class="btn btn-secondary btn-sm">กลับ</a>
        </div>
    </div>

    <form action="<?= $isEdit ? base_url('program-admin/activity/' . $activityId . '/update') : base_url('program-admin/activities/' . $program['id'] . '/store') ?>" method="post" id="activity-form" style="padding: 1.5rem;">
        <?= csrf_field() ?>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="title" class="form-label">ชื่อกิจกรรม *</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= $isEdit ? esc($activity['title']) : '' ?>" required maxlength="255">
        </div>

        <div class="form-group content-with-toolbar" style="margin-bottom: 1rem;">
            <label for="description" class="form-label">รายละเอียด</label>
            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
            </div>
            <textarea id="description" name="description" class="form-control" rows="6"><?= $isEdit ? esc($activity['description']) : '' ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="activity_date" class="form-label">วันที่จัดกิจกรรม</label>
                <input type="date" id="activity_date" name="activity_date" class="form-control" value="<?= $isEdit && !empty($activity['activity_date']) ? esc($activity['activity_date']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="location" class="form-label">สถานที่</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= $isEdit ? esc($activity['location'] ?? '') : '' ?>" maxlength="255">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_published" value="1" <?= ($isEdit && !empty($activity['is_published'])) ? 'checked' : '' ?>>
                <span>เผยแพร่บนเว็บ</span>
            </label>
        </div>

        <?php if ($isEdit): ?>
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label">รูปภาพกิจกรรม</label>
            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">อัปโหลดรูปภาพ (jpg, png, gif, webp)</p>
            <input type="file" id="activity-image-input" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control" style="max-width: 20rem;">
            <span id="upload-msg" style="font-size: 0.875rem; margin-top: 0.25rem;"></span>
            <div id="activity-images-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
                <?php if (!empty($activity['images'])): ?>
                    <?php foreach ($activity['images'] as $img): ?>
                        <div class="activity-image-item" data-image-id="<?= (int) $img['id'] ?>" style="position: relative; border-radius: 8px; overflow: hidden; border: 1px solid var(--color-gray-200);">
                            <img src="<?= esc(base_url('serve/uploads/' . $img['image_path'])) ?>" alt="" style="width: 100%; height: 100px; object-fit: cover; display: block;">
                            <button type="button" class="btn btn-danger btn-sm" style="position: absolute; top: 4px; right: 4px; padding: 0.25rem 0.5rem; font-size: 0.75rem;" data-image-id="<?= (int) $img['id'] ?>" aria-label="ลบรูป">ลบ</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-actions" style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึก' : 'สร้างกิจกรรม' ?></button>
            <a href="<?= base_url('program-admin/activities/' . $program['id']) ?>" class="btn btn-secondary">ยกเลิก</a>
        </div>
    </form>
</div>

<?php if ($isEdit): ?>
<script>
(function () {
    var activityId = <?= $activityId ?>;
    var baseUrl = '<?= base_url() ?>';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var uploadInput = document.getElementById('activity-image-input');
    var uploadMsg = document.getElementById('upload-msg');
    var imagesList = document.getElementById('activity-images-list');

    function addImageNode(id, url) {
        var div = document.createElement('div');
        div.className = 'activity-image-item';
        div.setAttribute('data-image-id', id);
        div.style.cssText = 'position: relative; border-radius: 8px; overflow: hidden; border: 1px solid var(--color-gray-200);';
        div.innerHTML = '<img src="' + url + '" alt="" style="width: 100%; height: 100px; object-fit: cover; display: block;">' +
            '<button type="button" class="btn btn-danger btn-sm" style="position: absolute; top: 4px; right: 4px; padding: 0.25rem 0.5rem; font-size: 0.75rem;" data-image-id="' + id + '" aria-label="ลบรูป">ลบ</button>';
        imagesList.appendChild(div);
        div.querySelector('button').addEventListener('click', function () { deleteImage(id, div); });
    }

    function deleteImage(id, node) {
        swalConfirm({ title: 'ลบรูปภาพนี้?', confirmText: 'ลบ', cancelText: 'ยกเลิก' }).then(function (ok) {
            if (!ok) return;
            var fd = new FormData();
            fd.append(csrfName, csrfHash);
            fetch(baseUrl + 'program-admin/activity-image/' + id + '/delete', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success && node) node.remove();
                });
        });
    }

    if (uploadInput) {
        uploadInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            uploadMsg.textContent = 'กำลังอัปโหลด...';
            uploadMsg.style.color = '';
            var fd = new FormData();
            fd.append('image', file);
            fd.append(csrfName, csrfHash);
            fetch(baseUrl + 'program-admin/activity/' + activityId + '/upload-image', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    uploadMsg.textContent = res.success ? 'อัปโหลดเรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด');
                    uploadMsg.style.color = res.success ? 'var(--color-green-600)' : 'var(--color-red-600)';
                    if (res.success && res.url) addImageNode(res.image_id, res.url);
                    uploadInput.value = '';
                })
                .catch(function () { uploadMsg.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ'; uploadMsg.style.color = 'var(--color-red-600)'; });
        });
    }

    imagesList.querySelectorAll('.activity-image-item button[data-image-id]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(this.getAttribute('data-image-id'), 10);
            var node = this.closest('.activity-image-item');
            deleteImage(id, node);
        });
    });
})();
</script>
<?php endif; ?>

<script>
document.querySelectorAll('.structure-tool').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var group = this.closest('.form-group');
        var ta = group ? group.querySelector('textarea') : null;
        if (!ta) return;
        var insert = this.getAttribute('data-insert') || '';
        var start = ta.selectionStart, end = ta.selectionEnd, val = ta.value;
        ta.value = val.substring(0, start) + insert + val.substring(end);
        ta.selectionStart = ta.selectionEnd = start + insert.length;
        ta.focus();
    });
});
</script>

<?= $this->endSection() ?>
