<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">ข่าววิจัย</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>ข่าววิจัย</span>
        </div>
    </div>
</section>

<!-- News List Section -->
<section class="section">
    <div class="container container--narrow">
        <?php if (!empty($news_items)): ?>
            <div class="news-grid">
                <?php foreach ($news_items as $index => $news): ?>
                    <article class="news-card animate-on-scroll <?= $index < 2 ? 'news-card--featured' : '' ?>">
                        <a href="<?= base_url('news/' . esc($news['id'])) ?>" class="news-card__wrap" aria-label="อ่านข่าว: <?= esc($news['title']) ?>">
                            <div class="news-card__image">
                                <?php if (!empty($news['featured_image'])): ?>
                                    <img src="<?= base_url('serve/thumb/news/' . esc(basename($news['featured_image']))) ?>" alt="<?= esc($news['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="news-card__placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
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
                                <h3 class="news-card__title"><?= esc($news['title']) ?></h3>
                                <?php if (!empty($news['excerpt'])): ?>
                                    <p class="news-card__excerpt"><?= esc(mb_substr($news['excerpt'], 0, 80)) ?>...</p>
                                <?php endif; ?>
                                <span class="news-card__link">
                                    อ่านเพิ่มเติม
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2" />
                    </svg>
                </div>
                <h3>ยังไม่มีข่าววิจัย</h3>
                <p>ข่าววิจัยจะปรากฏที่นี่เมื่อมีการเพิ่มเนื้อหา</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Helper function for Thai month
function thaiMonth($month)
{
    $months = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.'
    ];
    return $months[$month] ?? '';
}
?>

<style>
    .container--narrow {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        max-width: 100%;
        margin-bottom: 2.5rem;
        align-items: stretch;
    }

    .news-card {
        background: #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        z-index: 1;
        min-height: 280px;
    }

    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    .news-card--featured {
        grid-column: span 1;
    }

    .news-card__wrap {
        display: block;
        height: 100%;
        min-height: 280px;
        text-decoration: none;
        color: inherit;
        position: relative;
    }

    .news-card__image {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        overflow: hidden;
    }

    .news-card__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }

    .news-card:hover .news-card__image img {
        transform: scale(1.06);
    }

    .news-card__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    }

    .news-card__placeholder svg {
        width: 48px;
        height: 48px;
        color: #94a3b8;
    }

    .news-card__date {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 0.65rem;
        background: rgba(234, 179, 8, 0.95);
        color: #1e293b;
        border-radius: 10px;
        text-align: center;
        line-height: 1.2;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .news-date__day {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .news-date__month {
        font-size: 0.6rem;
        text-transform: uppercase;
        opacity: 0.9;
    }

    .news-date__year {
        font-size: 0.55rem;
        opacity: 0.85;
    }

    .news-card__content {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 1.25rem 1.25rem 1.15rem;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.75) 50%, transparent 100%);
        display: flex;
        flex-direction: column;
        z-index: 1;
    }

    .news-card__title {
        font-size: 1.05rem;
        margin: 0 0 0.35rem;
        line-height: 1.35;
        font-weight: 600;
        color: #fff;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .news-card:hover .news-card__title {
        color: #f8fafc;
    }

    .news-card__excerpt {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.8125rem;
        margin: 0 0 0.5rem;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    .news-card__link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #fcd34d;
        font-weight: 600;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        position: relative;
        z-index: 2;
    }

    .news-card__link svg {
        width: 18px;
        height: 18px;
        transition: transform 0.2s ease;
    }

    .news-card:hover .news-card__link {
        color: #fde047;
    }

    .news-card:hover .news-card__link svg {
        transform: translateX(3px);
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

    .news-card__wrap:focus {
        outline: 2px solid #eab308;
        outline-offset: 2px;
    }

    .news-card__wrap:focus:not(:focus-visible) {
        outline: none;
    }

    @media (max-width: 1400px) {
        .container--narrow {
            max-width: 1200px;
            padding: 0 1.5rem;
        }
    }

    @media (max-width: 1024px) {
        .container--narrow {
            max-width: 900px;
            padding: 0 1.5rem;
        }
        
        .news-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }

        .news-card,
        .news-card__wrap {
            min-height: 260px;
        }
    }

    @media (max-width: 768px) {
        .container--narrow {
            max-width: 100%;
            padding: 0 1rem;
        }
        
        .news-grid {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .news-card--featured {
            grid-column: span 1;
        }

        .news-card,
        .news-card__wrap {
            min-height: 320px;
        }

        .news-card__content {
            padding: 1.15rem 1.15rem 1rem;
        }
    }
</style>

<?= $this->endSection() ?>
