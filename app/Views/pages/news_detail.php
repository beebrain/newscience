<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header page-header--news">
    <div class="container">
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <a href="<?= base_url('news') ?>">ข่าวสาร</a>
            <span>/</span>
            <span>รายละเอียด</span>
        </div>
    </div>
</section>

<!-- News Detail Section -->
<section class="section">
    <div class="container">
        <div class="news-detail-grid">
            <!-- Main Content -->
            <article class="news-detail">
                <?php if (!empty($news['featured_image'])): ?>
                <div class="news-detail__image">
                    <img src="<?= esc($news['featured_image']) ?>" alt="<?= esc($news['title']) ?>">
                </div>
                <?php endif; ?>
                
                <div class="news-detail__header">
                    <h1 class="news-detail__title"><?= esc($news['title']) ?></h1>
                    
                    <div class="news-detail__meta">
                        <?php if (!empty($news['published_at'])): ?>
                        <span class="news-meta__date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <?= thaiDateFull($news['published_at']) ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($news['view_count'])): ?>
                        <span class="news-meta__views">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <?= number_format($news['view_count']) ?> ครั้ง
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="news-detail__content">
                    <?php if (!empty($news['content'])): ?>
                        <?= $news['content'] ?>
                    <?php else: ?>
                        <p class="no-content">ไม่มีเนื้อหาเพิ่มเติม</p>
                    <?php endif; ?>
                </div>
                
                <!-- Share Section -->
                <div class="news-detail__share">
                    <span class="share-label">แชร์:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" target="_blank" rel="noopener" class="share-btn share-btn--facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($news['title']) ?>" target="_blank" rel="noopener" class="share-btn share-btn--twitter">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                        </svg>
                    </a>
                    <a href="https://line.me/R/msg/text/?<?= urlencode($news['title'] . ' ' . current_url()) ?>" target="_blank" rel="noopener" class="share-btn share-btn--line">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                        </svg>
                    </a>
                </div>
                
                <!-- Back Button -->
                <div class="news-detail__actions">
                    <a href="<?= base_url('news') ?>" class="btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        กลับไปหน้าข่าวสาร
                    </a>
                </div>
            </article>
            
            <!-- Sidebar -->
            <aside class="news-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">ข่าวล่าสุด</h3>
                    <?php if (!empty($related_news)): ?>
                    <div class="related-news">
                        <?php foreach ($related_news as $related): ?>
                        <?php if ($related['id'] != $news['id']): ?>
                        <a href="<?= base_url('news/' . esc($related['slug'])) ?>" class="related-news__item">
                            <div class="related-news__image">
                                <?php if (!empty($related['featured_image'])): ?>
                                <img src="<?= esc($related['featured_image']) ?>" alt="<?= esc($related['title']) ?>">
                                <?php else: ?>
                                <div class="related-news__placeholder"></div>
                                <?php endif; ?>
                            </div>
                            <div class="related-news__content">
                                <h4><?= esc(mb_substr($related['title'], 0, 60)) ?>...</h4>
                                <?php if (!empty($related['published_at'])): ?>
                                <span class="related-news__date"><?= date('d/m/Y', strtotime($related['published_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php
// Helper function for Thai full date
function thaiDateFull($date) {
    $months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    return "{$day} {$month} {$year}";
}
?>

<style>
.page-header--news {
    padding: 2rem 0;
}

.news-detail-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 3rem;
    align-items: start;
}

.news-detail {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.news-detail__image {
    width: 100%;
    max-height: 500px;
    overflow: hidden;
}

.news-detail__image img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.news-detail__header {
    padding: 2rem 2rem 1rem;
}

.news-detail__title {
    font-size: 1.75rem;
    margin: 0 0 1rem;
    line-height: 1.4;
    color: var(--text-primary);
}

.news-detail__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.news-detail__meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.news-detail__meta svg {
    width: 18px;
    height: 18px;
}

.news-detail__content {
    padding: 0 2rem 2rem;
    line-height: 1.8;
    color: var(--text-primary);
}

.news-detail__content p {
    margin-bottom: 1.5rem;
}

.news-detail__content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.no-content {
    color: var(--text-secondary);
    font-style: italic;
}

.news-detail__share {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem;
    border-top: 1px solid var(--color-gray-200);
}

.share-label {
    font-weight: 500;
    color: var(--text-secondary);
}

.share-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    transition: transform 0.2s ease;
}

.share-btn:hover {
    transform: scale(1.1);
}

.share-btn svg {
    width: 20px;
    height: 20px;
}

.share-btn--facebook {
    background: #1877f2;
}

.share-btn--twitter {
    background: #1da1f2;
}

.share-btn--line {
    background: #00c300;
}

.news-detail__actions {
    padding: 0 2rem 2rem;
}

.news-detail__actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.news-detail__actions .btn svg {
    width: 18px;
    height: 18px;
}

/* Sidebar */
.news-sidebar {
    position: sticky;
    top: 100px;
}

.sidebar-section {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.sidebar-title {
    font-size: 1.1rem;
    margin: 0 0 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--color-primary);
}

.related-news {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.related-news__item {
    display: flex;
    gap: 1rem;
    text-decoration: none;
    padding: 0.75rem;
    border-radius: 8px;
    transition: background 0.2s ease;
}

.related-news__item:hover {
    background: var(--color-gray-50);
}

.related-news__image {
    flex-shrink: 0;
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
}

.related-news__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-news__placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--color-gray-100), var(--color-gray-200));
}

.related-news__content h4 {
    font-size: 0.9rem;
    margin: 0 0 0.25rem;
    color: var(--text-primary);
    line-height: 1.4;
}

.related-news__date {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

@media (max-width: 1024px) {
    .news-detail-grid {
        grid-template-columns: 1fr;
    }
    
    .news-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .news-detail__header,
    .news-detail__content,
    .news-detail__share,
    .news-detail__actions {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
    .news-detail__title {
        font-size: 1.5rem;
    }
}
</style>

<?= $this->endSection() ?>
