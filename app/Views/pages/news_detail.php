<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php helper('program_upload'); ?>
<!-- Skip link for accessibility (Rule: skip link for main content) -->
<a href="#news-detail-main" class="news-detail-skip">ข้ามไปเนื้อหา</a>

<!-- Page Header -->
<section class="page-header page-header--news">
    <div class="container">
        <nav class="page-header__breadcrumb" aria-label="breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span aria-hidden="true">/</span>
            <a href="<?= base_url('news') ?>">ข่าวสาร</a>
            <span aria-hidden="true">/</span>
            <span>รายละเอียด</span>
        </nav>
    </div>
</section>

<!-- News Detail Section -->
<section class="section" aria-label="รายละเอียดข่าว">
    <div class="container">
        <div class="news-detail-grid">
            <!-- Main Content -->
            <article id="news-detail-main" class="news-detail" tabindex="-1">
                <?php if (!empty($news['featured_image'])): ?>
                    <?php $imgUrl = featured_image_serve_url($news['featured_image'], false); ?>
                    <div class="news-detail__image">
                        <img src="<?= $imgUrl ?>" alt="<?= esc($news['title']) ?>" width="1200" height="630" loading="lazy" decoding="async">
                    </div>
                <?php endif; ?>

                <div class="news-detail__header">
                    <h1 class="news-detail__title"><?= esc($news['title']) ?></h1>

                    <div class="news-detail__meta">
                        <?php if (!empty($news['published_at'])): ?>
                            <span class="news-meta__date">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                <?= thaiDateFull($news['published_at']) ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($news['view_count'])): ?>
                            <span class="news-meta__views">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <?= number_format($news['view_count']) ?> ครั้ง
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($news_tags)): ?>
                        <div class="news-detail__tags">
                            <?php foreach ($news_tags as $tag): ?>
                                <a href="<?= base_url('news?tag=' . esc($tag['slug'])) ?>" class="news-tag">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
                                        <line x1="7" y1="7" x2="7.01" y2="7" />
                                    </svg>
                                    <?= esc($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="news-detail__content">
                    <?php if (!empty($news['content'])): ?>
                        <?= $news['content'] ?>
                    <?php else: ?>
                        <p class="no-content">ไม่มีเนื้อหาเพิ่มเติม</p>
                    <?php endif; ?>
                </div>

                <?php $has_docs = !empty($news_documents); $has_imgs = !empty($news_images); ?>
                <?php if ($has_docs || $has_imgs): ?>
                    <div class="news-detail__attachments">
                        <h2 class="news-detail__section-title">เอกสารและรูปภาพแนบ</h2>

                        <?php if ($has_docs): ?>
                            <?php
                            $docExtLabels = ['pdf' => 'PDF', 'doc' => 'DOC', 'docx' => 'DOCX', 'xls' => 'XLS', 'xlsx' => 'XLSX', 'ppt' => 'PPT', 'pptx' => 'PPTX'];
                            ?>
                            <div class="news-detail__documents">
                                <h3 class="news-detail__subsection-title">เอกสารแนบ</h3>
                                <ul class="document-list">
                                    <?php foreach ($news_documents as $doc): ?>
                                        <?php
                                        $docFile = basename($doc['image_path']);
                                        $docExt = strtolower(pathinfo($docFile, PATHINFO_EXTENSION));
                                        $docLabel = $docExtLabels[$docExt] ?? strtoupper($docExt);
                                        $docName = $doc['caption'] ?: $docFile;
                                        ?>
                                        <li>
                                            <a href="<?= base_url('serve/uploads/news/' . esc($docFile)) ?>" target="_blank" rel="noopener" class="document-item">
                                                <span class="document-item__type" aria-label="ชนิดไฟล์"><?= esc($docLabel) ?></span>
                                                <span class="document-item__name"><?= esc($docName) ?></span>
                                                <svg class="document-item__download" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                </svg>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($has_imgs): ?>
                            <div class="news-detail__gallery" id="newsGallery" role="list">
                                <h3 class="news-detail__subsection-title">รูปภาพประกอบ</h3>
                                <?php foreach ($news_images as $idx => $img): ?>
                                    <?php $imgUrl = featured_image_serve_url($img['image_path'], false); $imgCaption = $img['caption'] ?? $news['title']; ?>
                                    <figure class="news-detail__gallery-item">
                                        <button type="button" class="news-gallery-thumb" data-index="<?= $idx ?>" data-src="<?= $imgUrl ?>" data-alt="<?= esc($imgCaption) ?>" aria-label="ดูรูปภาพขนาดใหญ่">
                                            <img src="<?= $imgUrl ?>" alt="<?= esc($imgCaption) ?>" width="400" height="300" loading="lazy" decoding="async">
                                        </button>
                                        <?php if (!empty($img['caption'])): ?>
                                            <figcaption class="news-detail__gallery-caption"><?= esc($img['caption']) ?></figcaption>
                                        <?php endif; ?>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                            <!-- Lightbox -->
                            <div id="newsLightbox" class="news-lightbox" role="dialog" aria-modal="true" aria-label="ดูรูปภาพขนาดใหญ่" hidden>
                                <button type="button" class="news-lightbox__close" aria-label="ปิด">×</button>
                                <button type="button" class="news-lightbox__prev" aria-label="รูปก่อนหน้า">‹</button>
                                <div class="news-lightbox__content">
                                    <img id="newsLightboxImg" src="" alt="" decoding="async">
                                    <p id="newsLightboxCaption" class="news-lightbox__caption"></p>
                                </div>
                                <button type="button" class="news-lightbox__next" aria-label="รูปถัดไป">›</button>
                                <span class="news-lightbox__counter" id="newsLightboxCounter"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($news['facebook_url'])): ?>
                    <div class="news-detail__facebook-link">
                        <a href="<?= esc($news['facebook_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-facebook-link" id="newsFacebookLink" data-href="<?= esc($news['facebook_url']) ?>" aria-label="ไปยังโพสต์ Facebook ของข่าวนี้">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                            </svg>
                            ดูบน Facebook
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Share Section -->
                <div class="news-detail__share">
                    <span class="share-label" id="share-label">แชร์</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" target="_blank" rel="noopener noreferrer" class="share-btn share-btn--facebook" aria-label="แชร์ไปที่ Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($news['title']) ?>" target="_blank" rel="noopener noreferrer" class="share-btn share-btn--twitter" aria-label="แชร์ไปที่ X (Twitter)">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
                        </svg>
                    </a>
                    <a href="https://line.me/R/msg/text/?<?= urlencode($news['title'] . ' ' . current_url()) ?>" target="_blank" rel="noopener noreferrer" class="share-btn share-btn--line" aria-label="แชร์ไปที่ Line">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                            <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
                        </svg>
                    </a>
                </div>

                <!-- Back Button -->
                <div class="news-detail__actions">
                    <a href="<?= base_url('news') ?>" class="btn btn-outline news-detail__back">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        กลับไปหน้าข่าวสาร
                    </a>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="news-sidebar" aria-label="ข่าวที่เกี่ยวข้อง">
                <div class="sidebar-section">
                    <h2 class="sidebar-title">ข่าวล่าสุด</h2>
                    <?php if (!empty($related_news)): ?>
                        <ul class="related-news" role="list">
                            <?php foreach ($related_news as $related): ?>
                                <?php if ($related['id'] != $news['id']): ?>
                                    <li>
                                        <a href="<?= base_url('news/' . esc($related['id'])) ?>" class="related-news__item">
                                            <span class="related-news__image">
                                                <?php if (!empty($related['featured_image'])): ?>
                                                    <img src="<?= featured_image_serve_url($related['featured_image'], true) ?>" alt="" width="80" height="60" loading="lazy" decoding="async">
                                                <?php else: ?>
                                                    <span class="related-news__placeholder" aria-hidden="true"></span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="related-news__content">
                                                <span class="related-news__title"><?= esc(mb_substr($related['title'], 0, 60)) ?><?= mb_strlen($related['title']) > 60 ? '…' : '' ?></span>
                                                <?php if (!empty($related['published_at'])): ?>
                                                    <span class="related-news__date"><?= date('d/m/Y', strtotime($related['published_at'])) ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="related-news__empty">ยังไม่มีข่าวอื่น</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php
// Helper function for Thai full date
function thaiDateFull($date)
{
    $months = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    return "{$day} {$month} {$year}";
}
?>

<style>
    /* Skip link (Rule: skip link for main content) – โชว์เมื่อโฟกัส */
    .news-detail-skip {
        position: absolute;
        left: -9999px;
        top: 0.5rem;
        z-index: 100;
        padding: 0.5rem 1rem;
        background: var(--color-primary);
        color: var(--color-dark);
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
    }

    .news-detail-skip:focus {
        left: 1rem;
        outline: 2px solid var(--color-dark);
        outline-offset: 2px;
    }

    #news-detail-main {
        scroll-margin-top: 2rem;
    }

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
        background: var(--color-white);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .news-detail__image {
        width: 100%;
        aspect-ratio: 1200 / 630;
        max-height: 500px;
        overflow: hidden;
    }

    .news-detail__image img {
        width: 100%;
        height: 100%;
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
        text-wrap: balance;
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

    .news-detail__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .news-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        background: var(--color-primary);
        color: var(--color-dark);
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 20px;
        text-decoration: none;
        transition: background 0.2s ease, transform 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .news-tag:hover {
        background: var(--color-primary-dark, #ca8a04);
        transform: translateY(-1px);
    }

    .news-tag:focus-visible {
        outline: 2px solid var(--color-dark);
        outline-offset: 2px;
    }

    .news-tag svg {
        width: 14px;
        height: 14px;
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
        -webkit-tap-highlight-color: transparent;
    }

    .share-btn:hover {
        transform: scale(1.1);
    }

    .share-btn:focus-visible {
        outline: 2px solid var(--color-dark);
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        .share-btn:hover {
            transform: none;
        }
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

    .news-detail__facebook-link {
        padding: 0 2rem 1rem;
        border-top: 1px solid var(--color-gray-200);
    }

    .btn-facebook-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        background: #1877f2;
        color: #fff;
        font-weight: 500;
        text-decoration: none;
        border-radius: 8px;
        transition: background 0.2s ease, transform 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .btn-facebook-link:hover {
        background: #166fe5;
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-facebook-link:focus-visible {
        outline: 2px solid var(--color-dark);
        outline-offset: 2px;
    }

    .btn-facebook-link svg {
        width: 20px;
        height: 20px;
    }

    .news-detail__actions {
        padding: 0 2rem 2rem;
    }

    .news-detail__back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .news-detail__back:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
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
        background: var(--color-white);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .sidebar-title {
        font-size: 1.1rem;
        margin: 0 0 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--color-primary);
    }

    .related-news {
        list-style: none;
        padding: 0;
        margin: 0;
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
        -webkit-tap-highlight-color: transparent;
    }

    .related-news__item:hover {
        background: var(--color-gray-50);
    }

    .related-news__item:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .related-news__image {
        flex-shrink: 0;
        width: 80px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        display: block;
    }

    .related-news__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-news__placeholder {
        width: 100%;
        height: 100%;
        display: block;
        background: linear-gradient(135deg, var(--color-gray-100), var(--color-gray-200));
    }

    .related-news__content {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .related-news__title {
        font-size: 0.9rem;
        margin: 0 0 0.25rem;
        color: var(--text-primary);
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .related-news__date {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .related-news__empty {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.9rem;
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
        .news-detail__facebook-link,
        .news-detail__share,
        .news-detail__actions {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .news-detail__title {
            font-size: 1.5rem;
        }
    }

    .news-detail__attachments {
        padding: 0 2rem 2rem;
        border-top: 1px solid var(--color-gray-100);
        margin-top: 1rem;
        padding-top: 2rem;
    }

    .news-detail__attachments .news-detail__section-title {
        margin-bottom: 1.25rem;
    }

    .news-detail__documents {
        margin-bottom: 2rem;
    }

    .news-detail__documents:last-child {
        margin-bottom: 0;
    }

    .news-detail__subsection-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .news-detail__gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .news-detail__gallery .news-detail__subsection-title {
        margin-top: 0;
        margin-bottom: 1rem;
        grid-column: 1 / -1;
    }

    .news-detail__gallery-item {
        margin: 0;
        overflow: hidden;
        border-radius: 8px;
    }

    .news-detail__gallery .news-gallery-thumb {
        display: block;
        width: 100%;
        padding: 0;
        border: none;
        background: none;
        cursor: pointer;
    }

    .news-detail__gallery .news-gallery-thumb img {
        width: 100%;
        height: auto;
        aspect-ratio: 4/3;
        object-fit: cover;
        display: block;
    }

    .news-detail__gallery-caption {
        font-size: 0.8125rem;
        color: var(--color-gray-600);
        margin-top: 0.35rem;
        padding: 0 0.25rem;
    }

    .news-detail__section-title {
        font-size: 1.25rem;
        margin-bottom: 1rem;
        color: var(--color-primary-dark);
    }

    .document-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .document-item {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--color-gray-50);
        border: 1px solid var(--color-gray-200);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-primary);
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .document-item:hover {
        background: var(--color-white);
        border-color: var(--color-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .document-item svg {
        width: 20px;
        height: 20px;
        color: var(--color-primary);
        flex-shrink: 0;
    }

    .document-item__type {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        background: var(--color-primary);
        color: var(--color-white, #fff);
        border-radius: 4px;
        flex-shrink: 0;
    }

    .document-item__name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .document-item__download {
        width: 18px;
        height: 18px;
        opacity: 0.7;
    }

    .news-gallery-thumb {
        display: block;
        width: 100%;
        padding: 0;
        border: none;
        background: none;
        cursor: pointer;
        border-radius: 8px;
        overflow: hidden;
        -webkit-tap-highlight-color: transparent;
    }

    .news-gallery-thumb:hover img,
    .news-gallery-thumb:focus-visible img {
        transform: scale(1.03);
    }

    .news-gallery-thumb img {
        display: block;
        width: 100%;
        height: auto;
        transition: transform 0.25s ease;
    }

    .news-gallery-thumb:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .news-lightbox {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        box-sizing: border-box;
    }

    .news-lightbox[hidden] {
        display: none !important;
    }

    .news-lightbox__close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 44px;
        height: 44px;
        border: none;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 2rem;
        line-height: 1;
        cursor: pointer;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        z-index: 10;
    }

    .news-lightbox__close:hover {
        background: rgba(255,255,255,0.3);
    }

    .news-lightbox__prev,
    .news-lightbox__next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border: none;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 2.5rem;
        line-height: 1;
        cursor: pointer;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        z-index: 10;
    }

    .news-lightbox__prev { left: 1rem; }
    .news-lightbox__next { right: 1rem; }

    .news-lightbox__prev:hover,
    .news-lightbox__next:hover {
        background: rgba(255,255,255,0.3);
    }

    .news-lightbox__content {
        max-width: 90vw;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .news-lightbox__content img {
        max-width: 100%;
        max-height: 75vh;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .news-lightbox__caption {
        color: rgba(255,255,255,0.9);
        margin-top: 0.75rem;
        font-size: 0.95rem;
        text-align: center;
    }

    .news-lightbox__counter {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }
</style>

<script src="<?= base_url('assets/js/news-lightbox.js') ?>"></script>
<?php if (!empty($news['facebook_url'])): ?>
<script>
(function () {
    var el = document.getElementById('newsFacebookLink');
    if (!el) return;
    var href = el.getAttribute('data-href');
    el.addEventListener('click', function (e) {
        e.preventDefault();
        (typeof swalConfirm !== 'undefined' ? swalConfirm('จะพาท่านไปยัง Facebook') : Promise.resolve(confirm('จะพาท่านไปยัง Facebook'))).then(function (ok) {
            if (ok) window.open(href, '_blank', 'noopener,noreferrer');
        });
    });
})();
</script>
<?php endif; ?>

<?= $this->endSection() ?>