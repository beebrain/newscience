<?= $this->extend($layout) ?>

<?php
// Helper function to get program image (fallback when no hero_image)
function getProgramImage($programName)
{
    $name = mb_strtolower($programName);
    $basePath = 'assets/images/programs/';

    // Exact match for Thai filenames (common pattern)
    if (strpos($name, 'คณิตศาสตร์ประยุกต์') !== false) return base_url($basePath . 'วิทยาศาสตรบัณฑิต สาขาวิชาคณิตศาสตร์ประยุกต์.jpg');
    if (strpos($name, 'วิทยาการข้อมูล') !== false) return base_url($basePath . 'วิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการข้อมูล.jpg');
    if (strpos($name, 'วิทยาการคอมพิวเตอร์') !== false) return base_url($basePath . 'วิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการคอมพิวเตอร์.jpg');
    if (strpos($name, 'อาหารและโภชนาการ') !== false) return base_url($basePath . 'วิทยาศาสตรบัณฑิต สาขาวิชาอาหารและโภชนาการ.jpg');
    if (strpos($name, 'เคมี') !== false) return base_url($basePath . 'วิทยาศาสตรบัณฑิต สาขาวิชาเคมี.jpg');
    if (strpos($name, 'เทคโนโลยีสารสนเทศ') !== false) return base_url($basePath . 'วิทยาศาสตรบัณฑิต สาขาวิชาเทคโนโลยีสารสนเทศ.jpg');
    if (strpos($name, 'สาธารณสุข') !== false) return base_url($basePath . 'สาธารณสุขศาสตรบัณฑิต สาขาวิชาสาธารณสุขศาสตร์.jpg');

    // Keyword match for English/Other filenames
    if (strpos($name, 'ชีว') !== false || strpos($name, 'biology') !== false) return base_url($basePath . 'biology.png');
    if (strpos($name, 'สิ่งแวดล้อม') !== false || strpos($name, 'env') !== false) return base_url($basePath . 'environmental_science.png');
    if (strpos($name, 'กีฬา') !== false || strpos($name, 'sport') !== false) return base_url($basePath . 'sports_science.png');
    if (strpos($name, 'ai') !== false || strpos($name, 'ปัญญา') !== false) return base_url($basePath . 'ai_data_science.png');

    // Default fallback
    return base_url($basePath . 'biology_lab.png');
}

// Card image: prefer program hero_image (from program_pages), else fallback
function getProgramCardImageUrl(array $program): string
{
    $hero = trim($program['hero_image'] ?? '');
    if ($hero !== '') {
        $path = ltrim(str_replace('\\', '/', $hero), '/');
        return base_url('serve/uploads/' . $path);
    }
    return getProgramImage($program['name_th'] ?? $program['name_en'] ?? '');
}
?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">หลักสูตร</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>หลักสูตร</span>
        </div>
    </div>
</section>

<!-- Into Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Academic Programs</span>
            <h2 class="section-header__title">หลักสูตรการศึกษา</h2>
            <p class="section-header__description">
                <?= esc($site_info['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?> เปิดสอนหลักสูตรระดับปริญญาตรี ปริญญาโท และปริญญาเอก
                รวม <?= count($bachelor_programs ?? []) + count($master_programs ?? []) + count($doctorate_programs ?? []) ?> หลักสูตร
                ครอบคลุมสาขาวิชาต่างๆ ทางวิทยาศาสตร์และเทคโนโลยี
            </p>
        </div>

        <!-- Quick Stats -->
        <div class="program-stats">
            <div class="program-stat">
                <span class="program-stat__number"><?= count($bachelor_programs ?? []) ?></span>
                <span class="program-stat__label">หลักสูตรปริญญาตรี</span>
            </div>
            <div class="program-stat">
                <span class="program-stat__number"><?= count($master_programs ?? []) ?></span>
                <span class="program-stat__label">หลักสูตรปริญญาโท</span>
            </div>
            <div class="program-stat">
                <span class="program-stat__number"><?= count($doctorate_programs ?? []) ?></span>
                <span class="program-stat__label">หลักสูตรปริญญาเอก</span>
            </div>
        </div>
    </div>
</section>

<!-- Bachelor Programs Section -->
<?php if (!empty($bachelor_programs)): ?>
    <section class="section section-light" id="bachelor">
        <div class="container">
            <div class="section-header">
                <span class="section-header__subtitle">ระดับปริญญาตรี</span>
                <h2 class="section-header__title">หลักสูตรปริญญาตรี</h2>
                <p class="section-header__description">หลักสูตร 4 ปี สำหรับผู้สำเร็จการศึกษาระดับมัธยมศึกษาตอนปลายหรือเทียบเท่า</p>
            </div>

            <div class="grid grid-3">
                <?php foreach ($bachelor_programs as $program): ?>
                    <a href="<?= base_url('program/' . $program['id']) ?>" class="card program-card animate-on-scroll" aria-label="<?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?>">
                        <div class="card__image-wrapper">
                            <img src="<?= esc(getProgramCardImageUrl($program)) ?>" alt="<?= esc($program['name_th'] ?? '') ?>" class="card__image">
                        </div>
                        <div class="card__content">
                            <div class="program-degree-badge">
                                <?= esc($program['degree_th'] ?? 'วท.บ.') ?>
                            </div>
                            <h3 class="card__title"><?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?></h3>
                            <?php if (!empty($program['description'])): ?>
                                <p class="card__excerpt"><?= esc(mb_substr($program['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>

                            <div class="program-meta">
                                <span><i class="fas fa-clock"></i> <?= esc($program['duration'] ?? 4) ?> ปี</span>
                                <?php if (!empty($program['credits'])): ?>
                                    <span><i class="fas fa-book"></i> <?= esc($program['credits']) ?> หน่วยกิต</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($program['website'])): ?>
                                <a href="<?= esc($program['website']) ?>" target="_blank" class="program-link">
                                    เยี่ยมชมเว็บไซต์ <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Master Programs Section -->
<?php if (!empty($master_programs)): ?>
    <section class="section" id="master">
        <div class="container">
            <div class="section-header">
                <span class="section-header__subtitle">ระดับปริญญาโท</span>
                <h2 class="section-header__title">หลักสูตรปริญญาโท</h2>
            </div>

            <div class="grid grid-2">
                <?php foreach ($master_programs as $program): ?>
                    <a href="<?= base_url('program/' . $program['id']) ?>" class="card program-card program-card--master animate-on-scroll" aria-label="<?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?>">
                        <div class="card__image-wrapper">
                            <img src="<?= esc(getProgramCardImageUrl($program)) ?>" alt="<?= esc($program['name_th'] ?? '') ?>" class="card__image">
                        </div>
                        <div class="card__content">
                            <div class="program-degree-badge program-degree-badge--master">
                                <?= esc($program['degree_th'] ?? 'วท.ม.') ?>
                            </div>
                            <h3 class="card__title"><?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?></h3>
                            <?php if (!empty($program['description'])): ?>
                                <p class="card__excerpt"><?= esc(mb_substr($program['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>

                            <div class="program-meta">
                                <span><i class="fas fa-clock"></i> <?= esc($program['duration'] ?? 2) ?> ปี</span>
                                <?php if (!empty($program['credits'])): ?>
                                    <span><i class="fas fa-book"></i> <?= esc($program['credits']) ?> หน่วยกิต</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($program['website'])): ?>
                                <a href="<?= esc($program['website']) ?>" target="_blank" class="program-link">
                                    เยี่ยมชมเว็บไซต์ <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Doctorate Programs Section -->
<?php if (!empty($doctorate_programs)): ?>
    <section class="section section-light" id="doctorate">
        <div class="container">
            <div class="section-header">
                <span class="section-header__subtitle">ระดับปริญญาเอก</span>
                <h2 class="section-header__title">หลักสูตรปริญญาเอก</h2>
            </div>

            <div class="grid grid-2">
                <?php foreach ($doctorate_programs as $program): ?>
                    <a href="<?= base_url('program/' . $program['id']) ?>" class="card program-card program-card--doctorate animate-on-scroll" aria-label="<?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?>">
                        <div class="card__image-wrapper">
                            <img src="<?= esc(getProgramCardImageUrl($program)) ?>" alt="<?= esc($program['name_th'] ?? '') ?>" class="card__image">
                        </div>
                        <div class="card__content">
                            <div class="program-degree-badge program-degree-badge--doctorate">
                                <?= esc($program['degree_th'] ?? 'ปร.ด.') ?>
                            </div>
                            <h3 class="card__title"><?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?></h3>
                            <?php if (!empty($program['description'])): ?>
                                <p class="card__excerpt"><?= esc(mb_substr($program['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>

                            <div class="program-meta">
                                <span><i class="fas fa-clock"></i> <?= esc($program['duration'] ?? 3) ?> ปี</span>
                                <?php if (!empty($program['credits'])): ?>
                                    <span><i class="fas fa-book"></i> <?= esc($program['credits']) ?> หน่วยกิต</span>
                                <?php endif; ?>
                            </div>
                    </a>
                    <?php if (!empty($program['website'])): ?>
                        <a href="<?= esc($program['website']) ?>" target="_blank" class="program-link">
                            เยี่ยมชมเว็บไซต์ <i class="fas fa-external-link-alt"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        </div>
    </section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">พร้อมสมัครเรียนหรือยัง?</h2>
        <p class="cta-section__description">
            เริ่มต้นเส้นทางการศึกษาของคุณกับ <?= esc($site_info['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?>
        </p>
        <a href="<?= base_url('admission') ?>" class="btn btn-secondary btn-lg">สมัครเรียน</a>
    </div>
</section>

<style>
    /* Stats */
    .program-stats {
        display: flex;
        justify-content: center;
        gap: 3rem;
        margin-top: 2rem;
        padding: 2rem;
        background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary));
        border-radius: 16px;
        color: white;
    }

    .program-stat {
        text-align: center;
    }

    .program-stat__number {
        display: block;
        font-size: 3rem;
        font-weight: 700;
        line-height: 1;
    }

    .program-stat__label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-top: 0.5rem;
    }

    /* New Card Design */
    .program-card {
        border-radius: 16px;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .program-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    }

    .card__image-wrapper {
        width: 100%;
        height: 180px;
        overflow: hidden;
    }

    .card__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .program-card:hover .card__image {
        transform: scale(1.1);
    }

    .card__content {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .program-degree-badge {
        display: inline-block;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 6px;
        font-weight: 600;
        margin-bottom: 0.75rem;
        align-self: flex-start;
    }

    .program-degree-badge--master {
        background: #DCFCE7;
        /* Light Green */
        color: #166534;
    }

    .program-degree-badge--doctorate {
        background: #F3E8FF;
        /* Light Purple */
        color: #6B21A8;
    }

    .program-card .card__title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
        min-height: 3rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .program-card .card__excerpt {
        font-size: 0.85rem;
        color: var(--color-gray-600);
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .program-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--color-gray-200);
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .program-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .program-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.75rem;
        background-color: var(--color-gray-100);
        color: var(--color-primary);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .program-link:hover {
        background-color: var(--color-primary);
        color: white;
        text-decoration: none;
    }

    /* Page Header Override */
    .page-header {
        background: linear-gradient(135deg, var(--color-dark) 0%, var(--color-gray-900) 100%);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        background: url('<?= base_url('assets/images/hero_background.png') ?>') center/cover;
        opacity: 0.15;
        border-radius: 0;
        transform: none;
    }

    @media (max-width: 768px) {
        .program-stats {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>

<?= $this->endSection() ?>