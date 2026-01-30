<?= $this->extend($layout) ?>

<?php
// Helper function to get program icon
function getProgramIcon($programName)
{
    $name = mb_strtolower($programName);

    // Map program names to icons
    if (strpos($name, 'คณิต') !== false || strpos($name, 'math') !== false) {
        // Mathematics icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M8 8h8M8 12h8M8 16h8"/></svg>';
    } elseif (strpos($name, 'เทคโนโลยีสารสนเทศ') !== false || strpos($name, 'information technology') !== false || strpos($name, 'it') !== false) {
        // IT icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
    } elseif (strpos($name, 'คอมพิวเตอร์') !== false || strpos($name, 'computer') !== false) {
        // Computer Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="12" rx="2" ry="2"/><line x1="6" y1="8" x2="6.01" y2="8"/><line x1="10" y1="8" x2="10.01" y2="8"/><line x1="14" y1="8" x2="14.01" y2="8"/><line x1="18" y1="8" x2="18.01" y2="8"/></svg>';
    } elseif (strpos($name, 'ชีว') !== false || strpos($name, 'biology') !== false || strpos($name, 'bio') !== false) {
        // Biology icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>';
    } elseif (strpos($name, 'เคมี') !== false || strpos($name, 'chemistry') !== false || strpos($name, 'chem') !== false) {
        // Chemistry icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6M15 2v6M12 17v5M5 8h14M6 12h12M7 16h10"/><circle cx="12" cy="12" r="3"/></svg>';
    } elseif (strpos($name, 'สิ่งแวดล้อม') !== false || strpos($name, 'environment') !== false) {
        // Environmental Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    } elseif (strpos($name, 'ข้อมูล') !== false || strpos($name, 'data') !== false) {
        // Data Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>';
    } elseif (strpos($name, 'กีฬา') !== false || strpos($name, 'sport') !== false || strpos($name, 'exercise') !== false) {
        // Sports Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>';
    } elseif (strpos($name, 'ประยุกต์') !== false || strpos($name, 'applied') !== false) {
        // Applied Science icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>';
    } elseif (strpos($name, 'วิศวกรรม') !== false || strpos($name, 'engineering') !== false) {
        // Engineering icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/></svg>';
    } elseif (strpos($name, 'ปัญญา') !== false || strpos($name, 'artificial') !== false || strpos($name, 'ai') !== false) {
        // AI icon
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><line x1="12" y1="2" x2="12" y2="22"/></svg>';
    } else {
        // Default icon (graduation cap)
        return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>';
    }
}
?>

<?= $this->section('content') ?>

<?php
// Get settings with defaults
$siteName = $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี';
$siteNameEn = $settings['site_name_en'] ?? 'Faculty of Science and Technology';
$university = $settings['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์';
$universityEn = $settings['university_name_en'] ?? 'Uttaradit Rajabhat University';
$heroTitle = $settings['hero_title_th'] ?? 'ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี';
$heroSubtitle = $settings['hero_subtitle_th'] ?? $university;
$heroDesc = $settings['hero_description_th'] ?? 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น';
?>

<!-- Hero Carousel Section -->
<section class="hero-carousel">
    <?php
    // Hero slides - can be managed from admin or hardcoded
    $heroSlides = $hero_slides ?? [
        [
            'image' => base_url('assets/images/hero_background.png'),
            'title' => $heroTitle,
            'subtitle' => $universityEn,
            'description' => $heroDesc,
            'show_buttons' => true,
        ],
        [
            'image' => base_url('assets/images/research_laboratory.png'),
            'title' => 'งานวิจัยและนวัตกรรม',
            'subtitle' => 'Research & Innovation',
            'description' => 'พัฒนางานวิจัยเพื่อตอบโจทย์ชุมชนและท้องถิ่น ส่งเสริมการสร้างนวัตกรรมที่มีคุณค่า',
            'link' => base_url('research'),
        ],
        [
            'image' => base_url('assets/images/student_activities.png'),
            'title' => 'กิจกรรมนักศึกษา',
            'subtitle' => 'Student Activities',
            'description' => 'ร่วมสร้างประสบการณ์การเรียนรู้นอกห้องเรียน พัฒนาทักษะและความสามารถรอบด้าน',
            'link' => base_url('campus-life'),
        ],
    ];

    // If no slides from database, use default
    if (empty($heroSlides)) {
        $heroSlides = [[
            'image' => base_url('assets/images/hero_background.png'),
            'title' => $heroTitle,
            'subtitle' => $universityEn,
            'description' => $heroDesc,
            'show_buttons' => true,
        ]];
    }
    ?>

    <div class="hero-carousel__container" id="heroCarousel">
        <div class="hero-carousel__slides">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <div class="hero-carousel__slide <?= $index === 0 ? 'active' : '' ?>"
                    style="background-image: url('<?= esc($slide['image'] ?? '') ?>');">
                    <div class="hero-carousel__overlay"></div>
                    <div class="container">
                        <div class="hero__content">
                            <?php if (!empty($slide['subtitle'])): ?>
                                <span class="hero__subtitle"><?= esc($slide['subtitle']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($slide['title'])): ?>
                                <h1 class="hero__title"><?= esc($slide['title']) ?></h1>
                            <?php endif; ?>
                            <?php if (!empty($slide['description'])): ?>
                                <p class="hero__description"><?= esc($slide['description']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($slide['show_buttons'])): ?>
                                <div class="hero__actions">
                                    <a href="<?= base_url('about') ?>" class="btn btn-primary btn-lg">เกี่ยวกับเรา</a>
                                    <a href="<?= base_url('admission') ?>" class="btn btn-outline btn-lg">สมัครเรียน</a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($slide['link'])): ?>
                                <div class="hero__actions">
                                    <a href="<?= esc($slide['link']) ?>" class="btn btn-primary btn-lg">ดูรายละเอียด</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($heroSlides) > 1): ?>
            <!-- Navigation Arrows -->
            <button class="hero-carousel__nav hero-carousel__nav--prev" onclick="heroCarouselNav(-1)" aria-label="Previous slide">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="hero-carousel__nav hero-carousel__nav--next" onclick="heroCarouselNav(1)" aria-label="Next slide">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <!-- Dots Indicator -->
            <div class="hero-carousel__dots">
                <?php foreach ($heroSlides as $index => $slide): ?>
                    <button class="hero-carousel__dot <?= $index === 0 ? 'active' : '' ?>"
                        onclick="heroCarouselGoto(<?= $index ?>)"
                        aria-label="Go to slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Service Grid Section (Database & Journals) -->
<section class="section section-blue-light" style="padding: var(--spacing-6) 0;">
    <div class="container">
        <div class="section-header" style="margin-bottom: var(--spacing-4);">
            <h2 class="section-header__title" style="font-size: 1.5rem;">ระบบฐานข้อมูลและวารสาร</h2>
            <p class="section-header__description" style="font-size: 0.9rem; margin-top: 0.5rem;">เข้าถึงระบบสารสนเทศและวารสารวิชาการได้อย่างรวดเร็ว</p>
        </div>

        <div class="service-grid animate-on-scroll">
            <!-- Systems -->
            <a href="http://edoc.sci.uru.ac.th/" target="_blank" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">งานวิชาการ</h3>
                    <span class="service-card__desc">e-Doc System</span>
                </div>
            </a>

            <a href="http://sci.uru.ac.th/scienceadmin" target="_blank" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ฐานข้อมูลบริหาร</h3>
                    <span class="service-card__desc">Management DB</span>
                </div>
            </a>

            <a href="https://advisor.uru.ac.th" target="_blank" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">อาจารย์ที่ปรึกษา</h3>
                    <span class="service-card__desc">Advisor System</span>
                </div>
            </a>

            <a href="https://workload.uru.ac.th/" target="_blank" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ภาระงาน</h3>
                    <span class="service-card__desc">Workload</span>
                </div>
            </a>

            <a href="https://sci.uru.ac.th/docs/qa2568.pdf" target="_blank" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ประกันคุณภาพ</h3>
                    <span class="service-card__desc">QA System</span>
                </div>
            </a>

            <!-- Journals -->
            <a href="https://ph03.tci-thaijo.org/index.php/ajsas" target="_blank" class="service-card service-card--journal">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">วารสารวิทยาศาสตร์ฯ</h3>
                    <span class="service-card__desc">AJSAS Journal</span>
                </div>
            </a>

            <a href="http://www.rmj.uru.ac.th/" target="_blank" class="service-card service-card--journal">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">วารสารคณิตศาสตร์</h3>
                    <span class="service-card__desc">RMS Journal</span>
                </div>
            </a>

            <a href="https://sci.uru.ac.th/academic" target="_blank" class="service-card">
                <div class="service-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 0 1 .665 6.479A11.952 11.952 0 0 0 12 20.055a11.952 11.952 0 0 0-6.824-2.998 12.078 12.078 0 0 1 .665-6.479L12 14z"></path>
                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path d="M12 14v6"></path>
                    </svg>
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">ตำแหน่งทางวิชาการ</h3>
                    <span class="service-card__desc">Academic Rank</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Research Grants & Scholarships Section -->
<section class="news-section section-blue-light">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวทุนวิจัยและทุนการศึกษา</h2>
            <a href="<?= base_url('news') ?>" class="news-section__link">
                ดูทั้งหมด
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <div class="featured-news" data-category="research_grant" data-limit="6">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดข่าว...</p>
            </div>
        </div>
    </div>
</section>

<!-- Student Activities Section -->
<section class="news-section section-cream">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวกิจกรรมนักศึกษา</h2>
            <a href="<?= base_url('news') ?>" class="news-section__link">
                ดูทั้งหมด
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <div class="featured-news" data-category="student_activity" data-limit="6">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดข่าว...</p>
            </div>
        </div>
    </div>
</section>

<style>
    /* Hero Carousel Styles */
    .hero-carousel {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .hero-carousel__container {
        position: relative;
        width: 100%;
    }

    .hero-carousel__slides {
        position: relative;
        width: 100%;
        height: 70vh;
        min-height: 500px;
        max-height: 800px;
    }

    .hero-carousel__slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.8s ease, visibility 0.8s ease;
        display: flex;
        align-items: center;
    }

    .hero-carousel__slide.active {
        opacity: 1;
        visibility: visible;
    }

    .hero-carousel__overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(30, 58, 95, 0.25) 0%, rgba(45, 90, 135, 0.15) 100%);
    }

    .hero-carousel__slide .container {
        position: relative;
        z-index: 2;
    }

    .hero-carousel__slide .hero__content {
        max-width: 700px;
    }

    .hero-carousel__slide .hero__subtitle {
        display: inline-block;
        padding: 0.5rem 1.2rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 50px;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        color: #1e3a5f;
    }

    .hero-carousel__slide .hero__title {
        display: inline-block;
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 1rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        color: #1e3a5f;
    }

    .hero-carousel__slide .hero__description {
        display: inline-block;
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 2rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 0.8rem 1.2rem;
        border-radius: 10px;
        color: #374151;
    }

    /* Navigation Arrows */
    .hero-carousel__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        z-index: 10;
    }

    .hero-carousel__nav:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: translateY(-50%) scale(1.1);
    }

    .hero-carousel__nav--prev {
        left: 20px;
    }

    .hero-carousel__nav--next {
        right: 20px;
    }

    /* Dots Indicator */
    .hero-carousel__dots {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 12px;
        z-index: 10;
    }

    .hero-carousel__dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.6);
        background: transparent;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .hero-carousel__dot:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .hero-carousel__dot.active {
        background: white;
        border-color: white;
        transform: scale(1.2);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .hero-carousel__slides {
            height: 60vh;
            min-height: 400px;
        }

        .hero-carousel__nav {
            width: 40px;
            height: 40px;
        }

        .hero-carousel__nav--prev {
            left: 10px;
        }

        .hero-carousel__nav--next {
            right: 10px;
        }

        .hero-carousel__dots {
            bottom: 20px;
        }

        .hero-carousel__dot {
            width: 10px;
            height: 10px;
        }
    }
</style>

<script>
    // Hero Carousel JavaScript
    (function() {
        let currentSlide = 0;
        let autoplayInterval;
        const autoplayDelay = 6000; // 6 seconds

        function getSlides() {
            return document.querySelectorAll('.hero-carousel__slide');
        }

        function getDots() {
            return document.querySelectorAll('.hero-carousel__dot');
        }

        window.heroCarouselGoto = function(index) {
            const slides = getSlides();
            const dots = getDots();

            if (slides.length === 0) return;

            // Wrap around
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            // Update slides
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });

            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            currentSlide = index;
            resetAutoplay();
        };

        window.heroCarouselNav = function(direction) {
            heroCarouselGoto(currentSlide + direction);
        };

        function resetAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
            }
            autoplayInterval = setInterval(() => {
                heroCarouselNav(1);
            }, autoplayDelay);
        }

        // Touch/Swipe support
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('heroCarousel');
            if (!carousel) return;

            let touchStartX = 0;
            let touchEndX = 0;

            carousel.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, {
                passive: true
            });

            carousel.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > 50) { // Minimum swipe distance
                    if (diff > 0) {
                        heroCarouselNav(1); // Swipe left = next
                    } else {
                        heroCarouselNav(-1); // Swipe right = prev
                    }
                }
            }, {
                passive: true
            });

            // Start autoplay
            resetAutoplay();

            // Pause on hover
            carousel.addEventListener('mouseenter', () => {
                if (autoplayInterval) clearInterval(autoplayInterval);
            });

            carousel.addEventListener('mouseleave', () => {
                resetAutoplay();
            });
        });
    })();
</script>

<!-- Campus News Section -->
<section class="news-section section-white-pattern">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวประชาสัมพันธ์</h2>
            <a href="<?= base_url('news') ?>" class="news-section__link">
                ดูข่าวทั้งหมด
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <div class="featured-news" data-category="general" data-limit="6">
            <div class="news-loading" style="text-align: center; padding: 2rem;">
                <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1e3a5f; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">กำลังโหลดข่าว...</p>
            </div>
        </div>
    </div>
</section>



<!-- Programs Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">หลักสูตร</span>
            <h2 class="section-header__title">หลักสูตรที่เปิดสอน</h2>
            <div class="degree-badges">
                <span class="degree-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    ป.ตรี
                </span>
                <span class="degree-badge degree-badge--master">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    ป.โท
                </span>
                <span class="degree-badge degree-badge--doctorate">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    ป.เอก
                </span>
            </div>
        </div>

        <?php if (!empty($bachelor_programs)): ?>
            <h3 class="text-center mb-4" style="font-size: 1.1rem; color: var(--primary);">ระดับปริญญาตรี</h3>

            <!-- Programs Carousel -->
            <div class="programs-carousel-wrapper">
                <div class="programs-carousel" id="bachelorProgramsCarousel">
                    <?php foreach ($bachelor_programs as $program): ?>
                        <?php
                        // Program link mapping
                        $programLinks = [
                            'คณิตศาสตร์' => 'https://sci.uru.ac.th/doctopic/237',
                            'ชีววิทยา' => 'https://sci.uru.ac.th/doctopic/236',
                            'เคมี' => 'https://sci.uru.ac.th/doctopic/235',
                            'เทคโนโลยีสารสนเทศ' => 'https://sci.uru.ac.th/doctopic/234',
                            'วิยาการคอมพิวเตอร์' => 'https://sci.uru.ac.th/doctopic/233', // Note: Check typo in key if needed, or use 'คอมพิวเตอร์'
                            'คอมพิวเตอร์' => 'https://sci.uru.ac.th/doctopic/233',
                            'วิทยาการข้อมูล' => 'https://sci.uru.ac.th/doctopic/232',
                            'กีฬา' => 'https://sci.uru.ac.th/doctopic/231',
                            'สิ่งแวดล้อม' => 'https://sci.uru.ac.th/doctopic/230',
                            'สาธารณสุข' => 'https://sci.uru.ac.th/doctopic/229',
                            'อาหาร' => 'https://sci.uru.ac.th/doctopic/228',
                        ];

                        $programLink = '#'; // Default
                        foreach ($programLinks as $keyword => $link) {
                            if (mb_strpos($program['name_th'], $keyword) !== false) {
                                $programLink = $link;
                                break;
                            }
                        }
                        ?>
                        <a href="<?= htmlspecialchars($programLink) ?>" target="_blank" class="program-carousel-card" style="text-decoration: none; color: inherit;">
                            <?php
                            // Program image mapping (Thai name keywords -> short English filename)
                            $programImageMap = [
                                'ชีววิทยา' => 'biology.png',
                                'ชีว' => 'biology.png',
                                'เคมี' => 'วิทยาศาสตรบัณฑิต สาขาวิชาเคมี.jpg',
                                'คณิตศาสตร์' => 'วิทยาศาสตรบัณฑิต สาขาวิชาคณิตศาสตร์ประยุกต์.jpg',
                                'คอมพิวเตอร์' => 'วิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการคอมพิวเตอร์.jpg',
                                'เทคโนโลยีสารสนเทศ' => 'วิทยาศาสตรบัณฑิต สาขาวิชาเทคโนโลยีสารสนเทศ.jpg',
                                'วิทยาการข้อมูล' => 'วิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการข้อมูล.jpg',
                                'ข้อมูล' => 'ai_data_science.png',
                                'สิ่งแวดล้อม' => 'environmental_science.png',
                                'กีฬา' => 'sports_science.png',
                                'สาธารณสุข' => 'สาธารณสุขศาสตรบัณฑิต สาขาวิชาสาธารณสุขศาสตร์.jpg',
                                'อาหาร' => 'วิทยาศาสตรบัณฑิต สาขาวิชาอาหารและโภชนาการ.jpg',
                                'โภชนาการ' => 'วิทยาศาสตรบัณฑิต สาขาวิชาอาหารและโภชนาการ.jpg',
                                'ปัญญาประดิษฐ์' => 'ai_data_science.png',
                            ];

                            $programImage = $program['image'] ?? '';
                            if (empty($programImage) && !empty($program['name_th'])) {
                                $foundImage = false;
                                foreach ($programImageMap as $keyword => $filename) {
                                    if (mb_strpos($program['name_th'], $keyword) !== false) {
                                        $programImage = base_url('assets/images/programs/' . $filename);
                                        $foundImage = true;
                                        break;
                                    }
                                }
                                if (!$foundImage) {
                                    $programImage = base_url('assets/images/programs/biology.png');
                                }
                            }
                            if (empty($programImage)) {
                                $programImage = base_url('assets/images/programs/biology.png');
                            }
                            ?>
                            <div class="program-carousel-card__image-wrapper">
                                <img src="<?= esc($programImage) ?>"
                                    alt="<?= esc($program['name_th']) ?>"
                                    class="program-carousel-card__image"
                                    onerror="this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=600&fit=crop'">
                                <div class="program-carousel-card__overlay">
                                    <div class="program-icon-square program-icon-square--carousel">
                                        <?= getProgramIcon($program['name_th'] ?? $program['name_en'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="program-carousel-card__content">
                                <span class="program-carousel-card__degree"><?= esc($program['degree_th'] ?? 'วท.บ.') ?></span>
                                <h4 class="program-carousel-card__name"><?= esc($program['name_th']) ?></h4>
                                <?php if (!empty($program['description'])): ?>
                                    <p class="program-carousel-card__description">
                                        <?= esc(mb_substr($program['description'], 0, 100)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Navigation -->
                <button class="carousel-nav carousel-nav--prev" onclick="scrollCarousel('bachelorProgramsCarousel', -1)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="carousel-nav carousel-nav--next" onclick="scrollCarousel('bachelorProgramsCarousel', 1)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <?php if (count($bachelor_programs) > 6): ?>
                <div class="text-center mt-4">
                    <a href="<?= base_url('academics') ?>" class="btn btn-outline">ดูหลักสูตรทั้งหมด</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($master_programs) || !empty($doctorate_programs)): ?>
            <h3 class="text-center mt-6 mb-4" style="font-size: 1.1rem; color: var(--primary);">ระดับบัณฑิตศึกษา</h3>

            <!-- Graduate Programs Carousel -->
            <div class="programs-carousel-wrapper">
                <div class="programs-carousel" id="graduateProgramsCarousel">
                    <?php foreach ($master_programs as $program): ?>
                        <div class="program-carousel-card program-carousel-card--master">
                            <?php
                            // Reuse program image mapping
                            $programImage = $program['image'] ?? '';
                            if (empty($programImage) && !empty($program['name_th'])) {
                                $foundImage = false;
                                foreach ($programImageMap as $keyword => $filename) {
                                    if (mb_strpos($program['name_th'], $keyword) !== false) {
                                        $programImage = base_url('assets/images/programs/' . $filename);
                                        $foundImage = true;
                                        break;
                                    }
                                }
                                if (!$foundImage) {
                                    $programImage = base_url('assets/images/programs/biology.png');
                                }
                            }
                            if (empty($programImage)) {
                                $programImage = base_url('assets/images/programs/biology.png');
                            }
                            ?>
                            <div class="program-carousel-card__image-wrapper">
                                <img src="<?= esc($programImage) ?>"
                                    alt="<?= esc($program['name_th']) ?>"
                                    class="program-carousel-card__image"
                                    onerror="this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=600&fit=crop'">
                                <div class="program-carousel-card__overlay">
                                    <div class="program-icon-square program-icon-square--carousel program-icon-square--master">
                                        <?= getProgramIcon($program['name_th'] ?? $program['name_en'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="program-carousel-card__content">
                                <span class="program-carousel-card__degree"><?= esc($program['degree_th'] ?? 'วท.ม.') ?></span>
                                <h4 class="program-carousel-card__name"><?= esc($program['name_th']) ?></h4>
                                <?php if (!empty($program['description'])): ?>
                                    <p class="program-carousel-card__description">
                                        <?= esc(mb_substr($program['description'], 0, 100)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($doctorate_programs as $program): ?>
                        <div class="program-carousel-card program-carousel-card--doctorate">
                            <?php
                            // Reuse program image mapping
                            $programImage = $program['image'] ?? '';
                            if (empty($programImage) && !empty($program['name_th'])) {
                                $foundImage = false;
                                foreach ($programImageMap as $keyword => $filename) {
                                    if (mb_strpos($program['name_th'], $keyword) !== false) {
                                        $programImage = base_url('assets/images/programs/' . $filename);
                                        $foundImage = true;
                                        break;
                                    }
                                }
                                if (!$foundImage) {
                                    $programImage = base_url('assets/images/programs/biology.png');
                                }
                            }
                            if (empty($programImage)) {
                                $programImage = base_url('assets/images/programs/biology.png');
                            }
                            ?>
                            <div class="program-carousel-card__image-wrapper">
                                <img src="<?= esc($programImage) ?>"
                                    alt="<?= esc($program['name_th']) ?>"
                                    class="program-carousel-card__image"
                                    onerror="this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=600&fit=crop'">
                                <div class="program-carousel-card__overlay">
                                    <div class="program-icon-square program-icon-square--carousel program-icon-square--doctorate">
                                        <?= getProgramIcon($program['name_th'] ?? $program['name_en'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="program-carousel-card__content">
                                <span class="program-carousel-card__degree"><?= esc($program['degree_th'] ?? 'ปร.ด.') ?></span>
                                <h4 class="program-carousel-card__name"><?= esc($program['name_th']) ?></h4>
                                <?php if (!empty($program['description'])): ?>
                                    <p class="program-carousel-card__description">
                                        <?= esc(mb_substr($program['description'], 0, 100)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Navigation -->
                <button class="carousel-nav carousel-nav--prev" onclick="scrollCarousel('graduateProgramsCarousel', -1)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="carousel-nav carousel-nav--next" onclick="scrollCarousel('graduateProgramsCarousel', 1)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>




<style>
    /* Support Staff & Internal Documents Section Styles */
    .support-staff-section,
    .internal-docs-section {
        background: #fff;
        padding: 0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .support-staff-section:hover,
    .internal-docs-section:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .support-staff-list,
    .internal-docs-list {
        list-style: none;
        padding: 1.5rem;
        margin: 0;
    }

    .support-staff-list__item,
    .internal-docs-list__item {
        margin-bottom: 0.75rem;
    }

    .support-staff-list__item:last-child,
    .internal-docs-list__item:last-child {
        margin-bottom: 0;
    }

    .support-staff-list__link,
    .internal-docs-list__link {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        padding: 1rem;
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-color);
        background: var(--color-gray-200);
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .support-staff-list__link:hover,
    .internal-docs-list__link:hover {
        background: var(--color-gray-100);
        border-color: var(--color-primary);
        color: var(--color-primary);
        transform: translateX(4px);
    }

    .support-staff-list__bullet,
    .internal-docs-list__bullet {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: var(--color-gray-700);
        border-radius: 50%;
        margin-top: 0.5rem;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .support-staff-list__link:hover .support-staff-list__bullet,
    .internal-docs-list__link:hover .internal-docs-list__bullet {
        background: var(--color-primary);
        transform: scale(1.3);
    }

    .support-staff-list__link span:last-child,
    .internal-docs-list__link span:last-child {
        flex: 1;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }

        .support-staff-section,
        .internal-docs-section {
            margin-bottom: 2rem;
        }

        .section-header__title {
            font-size: 1.25rem !important;
        }

        .support-staff-list,
        .internal-docs-list {
            padding: 1rem;
        }
    }
</style>

<!-- Quality Assurance & Academic Journals Section -->
<!-- <section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">งานประกันคุณภาพและวารสารวิชาการ</span>
            <h2 class="section-header__title">ระบบฐานข้อมูลและวารสาร</h2>
        </div>

        <div class="qa-compact-grid">
            <a href="#" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 12h8M12 8v8"></path>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>QA-SCIURU</h4>
                    <p>งานประกันคุณภาพ</p>
                </div>
            </a>

            <a href="https://scitech.kpru.ac.th/qa/login.php" target="_blank" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--dark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>ระบบฐานข้อมูลงานประกันคุณภาพ</h4>
                    <p>เข้าสู่ระบบ</p>
                </div>
            </a>

            <a href="#" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--dark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>ฐานข้อมูลผู้ทรงคุณวุฒิ</h4>
                    <p>เครือข่าย มรภ.</p>
                </div>
            </a>

            <a href="#" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--dark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>ฐานข้อมูลวารสารของเครือข่าย</h4>
                    <p>กลุ่ม มรภ. แห่งประเทศไทย</p>
                </div>
            </a>

            <a href="https://ph03.tci-thaijo.org/index.php/ajsas" target="_blank" class="qa-compact-item animate-on-scroll">
                <div class="qa-compact-item__icon qa-compact-item__icon--accent">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
                <div class="qa-compact-item__text">
                    <h4>AJSAS</h4>
                    <p>วารสารวิชาการวิทยาศาสตร์ฯ</p>
                </div>
            </a>
        </div>
    </div>
</section> -->

<style>
    .qa-compact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .qa-compact-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        background: white;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .qa-compact-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: var(--primary, #1e3a5f);
    }

    .qa-compact-item__icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 8px;
        color: var(--primary, #1e3a5f);
    }

    .qa-compact-item__icon--dark {
        background: #1e293b;
        color: white;
    }

    .qa-compact-item__icon--accent {
        background: #fef3c7;
        color: #d97706;
    }

    .qa-compact-item__text h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e3a5f;
        margin: 0;
        line-height: 1.3;
    }

    .qa-compact-item__text p {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0.15rem 0 0 0;
    }

    @media (max-width: 768px) {
        .qa-compact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


<!-- Executive Highlight Section -->
<section class="section section-slate-gradient">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ผู้บริหารคณะ</span>
            <h2 class="section-header__title">ทีมผู้บริหาร</h2>
        </div>

        <div class="executive-highlight">
            <!-- Dean Card -->
            <div class="dean-card animate-on-scroll">
                <div class="dean-card__image">
                    <img src="https://ui-avatars.com/api/?name=ปริญญา+ไ&background=1e3a5f&color=fff&size=200&font-size=0.4" alt="คณบดี">
                </div>
                <div class="dean-card__content">
                    <div class="dean-card__badge">คณบดี</div>
                    <h3 class="dean-card__name">ผู้ช่วยศาสตราจารย์ปริญญา ไกรวุฒินันท์</h3>
                    <p class="dean-card__title">Dean, Faculty of Science and Technology</p>
                    <p class="dean-card__message">
                        "คณะวิทยาศาสตร์และเทคโนโลยี มุ่งมั่นผลิตบัณฑิตที่มีคุณภาพ พร้อมทั้งเป็นแหล่งเรียนรู้และบริการวิชาการแก่ชุมชนท้องถิ่น"
                    </p>
                </div>
            </div>

            <!-- Vice Deans -->
            <div class="vice-deans">
                <div class="vice-dean-card animate-on-scroll">
                    <img src="https://ui-avatars.com/api/?name=จุฬาลักษณ์+ม&background=2d7d46&color=fff&size=80&font-size=0.4" alt="รองคณบดี">
                    <div class="vice-dean-card__info">
                        <span class="vice-dean-card__position">รองคณบดี</span>
                        <span class="vice-dean-card__name">ผศ.จุฬาลักษณ์ มหาวัน</span>
                    </div>
                </div>
                <div class="vice-dean-card animate-on-scroll">
                    <img src="https://ui-avatars.com/api/?name=วารุณี+จ&background=2d7d46&color=fff&size=80&font-size=0.4" alt="รองคณบดี">
                    <div class="vice-dean-card__info">
                        <span class="vice-dean-card__position">รองคณบดี</span>
                        <span class="vice-dean-card__name">ผศ.ดร.วารุณี จอมกิติชัย</span>
                    </div>
                </div>
                <div class="vice-dean-card animate-on-scroll">
                    <img src="https://ui-avatars.com/api/?name=วีระศักดิ์+แ&background=2d7d46&color=fff&size=80&font-size=0.4" alt="รองคณบดี">
                    <div class="vice-dean-card__info">
                        <span class="vice-dean-card__position">รองคณบดี</span>
                        <span class="vice-dean-card__name">อ.ดร.วีระศักดิ์ แก้วทรัพย์</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?= base_url('about') ?>" class="btn btn-outline">ดูข้อมูลเพิ่มเติม</a>
        </div>
    </div>
</section>

<!-- Research Section -->
<section class="section section-blue-light">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="<?= base_url('assets/images/research_laboratory.png') ?>" alt="Research Laboratory">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">งานวิจัย</span>
                <h2 class="feature-section__title">ขับเคลื่อนนวัตกรรมและการค้นพบ</h2>
                <p class="feature-section__description">
                    นักวิจัยของเรากำลังแก้ไขปัญหาที่ท้าทายที่สุดของโลก ตั้งแต่การเปลี่ยนแปลงสภาพภูมิอากาศ
                    ไปจนถึงเทคโนโลยีและสุขภาพ
                </p>
                <ul class="feature-list">
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>ศูนย์วิจัยระดับประเทศ</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>ความร่วมมือแบบสหวิทยาการ</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>ผลกระทบในโลกจริง</span>
                    </li>
                </ul>
                <a href="<?= base_url('research') ?>" class="btn btn-primary">ค้นพบงานวิจัย</a>
            </div>
        </div>
    </div>
</section>

<!-- Campus Life Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ชีวิตในมหาวิทยาลัย</span>
            <h2 class="section-header__title">ชุมชนที่มีชีวิตชีวา</h2>
            <p class="section-header__description">
                สัมผัสวัฒนธรรมในมหาวิทยาลัยที่หลากหลาย มีกิจกรรม องค์กร และโอกาสในการเติบโต
            </p>
        </div>

        <div class="grid grid-3">
            <article class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/student_activities.png') ?>" alt="Student Activities" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">กิจกรรมนักศึกษา</h3>
                    <p class="card__excerpt">
                        ค้นพบวิธีมากมายในการเชื่อมต่อ เติบโต และสนุกสนาน
                    </p>
                </div>
            </article>

            <article class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/wellness_recreation.png') ?>" alt="Wellness Center" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">สุขภาพและสันทนาการ</h3>
                    <p class="card__excerpt">
                        สิ่งอำนวยความสะดวกและโปรแกรมที่ทันสมัยเพื่อสนับสนุนสุขภาพของคุณ
                    </p>
                </div>
            </article>

            <article class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/community_service.png') ?>" alt="Community Events" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">บริการวิชาการ</h3>
                    <p class="card__excerpt">
                        สร้างความแตกต่างผ่านการเรียนรู้เพื่อบริการชุมชน
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>


<!-- Admission CTA Section -->
<section class="cta-section section-dark-blue">
    <div class="container">
        <h2 class="cta-section__title">เริ่มต้นเส้นทางของคุณ</h2>
        <p class="cta-section__description">
            สำรวจความเป็นไปได้ของการศึกษาที่คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์
        </p>
        <div class="flex justify-center gap-4">
            <a href="<?= base_url('admission') ?>" class="btn btn-secondary btn-lg">สมัครเรียน</a>
            <a href="<?= base_url('contact') ?>" class="btn btn-outline btn-lg">ติดต่อเรา</a>
        </div>
    </div>
</section>

<!-- Contact Info Footer -->
<section class="section section-light">
    <div class="container">
        <div class="grid grid-2" style="gap: 2rem;">
            <div>
                <h3 style="margin-bottom: 1rem;">ติดต่อเรา</h3>
                <p style="margin-bottom: 0.5rem;">
                    <strong><?= esc($siteName) ?></strong><br>
                    <?= esc($university) ?>
                </p>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    <?= esc($settings['address_th'] ?? '27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000') ?>
                </p>
            </div>
            <div>
                <h3 style="margin-bottom: 1rem;">ข้อมูลติดต่อ</h3>
                <p style="margin-bottom: 0.5rem;">
                    <strong>โทรศัพท์:</strong> <?= esc($settings['phone'] ?? '055-411096') ?>
                </p>
                <p style="margin-bottom: 0.5rem;">
                    <strong>อีเมล:</strong> <?= esc($settings['email'] ?? 'sci@uru.ac.th') ?>
                </p>
                <?php if (!empty($settings['facebook'])): ?>
                    <p>
                        <a href="<?= esc($settings['facebook']) ?>" target="_blank" class="btn btn-sm btn-outline">
                            Facebook
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
    /* Executive Highlight Section */
    .executive-highlight {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        align-items: center;
    }

    .dean-card {
        display: flex;
        align-items: center;
        gap: 2rem;
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        max-width: 700px;
        width: 100%;
    }

    .dean-card__image img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--color-primary);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .dean-card__content {
        flex: 1;
    }

    .dean-card__badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        background: linear-gradient(135deg, #1e3a5f, #2d5a87);
        color: white;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .dean-card__name {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.25rem;
    }

    .dean-card__title {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin: 0 0 1rem;
    }

    .dean-card__message {
        font-size: 0.95rem;
        color: var(--text-secondary);
        line-height: 1.6;
        font-style: italic;
        margin: 0;
        padding-left: 1rem;
        border-left: 3px solid var(--color-primary);
    }

    .vice-deans {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .vice-dean-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .vice-dean-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .vice-dean-card img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .vice-dean-card__info {
        display: flex;
        flex-direction: column;
    }

    .vice-dean-card__position {
        font-size: 0.75rem;
        color: #2d7d46;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .vice-dean-card__name {
        font-size: 0.9rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .text-center {
        text-align: center;
    }

    @media (max-width: 768px) {
        .dean-card {
            flex-direction: column;
            text-align: center;
            padding: 1.5rem;
        }

        .dean-card__message {
            border-left: none;
            border-top: 3px solid var(--color-primary);
            padding-left: 0;
            padding-top: 1rem;
        }

        .vice-deans {
            flex-direction: column;
            align-items: stretch;
        }

        .vice-dean-card {
            justify-content: flex-start;
        }
    }

    /* Degree Badges */
    .degree-badges {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .degree-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #1e3a5f, #2d5a87);
        color: white;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3);
        transition: all 0.3s ease;
    }

    .degree-badge:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(30, 58, 95, 0.4);
    }

    .degree-badge svg {
        stroke: currentColor;
    }

    .degree-badge--master {
        background: linear-gradient(135deg, #2d7d46, #3da55d);
        box-shadow: 0 4px 15px rgba(45, 125, 70, 0.3);
    }

    .degree-badge--master:hover {
        box-shadow: 0 6px 20px rgba(45, 125, 70, 0.4);
    }

    .degree-badge--doctorate {
        background: linear-gradient(135deg, #9333ea, #a855f7);
        box-shadow: 0 4px 15px rgba(147, 51, 234, 0.3);
    }

    .degree-badge--doctorate:hover {
        box-shadow: 0 6px 20px rgba(147, 51, 234, 0.4);
    }

    @media (max-width: 480px) {
        .degree-badges {
            gap: 0.75rem;
        }

        .degree-badge {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .degree-badge svg {
            width: 20px;
            height: 20px;
        }
    }

    /* Programs Carousel Styles */
    .programs-carousel-wrapper {
        position: relative;
        margin: 2rem 0;
        padding: 0 3rem;
    }

    .programs-carousel {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none;
        /* Firefox */
        -ms-overflow-style: none;
        /* IE and Edge */
        padding: 1rem 0;
    }

    .programs-carousel::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari, Opera */
    }

    .program-carousel-card {
        flex: 0 0 320px;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .program-carousel-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .program-carousel-card__image-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .program-carousel-card__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .program-carousel-card:hover .program-carousel-card__image {
        transform: scale(1.1);
    }

    .program-carousel-card__overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.6));
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .program-carousel-card:hover .program-carousel-card__overlay {
        opacity: 1;
    }

    .program-icon-square--carousel {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .program-icon-square--carousel svg {
        width: 40px;
        height: 40px;
        color: var(--color-primary);
    }

    .program-carousel-card__content {
        padding: 1.5rem;
    }

    .program-carousel-card__degree {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .program-carousel-card--master .program-carousel-card__degree {
        background: var(--color-secondary-light, #e8f5e9);
        color: var(--color-secondary-dark, #2d7d46);
    }

    .program-carousel-card--doctorate .program-carousel-card__degree {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .program-carousel-card__name {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: var(--text-color);
        line-height: 1.4;
    }

    .program-carousel-card__description {
        font-size: 0.875rem;
        color: var(--color-gray-600);
        line-height: 1.6;
        margin: 0;
    }

    .carousel-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: white;
        border: 2px solid var(--color-gray-300);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .carousel-nav:hover {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transform: translateY(-50%) scale(1.1);
    }

    .carousel-nav--prev {
        left: 0;
    }

    .carousel-nav--next {
        right: 0;
    }

    .carousel-nav svg {
        width: 24px;
        height: 24px;
    }

    @media (max-width: 768px) {
        .programs-carousel-wrapper {
            padding: 0 2.5rem;
        }

        .program-carousel-card {
            flex: 0 0 280px;
        }

        .carousel-nav {
            width: 40px;
            height: 40px;
        }

        .carousel-nav svg {
            width: 20px;
            height: 20px;
        }
    }

    /* Program Cards - Compact Style */
    .program-card {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 3px solid var(--primary, #1e3a5f);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .program-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
    }

    /* Square Icon Styles for Home Page */
    .program-icon-square {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary, #1e3a5f), #2d5a87);
        border-radius: 12px;
        margin-bottom: 0.75rem;
        color: white;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .program-card:hover .program-icon-square {
        transform: scale(1.05) rotate(5deg);
        box-shadow: 0 6px 20px rgba(30, 58, 95, 0.3);
    }

    .program-icon-square--master {
        background: linear-gradient(135deg, #2d7d46, #3da55d);
        box-shadow: 0 4px 12px rgba(45, 125, 70, 0.2);
    }

    .program-card--master:hover .program-icon-square--master {
        box-shadow: 0 6px 20px rgba(45, 125, 70, 0.3);
    }

    .program-icon-square--doctorate {
        background: linear-gradient(135deg, #8b5cf6, #9333ea);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
    }

    .program-card--doctorate:hover .program-icon-square--doctorate {
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
    }

    .program-icon-square svg {
        width: 28px;
        height: 28px;
        stroke: currentColor;
    }

    .program-card__degree {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
        background: var(--primary, #1e3a5f);
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        margin-bottom: 0.5rem;
    }

    .program-card__name {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-primary, #333);
        margin: 0;
        line-height: 1.3;
    }

    .program-card--master {
        border-left-color: #2d7d46;
    }

    .program-card--master .program-card__degree {
        background: #2d7d46;
    }

    .program-card--doctorate {
        border-left-color: #9333ea;
    }

    .program-card--doctorate .program-card__degree {
        background: #9333ea;
    }

    @media (max-width: 768px) {
        .program-icon-square {
            width: 48px;
            height: 48px;
        }

        .program-icon-square svg {
            width: 24px;
            height: 24px;
        }
    }
</style>

<script>
    // Carousel scroll function
    function scrollCarousel(carouselId, direction) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;

        const scrollAmount = 340; // Card width + gap
        const currentScroll = carousel.scrollLeft;
        const newScroll = currentScroll + (scrollAmount * direction);

        carousel.scrollTo({
            left: newScroll,
            behavior: 'smooth'
        });
    }

    // Touch/swipe support for mobile
    document.addEventListener('DOMContentLoaded', function() {
        const carousels = document.querySelectorAll('.programs-carousel');

        carousels.forEach(carousel => {
            let isDown = false;
            let startX;
            let scrollLeft;

            carousel.addEventListener('mousedown', (e) => {
                isDown = true;
                carousel.style.cursor = 'grabbing';
                startX = e.pageX - carousel.offsetLeft;
                scrollLeft = carousel.scrollLeft;
            });

            carousel.addEventListener('mouseleave', () => {
                isDown = false;
                carousel.style.cursor = 'grab';
            });

            carousel.addEventListener('mouseup', () => {
                isDown = false;
                carousel.style.cursor = 'grab';
            });

            carousel.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - carousel.offsetLeft;
                const walk = (x - startX) * 2;
                carousel.scrollLeft = scrollLeft - walk;
            });

            // Touch events for mobile
            let touchStartX = 0;
            let touchScrollLeft = 0;

            carousel.addEventListener('touchstart', (e) => {
                touchStartX = e.touches[0].pageX - carousel.offsetLeft;
                touchScrollLeft = carousel.scrollLeft;
            });

            carousel.addEventListener('touchmove', (e) => {
                const x = e.touches[0].pageX - carousel.offsetLeft;
                const walk = (x - touchStartX) * 2;
                carousel.scrollLeft = touchScrollLeft - walk;
            });
        });
    });

    // Load news sections via AJAX
    (function() {
        const baseUrl = window.BASE_URL || '<?= base_url() ?>';
        
        // Category labels
        const categoryLabels = {
            'research_grant': { main: 'ทุนวิจัย', secondary: 'ทุนการศึกษา' },
            'student_activity': { main: 'กิจกรรม', secondary: 'กิจกรรม' },
            'general': { main: 'ข่าวล่าสุด', secondary: 'ข่าว' }
        };
        
        // Format date helper (Thai format)
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = date.getDate();
            const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${day} ${month} ${year}`;
        }
        
        // Render news card (all cards are equal size now)
        function renderNewsCard(article, index = 0) {
            // Use featured_image from database, fallback only if null/empty/undefined
            let imageUrl = '';
            
            // Check if featured_image exists and is not empty
            // API returns empty string '' when no image, so we need to check explicitly
            const hasImage = article.featured_image !== null && 
                            article.featured_image !== undefined && 
                            article.featured_image !== '' &&
                            typeof article.featured_image === 'string' && 
                            article.featured_image.trim() !== '';
            
            if (hasImage) {
                // Use image from database (already formatted with base_url by API)
                imageUrl = article.featured_image;
            } else {
                // Fallback image only when no image in database
                imageUrl = 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=400&h=200&fit=crop';
            }
            
            const category = article.category || 'general';
            const categoryLabel = categoryLabels[category] || categoryLabels['general'];
            const label = categoryLabel.secondary;
            const title = article.title.length > 100 ? article.title.substring(0, 100) + '...' : article.title;
            
            return `
                <article class="card animate-on-scroll">
                    <img src="${imageUrl}" alt="${article.title}" class="card__image" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=400&h=200&fit=crop';">
                    <div class="card__content card__content--${category}">
                        <span class="card__category">${label}</span>
                        <h3 class="card__title">
                            <a href="${baseUrl}news/${article.id}">${title}</a>
                        </h3>
                        ${article.excerpt ? `<p class="card__excerpt">${article.excerpt.substring(0, 100)}...</p>` : ''}
                        <div class="card__meta">
                            <span>${formatDate(article.published_at)}</span>
                        </div>
                    </div>
                </article>
            `;
        }
        
        // Load news for a section
        function loadNewsSection(container) {
            const category = container.getAttribute('data-category');
            const limit = parseInt(container.getAttribute('data-limit')) || 6;
            
            fetch(`${baseUrl}api/news/category/${category}?limit=${limit}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data && result.data.length > 0) {
                        const news = result.data;
                        let html = '';
                        
                        // Render all 6 news items (equal size, 3 columns x 2 rows)
                        for (let i = 0; i < Math.min(6, news.length); i++) {
                            html += renderNewsCard(news[i], i);
                        }
                        
                        container.innerHTML = html;
                        
                        // Trigger animations
                        if (typeof initAnimations === 'function') {
                            initAnimations();
                        }
                    } else {
                        container.innerHTML = '<div class="text-center py-8"><p class="text-muted">ยังไม่มีข่าวในหมวดหมู่นี้</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading news:', error);
                    container.innerHTML = '<div class="text-center py-8"><p class="text-muted">เกิดข้อผิดพลาดในการโหลดข่าว</p></div>';
                });
        }
        
        // Load all news sections when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const newsSections = document.querySelectorAll('.featured-news[data-category]');
            
            // Load each section with a small delay to stagger requests
            newsSections.forEach((section, index) => {
                setTimeout(() => {
                    loadNewsSection(section);
                }, index * 200); // 200ms delay between each request
            });
        });
    })();
</script>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<?= $this->endSection() ?>