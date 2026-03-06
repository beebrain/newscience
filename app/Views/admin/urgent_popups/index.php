<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>ประกาศด่วน (ป๊อปอัปหน้าแรก)</h2>
        <?php if ($can_add): ?>
        <a href="<?= base_url('admin/urgent-popups/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            เพิ่มประกาศ
        </a>
        <?php else: ?>
        <span class="text-muted" style="font-size: 0.9rem;">ประกาศด่วนมีได้สูงสุด <?= $max_items ?> รายการ — ลบหรือปิดการแสดงผลก่อนเพิ่มใหม่</span>
        <?php endif; ?>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (session('success')): ?>
            <div class="alert alert-success" style="margin: 1rem;"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (empty($popups)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <h3>ยังไม่มีประกาศด่วน</h3>
                <p>เพิ่มประกาศด่วนเพื่อแสดงป๊อปอัปบนหน้าแรก (สูงสุด <?= $max_items ?> รายการ)</p>
                <?php if ($can_add): ?>
                <a href="<?= base_url('admin/urgent-popups/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">เพิ่มประกาศ</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="table" id="popupsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 80px;">รูป</th>
                        <th>หัวข้อ</th>
                        <th style="width: 100px;">สถานะ</th>
                        <th style="width: 140px;">กำหนดการ</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popups as $i => $p): ?>
                        <tr>
                            <td><?= (int)($p['sort_order'] ?? $i) ?></td>
                            <td>
                                <?php if (!empty($p['image'])): ?>
                                    <img src="<?= base_url('serve/uploads/urgent_popups/' . basename($p['image'])) ?>"
                                         alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= esc($p['title'] ?: '(ไม่มีชื่อ)') ?></strong>
                                <?php if (!empty($p['content'])): ?>
                                    <br><small class="text-muted"><?= esc(mb_substr(strip_tags($p['content']), 0, 60)) ?>…</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm toggle-active <?= $p['is_active'] ? 'btn-success' : 'btn-secondary' ?>"
                                        data-id="<?= $p['id'] ?>">
                                    <?= $p['is_active'] ? 'เปิด' : 'ปิด' ?>
                                </button>
                            </td>
                            <td>
                                <?php if (!empty($p['start_date']) || !empty($p['end_date'])): ?>
                                    <small>
                                        <?= $p['start_date'] ? date('d/m/Y', strtotime($p['start_date'])) : '—' ?>
                                        ถึง
                                        <?= $p['end_date'] ? date('d/m/Y', strtotime($p['end_date'])) : '—' ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">ตลอดเวลา</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= base_url('admin/urgent-popups/edit/' . $p['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                    <a href="<?= base_url('admin/urgent-popups/delete/' . $p['id']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('ยืนยันการลบประกาศนี้?')">ลบ</a>
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
        fetch('<?= base_url('admin/urgent-popups/toggle-active/') ?>' + id, {
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
