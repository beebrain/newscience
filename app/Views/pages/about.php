<?= $this->extend('layouts/main_layout') ?>

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

<!-- Philosophy & Vision Section -->
<section class="section">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=600&h=400&fit=crop" alt="<?= $site_info['site_name_th'] ?? 'Campus' ?>">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">ปรัชญา</span>
                <h2 class="feature-section__title"><?= esc($philosophy ?? 'สร้างองค์ความรู้และพัฒนาคนในชาติ ด้วยวิทยาศาสตร์และเทคโนโลยี') ?></h2>
                <div class="about-badge">
                    <span class="about-badge__label">อัตลักษณ์</span>
                    <span class="about-badge__value"><?= esc($identity ?? 'บัณฑิตนักปฏิบัติ') ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision Section -->
<section class="section section-light">
    <div class="container">
        <div class="feature-section feature-section--reverse">
            <div class="feature-section__image animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1607237138185-eedd9c632b0b?w=600&h=400&fit=crop" alt="วิสัยทัศน์">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">วิสัยทัศน์</span>
                <h2 class="feature-section__title">องค์กรแห่งความสุข</h2>
                <p class="feature-section__description">
                    <?= esc($vision ?? 'คณะวิทยาศาสตร์และเทคโนโลยี เป็นองค์กรแห่งความสุข มุ่งพัฒนาและผลิตบัณฑิตให้เป็นคนดี คนเก่ง มีจิตอาสา นำพาสังคม พร้อมทั้งเป็นแหล่งเรียนรู้และบริการวิชาการแก่ชุมชน ท้องถิ่น ระดับชาติและนานาชาติ') ?>
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
        
        <div class="grid grid-2">
            <div class="card animate-on-scroll">
                <div class="card__content">
                    <div class="mission-number">1</div>
                    <h3 class="card__title">ผลิตบัณฑิต</h3>
                    <p class="card__excerpt">
                        ผลิตบัณฑิตที่มีคุณภาพตามมาตรฐานวิชาชีพ มีความรู้ความสามารถด้านวิทยาศาสตร์และเทคโนโลยี
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content">
                    <div class="mission-number">2</div>
                    <h3 class="card__title">วิจัยและพัฒนา</h3>
                    <p class="card__excerpt">
                        วิจัยและพัฒนาองค์ความรู้ด้านวิทยาศาสตร์และเทคโนโลยี เพื่อตอบสนองความต้องการของท้องถิ่นและประเทศชาติ
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content">
                    <div class="mission-number">3</div>
                    <h3 class="card__title">บริการวิชาการ</h3>
                    <p class="card__excerpt">
                        บริการวิชาการแก่สังคมและชุมชน เพื่อยกระดับคุณภาพชีวิตของประชาชนในท้องถิ่น
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content">
                    <div class="mission-number">4</div>
                    <h3 class="card__title">ทำนุบำรุงศิลปวัฒนธรรม</h3>
                    <p class="card__excerpt">
                        ส่งเสริมและทำนุบำรุงศิลปวัฒนธรรมท้องถิ่นและของชาติ
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

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

<!-- Departments Section -->
<?php if (!empty($departments)): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">สาขาวิชา</span>
            <h2 class="section-header__title">หน่วยงานภายในคณะ</h2>
        </div>
        
        <div class="grid grid-3">
            <?php foreach ($departments as $dept): ?>
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="dept-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px;">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                    <h3 class="card__title"><?= esc($dept['name_th'] ?? $dept['name_en'] ?? 'สาขาวิชา') ?></h3>
                    <p class="card__excerpt text-muted"><?= esc($dept['name_en'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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
.about-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    border-radius: 50px;
    color: white;
}

.about-badge__label {
    font-size: 0.85rem;
    opacity: 0.9;
}

.about-badge__value {
    font-weight: 600;
    font-size: 1.1rem;
}

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
    box-shadow: 0 15px 50px rgba(0,0,0,0.12);
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
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    text-align: center;
    transition: all 0.3s ease;
}

.executive-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 50px rgba(0,0,0,0.15);
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
    box-shadow: 0 6px 25px rgba(0,0,0,0.12);
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
