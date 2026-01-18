<?= $this->extend('layouts/main_layout') ?>

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

<!-- Hero Section -->
<section class="hero hero--science">
    <div class="container">
        <div class="hero__content">
            <span class="hero__subtitle"><?= esc($universityEn) ?></span>
            <h1 class="hero__title"><?= esc($heroTitle) ?></h1>
            <p class="hero__description">
                <?= esc($heroDesc) ?>
            </p>
            <div class="hero__actions">
                <a href="<?= base_url('about') ?>" class="btn btn-primary btn-lg">เกี่ยวกับเรา</a>
                <a href="<?= base_url('admission') ?>" class="btn btn-outline btn-lg">สมัครเรียน</a>
            </div>
        </div>
    </div>
</section>

<!-- Campus News Section -->
<section class="news-section">
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
        
        <?php if (!empty($news)): ?>
        <div class="featured-news">
            <!-- Main Featured Article -->
            <?php if (!empty($news[0])): ?>
            <div class="featured-news__main">
                <article class="card animate-on-scroll">
                    <?php if (!empty($news[0]['featured_image'])): ?>
                    <img src="<?= esc($news[0]['featured_image']) ?>" alt="<?= esc($news[0]['title']) ?>" class="card__image">
                    <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=400&fit=crop" alt="News" class="card__image">
                    <?php endif; ?>
                    <div class="card__content">
                        <span class="card__category">ข่าวล่าสุด</span>
                        <h3 class="card__title">
                            <a href="<?= base_url('news/' . $news[0]['slug']) ?>"><?= esc($news[0]['title']) ?></a>
                        </h3>
                        <?php if (!empty($news[0]['excerpt'])): ?>
                        <p class="card__excerpt"><?= esc(mb_substr($news[0]['excerpt'], 0, 150)) ?>...</p>
                        <?php endif; ?>
                        <div class="card__meta">
                            <span><?= !empty($news[0]['published_at']) ? date('d M Y', strtotime($news[0]['published_at'])) : '' ?></span>
                        </div>
                    </div>
                </article>
            </div>
            <?php endif; ?>
            
            <!-- Secondary Articles -->
            <?php for ($i = 1; $i < min(3, count($news)); $i++): ?>
            <article class="card animate-on-scroll">
                <?php if (!empty($news[$i]['featured_image'])): ?>
                <img src="<?= esc($news[$i]['featured_image']) ?>" alt="<?= esc($news[$i]['title']) ?>" class="card__image">
                <?php else: ?>
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=400&h=200&fit=crop" alt="News" class="card__image">
                <?php endif; ?>
                <div class="card__content">
                    <span class="card__category">ข่าว</span>
                    <h3 class="card__title">
                        <a href="<?= base_url('news/' . $news[$i]['slug']) ?>"><?= esc(mb_substr($news[$i]['title'], 0, 80)) ?>...</a>
                    </h3>
                    <div class="card__meta">
                        <span><?= !empty($news[$i]['published_at']) ? date('d M Y', strtotime($news[$i]['published_at'])) : '' ?></span>
                    </div>
                </div>
            </article>
            <?php endfor; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8">
            <p class="text-muted">ยังไม่มีข่าวประชาสัมพันธ์</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Programs Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">หลักสูตร</span>
            <h2 class="section-header__title">หลักสูตรที่เปิดสอน</h2>
            <p class="section-header__description">
                คณะวิทยาศาสตร์และเทคโนโลยีเปิดสอนหลักสูตรที่หลากหลาย ทั้งระดับปริญญาตรี ปริญญาโท และปริญญาเอก
            </p>
        </div>
        
        <?php if (!empty($bachelor_programs)): ?>
        <h3 class="text-center mb-4" style="font-size: 1.25rem; color: var(--primary);">ระดับปริญญาตรี</h3>
        <div class="grid grid-3">
            <?php foreach (array_slice($bachelor_programs, 0, 6) as $program): ?>
            <article class="card animate-on-scroll">
                <div class="card__content">
                    <span class="card__category"><?= esc($program['degree_th'] ?? 'วท.บ.') ?></span>
                    <h3 class="card__title"><?= esc($program['name_th']) ?></h3>
                    <?php if (!empty($program['name_en'])): ?>
                    <p class="card__excerpt" style="font-size: 0.875rem; color: var(--text-muted);"><?= esc($program['name_en']) ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php if (count($bachelor_programs) > 6): ?>
        <div class="text-center mt-4">
            <a href="<?= base_url('academics') ?>" class="btn btn-outline">ดูหลักสูตรทั้งหมด</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!empty($master_programs) || !empty($doctorate_programs)): ?>
        <h3 class="text-center mt-8 mb-4" style="font-size: 1.25rem; color: var(--primary);">ระดับบัณฑิตศึกษา</h3>
        <div class="grid grid-3">
            <?php foreach ($master_programs as $program): ?>
            <article class="card animate-on-scroll">
                <div class="card__content">
                    <span class="card__category" style="background: var(--accent);"><?= esc($program['degree_th'] ?? 'วท.ม.') ?></span>
                    <h3 class="card__title"><?= esc($program['name_th']) ?></h3>
                    <p class="card__excerpt" style="font-size: 0.875rem;">ปริญญาโท</p>
                </div>
            </article>
            <?php endforeach; ?>
            <?php foreach ($doctorate_programs as $program): ?>
            <article class="card animate-on-scroll">
                <div class="card__content">
                    <span class="card__category" style="background: var(--secondary);"><?= esc($program['degree_th'] ?? 'ปร.ด.') ?></span>
                    <h3 class="card__title"><?= esc($program['name_th']) ?></h3>
                    <p class="card__excerpt" style="font-size: 0.875rem;">ปริญญาเอก</p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Stats Section -->
<section class="section section-primary">
    <div class="container">
        <div class="stats">
            <div class="stat animate-on-scroll">
                <div class="stat__number">10+</div>
                <div class="stat__label">หลักสูตร</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">11</div>
                <div class="stat__label">สาขาวิชา</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">89</div>
                <div class="stat__label">ปีแห่งความภาคภูมิใจ</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">3</div>
                <div class="stat__label">ระดับการศึกษา</div>
            </div>
        </div>
    </div>
</section>

<!-- Executive Highlight Section -->
<section class="section" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ผู้บริหารคณะ</span>
            <h2 class="section-header__title">ทีมผู้บริหาร</h2>
        </div>
        
        <div class="executive-highlight">
            <!-- Dean Card -->
            <div class="dean-card animate-on-scroll">
                <div class="dean-card__image">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/54" alt="คณบดี" onerror="this.src='https://ui-avatars.com/api/?name=ปริญญา&background=1e3a5f&color=fff&size=200'">
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
                    <img src="https://sci.uru.ac.th/image/getpersonimage/87" alt="รองคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=จุฬาลักษณ์&background=2d7d46&color=fff&size=80'">
                    <div class="vice-dean-card__info">
                        <span class="vice-dean-card__position">รองคณบดี</span>
                        <span class="vice-dean-card__name">ผศ.จุฬาลักษณ์ มหาวัน</span>
                    </div>
                </div>
                <div class="vice-dean-card animate-on-scroll">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/25" alt="รองคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=วารุณี&background=2d7d46&color=fff&size=80'">
                    <div class="vice-dean-card__info">
                        <span class="vice-dean-card__position">รองคณบดี</span>
                        <span class="vice-dean-card__name">ผศ.ดร.วารุณี จอมกิติชัย</span>
                    </div>
                </div>
                <div class="vice-dean-card animate-on-scroll">
                    <img src="https://sci.uru.ac.th/image/getpersonimage/103" alt="รองคณบดี" onerror="this.src='https://ui-avatars.com/api/?name=วีระศักดิ์&background=2d7d46&color=fff&size=80'">
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
<section class="section">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&h=400&fit=crop" alt="Research Laboratory">
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
                <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400&h=250&fit=crop" alt="Student Activities" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">กิจกรรมนักศึกษา</h3>
                    <p class="card__excerpt">
                        ค้นพบวิธีมากมายในการเชื่อมต่อ เติบโต และสนุกสนาน
                    </p>
                </div>
            </article>
            
            <article class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?w=400&h=250&fit=crop" alt="Wellness Center" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">สุขภาพและสันทนาการ</h3>
                    <p class="card__excerpt">
                        สิ่งอำนวยความสะดวกและโปรแกรมที่ทันสมัยเพื่อสนับสนุนสุขภาพของคุณ
                    </p>
                </div>
            </article>
            
            <article class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=400&h=250&fit=crop" alt="Community Events" class="card__image">
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

<!-- More News Section -->
<?php if (count($news) > 3): ?>
<section class="section">
    <div class="container">
        <div class="news-section__header">
            <h2 class="news-section__title">ข่าวอื่นๆ</h2>
        </div>
        
        <div class="grid grid-3">
            <?php for ($i = 3; $i < min(6, count($news)); $i++): ?>
            <article class="card animate-on-scroll">
                <div class="card__content">
                    <span class="card__category">ข่าว</span>
                    <h3 class="card__title">
                        <a href="<?= base_url('news/' . $news[$i]['slug']) ?>"><?= esc($news[$i]['title']) ?></a>
                    </h3>
                    <div class="card__meta">
                        <span><?= !empty($news[$i]['published_at']) ? date('d M Y', strtotime($news[$i]['published_at'])) : '' ?></span>
                    </div>
                </div>
            </article>
            <?php endfor; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Admission CTA Section -->
<section class="cta-section">
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
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 700px;
    width: 100%;
}

.dean-card__image img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--color-primary);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
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
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.vice-dean-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
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
</style>

<?= $this->endSection() ?>
