<?= $this->extend($layout ?? 'admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem; color: var(--color-primary-600);">
                        <path d="M18 20V10"></path>
                        <path d="M12 20V4"></path>
                        <path d="M6 20v-6"></path>
                    </svg>
                    กิจกรรมภายในหลักสูตร
                </h2>
                <p style="margin: 0.25rem 0 0 0; color: var(--color-gray-600);">
                    <?= esc($program['name_th'] ?? $program['name_en']) ?>
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="<?= base_url('program-admin/edit/' . $program['id']) ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    กลับ
                </a>
                <a href="<?= base_url('program-admin/activities/' . $program['id'] . '/create') ?>" class="btn btn-primary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    เพิ่มกิจกรรม
                </a>
            </div>
        </div>
    </div>

    <div style="padding: 1.5rem;">
        <?php if (empty($activities)): ?>
            <div style="text-align: center; padding: 3rem 1rem; color: var(--color-gray-500);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <h3 style="margin: 0 0 0.5rem 0; font-weight: 500;">ยังไม่มีกิจกรรม</h3>
                <p style="margin: 0 0 1rem 0; color: var(--color-gray-500);">เพิ่มกิจกรรมแรกของคุณ</p>
                <a href="<?= base_url('program-admin/activities/' . $program['id'] . '/create') ?>" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    เพิ่มกิจกรรม
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                <?php foreach ($activities as $a): ?>
                    <?php
                    $thumb = null;
                    if (!empty($a['images'])) {
                        $first = $a['images'][0];
                        $thumb = base_url('serve/uploads/' . $first['image_path']);
                    }
                    $dateStr = !empty($a['activity_date']) ? date('d/m/Y', strtotime($a['activity_date'])) : '-';
                    ?>
                    <div class="activity-card" style="border: 1px solid var(--color-gray-200); border-radius: 8px; overflow: hidden; background: white; display: flex; flex-direction: column;">
                        <div style="height: 140px; background: var(--color-gray-100); display: flex; align-items: center; justify-content: center;">
                            <?php if ($thumb): ?>
                                <img src="<?= esc($thumb) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-gray-400);">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 1rem; flex: 1;">
                            <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600;"><?= esc($a['title']) ?></h4>
                            <div style="font-size: 0.875rem; color: var(--color-gray-600);">
                                <span><?= esc($dateStr) ?></span>
                                <?php if (!empty($a['location'])): ?>
                                    <span> · <?= esc($a['location']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($a['is_published']): ?>
                                <span style="font-size: 0.75rem; background: var(--color-green-100); color: var(--color-green-700); padding: 0.125rem 0.5rem; border-radius: 4px; margin-top: 0.5rem; display: inline-block;">เผยแพร่</span>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 0 1rem 1rem; display: flex; gap: 0.5rem;">
                            <a href="<?= base_url('program-admin/activity/' . $a['id'] . '/edit') ?>" class="btn btn-outline btn-sm">แก้ไข</a>
                            <form action="<?= base_url('program-admin/activity/' . $a['id'] . '/delete') ?>" method="post" class="js-delete-activity-form" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('submit', function(e) {
        var form = e.target.closest('.js-delete-activity-form');
        if (!form) return;
        e.preventDefault();
        swalConfirm({ title: 'ต้องการลบกิจกรรมนี้?', confirmText: 'ลบ', cancelText: 'ยกเลิก' }).then(function(ok) {
            if (ok) form.submit();
        });
    });
});
</script>
<?= $this->endSection() ?>
