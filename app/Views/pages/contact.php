<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">ติดต่อเรา</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>ติดต่อเรา</span>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info animate-on-scroll">
                <div class="section-header section-header--left">
                    <span class="section-header__subtitle">ข้อมูลติดต่อ</span>
                    <h2 class="section-header__title"><?= esc($site_info['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?></h2>
                    <p class="section-header__description">
                        <?= esc($site_info['university_name_th'] ?? 'มหาวิทยาลัยราชภัฏอุตรดิตถ์') ?>
                    </p>
                </div>
                
                <div class="contact-items">
                    <!-- Address -->
                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="contact-item__content">
                            <h4>ที่อยู่</h4>
                            <p><?= esc($address_th ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000') ?></p>
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="contact-item__content">
                            <h4>โทรศัพท์</h4>
                            <p><a href="tel:<?= esc($phone ?? '055-411096') ?>"><?= esc($phone ?? '055-411096') ?></a></p>
                            <?php if (!empty($fax)): ?>
                            <p class="text-muted">โทรสาร: <?= esc($fax) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div class="contact-item__content">
                            <h4>อีเมล</h4>
                            <p><a href="mailto:<?= esc($email ?? 'sci@uru.ac.th') ?>"><?= esc($email ?? 'sci@uru.ac.th') ?></a></p>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                            </svg>
                        </div>
                        <div class="contact-item__content">
                            <h4>Facebook</h4>
                            <p><a href="<?= esc($facebook ?? 'https://www.facebook.com/scienceuru') ?>" target="_blank" rel="noopener">Science URU</a></p>
                        </div>
                    </div>
                    
                    <!-- Website -->
                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div class="contact-item__content">
                            <h4>เว็บไซต์</h4>
                            <p><a href="<?= esc($website ?? 'https://sci.uru.ac.th') ?>" target="_blank" rel="noopener"><?= esc($website ?? 'https://sci.uru.ac.th') ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Map -->
            <div class="contact-map animate-on-scroll">
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3854.4893892173!2d100.10095!3d17.6301!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30de86caa2f9bfb3%3A0x1c3e3e0e0e0e0e0e!2sUttaradit%20Rajabhat%20University!5e0!3m2!1sen!2sth!4v1704067200000!5m2!1sen!2sth"
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Hours Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">เวลาทำการ</span>
            <h2 class="section-header__title">เวลาเปิดทำการ</h2>
        </div>
        
        <div class="grid grid-3">
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="time-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <h3 class="card__title">วันจันทร์ - ศุกร์</h3>
                    <p class="card__excerpt">08:30 - 16:30 น.</p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="time-icon time-icon--secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <h3 class="card__title">วันหยุดนักขัตฤกษ์</h3>
                    <p class="card__excerpt">ปิดทำการ</p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="time-icon time-icon--tertiary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/>
                            <path d="M10 10a4 4 0 0 1 4 4"/>
                            <circle cx="12" cy="12" r="2"/>
                        </svg>
                    </div>
                    <h3 class="card__title">ติดต่อออนไลน์</h3>
                    <p class="card__excerpt">24 ชั่วโมง</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ลิงก์ที่เกี่ยวข้อง</span>
            <h2 class="section-header__title">หน่วยงานที่เกี่ยวข้อง</h2>
        </div>
        
        <div class="quick-links-grid">
            <a href="https://www.uru.ac.th" target="_blank" rel="noopener" class="quick-link animate-on-scroll">
                <span class="quick-link__icon">🏫</span>
                <span class="quick-link__text">มหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
            </a>
            <a href="https://reg.uru.ac.th" target="_blank" rel="noopener" class="quick-link animate-on-scroll">
                <span class="quick-link__icon">📋</span>
                <span class="quick-link__text">สำนักส่งเสริมวิชาการ</span>
            </a>
            <a href="https://std.uru.ac.th" target="_blank" rel="noopener" class="quick-link animate-on-scroll">
                <span class="quick-link__icon">👨‍🎓</span>
                <span class="quick-link__text">กิจการนักศึกษา</span>
            </a>
            <a href="https://library.uru.ac.th" target="_blank" rel="noopener" class="quick-link animate-on-scroll">
                <span class="quick-link__icon">📚</span>
                <span class="quick-link__text">สำนักวิทยบริการ</span>
            </a>
        </div>
    </div>
</section>

<style>
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
}

.section-header--left {
    text-align: left;
}

.section-header--left::before {
    margin-left: 0;
}

.contact-items {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.contact-item {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--color-gray-50);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.contact-item:hover {
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.contact-item__icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    border-radius: 12px;
    color: white;
}

.contact-item__icon svg {
    width: 24px;
    height: 24px;
}

.contact-item__content h4 {
    margin: 0 0 0.25rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
}

.contact-item__content p {
    margin: 0;
    color: var(--text-primary);
}

.contact-item__content a {
    color: var(--color-primary);
    text-decoration: none;
}

.contact-item__content a:hover {
    text-decoration: underline;
}

.text-muted {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.map-container {
    height: 100%;
    min-height: 500px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.time-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    border-radius: 50%;
    margin: 0 auto 1rem;
    color: white;
}

.time-icon svg {
    width: 28px;
    height: 28px;
}

.time-icon--secondary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.time-icon--tertiary {
    background: linear-gradient(135deg, #10b981, #059669);
}

.quick-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: white;
    border: 1px solid var(--color-gray-200);
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.quick-link:hover {
    border-color: var(--color-primary);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.quick-link__icon {
    font-size: 1.5rem;
}

.quick-link__text {
    font-weight: 500;
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .map-container {
        min-height: 300px;
    }
}
</style>

<?= $this->endSection() ?>

