<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>โปสเตอร์ผู้บริหาร (สไลด์หน้า About)</h2>
        <a href="<?= base_url('admin/executive-posters/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            เพิ่มโปสเตอร์
        </a>
    </div>

    <div class="alert alert-info" style="margin: 1rem; font-size: 0.9rem;">
        อัปโหลดโปสเตอร์ผู้บริหารได้ไม่จำกัดจำนวน — จะแสดงเป็น <strong>slider</strong> ในหน้า
        <a href="<?= base_url('about#executives') ?>" target="_blank">About → ทีมผู้บริหาร</a>
        เรียงตาม <code>sort_order</code> (น้อย → มาก)
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (session('success')): ?>
            <div class="alert alert-success" style="margin: 1rem;"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (empty($posters)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <polyline points="21 15 16 10 5 21" />
                </svg>
                <h3>ยังไม่มีโปสเตอร์</h3>
                <p>เพิ่มโปสเตอร์แนะนำผู้บริหารแต่ละท่าน — แสดงเป็น slider ในหน้า About</p>
                <a href="<?= base_url('admin/executive-posters/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">เพิ่มโปสเตอร์</a>
            </div>
        <?php else: ?>
            <table class="table" id="postersTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 90px;">โปสเตอร์</th>
                        <th>ชื่อ / ตำแหน่ง</th>
                        <th>คำบรรยาย</th>
                        <th style="width: 100px;">สถานะ</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posters as $i => $p): ?>
                        <tr>
                            <td><?= (int)($p['sort_order'] ?? $i) ?></td>
                            <td>
                                <?php if (!empty($p['image'])): ?>
                                    <img src="<?= esc(image_manager_serve_url('executive_poster', $p['image'])) ?>"
                                         alt="" style="width: 70px; height: 95px; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= esc($p['title'] ?: '(ไม่มีชื่อ)') ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($p['caption'])): ?>
                                    <small><?= esc(mb_substr($p['caption'], 0, 80)) ?><?= mb_strlen($p['caption']) > 80 ? '…' : '' ?></small>
                                <?php else: ?>
                                    <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm toggle-active <?= $p['is_active'] ? 'btn-success' : 'btn-secondary' ?>"
                                        data-id="<?= $p['id'] ?>">
                                    <?= $p['is_active'] ? 'เปิด' : 'ปิด' ?>
                                </button>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= base_url('admin/executive-posters/edit/' . $p['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                    <a href="<?= base_url('admin/executive-posters/delete/' . $p['id']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('ยืนยันการลบโปสเตอร์นี้?')">ลบ</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-active').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var el = this;
        var fd = new FormData();
        fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch('<?= base_url('admin/executive-posters/toggle-active/') ?>' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                el.textContent = data.is_active ? 'เปิด' : 'ปิด';
                el.className = 'btn btn-sm toggle-active ' + (data.is_active ? 'btn-success' : 'btn-secondary');
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
