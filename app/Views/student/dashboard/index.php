<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<style>
.portal-hub-title { font-size: 1.5rem; font-weight: 700; color: var(--color-gray-800); margin-bottom: 0.5rem; }
.portal-hub-desc { color: var(--color-gray-600); margin-bottom: 2rem; }
.portal-hub-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
}
.portal-hub-card {
    background: var(--color-white);
    border-radius: 16px;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    border: 1px solid var(--color-gray-200);
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.portal-hub-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    border-color: var(--color-gray-300);
}
.portal-hub-card-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}
.portal-hub-card-icon svg { width: 28px; height: 28px; }
.portal-hub-card--barcode .portal-hub-card-icon { background: #fef9c3; color: #a16207; }
.portal-hub-card--events .portal-hub-card-icon { background: #dbeafe; color: #1d4ed8; }
.portal-hub-card--admin .portal-hub-card-icon { background: var(--color-gray-200); color: var(--color-gray-700); }
.portal-hub-card--admin { border-color: var(--color-gray-300); }
.portal-hub-card--admin .portal-hub-card-desc { color: var(--color-gray-600); }
.portal-hub-section-title { font-size: 0.875rem; font-weight: 600; color: var(--color-gray-500); text-transform: uppercase; letter-spacing: 0.05em; margin: 1.5rem 0 0.75rem; }
.portal-hub-section-title:first-of-type { margin-top: 0; }
.portal-hub-card-title { font-weight: 600; font-size: 1.0625rem; color: var(--color-gray-800); margin-bottom: 0.25rem; }
.portal-hub-card-desc { font-size: 0.8125rem; color: var(--color-gray-500); }
</style>

<div class="portal-hub-title">Portal นักศึกษา</div>
<p class="portal-hub-desc">เลือกเมนูด้านล่างเพื่อเข้าใช้งานแต่ละส่วน</p>

<p class="portal-hub-section-title">บริการ</p>
<div class="portal-hub-grid">
    <a href="<?= base_url('student/barcodes') ?>" class="portal-hub-card portal-hub-card--barcode">
        <div class="portal-hub-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <span class="portal-hub-card-title">บาร์โค้ด</span>
        <span class="portal-hub-card-desc">บาร์โค้ดที่ได้รับจากกิจกรรม</span>
    </a>
    <a href="<?= base_url('student/events') ?>" class="portal-hub-card portal-hub-card--events">
        <div class="portal-hub-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <span class="portal-hub-card-title">ข่าว / Event</span>
        <span class="portal-hub-card-desc">กิจกรรมและข่าวที่กำลังจะมาถึง</span>
    </a>
</div>

<?php if (session()->get('student_role') === 'club'): ?>
<p class="portal-hub-section-title">สำหรับนักศึกษาสโมสร</p>
<div class="portal-hub-grid">
    <a href="<?= base_url('student-admin/barcode-events') ?>" class="portal-hub-card portal-hub-card--admin">
        <div class="portal-hub-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <span class="portal-hub-card-title">Student Admin</span>
        <span class="portal-hub-card-desc">จัดการบาร์โค้ดและ Event แจกบาร์โค้ด</span>
    </a>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
