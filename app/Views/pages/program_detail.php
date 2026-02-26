<?= $this->extend($layout ?? 'layouts/main_layout') ?>

<?= $this->section('content') ?>

<!-- Loading State (replaced by JS after data loads) -->
<div id="pd-content">
    <div class="pd-loading">
        <div class="spinner"></div>
        <p>กำลังโหลดข้อมูลหลักสูตร...</p>
    </div>
</div>

<!-- Real Content (hidden until JS populates it) -->
<div id="pd-real-content" style="display:none;">

    <!-- ===================== 1. Hero Section ===================== -->
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

    <!-- ===================== Sticky Nav ===================== -->
    <nav class="pd-sticky-nav" aria-label="เมนูส่วนต่างๆ ของหลักสูตร">
        <ul class="pd-sticky-nav__list">
            <li><a href="#pd-overview" class="pd-sticky-nav__link active">ภาพรวม</a></li>
            <li><a href="#pd-elo" class="pd-sticky-nav__link">ผลลัพธ์การเรียนรู้</a></li>
            <li><a href="#pd-curriculum" class="pd-sticky-nav__link">หลักสูตร</a></li>
            <li><a href="#pd-career" class="pd-sticky-nav__link">อาชีพ</a></li>
            <li><a href="#pd-staff" class="pd-sticky-nav__link">บุคลากร</a></li>
            <li><a href="#pd-news" class="pd-sticky-nav__link">ข่าวสาร</a></li>
            <li><a href="#pd-docs" class="pd-sticky-nav__link">เอกสาร QA</a></li>
        </ul>
    </nav>

    <!-- ===================== 2. Overview ===================== -->
    <section id="pd-overview" class="pd-section section-light" style="display:none;">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">1</span>
                <h2 class="pd-section__title">ภาพรวมหลักสูตร</h2>
                <p class="pd-section__subtitle">ปรัชญาและวิสัยทัศน์ของหลักสูตร</p>
            </div>
            <div class="pd-overview-grid" id="pd-overview-section">
                <div class="pd-overview-box">
                    <div class="pd-overview-box__label">ปรัชญา (Philosophy)</div>
                    <p class="pd-overview-box__text" id="pd-philosophy"></p>
                </div>
                <div class="pd-overview-box">
                    <div class="pd-overview-box__label">วิสัยทัศน์ (Vision)</div>
                    <p class="pd-overview-box__text" id="pd-vision"></p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== 3. ELOs (AUN-QA Core) ===================== -->
    <section id="pd-elo" class="pd-section">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">2</span>
                <h2 class="pd-section__title">ผลลัพธ์การเรียนรู้ที่คาดหวัง</h2>
                <p class="pd-section__subtitle">Expected Learning Outcomes (ELOs) — หัวใจสำคัญของ AUN-QA</p>
            </div>
            <div class="pd-elo-grid" id="pd-elo-grid">
                <!-- Populated by JS -->
            </div>
        </div>
    </section>

    <!-- ===================== 4. Curriculum ===================== -->
    <section id="pd-curriculum" class="pd-section section-light">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">3</span>
                <h2 class="pd-section__title">โครงสร้างหลักสูตรและแผนการเรียน</h2>
                <p class="pd-section__subtitle">Curriculum & Study Plan</p>
            </div>
            <div class="pd-accordion" id="pd-curriculum-accordion">
                <!-- Populated by JS -->
            </div>
        </div>
    </section>

    <!-- ===================== 5. Career ===================== -->
    <section id="pd-career" class="pd-section">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">4</span>
                <h2 class="pd-section__title">อาชีพที่สามารถประกอบได้</h2>
                <p class="pd-section__subtitle">Career Opportunities</p>
            </div>
            <div class="pd-career-grid" id="pd-career-grid">
                <!-- Populated by JS -->
            </div>
        </div>
    </section>

    <!-- ===================== 6. Staff ===================== -->
    <section id="pd-staff" class="pd-section section-light">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">5</span>
                <h2 class="pd-section__title">คณาจารย์ประจำหลักสูตร</h2>
                <p class="pd-section__subtitle">Academic Staff</p>
            </div>
            <div class="pd-staff-grid" id="pd-staff-grid">
                <!-- Populated by JS -->
            </div>
        </div>
    </section>

    <!-- ===================== 7. News ===================== -->
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

    <!-- ===================== 8. QA Documents ===================== -->
    <section id="pd-docs" class="pd-section section-light">
        <div class="container">
            <div class="pd-section__header">
                <span class="pd-section__number">7</span>
                <h2 class="pd-section__title">เอกสารประกันคุณภาพ</h2>
                <p class="pd-section__subtitle">QA Document Hub — เอกสารสำหรับดาวน์โหลด</p>
            </div>
            <div class="pd-doc-list" id="pd-doc-list">
                <!-- Populated by JS -->
            </div>
        </div>
    </section>

    <!-- ===================== CTA ===================== -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-section__title">พร้อมเริ่มต้นเส้นทางใหม่?</h2>
            <p class="cta-section__description">สมัครเรียนวันนี้ เพื่ออนาคตที่สดใส</p>
            <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="btn btn-secondary btn-lg cta-section__btn">สมัครเรียนออนไลน์</a>
        </div>
    </section>

</div>

<!-- Page-specific CSS & JS (script loads via footer_scripts after jQuery) -->
<link rel="stylesheet" href="<?= base_url('assets/css/program-detail.css') ?>?v=<?= is_file(FCPATH . 'assets/css/program-detail.css') ? filemtime(FCPATH . 'assets/css/program-detail.css') : '1' ?>">

<?php $this->section('footer_scripts'); ?>
<script src="<?= base_url('assets/js/program-detail.js') ?>?v=<?= is_file(FCPATH . 'assets/js/program-detail.js') ? filemtime(FCPATH . 'assets/js/program-detail.js') : '1' ?>"></script>
<?php $this->endSection(); ?>

<?= $this->endSection() ?>