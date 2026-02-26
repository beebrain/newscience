<?= $this->extend($layout ?? 'layouts/main_layout') ?>

<?= $this->section('content') ?>

<?php
$page = is_array($page ?? null) ? $page : [];
$programId = (int) ($program['id'] ?? 0);
$programAccent = !empty($page['theme_color']) ? $page['theme_color'] : '';
?>

<?php if (!$program): ?>
    <div class="program-page program-page--empty">
        <div class="container">
            <div class="program-empty">
                <h2 class="program-empty__title">ไม่พบหลักสูตร</h2>
                <p class="program-empty__text">หลักสูตรที่คุณค้นหาไม่มีอยู่ในระบบ</p>
                <a href="<?= base_url('academics') ?>" class="btn btn-primary">กลับไปหน้าหลักสูตร</a>
            </div>
        </div>
    </div>
<?php else: ?>

<div id="pd-content">
    <div class="pd-loading">
        <div class="spinner"></div>
        <p>กำลังโหลดข้อมูลหลักสูตร...</p>
    </div>
</div>

<div id="pd-real-content" style="display:none;">

    <!-- Hero Section -->
    <section class="pd-hero">
        <div id="pd-hero-bg" class="pd-hero__bg"></div>
        <div class="pd-hero__overlay"></div>
        <div class="container">
            <div class="pd-hero__badge" id="pd-hero-badge"></div>
            <h1 class="pd-hero__title" id="pd-hero-title"></h1>
            <p class="pd-hero__degree" id="pd-hero-degree"></p>
            <div class="hero__actions">
                <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="btn btn-primary btn-lg">สมัครเรียน</a>
                <a href="#pd-overview" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">ดูรายละเอียด</a>
            </div>
            <div class="pd-hero__stats">
                <div class="pd-hero__stat">
                    <div class="pd-hero__stat-number" id="pd-hero-credits">-</div>
                    <div class="pd-hero__stat-label">หน่วยกิต</div>
                </div>
                <div class="pd-hero__stat">
                    <div class="pd-hero__stat-number" id="pd-hero-duration">-</div>
                    <div class="pd-hero__stat-label">ปี</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sticky Nav -->
    <nav class="pd-sticky-nav" aria-label="เมนูส่วนต่างๆ ของหลักสูตร">
        <ul class="pd-sticky-nav__list">
            <li><a href="#pd-overview" class="pd-sticky-nav__link active">ภาพรวม</a></li>
            <li><a href="#pd-elo" class="pd-sticky-nav__link">ผลลัพธ์การเรียนรู้</a></li>
            <li><a href="#pd-curriculum" class="pd-sticky-nav__link">หลักสูตร</a></li>
            <li><a href="#pd-content-sections" class="pd-sticky-nav__link">รายละเอียด</a></li>
            <li><a href="#pd-staff" class="pd-sticky-nav__link">บุคลากร</a></li>
            <li><a href="#pd-news" class="pd-sticky-nav__link">ข่าวสาร</a></li>
            <li><a href="#pd-docs" class="pd-sticky-nav__link">เอกสาร QA</a></li>
        </ul>
    </nav>

    <!-- Overview -->
    <section id="pd-overview" class="pd-section section-light" style="display:none;">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">1</span>
                <h2 class="pd-section__title">ภาพรวมหลักสูตร</h2>
                <p class="pd-section__subtitle">ปรัชญาและวิสัยทัศน์ของหลักสูตร</p>
            </div>
            <div class="pd-overview-grid" id="pd-overview-section"></div>
        </div>
    </section>

    <!-- ELOs -->
    <section id="pd-elo" class="pd-section">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">2</span>
                <h2 class="pd-section__title">ผลลัพธ์การเรียนรู้ที่คาดหวัง</h2>
                <p class="pd-section__subtitle">Expected Learning Outcomes (ELOs) — หัวใจสำคัญของ AUN-QA</p>
            </div>
            <div class="pd-elo-grid" id="pd-elo-grid"></div>
        </div>
    </section>

    <!-- Curriculum -->
    <section id="pd-curriculum" class="pd-section section-light">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">3</span>
                <h2 class="pd-section__title">โครงสร้างหลักสูตรและแผนการเรียน</h2>
                <p class="pd-section__subtitle">Curriculum & Study Plan</p>
            </div>
            <div class="pd-accordion" id="pd-curriculum-accordion"></div>
        </div>
    </section>

    <!-- Content Sections (curriculum structure, study plan, career, tuition, admission, contact) -->
    <section id="pd-content-sections" class="pd-section">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">4</span>
                <h2 class="pd-section__title">รายละเอียดเพิ่มเติม</h2>
                <p class="pd-section__subtitle">ข้อมูลหลักสูตร โครงสร้าง ค่าเล่าเรียน การรับสมัคร และอื่นๆ</p>
            </div>
            <div id="pd-extra-content"></div>
        </div>
    </section>

    <!-- Staff -->
    <section id="pd-staff" class="pd-section section-light">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">5</span>
                <h2 class="pd-section__title">คณาจารย์ประจำหลักสูตร</h2>
                <p class="pd-section__subtitle">Academic Staff</p>
            </div>
            <div class="pd-staff-grid" id="pd-staff-grid"></div>
        </div>
    </section>

    <!-- News -->
    <section id="pd-news" class="pd-section">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">6</span>
                <h2 class="pd-section__title">ข่าวสารและกิจกรรม</h2>
                <p class="pd-section__subtitle">News & Activities</p>
            </div>
            <div class="grid grid-3" id="pd-news-grid">
                <p class="text-muted" style="grid-column:1/-1;text-align:center;padding:2rem;">กำลังโหลดข่าวสาร...</p>
            </div>
        </div>
    </section>

    <!-- QA Documents -->
    <section id="pd-docs" class="pd-section section-light">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">7</span>
                <h2 class="pd-section__title">เอกสารดาวน์โหลด</h2>
                <p class="pd-section__subtitle">QA Document Hub — เอกสารสำหรับดาวน์โหลด</p>
            </div>
            <div class="pd-doc-list" id="pd-doc-list"></div>
        </div>
    </section>

    <!-- Video -->
    <section id="pd-video" class="pd-section" style="display:none;">
        <div class="container">
            <div class="pd-section__header">
                <h2 class="pd-section__title">วิดีโอแนะนำ</h2>
            </div>
            <div class="program-video-wrap" id="pd-video-wrap"></div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-section__title">พร้อมเริ่มต้นเส้นทางใหม่?</h2>
            <p class="cta-section__description">สมัครเรียนวันนี้ เพื่ออนาคตที่สดใส</p>
            <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="btn btn-secondary btn-lg cta-section__btn">สมัครเรียนออนไลน์</a>
        </div>
    </section>

</div>

<script>
    window.PROGRAM_ID = <?= $programId ?>;
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/program-detail.css') ?>?v=<?= is_file(FCPATH . 'assets/css/program-detail.css') ? filemtime(FCPATH . 'assets/css/program-detail.css') : '1' ?>">

<style>
.program-page--empty { padding: var(--spacing-16) 0; }
.program-empty {
    text-align: center;
    padding: var(--spacing-12);
    background: var(--color-gray-50);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-gray-200);
}
.program-empty__title { margin-bottom: var(--spacing-2); color: var(--color-gray-900); }
.program-empty__text { color: var(--color-gray-600); margin-bottom: var(--spacing-6); }

.program-video-wrap {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
}
.program-video__iframe,
.program-video__player {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border: none;
}
</style>

<?php endif; ?>

<?php if ($program): ?>
<?php $this->section('footer_scripts'); ?>
<script src="<?= base_url('assets/js/program-detail.js') ?>?v=<?= is_file(FCPATH . 'assets/js/program-detail.js') ? filemtime(FCPATH . 'assets/js/program-detail.js') : '1' ?>"></script>
<?php $this->endSection(); ?>
<?php endif; ?>

<?= $this->endSection() ?>
