<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">กิจกรรม</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>กิจกรรม</span>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">กิจกรรมที่จะมาถึง</span>
            <h2 class="section-header__title">Upcoming Events</h2>
            <p class="section-header__description">
                กิจกรรมและโครงการต่างๆ ที่จะเกิดขึ้นของคณะวิทยาศาสตร์และเทคโนโลยี
            </p>
        </div>

        <?php if (empty($events)): ?>
            <div class="text-center py-8">
                <p class="text-muted">ยังไม่มีกิจกรรมในขณะนี้</p>
            </div>
        <?php else: ?>
            <div class="grid grid-2">
                <?php foreach ($events as $event): ?>
                    <?php
                    $eventDate = strtotime($event['event_date']);
                    $day = date('d', $eventDate);
                    $monthShort = date('M', $eventDate);
                    $timeStr = $event['event_time'] ? date('g:i A', strtotime($event['event_time'])) : '';
                    $location = $event['location'] ?: '';
                    ?>
                    <article class="card animate-on-scroll event-list-card">
                        <div class="event-list-card__date-block">
                            <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);"><?= $day ?></span>
                            <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;"><?= $monthShort ?></span>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title">
                                <a href="<?= base_url('events/' . $event['id']) ?>"><?= esc($event['title']) ?></a>
                            </h3>
                            <?php if ($event['excerpt']): ?>
                                <p class="card__excerpt"><?= esc($event['excerpt']) ?></p>
                            <?php endif; ?>
                            <div class="card__meta">
                                <?php if ($timeStr): ?>
                                    <span><?= $timeStr ?></span>
                                    <?php if ($location): ?><span>•</span><?php endif; ?>
                                <?php endif; ?>
                                <?php if ($location): ?>
                                    <span><?= esc($location) ?></span>
                                <?php endif; ?>
                                <?php if (!$timeStr && !$location): ?>
                                    <span>—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">มีกิจกรรมที่ต้องการประชาสัมพันธ์?</h2>
        <p class="cta-section__description">
            ติดต่อหน่วยประชาสัมพันธ์เพื่อนำกิจกรรมขึ้นเว็บไซต์
        </p>
        <a href="<?= base_url('contact') ?>" class="btn btn-secondary btn-lg">ติดต่อเรา</a>
    </div>
</section>

<?= $this->endSection() ?>