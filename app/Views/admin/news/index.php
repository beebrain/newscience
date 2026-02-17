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
                            <th style="width: 120px;">Tag</th>
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
                                    <?php if (!empty($article['tags'])): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                            <?php foreach ($article['tags'] as $tag): ?>
                                                <?php
                                                $tagSlug = $tag['slug'] ?? '';
                                                $badgeStyle = 'background: #6c757d; color: white;';

                                                // ประเภทข่าว
                                                if ($tagSlug === 'general') {
                                                    $badgeStyle = 'background: #17a2b8; color: white;';
                                                } elseif ($tagSlug === 'student_activity') {
                                                    $badgeStyle = 'background: #28a745; color: white;';
                                                } elseif ($tagSlug === 'research_grant') {
                                                    $badgeStyle = 'background: #ffc107; color: #000;';
                                                } elseif ($tagSlug === 'announcement') {
                                                    $badgeStyle = 'background: #dc3545; color: white;';
                                                } elseif ($tagSlug === 'news') {
                                                    $badgeStyle = 'background: #007bff; color: white;';
                                                } elseif ($tagSlug === 'event') {
                                                    $badgeStyle = 'background: #fd7e14; color: white;';
                                                }
                                                // หลักสูตร - สี pastel ต่างๆ
                                                elseif (strpos($tagSlug, 'math') !== false || $tagSlug === 'applied_mathematics') {
                                                    $badgeStyle = 'background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9;';
                                                } elseif (strpos($tagSlug, 'biology') !== false || $tagSlug === 'biology') {
                                                    $badgeStyle = 'background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;';
                                                } elseif (strpos($tagSlug, 'chemistry') !== false || $tagSlug === 'chemistry') {
                                                    $badgeStyle = 'background: #fff3e0; color: #ef6c00; border: 1px solid #ffcc80;';
                                                } elseif (strpos($tagSlug, 'computer') !== false || strpos($tagSlug, 'it') !== false) {
                                                    $badgeStyle = 'background: #f3e5f5; color: #6a1b9a; border: 1px solid #ce93d8;';
                                                } elseif (strpos($tagSlug, 'data') !== false) {
                                                    $badgeStyle = 'background: #e0f7fa; color: #00838f; border: 1px solid #80deea;';
                                                } elseif (strpos($tagSlug, 'environment') !== false) {
                                                    $badgeStyle = 'background: #e8f5e9; color: #1b5e20; border: 1px solid #81c784;';
                                                } elseif (strpos($tagSlug, 'food') !== false || strpos($tagSlug, 'nutrition') !== false) {
                                                    $badgeStyle = 'background: #fff8e1; color: #f57f17; border: 1px solid #ffd54f;';
                                                } elseif (strpos($tagSlug, 'physics') !== false) {
                                                    $badgeStyle = 'background: #eceff1; color: #37474f; border: 1px solid #90a4ae;';
                                                } elseif (strpos($tagSlug, 'public_health') !== false) {
                                                    $badgeStyle = 'background: #fce4ec; color: #c2185b; border: 1px solid #f48fb1;';
                                                } elseif (strpos($tagSlug, 'biotech') !== false) {
                                                    $badgeStyle = 'background: #f1f8e9; color: #558b2f; border: 1px solid #aed581;';
                                                } elseif (strpos($tagSlug, 'tourism') !== false) {
                                                    $badgeStyle = 'background: #e1f5fe; color: #0277bd; border: 1px solid #81d4fa;';
                                                } elseif (strpos($tagSlug, 'applied_sci') !== false) {
                                                    $badgeStyle = 'background: #ede7f6; color: #4527a0; border: 1px solid #b39ddb;';
                                                }
                                                ?>
                                                <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; white-space: nowrap; <?= $badgeStyle ?>"><?= esc($tag['name'] ?? $tagSlug) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--color-gray-400); font-size: 0.875rem;">—</span>
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