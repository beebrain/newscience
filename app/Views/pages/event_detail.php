<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <a href="<?= base_url('events') ?>">กิจกรรม</a>
            <span>/</span>
            <span>รายละเอียด</span>
        </div>
    </div>
</section>

<!-- Event Detail Section -->
<section class="section">
    <div class="container">
        <article class="news-detail">
            <?php if (!empty($event['featured_image'])): ?>
                <div class="news-detail__image">
                    <img src="<?= base_url('serve/uploads/events/' . esc($event['featured_image'])) ?>" alt="<?= esc($event['title']) ?>">
                </div>
            <?php endif; ?>

            <div class="news-detail__header">
                <h1 class="news-detail__title"><?= esc($event['title']) ?></h1>

                <div class="news-detail__meta">
                    <span class="news-meta__date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <?= date('j M Y', strtotime($event['event_date'])) ?>
                    </span>
                    <?php if (!empty($event['event_time'])): ?>
                        <span class="news-meta__date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <?= date('g:i A', strtotime($event['event_time'])) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($event['location'])): ?>
                        <span class="news-meta__date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <?= esc($event['location']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($event['excerpt'])): ?>
                <p class="news-detail__excerpt" style="font-size: 1.1rem; color: var(--text-muted, #64748b); margin-bottom: 1.5rem;">
                    <?= esc($event['excerpt']) ?>
                </p>
            <?php endif; ?>

            <div class="news-detail__content">
                <?php if (!empty($event['content'])): ?>
                    <?= $event['content'] ?>
                <?php else: ?>
                    <p class="no-content">ไม่มีเนื้อหาเพิ่มเติม</p>
                <?php endif; ?>
            </div>

            <div style="margin-top: 2rem;">
                <a href="<?= base_url('events') ?>" class="btn btn-secondary">← กลับหน้ากิจกรรมทั้งหมด</a>
            </div>
        </article>
    </div>
</section>

<?= $this->endSection() ?>