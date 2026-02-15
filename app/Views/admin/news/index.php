<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2>จัดการข่าวสาร</h2>
            <?php if (!empty($news)): ?>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">ทั้งหมด <?= count($news) ?> รายการ</p>
            <?php endif; ?>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <?php if (!empty($news)): ?>
                <?php
                $published = array_filter($news, fn($n) => ($n['status'] ?? '') === 'published');
                $drafts = array_filter($news, fn($n) => ($n['status'] ?? '') === 'draft');
                ?>
                <span class="news-stat-pill">
                    <strong><?= count($published) ?></strong> เผยแพร่
                </span>
                <span class="news-stat-pill">
                    <strong><?= count($drafts) ?></strong> ร่าง
                </span>
            <?php endif; ?>
            <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                เพิ่มข่าว
            </a>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($news)): ?>
            <div class="empty-state empty-state--news">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <h3>ยังไม่มีข่าว</h3>
                <p>เพิ่มข่าวสารฉบับแรกเพื่อแสดงบนเว็บไซต์</p>
                <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary">สร้างข่าว</a>
            </div>
        <?php else: ?>
            <div class="news-table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">ภาพ</th>
                            <th>หัวข้อ</th>
                            <th style="width: 120px;">ผู้เขียน</th>
                            <th style="width: 90px;">สถานะ</th>
                            <th style="width: 90px;">กิจกรรม</th>
                            <th style="width: 100px;">วันที่</th>
                            <th style="width: 160px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $article): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($article['featured_image'])): ?>
                                        <img src="<?= base_url('serve/thumb/news/' . basename($article['featured_image'])) ?>"
                                            alt="" class="news-thumb" width="72" height="48">
                                    <?php else: ?>
                                        <div class="news-thumb-placeholder">ไม่มีภาพ</div>
                                    <?php endif; ?>
                                </td>
                                <td class="news-title-cell">
                                    <a href="<?= base_url('admin/news/edit/' . $article['id']) ?>" class="news-title-link">
                                        <?= esc($article['title']) ?>
                                    </a>
                                    <?php if (!empty($article['excerpt'])): ?>
                                        <p class="news-excerpt"><?= esc(mb_substr($article['excerpt'], 0, 80)) ?>…</p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 0.875rem; color: var(--color-gray-600);">
                                        <?= esc(trim(($article['gf_name'] ?? '') . ' ' . ($article['gl_name'] ?? ''))) ?: '—' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= ($article['status'] ?? '') === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= ($article['status'] ?? '') === 'published' ? 'เผยแพร่' : 'ร่าง' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($article['display_as_event'])): ?>
                                        <span class="badge badge-success" title="แสดงใน section กิจกรรมที่จะมาถึง">Event</span>
                                    <?php else: ?>
                                        <span style="color: var(--color-gray-400); font-size: 0.875rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.875rem; color: var(--color-gray-600);">
                                    <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                </td>
                                <td class="news-actions-cell">
                                    <div class="actions">
                                        <a href="<?= base_url('admin/news/edit/' . $article['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                        <a href="<?= base_url('admin/news/delete/' . $article['id']) ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('ต้องการลบข่าวนี้หรือไม่?')">ลบ</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>