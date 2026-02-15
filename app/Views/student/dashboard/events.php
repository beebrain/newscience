<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">ข่าว / Event</h2>
        <a href="<?= base_url('student') ?>" class="btn btn-secondary">← กลับ Portal</a>
    </div>
    <div class="card-body">
        <p style="color: var(--color-gray-600); margin-bottom: 1.5rem;">กิจกรรมและข่าวที่กำลังจะมาถึง</p>

        <?php if (empty($events)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p style="margin: 0;">ยังไม่มีกิจกรรมที่กำลังจะมาถึง</p>
            </div>
        <?php else: ?>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($events as $ev): ?>
                    <li style="border-bottom: 1px solid var(--color-gray-100); padding: 1rem 0;">
                        <a href="<?= base_url('events/' . ($ev['id'] ?? '')) ?>" style="text-decoration: none; color: inherit; display: block;">
                            <strong style="color: var(--color-gray-800);"><?= esc($ev['title'] ?? '') ?></strong>
                            <span style="color: var(--color-gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                                <?= isset($ev['event_date']) ? date('d/m/Y', strtotime($ev['event_date'])) : '' ?>
                                <?= !empty($ev['event_time']) ? ' ' . date('H:i', strtotime($ev['event_time'])) : '' ?>
                                <?= !empty($ev['location']) ? ' · ' . esc($ev['location']) : '' ?>
                            </span>
                            <?php if (!empty($ev['excerpt'])): ?>
                                <p style="margin: 0.5rem 0 0; font-size: 0.9375rem; color: var(--color-gray-600);"><?= esc(substr($ev['excerpt'], 0, 120)) ?>...</p>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p style="margin-top: 1rem;">
                <a href="<?= base_url('events') ?>" class="btn btn-secondary">ดูทั้งหมดที่หน้าประกาศกิจกรรม</a>
            </p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
