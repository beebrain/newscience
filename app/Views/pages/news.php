<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">ข่าวสาร</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>ข่าวสาร</span>
        </div>
    </div>
</section>

<!-- News List Section -->
<section class="section">
    <div class="container">
        <?php if (!empty($news_items)): ?>
        <div class="news-grid">
            <?php foreach ($news_items as $index => $news): ?>
            <article class="news-card animate-on-scroll <?= $index < 2 ? 'news-card--featured' : '' ?>">
                <div class="news-card__image">
                    <?php if (!empty($news['featured_image'])): ?>
                    <img src="<?= esc($news['featured_image']) ?>" alt="<?= esc($news['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="news-card__placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                    <div class="news-card__date">
                        <?php if (!empty($news['published_at'])): ?>
                        <span class="news-date__day"><?= date('d', strtotime($news['published_at'])) ?></span>
                        <span class="news-date__month"><?= thaiMonth(date('n', strtotime($news['published_at']))) ?></span>
                        <span class="news-date__year"><?= (date('Y', strtotime($news['published_at'])) + 543) ?></span>
                        <?php else: ?>
                        <span class="news-date__day">-</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="news-card__content">
                    <h3 class="news-card__title">
                        <a href="<?= base_url('news/' . esc($news['slug'])) ?>"><?= esc($news['title']) ?></a>
                    </h3>
                    <?php if (!empty($news['excerpt'])): ?>
                    <p class="news-card__excerpt"><?= esc(mb_substr($news['excerpt'], 0, 120)) ?>...</p>
                    <?php endif; ?>
                    <a href="<?= base_url('news/' . esc($news['slug'])) ?>" class="news-card__link">
                        อ่านเพิ่มเติม
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"/>
                </svg>
            </div>
            <h3>ยังไม่มีข่าวสาร</h3>
            <p>ข่าวสารและประกาศจะปรากฏที่นี่เมื่อมีการเพิ่มเนื้อหา</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Helper function for Thai month
function thaiMonth($month) {
    $months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    return $months[$month] ?? '';
}
?>

<style>
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.news-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.news-card--featured {
    grid-column: span 1;
}

.news-card__image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.news-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.news-card:hover .news-card__image img {
    transform: scale(1.05);
}

.news-card__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-gray-100), var(--color-gray-200));
}

.news-card__placeholder svg {
    width: 48px;
    height: 48px;
    color: var(--color-gray-400);
}

.news-card__date {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: white;
    border-radius: 12px;
    text-align: center;
    line-height: 1.2;
}

.news-date__day {
    font-size: 1.5rem;
    font-weight: 700;
}

.news-date__month {
    font-size: 0.75rem;
    text-transform: uppercase;
}

.news-date__year {
    font-size: 0.7rem;
    opacity: 0.8;
}

.news-card__content {
    padding: 1.5rem;
}

.news-card__title {
    font-size: 1.1rem;
    margin: 0 0 0.75rem;
    line-height: 1.4;
}

.news-card__title a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.2s ease;
}

.news-card__title a:hover {
    color: var(--color-primary);
}

.news-card__excerpt {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0 0 1rem;
    line-height: 1.6;
}

.news-card__link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: gap 0.2s ease;
}

.news-card__link:hover {
    gap: 0.75rem;
}

.news-card__link svg {
    width: 16px;
    height: 16px;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state__icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-100);
    border-radius: 50%;
}

.empty-state__icon svg {
    width: 40px;
    height: 40px;
    color: var(--color-gray-400);
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-secondary);
    margin: 0;
}

@media (max-width: 768px) {
    .news-grid {
        grid-template-columns: 1fr;
    }
    
    .news-card--featured {
        grid-column: span 1;
    }
}
</style>

<?= $this->endSection() ?>
