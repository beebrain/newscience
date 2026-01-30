<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>จัดการ Hero Slides</h2>
        <a href="<?= base_url('admin/hero-slides/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            เพิ่ม Slide
        </a>
    </div>
    
    <div class="card-body" style="padding: 0;">
        <?php if (empty($slides)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <h3>ยังไม่มี Hero Slides</h3>
                <p>เพิ่ม Slide แรกเพื่อแสดงบนหน้าแรกเว็บไซต์</p>
                <a href="<?= base_url('admin/hero-slides/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">เพิ่ม Slide</a>
            </div>
        <?php else: ?>
            <table class="table" id="slidesTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">ลำดับ</th>
                        <th style="width: 120px;">รูปภาพ</th>
                        <th>ชื่อ</th>
                        <th style="width: 100px;">สถานะ</th>
                        <th style="width: 150px;">กำหนดการ</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="sortableSlides">
                    <?php foreach ($slides as $slide): ?>
                        <tr data-id="<?= $slide['id'] ?>">
                            <td class="drag-handle" style="cursor: move; text-align: center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="16" y2="6"/>
                                    <line x1="8" y1="12" x2="16" y2="12"/>
                                    <line x1="8" y1="18" x2="16" y2="18"/>
                                </svg>
                            </td>
                            <td>
                                <img src="<?= base_url($slide['image']) ?>" 
                                     alt="" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>
                                <strong><?= esc($slide['title'] ?: '(ไม่มีชื่อ)') ?></strong>
                                <?php if ($slide['subtitle']): ?>
                                    <br><small style="color: #6B7280;"><?= esc($slide['subtitle']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm <?= $slide['is_active'] ? 'btn-success' : 'btn-secondary' ?> toggle-active"
                                        data-id="<?= $slide['id'] ?>">
                                    <?= $slide['is_active'] ? 'เปิด' : 'ปิด' ?>
                                </button>
                            </td>
                            <td>
                                <?php if ($slide['start_date'] || $slide['end_date']): ?>
                                    <small>
                                        <?= $slide['start_date'] ? date('d/m/Y', strtotime($slide['start_date'])) : '-' ?>
                                        ถึง
                                        <?= $slide['end_date'] ? date('d/m/Y', strtotime($slide['end_date'])) : '-' ?>
                                    </small>
                                <?php else: ?>
                                    <small style="color: #9CA3AF;">ตลอดเวลา</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= base_url('admin/hero-slides/edit/' . $slide['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                    <a href="<?= base_url('admin/hero-slides/delete/' . $slide['id']) ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('ยืนยันการลบ Slide นี้?')">ลบ</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Drag and drop sorting
    const sortable = document.getElementById('sortableSlides');
    if (sortable) {
        new Sortable(sortable, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                const order = [];
                sortable.querySelectorAll('tr').forEach(row => {
                    order.push(row.dataset.id);
                });
                
                fetch('<?= base_url('admin/hero-slides/update-order') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ order: order })
                });
            }
        });
    }
    
    // Toggle active status
    document.querySelectorAll('.toggle-active').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`<?= base_url('admin/hero-slides/toggle-active') ?>/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.className = `btn btn-sm ${data.is_active ? 'btn-success' : 'btn-secondary'} toggle-active`;
                    this.textContent = data.is_active ? 'เปิด' : 'ปิด';
                }
            });
        });
    });
});
</script>

<?= $this->endSection() ?>
