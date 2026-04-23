<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">เกี่ยวกับคณะ</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>เกี่ยวกับคณะ</span>
        </div>
    </div>
</section>

<!-- History Section -->
<?php if (!empty($history)): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ประวัติ</span>
            <h2 class="section-header__title">ประวัติคณะ</h2>
        </div>
        <?php
        $historyParagraphs = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", (string) $history))));
        ?>
        <div class="about-prose animate-on-scroll">
            <?php foreach ($historyParagraphs as $p): ?>
                <p class="about-prose__p"><?= esc($p) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Philosophy & Vision Section -->
<section class="section">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="<?= base_url('assets/images/research_laboratory.png') ?>" alt="<?= $site_info['site_name_th'] ?? 'Campus' ?>">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">ปรัชญา</span>
                <h2 class="feature-section__title"><?= esc($philosophy ?? 'สร้างองค์ความรู้และพัฒนาคนในชาติ ด้วยวิทยาศาสตร์และเทคโนโลยี') ?></h2>
            </div>
        </div>
    </div>
</section>

<!-- Vision Section -->
<section class="section section-light">
    <div class="container">
        <div class="feature-section feature-section--reverse">
            <div class="feature-section__image animate-on-scroll">
                <img src="<?= base_url('assets/images/community_service.png') ?>" alt="วิสัยทัศน์">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">วิสัยทัศน์</span>
                <h2 class="feature-section__title">วิสัยทัศน์คณะ</h2>
                <p class="feature-section__description">
                    <?php
                    $visionText = (string) ($vision ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มุ่งพัฒนาและผลิตบัณฑิตให้เป็นคนดี คนเก่ง มีจิตอาสา นำพาสังคม พร้อมทั้งเป็นแหล่งเรียนรู้และบริการวิชาการแก่ชุมชน ท้องถิ่น ระดับชาติและนานาชาติ');
                    $visionText = str_replace('องค์กรแห่งความสุข', '', $visionText);
                    $visionText = trim(preg_replace('/\s+/', ' ', $visionText) ?? $visionText);
                    ?>
                    <?= esc($visionText) ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">พันธกิจ</span>
            <h2 class="section-header__title">ภารกิจหลักของคณะ</h2>
        </div>
        <?php
        $missionItems = !empty($mission) ? array_filter(array_map('trim', explode("\n", $mission))) : [];
        ?>
        <?php if (!empty($missionItems)): ?>
            <ol class="about-list about-list--mission animate-on-scroll">
                <?php foreach ($missionItems as $item): ?>
                    <li class="about-list__item"><?= esc($item) ?></li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="section-description">ผลิตบัณฑิต วิจัยและบริการวิชาการ ทำนุบำรุงศิลปวัฒนธรรม</p>
        <?php endif; ?>
    </div>
</section>

<!-- Policy Section -->
<?php if (!empty($policy)): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">นโยบาย</span>
            <h2 class="section-header__title">นโยบายคณะ</h2>
        </div>
        <?php $policyItems = array_filter(array_map('trim', explode("\n", $policy))); ?>
        <ol class="about-list about-list--policy animate-on-scroll">
            <?php foreach ($policyItems as $item): ?>
                <li class="about-list__item"><?= esc($item) ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

<!-- Strategy Section -->
<?php if (!empty($strategy_title) || !empty($strategies)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ยุทธศาสตร์</span>
            <h2 class="section-header__title"><?= esc($strategy_title ?: 'ยุทธศาสตร์ในการบริหารคณะ') ?></h2>
        </div>
        <ul class="about-list about-list--strategy animate-on-scroll">
            <?php foreach ($strategies ?? [] as $s): ?>
                <li class="about-list__item"><?= esc($s) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- Stats Section -->
<section class="section section-primary">
    <div class="container">
        <div class="stats">
            <div class="stat animate-on-scroll">
                <div class="stat__number">๘๙</div>
                <div class="stat__label">ปีแห่งความเป็นเลิศ</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">๑๓</div>
                <div class="stat__label">หลักสูตร</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">๑๑</div>
                <div class="stat__label">สาขาวิชา</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">๒๐๐๐+</div>
                <div class="stat__label">นักศึกษา</div>
            </div>
        </div>
    </div>
</section>

<!-- Executives Section -->
<section class="section executives-section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ผู้บริหาร</span>
            <h2 class="section-header__title">ทีมผู้บริหารคณะ</h2>
            <p class="section-header__description">ผู้บริหารคณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์</p>
        </div>

        <!-- Dean -->
        <div class="dean-highlight animate-on-scroll">
            <div class="dean-highlight__image">
                <img src="https://sci.uru.ac.th/image/getpersonimage/54" alt="คณบดี" onerror="this.src='https://ui-avatars.com/api/?name=ปริญญา&background=1e3a5f&color=fff&size=200'">
            </div>
            <div class="dean-highlight__content">
                <div class="dean-highlight__badge">คณบดี</div>
                <h3 class="dean-highlight__name">ผู้ช่วยศาสตราจารย์ปริญญา ไกรวุฒินันท์</h3>
                <p class="dean-highlight__title-en">Dean, Faculty of Science and Technology</p>
            </div>
        </div>

        <!-- Vice Deans -->
        <h4 class="executives-subtitle">รองคณบดี</h4>
        <div class="executives-grid">
            <div class="executive-card animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/87" alt="รองคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=จุฬาลักษณ์&background=2d7d46&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position">รองคณบดี</div>
                    <h3 class="executive-card__name">ผู้ช่วยศาสตราจารย์จุฬาลักษณ์ มหาวัน</h3>
                </div>
            </div>

            <div class="executive-card animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/25" alt="รองคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=วารุณี&background=2d7d46&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position">รองคณบดี</div>
                    <h3 class="executive-card__name">ผู้ช่วยศาสตราจารย์ ดร.วารุณี จอมกิติชัย</h3>
                </div>
            </div>

            <div class="executive-card animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/103" alt="รองคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=วีระศักดิ์&background=2d7d46&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position">รองคณบดี</div>
                    <h3 class="executive-card__name">อาจารย์ ดร.วีระศักดิ์ แก้วทรัพย์</h3>
                </div>
            </div>
        </div>

        <!-- Assistant Deans -->
        <h4 class="executives-subtitle">ผู้ช่วยคณบดี</h4>
        <div class="executives-grid executives-grid--4">
            <div class="executive-card executive-card--small animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/55" alt="ผู้ช่วยคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=พิศิษฐ์&background=f59e0b&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position executive-card__position--assistant">ผู้ช่วยคณบดี</div>
                    <h3 class="executive-card__name">ผศ.ดร.พิศิษฐ์ นาคใจ</h3>
                </div>
            </div>

            <div class="executive-card executive-card--small animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/69" alt="ผู้ช่วยคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=ธนากร&background=f59e0b&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position executive-card__position--assistant">ผู้ช่วยคณบดี</div>
                    <h3 class="executive-card__name">ผศ.ดร.ธนากร ธนวัฒน์</h3>
                </div>
            </div>

            <div class="executive-card executive-card--small animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/31" alt="ผู้ช่วยคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=อัมพวัน&background=f59e0b&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position executive-card__position--assistant">ผู้ช่วยคณบดี</div>
                    <h3 class="executive-card__name">ผศ.ดร.อัมพวัน วิริยะรัตนกุล</h3>
                </div>
            </div>

            <div class="executive-card executive-card--small animate-on-scroll">
                <div class="executive-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/18" alt="หัวหน้าหน่วยจัดการงานวิจัย" onerror="this.src='https://ui-avatars.com/api/?name=สุทธิดา&background=8b5cf6&color=fff&size=200'">
                </div>
                <div class="executive-card__content">
                    <div class="executive-card__position executive-card__position--research">หัวหน้าหน่วยจัดการงานวิจัย</div>
                    <h3 class="executive-card__name">ผศ.ดร.สุทธิดา วิทนาลัย</h3>
                </div>
            </div>
        </div>

        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?= base_url('personnel') ?>" class="btn btn-outline">ดูบุคลากรทั้งหมด</a>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">ติดต่อคณะ</h2>
        <p class="cta-section__description">
            <?= esc($site_info['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?> <?= esc($site_info['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?>
        </p>
        <a href="<?= base_url('contact') ?>" class="btn btn-secondary btn-lg">ติดต่อเรา</a>
    </div>
</section>

<style>
    .mission-number {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        border-radius: 12px;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .dept-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary));
        border-radius: 50%;
        margin: 0 auto 1rem;
        color: white;
    }

    .text-muted {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .about-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem;
        max-width: 800px;
    }
    .about-list--mission { list-style: decimal; padding-left: 1.5rem; }
    .about-list--policy { list-style: decimal; padding-left: 1.5rem; }
    .about-list--strategy { list-style: disc; padding-left: 1.5rem; }
    .about-list__item {
        padding: 0.5rem 0;
        line-height: 1.6;
        color: var(--text-primary, #1e293b);
    }
    .about-list--mission .about-list__item,
    .about-list--policy .about-list__item { margin-bottom: 0.5rem; }

    .about-prose {
        max-width: 900px;
        margin: 0 auto;
        color: var(--text-primary, #1e293b);
        line-height: 1.8;
    }
    .about-prose__p {
        margin: 0 0 0.9rem;
        color: inherit;
    }
    .about-prose__p:last-child { margin-bottom: 0; }

    /* Executives Section */
    .executives-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .executives-subtitle {
        text-align: center;
        font-size: 1.2rem;
        color: var(--text-secondary);
        margin: 2.5rem 0 1.5rem;
        position: relative;
    }

    .executives-subtitle::before {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: -8px;
        width: 60px;
        height: 3px;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        border-radius: 2px;
    }

    /* Dean Highlight */
    .dean-highlight {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        background: white;
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
        max-width: 500px;
        margin: 0 auto 1rem;
        position: relative;
        overflow: hidden;
    }

    .dean-highlight::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(135deg, #1e3a5f, #2d7d46);
    }

    .dean-highlight__image img {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        margin-bottom: 1.5rem;
    }

    .dean-highlight__badge {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        background: linear-gradient(135deg, #1e3a5f, #2d5a87);
        color: white;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .dean-highlight__name {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.5rem;
    }

    .dean-highlight__title-en {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin: 0;
    }

    /* Executives Grid */
    .executives-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .executives-grid--4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .executive-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.3s ease;
    }

    .executive-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
    }

    .executive-card--small {
        padding: 0;
    }

    .executive-card__image {
        padding: 1.5rem 1.5rem 0.5rem;
        background: linear-gradient(135deg, rgba(30, 58, 95, 0.03), rgba(45, 125, 70, 0.03));
    }

    .executive-card__image img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
        transition: transform 0.3s ease;
    }

    .executive-card--small .executive-card__image img {
        width: 100px;
        height: 100px;
    }

    .executive-card:hover .executive-card__image img {
        transform: scale(1.05);
    }

    .executive-card__content {
        padding: 1rem 1rem 1.5rem;
    }

    .executive-card__position {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        background: linear-gradient(135deg, #2d7d46, #3d9d56);
        color: white;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .executive-card__position--assistant {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .executive-card__position--research {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .executive-card__name {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.4;
    }

    .executive-card--small .executive-card__name {
        font-size: 0.85rem;
    }

    @media (max-width: 1024px) {
        .executives-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .executives-grid--4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {

        .executives-grid,
        .executives-grid--4 {
            grid-template-columns: 1fr;
        }

        .dean-highlight {
            padding: 1.5rem;
        }

        .dean-highlight__image img {
            width: 150px;
            height: 150px;
        }
    }
</style>

<?= $this->endSection() ?>