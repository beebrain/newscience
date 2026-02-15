<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<style>
    :root {
        --dash-bg: #f9fafb;
        --dash-card-bg: #ffffff;
        --dash-text-primary: #1f2937;
        --dash-text-secondary: #6b7280;
        --dash-border: #e5e7eb;
        --dash-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .dashboard-container {
        padding: 1rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Welcome Section */
    .welcome-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        margin-bottom: 2.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid var(--dash-border);
    }
    
    .welcome-avatar {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #eab308, #ca8a04);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.75rem;
        font-weight: bold;
    }

    .welcome-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dash-text-primary);
        margin: 0 0 0.25rem 0;
    }

    .welcome-text p {
        color: var(--dash-text-secondary);
        margin: 0;
        font-size: 0.95rem;
    }

    /* Section Headers */
    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--dash-border);
        padding-bottom: 0.75rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--dash-text-primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
    }

    .section-icon {
        color: var(--dash-text-secondary);
        width: 20px;
        height: 20px;
    }

    /* Grid Layouts */
    .system-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    /* System Card (Large) */
    .system-card {
        background: var(--dash-card-bg);
        border-radius: 12px;
        padding: 1.75rem;
        border: 1px solid var(--dash-border);
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
    }

    .system-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--dash-hover-shadow);
        border-color: #d1d5db;
    }

    .system-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        transition: transform 0.2s ease;
    }

    .system-card:hover .system-card-icon {
        transform: scale(1.1);
    }

    .system-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--dash-text-primary);
        margin-bottom: 0.5rem;
    }

    .system-card-desc {
        font-size: 0.875rem;
        color: var(--dash-text-secondary);
        line-height: 1.5;
    }

    .system-card-arrow {
        position: absolute;
        top: 1.75rem;
        right: 1.75rem;
        color: #d1d5db;
        transition: color 0.2s, transform 0.2s;
    }

    .system-card:hover .system-card-arrow {
        color: var(--dash-text-primary);
        transform: translateX(4px);
    }

    /* Styles Specific to Systems */
    .card-edoc .system-card-icon {
        background: #eff6ff;
        color: #2563eb;
    }
    .card-edoc:hover .system-card-title { color: #2563eb; }

    .card-research .system-card-icon {
        background: #ecfdf5;
        color: #059669;
    }
    .card-research:hover .system-card-title { color: #059669; }

    /* Admin Card (Small) */
    .admin-card {
        background: white;
        border: 1px solid var(--dash-border);
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .admin-card:hover {
        background: #fdfdfd;
        border-color: #9ca3af;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .admin-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background: #f3f4f6;
        color: #4b5563;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .admin-card:hover .admin-icon-box {
        background: #e5e7eb;
        color: #111827;
    }

    .admin-card-text {
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--dash-text-primary);
    }
</style>

<div class="dashboard-container">
    
    <!-- 1. Profile / Welcome Section -->
    <?php $p = $profile ?? []; ?>
    <div class="welcome-card">
        <div class="welcome-avatar">
            <?= strtoupper(substr($p['name_en'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="welcome-text">
            <h1>สวัสดี, <?= esc($p['name_th'] ?: ($p['name_en'] ?? 'User')) ?></h1>
            <p>
                <?= esc($p['email'] ?? '') ?> 
                <span style="margin: 0 0.5rem; color: #d1d5db;">|</span> 
                <span class="badge" style="background-color: #f3f4f6; color: #4b5563; font-size: 0.75rem;"><?= esc(ucfirst($p['role'] ?? 'User')) ?></span>
            </p>
        </div>
    </div>

    <!-- 2. User Systems (Main Services) -->
    <div class="section-header">
        <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <h2 class="section-title">ระบบสารสนเทศ (Systems & Services)</h2>
    </div>

    <div class="system-grid">
        <!-- Edoc System -->
        <?php
        $edocSso = config(\Config\EdocSso::class);
        if ($edocSso->enabled && $edocSso->baseUrl !== ''):
        ?>
        <a href="<?= esc(rtrim($edocSso->baseUrl, '/')) ?>" target="_blank" class="system-card card-edoc">
            <div class="system-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="system-card-title">งานสารบรรณ (e-Doc)</div>
            <div class="system-card-desc">ระบบรับ-ส่งหนังสือราชการและเอกสารอิเล็กทรอนิกส์</div>
            <svg class="system-card-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
        <?php endif; ?>

        <!-- Research Record System -->
        <?php
        $researchSso = config(\Config\ResearchRecordSso::class);
        if ($researchSso->enabled && $researchSso->baseUrl !== ''):
        ?>
        <a href="<?= esc(rtrim($researchSso->baseUrl, '/') . '/index.php/dashboard') ?>" target="_blank" class="system-card card-research">
            <div class="system-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" opacity="0.1"></path>
                    <path d="M9 12l2 2 4-4"></path>
                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div class="system-card-title">จัดการงานวิจัย</div>
            <div class="system-card-desc">ระบบบันทึกและจัดการข้อมูลงานวิจัย (Research Record)</div>
            <svg class="system-card-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
        <?php endif; ?>
    </div>

    <!-- 3. Admin Systems (Condition: Admin Only) -->
    <?php
    $sidebarAdminRole = session()->get('admin_role');
    $isAdmin = in_array($sidebarAdminRole, ['admin', 'editor', 'super_admin', 'faculty_admin'], true);
    if ($isAdmin):
    ?>
    <div class="section-header" style="margin-top: 2rem;">
        <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2.5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H11a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h1zm2.5 1a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1z"></path>
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
        </svg>
        <h2 class="section-title">ผู้ดูแลระบบ (Admin Tools)</h2>
    </div>

    <div class="admin-grid">
        <a href="<?= base_url('admin/news') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
            </div>
            <div class="admin-card-text">จัดการข่าว</div>
        </a>

        <a href="<?= base_url('admin/organization') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="admin-card-text">โครงสร้างองค์กร</div>
        </a>

        <a href="<?= base_url('admin/programs') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <div class="admin-card-text">หลักสูตร</div>
        </a>

        <a href="<?= base_url('admin/users') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="admin-card-text">ผู้ใช้งาน</div>
        </a>

        <a href="<?= base_url('admin/hero-slides') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </div>
            <div class="admin-card-text">ภาพสไลด์</div>
        </a>

        <a href="<?= base_url('admin/events') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="admin-card-text">กิจกรรม</div>
        </a>

        <a href="<?= base_url('student-admin/barcode-events') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <div class="admin-card-text">จัดการบาร์โค้ด</div>
        </a>

        <?php if (in_array($sidebarAdminRole, ['super_admin'])): ?>
        <a href="<?= base_url('utility/import-data') ?>" class="admin-card">
            <div class="admin-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
            </div>
            <div class="admin-card-text">Import Data</div>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
