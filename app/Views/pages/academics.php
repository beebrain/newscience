<?= $this->extend('layouts/main_layout') ?>

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

<!-- Intro Section -->
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
            <div class="card program-card animate-on-scroll">
                <div class="card__content">
                    <div class="program-level-badge">ปริญญาตรี</div>
                    <h3 class="card__title"><?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?></h3>
                    <p class="program-name-en"><?= esc($program['name_en'] ?? '') ?></p>
                    <div class="program-degree">
                        <span class="degree-th"><?= esc($program['degree_th'] ?? 'วท.บ.') ?></span>
                    </div>
                    <?php if (!empty($program['description'])): ?>
                    <p class="card__excerpt"><?= esc(mb_substr($program['description'], 0, 120)) ?>...</p>
                    <?php endif; ?>
                    <div class="program-meta">
                        <span><strong>ระยะเวลา:</strong> <?= esc($program['duration'] ?? 4) ?> ปี</span>
                        <?php if (!empty($program['credits'])): ?>
                        <span><strong>หน่วยกิต:</strong> <?= esc($program['credits']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
            <p class="section-header__description">หลักสูตร 2 ปี สำหรับผู้สำเร็จการศึกษาระดับปริญญาตรี</p>
        </div>
        
        <div class="grid grid-2">
            <?php foreach ($master_programs as $program): ?>
            <div class="card program-card program-card--master animate-on-scroll">
                <div class="card__content">
                    <div class="program-level-badge program-level-badge--master">ปริญญาโท</div>
                    <h3 class="card__title"><?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?></h3>
                    <p class="program-name-en"><?= esc($program['name_en'] ?? '') ?></p>
                    <div class="program-degree">
                        <span class="degree-th"><?= esc($program['degree_th'] ?? 'วท.ม.') ?></span>
                    </div>
                    <?php if (!empty($program['description'])): ?>
                    <p class="card__excerpt"><?= esc($program['description']) ?></p>
                    <?php endif; ?>
                    <div class="program-meta">
                        <span><strong>ระยะเวลา:</strong> <?= esc($program['duration'] ?? 2) ?> ปี</span>
                    </div>
                </div>
            </div>
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
            <p class="section-header__description">หลักสูตร 3 ปี สำหรับผู้สำเร็จการศึกษาระดับปริญญาโท</p>
        </div>
        
        <div class="grid grid-2">
            <?php foreach ($doctorate_programs as $program): ?>
            <div class="card program-card program-card--doctorate animate-on-scroll">
                <div class="card__content">
                    <div class="program-level-badge program-level-badge--doctorate">ปริญญาเอก</div>
                    <h3 class="card__title"><?= esc($program['name_th'] ?? $program['name_en'] ?? '') ?></h3>
                    <p class="program-name-en"><?= esc($program['name_en'] ?? '') ?></p>
                    <div class="program-degree">
                        <span class="degree-th"><?= esc($program['degree_th'] ?? 'ปร.ด.') ?></span>
                    </div>
                    <?php if (!empty($program['description'])): ?>
                    <p class="card__excerpt"><?= esc($program['description']) ?></p>
                    <?php endif; ?>
                    <div class="program-meta">
                        <span><strong>ระยะเวลา:</strong> <?= esc($program['duration'] ?? 3) ?> ปี</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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

.program-card {
    border-left: 4px solid var(--color-primary);
    transition: all 0.3s ease;
}

.program-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
}

.program-card--master {
    border-left-color: var(--color-secondary);
}

.program-card--doctorate {
    border-left-color: #8b5cf6;
}

.program-level-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--color-primary);
    color: white;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.program-level-badge--master {
    background: var(--color-secondary);
}

.program-level-badge--doctorate {
    background: #8b5cf6;
}

.program-name-en {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.program-degree {
    margin: 0.75rem 0;
}

.degree-th {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: var(--color-gray-100);
    border-radius: 4px;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.program-meta {
    display: flex;
    gap: 1.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-gray-200);
    font-size: 0.85rem;
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .program-stats {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .program-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<?= $this->endSection() ?>
