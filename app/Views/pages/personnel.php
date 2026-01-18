<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">บุคลากร</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>บุคลากร</span>
        </div>
    </div>
</section>

<!-- Personnel Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Our Team</span>
            <h2 class="section-header__title">ผู้บริหารและบุคลากร</h2>
            <p class="section-header__description">
                บุคลากร <?= esc($site_info['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี') ?>
            </p>
        </div>
        
        <?php if (!empty($personnel)): ?>
        <div class="personnel-grid">
            <?php foreach ($personnel as $person): ?>
            <div class="personnel-card animate-on-scroll">
                <div class="personnel-card__image">
                    <?php if (!empty($person['profile_image'])): ?>
                    <img src="<?= esc($person['profile_image']) ?>" alt="<?= esc($person['title'] . $person['first_name'] . ' ' . $person['last_name']) ?>">
                    <?php else: ?>
                    <div class="personnel-card__avatar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="personnel-card__content">
                    <h3 class="personnel-card__name">
                        <?= esc($person['title'] ?? '') ?><?= esc($person['first_name'] ?? '') ?> <?= esc($person['last_name'] ?? '') ?>
                    </h3>
                    <?php if (!empty($person['position'])): ?>
                    <p class="personnel-card__position"><?= esc($person['position']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($person['email'])): ?>
                    <a href="mailto:<?= esc($person['email']) ?>" class="personnel-card__email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <?= esc($person['email']) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3>ยังไม่มีข้อมูลบุคลากร</h3>
            <p>ข้อมูลบุคลากรจะปรากฏที่นี่เมื่อมีการเพิ่มเนื้อหา</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.personnel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.personnel-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    text-align: center;
    transition: all 0.3s ease;
}

.personnel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.personnel-card__image {
    padding: 2rem 2rem 0;
}

.personnel-card__image img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid var(--color-gray-100);
}

.personnel-card__avatar {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-gray-100), var(--color-gray-200));
    border-radius: 50%;
}

.personnel-card__avatar svg {
    width: 60px;
    height: 60px;
    color: var(--color-gray-400);
}

.personnel-card__content {
    padding: 1.5rem;
}

.personnel-card__name {
    font-size: 1.1rem;
    margin: 0 0 0.5rem;
    color: var(--text-primary);
}

.personnel-card__position {
    color: var(--color-primary);
    font-weight: 500;
    margin: 0 0 1rem;
    font-size: 0.9rem;
}

.personnel-card__email {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.85rem;
}

.personnel-card__email:hover {
    color: var(--color-primary);
}

.personnel-card__email svg {
    width: 16px;
    height: 16px;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state__icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-100);
    border-radius: 50%;
}

.empty-state__icon svg {
    width: 40px;
    height: 40px;
    color: var(--color-gray-400);
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-secondary);
    margin: 0;
}
</style>

<?= $this->endSection() ?>
