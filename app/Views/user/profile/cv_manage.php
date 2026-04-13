<?php
helper(['form', 'security']);
?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('styles') ?>
<style>
/* ใช้ความกว้างเต็มภายใน user_layout (main ไม่มี container) */
.cv-manage-page {
    width: 100%;
    max-width: none;
    box-sizing: border-box;
    /* สอดคล้อง user_layout: Sarabun + Noto Sans Thai, ขนาดพื้นฐาน 1rem / บรรทัด 1.6 */
    font-family: 'Sarabun', 'Noto Sans Thai', sans-serif;
    font-size: 1rem;
    line-height: 1.6;
}
/* สวิตช์แสดงสาธารณะ (รายการ / หัวข้อ) */
.cv-list-vis-switch:disabled {
    opacity: 0.55;
    cursor: wait;
}
/* Modal รายการ CV — ฟอนต์/ขนาดเดียวกับหน้าแก้ไข + จัดกึ่งกลาง */
#cv-entry-modal {
    font-family: 'Sarabun', 'Noto Sans Thai', sans-serif;
    font-size: 1rem;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}
#cv-entry-modal .cv-entry-modal__panel {
    max-height: min(92vh, 880px);
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: min(96vw, 56rem);
    margin: auto;
    border-radius: 8px;
    border: 1px solid var(--color-gray-300, #e5e7eb);
    box-shadow: var(--shadow-lg, 0 10px 15px rgba(0, 0, 0, 0.1));
}
#cv-entry-modal .cv-entry-modal__body {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}
#cv-entry-modal .cv-entry-modal__meta {
    padding-right: 2.75rem;
}
@media (min-width: 640px) {
    #cv-entry-modal .cv-entry-modal__meta {
        padding-right: 3rem;
    }
}
#cv-entry-modal .cv-entry-modal__display-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
@media (min-width: 640px) {
    #cv-entry-modal .cv-entry-modal__display-row {
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.5rem;
    }
}
#cv-entry-modal .cv-entry-modal__vis-toggle {
    border: 1px solid var(--color-gray-300, #e5e7eb);
    border-radius: 8px;
    background: var(--color-white, #fff);
    padding: 0.5rem 0.75rem;
}
#cv-entry-modal .cv-entry-modal__vis-toggle:hover {
    background: var(--color-gray-100, #f9fafb);
}
#cv-entry-modal .cv-edit-modal-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--text-primary, #1e5c32);
    margin-bottom: 0.4rem;
}
#cv-entry-modal .cv-edit-modal-input,
#cv-entry-modal .cv-edit-modal-textarea,
#cv-entry-modal .cv-edit-modal-select {
    width: 100%;
    border: 1px solid var(--color-gray-300, #e5e7eb);
    border-radius: 6px;
    padding: 0.55rem 0.85rem;
    font-size: 0.875rem;
    line-height: 1.5;
    color: var(--color-gray-800, #374151);
    background: var(--color-white, #fff);
}
#cv-entry-modal .cv-edit-modal-textarea {
    min-height: 6.5rem;
}
#cv-entry-modal .cv-edit-modal-input:focus,
#cv-entry-modal .cv-edit-modal-textarea:focus,
#cv-entry-modal .cv-edit-modal-select:focus {
    outline: none;
    border-color: var(--secondary, #2d7d46);
    box-shadow: 0 0 0 2px rgba(45, 125, 70, 0.2);
}
#cv-entry-modal .cv-entry-modal__footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--color-gray-300, #e5e7eb);
    background: linear-gradient(180deg, var(--color-gray-100, #f9fafb) 0%, var(--color-gray-200, #f3f4f6) 100%);
}
@media (min-width: 640px) {
    #cv-entry-modal .cv-entry-modal__footer {
        padding: 1rem 1.5rem;
    }
}
#cv-entry-modal .cv-entry-modal__headstrip {
    border-bottom: 2px solid var(--secondary, #2d7d46);
    padding-bottom: 0.75rem;
    margin-bottom: 1.25rem;
}
#cv-entry-modal .cv-entry-modal__headstrip p.cv-edit-stitch-kicker {
    margin-bottom: 0.25rem;
}
#cv-entry-modal .cv-entry-modal__formstack {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}
#cv-entry-modal .cv-entry-modal__grid2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem 1.25rem;
}
@media (min-width: 640px) {
    #cv-entry-modal .cv-entry-modal__grid2 {
        grid-template-columns: 1fr 1fr;
    }
}
/* หน้าแก้ไข CV — โครง Stitch “Personal Details Editor” (screen bf1405903f0e4e2f9c24f4fe9036644a, project 4646948630959592255) */
.cv-edit-stitch-shell {
    border-radius: 6px;
    border: 1px solid var(--color-gray-300, #e5e7eb);
    box-shadow: var(--shadow-md, 0 4px 6px rgba(0, 0, 0, 0.08));
    overflow: hidden;
    background: var(--color-white, #fff);
}
.cv-edit-stitch-sheet {
    display: grid;
    grid-template-columns: 1fr;
    align-items: stretch;
    min-height: min(78vh, 56rem);
}
@media (min-width: 960px) {
    .cv-edit-stitch-sheet {
        grid-template-columns: minmax(15rem, 17.5rem) minmax(0, 1fr);
    }
}
.cv-edit-stitch-sidebar {
    background: linear-gradient(165deg, var(--secondary-dark, #1e5c32) 0%, var(--secondary, #2d7d46) 50%, var(--secondary-light, #3da55d) 100%);
    color: var(--color-white, #fff);
    padding: clamp(1.25rem, 2.5vw, 2rem) clamp(1.15rem, 2.5vw, 1.65rem);
    display: flex;
    flex-direction: column;
    align-items: stretch;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
@media (min-width: 960px) {
    .cv-edit-stitch-sidebar {
        border-bottom: none;
        border-right: 1px solid rgba(255, 255, 255, 0.12);
        min-height: 100%;
    }
}
.cv-edit-stitch-sidebar-photo {
    width: clamp(6.5rem, 18vw, 7.5rem);
    height: clamp(6.5rem, 18vw, 7.5rem);
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.14);
    background: var(--secondary-dark, #1e5c32);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}
.cv-edit-stitch-sidebar-nav .cv-edit-tab-nav:focus-visible {
    outline: 2px solid var(--primary, #eab308);
    outline-offset: 2px;
}
.cv-edit-tab-nav {
    display: block;
    width: 100%;
    text-align: left;
    text-decoration: none;
    border-radius: 6px;
    padding: 0.5rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.35;
    color: rgba(241, 245, 249, 0.95);
    transition: background-color 0.15s ease, color 0.15s ease;
}
.cv-edit-tab-nav:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
}
.cv-edit-tab-nav--active {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    border-left: 3px solid var(--primary, #eab308);
    padding-left: calc(0.5rem - 3px);
    margin-left: 2px;
}
.cv-edit-stitch-main {
    background: var(--color-white, #fff);
    min-width: 0;
    /* ระยะจากขอบแผ่นหลัก — คู่กับ padding ในแต่ละแผง */
    padding-bottom: clamp(0.75rem, 2vw, 1.5rem);
}
.cv-edit-stitch-masthead {
    background: linear-gradient(180deg, var(--color-gray-100, #f9fafb) 0%, var(--color-white, #fff) 100%);
}
.cv-edit-stitch-panel {
    padding: clamp(1.35rem, 3.2vw, 2.25rem) clamp(1.25rem, 4.5vw, 3rem);
    border-bottom: 1px solid var(--color-gray-300, #e5e7eb);
}
/* รายการหัวข้อ CV — ไม่ให้การ์ดชิดขอบแผงหลัก */
#cv-sections-container {
    padding-left: clamp(0.75rem, 2.5vw, 1.5rem);
    padding-right: clamp(0.75rem, 2.5vw, 1.5rem);
    padding-bottom: 0.5rem;
}
@media (min-width: 640px) {
    #cv-sections-container {
        padding-left: clamp(1rem, 3vw, 2rem);
        padding-right: clamp(1rem, 3vw, 2rem);
    }
}
@media (min-width: 1024px) {
    #cv-sections-container {
        padding-left: clamp(1.25rem, 3.5vw, 2.5rem);
        padding-right: clamp(1.25rem, 3.5vw, 2.5rem);
    }
}
.cv-section-item .cv-section-head {
    padding-left: clamp(1rem, 2.8vw, 1.75rem);
    padding-right: clamp(1rem, 2.8vw, 1.75rem);
}
.cv-section-item .cv-section-foot {
    padding-left: clamp(1rem, 2.8vw, 1.75rem);
    padding-right: clamp(1rem, 2.8vw, 1.75rem);
}
.cv-section-item .cv-entries-container {
    padding-left: clamp(1rem, 2.8vw, 1.75rem);
    padding-right: clamp(1rem, 2.8vw, 1.75rem);
}
/* รายการ CV — แถวเดียว กะทัดรัด */
.cv-entry-row {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    min-height: 2.75rem;
}
@media (min-width: 640px) {
    .cv-entry-row {
        gap: 0.65rem;
    }
}
.cv-entry-row__body {
    flex: 1 1 0%;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.875rem;
    line-height: 1.45;
    color: var(--color-gray-800, #374151);
}
.cv-entry-row__title-line {
    font-weight: 600;
    color: var(--color-gray-900, #1f2937);
    font-size: 0.875rem;
    line-height: 1.35;
    word-break: break-word;
}
.cv-entry-row__meta-line {
    font-size: 0.8125rem;
    line-height: 1.45;
    color: #64748b;
    word-break: break-word;
}
.cv-entry-row__actions {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    align-self: center;
    gap: 0.35rem;
}
@media (min-width: 640px) {
    .cv-entry-row__actions {
        gap: 0.5rem;
    }
}
.cv-edit-stitch-kicker {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--text-primary, #1e5c32);
}
</style>
<?php if (!empty($cv_photo_supported)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous">
<style>
.cv-photo-crop-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.cv-photo-crop-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.6); }
.cv-photo-crop-modal__box { position: relative; background: #fff; border-radius: 12px; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.cv-photo-crop-modal__header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; flex-shrink: 0; }
.cv-photo-crop-modal__title { margin: 0; font-size: 1.125rem; font-weight: 600; color: #111827; }
.cv-photo-crop-modal__close { background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #6b7280; padding: 0 0.25rem; }
.cv-photo-crop-modal__body { padding: 0; overflow: hidden; flex: 1; min-height: 0; }
.cv-photo-crop-container { width: 100%; height: min(60vh, 500px); background: #000; overflow: hidden; }
.cv-photo-crop-container img { max-width: 100%; display: block; }
.cv-photo-crop-modal__footer { padding: 1rem 1.25rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.75rem; flex-shrink: 0; }
</style>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$cvSections                 = $cv_sections ?? [];
$csrfName                   = csrf_token();
$csrfHash                   = csrf_hash();
$research_sync_configured    = $research_sync_configured ?? false;
$rr_sync_notice              = $rr_sync_notice ?? null;
$rr_last_pull_at             = $rr_last_pull_at ?? null;
$rr_auto_pull_max_age_days   = $rr_auto_pull_max_age_days ?? null;
$cvEditTabs = ['narrative', 'photo', 'orcid', 'sections'];
$tTab = strtolower((string) ($cv_edit_active_tab ?? 'narrative'));
$cvEditActiveTab = in_array($tTab, $cvEditTabs, true) ? $tTab : 'narrative';
?>

<div class="cv-manage-page px-3 sm:px-5 md:px-8 lg:px-10 xl:px-12 py-4 sm:py-6 space-y-4 sm:space-y-6 min-h-[calc(100dvh-11rem)] w-full">

    <?php if (is_array($rr_sync_notice) && ! empty($rr_sync_notice['text'])): ?>
        <div class="rounded-xl px-4 py-3 text-sm border <?= ($rr_sync_notice['type'] ?? '') === 'success' ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-amber-50 text-amber-900 border-amber-200' ?>">
            <p class="font-semibold"><?= esc($rr_sync_notice['text']) ?></p>
            <?php if (! empty($rr_sync_notice['detail'])): ?>
                <p class="mt-1 text-xs opacity-90"><?= esc($rr_sync_notice['detail']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php
    $pNarr = $person ?? [];
    $vBio = old('bio', $pNarr['bio'] ?? '');
    $vEdu = old('education', $pNarr['education'] ?? '');
    $vExp = old('expertise', $pNarr['expertise'] ?? '');
    $narrCvEmail = '';
    if (! empty($pNarr['user_email'])) {
        $narrCvEmail = \App\Libraries\CvProfile::normalizeEmail((string) $pNarr['user_email']);
    } elseif (! empty($pNarr['email'])) {
        $narrCvEmail = \App\Libraries\CvProfile::normalizeEmail((string) $pNarr['email']);
    }
    $narrCvUrl = $narrCvEmail !== '' ? base_url('personnel-cv/' . rawurlencode($narrCvEmail)) : '';

    $cvPubEmail = '';
    if (! empty($pNarr['user_email'])) {
        $cvPubEmail = \App\Libraries\CvProfile::normalizeEmail((string) $pNarr['user_email']);
    } elseif (! empty($pNarr['email'])) {
        $cvPubEmail = \App\Libraries\CvProfile::normalizeEmail((string) $pNarr['email']);
    }

    $tf = trim((string) ($pNarr['user_tf_name'] ?? ''));
    $tl = trim((string) ($pNarr['user_tl_name'] ?? ''));
    $cvEditFullName = trim($tf . ' ' . $tl);
    if ($cvEditFullName === '') {
        $cvEditFullName = trim((string) ($pNarr['name'] ?? ''));
    }
    if ($cvEditFullName === '') {
        $cvEditFullName = 'บุคลากร';
    }
    $cvEditEmailLine = trim((string) ($pNarr['user_email'] ?? ''));
    if ($cvEditEmailLine === '') {
        $cvEditEmailLine = trim((string) ($pNarr['email'] ?? ''));
    }
    $cvEditPosition = trim((string) ($pNarr['position'] ?? ''));
    $cvEditPosDetail = trim((string) ($pNarr['position_detail'] ?? ''));
    if ($cvEditPosDetail !== '') {
        $cvEditPosition = trim($cvEditPosition . ' ' . $cvEditPosDetail);
    }

    $cvPhotoOk = $cv_photo_supported ?? false;
    $cvImgPath = trim((string) ($pNarr['cv_profile_image'] ?? ''));
    $cvImgPreview = '';
    if ($cvImgPath !== '') {
        $cvImgPreview = strpos($cvImgPath, 'http') === 0
            ? $cvImgPath
            : base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $cvImgPath)));
    }
    $cvEmptyPreview = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="112" height="112"><circle cx="56" cy="56" r="56" fill="#e2e8f0"/></svg>');
    ?>
    <div class="cv-edit-stitch-shell">
        <div class="cv-edit-stitch-sheet">
            <?php /* Stitch: CV โปรมืออาชีพ — Personal Details Editor (bf1405903f0e4e2f9c24f4fe9036644a), project 4646948630959592255 */ ?>
            <aside class="cv-edit-stitch-sidebar">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">แก้ไขข้อมูล CV</p>
                <h2 class="mt-1.5 text-lg font-bold text-white leading-snug">รายละเอียดส่วนตัว</h2>
                <div class="mt-5 flex justify-center">
                    <?php if (! $cvPhotoOk): ?>
                        <div class="cv-edit-stitch-sidebar-photo flex items-center justify-center text-[10px] text-slate-400 text-center px-2 leading-snug">
                            รัน migrate เพื่อเปิดรูป CV
                        </div>
                    <?php else: ?>
                        <img id="cv-photo-preview" src="<?= esc($cvImgPreview !== '' ? $cvImgPreview : $cvEmptyPreview, 'attr') ?>" alt="" width="120" height="120"
                             class="cv-edit-stitch-sidebar-photo mx-auto block">
                    <?php endif; ?>
                </div>
                <p class="mt-4 text-center text-sm font-semibold text-white leading-snug px-1"><?= esc($cvEditFullName) ?></p>
                <?php if ($cvEditPosition !== ''): ?>
                    <p class="mt-1 text-center text-xs text-slate-300/95 leading-relaxed px-1"><?= esc($cvEditPosition) ?></p>
                <?php endif; ?>
                <?php if ($cvEditEmailLine !== ''): ?>
                    <p class="mt-2 text-center text-[11px] text-slate-400 break-all px-1"><?= esc($cvEditEmailLine) ?></p>
                <?php endif; ?>

                <nav class="cv-edit-stitch-sidebar-nav mt-7 border-t border-white/10 pt-5 text-sm space-y-0.5" aria-label="สลับส่วนแก้ไข CV (โหลดหน้าใหม่)">
                    <a href="<?= esc(base_url('dashboard/profile/cv?tab=narrative'), 'attr') ?>"
                       class="cv-edit-tab-nav <?= $cvEditActiveTab === 'narrative' ? 'cv-edit-tab-nav--active' : '' ?>">ข้อความบนหน้าสาธารณะ</a>
                    <a href="<?= esc(base_url('dashboard/profile/cv?tab=photo'), 'attr') ?>"
                       class="cv-edit-tab-nav <?= $cvEditActiveTab === 'photo' ? 'cv-edit-tab-nav--active' : '' ?>">รูปประกอบ CV</a>
                    <a href="<?= esc(base_url('dashboard/profile/cv?tab=orcid'), 'attr') ?>"
                       class="cv-edit-tab-nav <?= $cvEditActiveTab === 'orcid' ? 'cv-edit-tab-nav--active' : '' ?>">ORCID</a>
                    <a href="<?= esc(base_url('dashboard/profile/cv?tab=sections'), 'attr') ?>"
                       class="cv-edit-tab-nav <?= $cvEditActiveTab === 'sections' ? 'cv-edit-tab-nav--active' : '' ?>">หัวข้อและรายการ</a>
                </nav>

                <div class="mt-auto pt-8 border-t border-white/10 space-y-2">
                    <a href="<?= base_url('dashboard/profile') ?>"
                       class="flex w-full items-center justify-center rounded-md border border-white/15 bg-white/5 px-3 py-2 text-xs font-medium text-slate-100 hover:bg-white/10 transition-colors">โปรไฟล์</a>
                    <a href="<?= base_url('dashboard') ?>"
                       class="flex w-full items-center justify-center rounded-md border border-white/15 bg-white/5 px-3 py-2 text-xs font-medium text-slate-100 hover:bg-white/10 transition-colors">หน้าหลัก</a>
                    <?php if ($cvPubEmail !== ''): ?>
                        <a href="<?= base_url('personnel-cv/' . rawurlencode($cvPubEmail)) ?>" target="_blank" rel="noopener noreferrer"
                           class="flex w-full items-center justify-center gap-1 rounded-md border border-emerald-400/35 bg-emerald-500/15 px-3 py-2 text-xs font-semibold text-emerald-100 hover:bg-emerald-500/25 transition-colors">
                            ดู CV สาธารณะ
                            <svg class="w-3 h-3 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="cv-edit-stitch-main">
                <header class="cv-edit-stitch-masthead border-b-2 border-secondary-dark px-5 sm:px-8 lg:px-10 xl:px-12 py-5 sm:py-6">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0">
                            <p class="cv-edit-stitch-kicker mb-1">แก้ไขประวัติในระบบ</p>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">จัดการ CV</h1>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed max-w-3xl">แบบเดียวกับ กบศ — หัวข้อกำหนดเอง ลากเรียงได้ รายการมีองค์กร สถานที่ วันที่</p>
                            <?php if ($research_sync_configured): ?>
                                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <?php if ($rr_last_pull_at !== null && $rr_last_pull_at !== ''): ?>
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 shrink-0" aria-hidden="true"></span>
                                            ดึงจาก กบศ ล่าสุด: <strong class="font-medium text-slate-700"><?= esc($rr_last_pull_at) ?></strong>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300 shrink-0" aria-hidden="true"></span>
                                            ยังไม่มีประวัติดึงจาก กบศ ในหน้านี้
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($rr_auto_pull_max_age_days !== null): ?>
                                        <span class="text-slate-400 hidden sm:inline" aria-hidden="true">|</span>
                                        <span>อัตโนมัติเมื่อเกิน <strong class="text-slate-600"><?= (int) $rr_auto_pull_max_age_days ?></strong> วันหลังดึงล่าสุด</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($research_sync_configured): ?>
                            <div class="flex flex-wrap items-stretch gap-2 shrink-0">
                                <form method="post" action="<?= base_url('dashboard/profile/cv/sync-from-rr') ?>" class="inline-flex"
                                      onsubmit="if (!confirm('ดึง CV และผลงานจาก กบศ มาแทนที่ข้อมูลใน ฐานข้อมูลคณะ ตอนนี้?')) return false; var b=this.querySelector('button[type=submit]'); if (b.dataset.busy==='1') return false; b.dataset.busy='1'; b.disabled=true; return true;">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-1.5 text-sm px-4 py-2.5 rounded-[6px] bg-emerald-600 text-white font-semibold shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                                        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        ดึงจาก กบศ ตอนนี้
                                    </button>
                                </form>
                                <a href="<?= base_url('dashboard/profile/research-record-sync') ?>"
                                   class="inline-flex items-center justify-center text-sm px-3 py-2.5 rounded-[6px] border border-slate-200 bg-white text-slate-700 font-medium hover:bg-slate-50 hover:border-slate-300 transition-colors">เปรียบเทียบ / ดึงแบบละเอียด</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </header>

                <section id="cv-edit-narrative" class="cv-edit-stitch-panel <?= $cvEditActiveTab !== 'narrative' ? 'hidden' : '' ?>">
                    <div class="border-b-2 border-secondary-dark pb-2 mb-5">
                        <p class="cv-edit-stitch-kicker mb-1">ข้อความบนหน้า CV สาธารณะ</p>
                        <p class="text-sm text-slate-600">
                            แก้ไขได้เอง — แสดงบน
                            <?php if ($narrCvUrl !== ''): ?>
                                <a href="<?= esc($narrCvUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer" class="text-secondary-dark underline font-medium">หน้า CV สาธารณะ</a>
                            <?php else: ?>
                                <span class="font-medium text-slate-800">หน้า CV สาธารณะ</span>
                            <?php endif; ?>
                            คู่กับรูปและหัวข้อ CV ด้านล่าง
                        </p>
                    </div>
                    <form method="post" action="<?= base_url('dashboard/profile/cv/narrative') ?>" class="space-y-6">
                        <?= csrf_field() ?>
                        <div>
                            <label for="cv-narrative-bio" class="block text-sm font-semibold text-slate-900">การแนะนำข้อมูล (ประวัตินำ)</label>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed max-w-4xl">
                                ใช้แนะนำตัวหรือภาพรวมงานสายวิชาการของท่าน เช่น ความสนใจวิจัย บทบาทในคณะ หรือเป้าหมายการสอน — แนะนำ <strong class="text-slate-800">2–10 ประโยค</strong> อ่านง่าย ไม่ต้องยาวเท่าประวัติเต็ม
                                <span class="text-slate-500">(บนหน้าสาธารณะอยู่หัวข้อ &ldquo;การแนะนำข้อมูล&rdquo;)</span>
                            </p>
                            <textarea id="cv-narrative-bio" name="bio" rows="5" maxlength="20000"
                                      class="mt-2 w-full text-sm border border-slate-200 rounded-[6px] px-3 py-2 focus:ring-2 focus:ring-primary/35 focus:border-secondary"><?= esc($vBio) ?></textarea>
                        </div>
                        <div>
                            <label for="cv-narrative-education" class="block text-sm font-semibold text-slate-900">การศึกษา (ข้อความสรุป)</label>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed max-w-4xl">
                                สำหรับสรุปวุฒิหรือสถาบันแบบย่อ <strong class="text-slate-800">ทีละบรรทัดหรือย่อหน้า</strong>ได้ — ใช้คู่กับตารางหัวข้อ &ldquo;การศึกษา&rdquo; ใน CV ถ้าท่านมีรายการแบบมีโครงสร้างในแท็บด้านล่าง
                            </p>
                            <textarea id="cv-narrative-education" name="education" rows="4" maxlength="20000"
                                      class="mt-2 w-full text-sm border border-slate-200 rounded-[6px] px-3 py-2 focus:ring-2 focus:ring-primary/35 focus:border-secondary"><?= esc($vEdu) ?></textarea>
                        </div>
                        <div>
                            <label for="cv-narrative-expertise" class="block text-sm font-semibold text-slate-900">ความเชี่ยวชาญส่วนบุคคล</label>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed max-w-4xl">
                                ระบุหัวข้อความเชี่ยวชาญหลายข้อได้ — <strong class="text-slate-800">คั่นแต่ละข้อด้วยเครื่องหมายจุลภาค (,)</strong> หรือเซมิโคลอน (;) หรือ顿号 (、) ระบบจะแสดงเป็นแท็กในแถบด้านข้างของ CV สาธารณะ
                                <span class="text-slate-500">ตัวอย่าง: <span class="font-mono text-[11px] bg-slate-100 px-1 rounded">เคมีวิเคราะห์, วัสดุนาโน, การเรียนรู้เชิงลึก</span></span>
                            </p>
                            <textarea id="cv-narrative-expertise" name="expertise" rows="3" maxlength="5000" placeholder="เช่น วิทยาการหลังประกาศ, การประเมินผลการเรียนรู้, ..."
                                      class="mt-2 w-full text-sm border border-slate-200 rounded-[6px] px-3 py-2 focus:ring-2 focus:ring-primary/35 focus:border-secondary"><?= esc($vExp) ?></textarea>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 pt-1 border-t border-slate-100">
                            <button type="submit" class="inline-flex items-center justify-center text-sm px-5 py-2.5 rounded-[6px] bg-secondary hover:bg-secondary-dark text-white font-semibold shadow-sm transition-colors">
                                บันทึกข้อความนี้
                            </button>
                            <p class="text-xs text-slate-500">บันทึกแล้วจะเห็นผลทันทีบนหน้า CV สาธารณะ</p>
                        </div>
                    </form>
                </section>

                <section id="cv-edit-photo" class="cv-edit-stitch-panel <?= $cvEditActiveTab !== 'photo' ? 'hidden' : '' ?>">
                    <div class="border-b-2 border-secondary-dark pb-2 mb-4">
                        <p class="cv-edit-stitch-kicker mb-1">รูปประกอบ CV</p>
                        <p class="text-sm text-slate-600">แสดงเฉพาะหน้า CV สาธารณะ — ไม่เปลี่ยนรูปโปรไฟล์บัญชีผู้ใช้ (เช่นในหน้าอื่นหรือรายการบุคลากร) ตัวอย่างแสดงในแถบด้านซ้าย</p>
                    </div>
                    <div class="space-y-4 min-w-0">
                        <?php if ($cvPhotoOk): ?>
                            <form id="cv-photo-form" action="<?= base_url('dashboard/profile/cv/photo') ?>" method="post" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                                <?= csrf_field() ?>
                                <div class="flex-1 min-w-[200px]">
                                    <label for="cv-photo-file" class="block text-xs font-medium text-slate-600 mb-1">อัปโหลดรูปใหม่</label>
                                    <input type="file" name="image" id="cv-photo-file" accept="image/jpeg,image/png,image/gif,image/webp"
                                           class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-[6px] file:border-0 file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 border border-slate-200 rounded-[6px]">
                                    <p class="text-xs text-slate-500 mt-1">JPG, PNG, GIF หรือ WebP — เลือกแล้วจะเปิดตัดภาพเป็นสี่เหลี่ยมจัตุรัส แล้วอัปโหลดอัตโนมัติ (สูงสุด 20MB)</p>
                                </div>
                                <button type="submit" class="bg-secondary hover:bg-secondary-dark text-white py-2 px-5 rounded-[6px] text-sm font-semibold transition shrink-0">
                                    บันทึกรูป
                                </button>
                            </form>
                            <?php if ($cvImgPath !== ''): ?>
                                <form action="<?= base_url('dashboard/profile/cv/photo/remove') ?>" method="post" onsubmit="return confirm('ลบรูปประกอบ CV นี้?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 underline">
                                        ลบรูป CV
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-sm text-amber-800">คอลัมน์ <code class="text-xs bg-amber-100 px-1 rounded">cv_profile_image</code> ยังไม่มีในฐานข้อมูล — รัน <code class="text-xs bg-amber-100 px-1 rounded">php spark migrate</code></p>
                        <?php endif; ?>
                    </div>
                </section>

    <?php if (!empty($cvPhotoOk)): ?>
    <div id="cv-photo-crop-modal" class="cv-photo-crop-modal" role="dialog" aria-modal="true" aria-labelledby="cv-photo-crop-title" style="display: none;">
        <div class="cv-photo-crop-modal__backdrop"></div>
        <div class="cv-photo-crop-modal__box">
            <div class="cv-photo-crop-modal__header">
                <h3 id="cv-photo-crop-title" class="cv-photo-crop-modal__title">ตัดรูปโปรไฟล์ CV (สี่เหลี่ยมจัตุรัส)</h3>
                <button type="button" class="cv-photo-crop-modal__close" id="cvPhotoCropClose" aria-label="ปิด">×</button>
            </div>
            <div class="cv-photo-crop-modal__body">
                <div class="cv-photo-crop-container">
                    <img id="cv-photo-crop-image" src="" alt="">
                </div>
            </div>
            <div class="cv-photo-crop-modal__footer">
                <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium" id="cvPhotoCropCancel">ยกเลิก</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold" id="cvPhotoCropConfirm">ตัดและอัปโหลด</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

                <section id="cv-edit-orcid" class="cv-edit-stitch-panel <?= $cvEditActiveTab !== 'orcid' ? 'hidden' : '' ?>">
                    <div class="border-b-2 border-secondary-dark pb-2 mb-4">
                        <p class="cv-edit-stitch-kicker mb-1">ORCID</p>
                        <p class="text-sm text-slate-600">ดึงรายการที่ตั้งเป็น<strong class="text-slate-800">สาธารณะ</strong>จาก <a href="https://orcid.org" target="_blank" rel="noopener noreferrer" class="text-secondary-dark underline">orcid.org</a> แล้วเพิ่ม/อัปเดตใน CV — เลือกประเภทด้านล่าง (จับคู่รายการเดิมด้วย <code class="text-xs bg-slate-100 px-1 rounded border border-slate-200">put-code</code> หรือคีย์สำรองเมื่อไม่มี put-code)</p>
                    </div>
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-700">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="cv-scope-education" class="rounded border-slate-300 text-secondary focus:ring-secondary" checked>
                                การศึกษา
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="cv-scope-employment" class="rounded border-slate-300 text-secondary focus:ring-secondary" checked>
                                การจ้างงาน
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="cv-scope-works" class="rounded border-slate-300 text-secondary focus:ring-secondary" checked>
                                ผลงานตีพิมพ์ (works)
                            </label>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                            <div class="flex-1 min-w-[220px]">
                                <label for="cv-orcid-input" class="block text-xs font-medium text-slate-600 mb-1">ORCID iD</label>
                                <input type="text" id="cv-orcid-input" maxlength="19" placeholder="0000-0002-1825-0097"
                                       value="<?= esc($person['orcid_id'] ?? '') ?>"
                                       class="w-full text-sm border border-slate-200 rounded-[6px] px-3 py-2 font-mono focus:ring-2 focus:ring-primary/35 focus:border-secondary">
                            </div>
                            <button type="button" id="cv-orcid-import-btn" onclick="importOrcidCv()"
                                    class="bg-slate-800 hover:bg-slate-900 text-white py-2 px-5 rounded-[6px] text-sm font-semibold transition shrink-0">
                                นำเข้าจาก ORCID
                            </button>
                        </div>
                    </div>
                </section>

                <div id="cv-edit-sections" class="<?= $cvEditActiveTab !== 'sections' ? 'hidden' : '' ?>">
                <section class="cv-edit-stitch-panel">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-5 pb-4 border-b border-slate-100">
                        <div>
                            <p class="cv-edit-stitch-kicker mb-1">สรุป</p>
                            <div class="flex gap-6 mt-1 text-sm text-slate-700">
                                <span><strong class="text-slate-900"><?= count($cvSections) ?></strong> หัวข้อ</span>
                                <?php
                                $totalE = 0;
                                foreach ($cvSections as $sx) {
                                    $totalE += count($sx['entries'] ?? []);
                                }
                                ?>
                                <span><strong class="text-slate-900"><?= $totalE ?></strong> รายการ</span>
                            </div>
                        </div>
                    </div>
                    <form action="<?= base_url('dashboard/profile/cv/section/save') ?>" method="post" class="flex flex-wrap items-end gap-3">
                        <?= csrf_field() ?>
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-medium text-slate-600 mb-1">ชื่อหัวข้อ</label>
                            <input type="text" name="title" required maxlength="255" placeholder="เช่น ประวัติการศึกษา งานวิจัย วิทยากร..."
                                   class="w-full text-sm border border-slate-200 rounded-[6px] px-3 py-2 focus:ring-2 focus:ring-primary/35 focus:border-secondary">
                        </div>
                        <div class="min-w-[160px]">
                            <label class="block text-xs font-medium text-slate-600 mb-1">ประเภท</label>
                            <select name="type" class="w-full text-sm border border-slate-200 rounded-[6px] px-3 py-2 focus:ring-2 focus:ring-primary/35 focus:border-secondary">
                                <option value="custom">กำหนดเอง</option>
                                <option value="education">การศึกษา</option>
                                <option value="work">งาน / ตำแหน่ง</option>
                                <option value="experience">ประสบการณ์</option>
                                <option value="research">งานวิจัย</option>
                                <option value="articles">บทความ</option>
                                <option value="courses">รายวิชา</option>
                                <option value="service">บริการวิชาการ</option>
                                <option value="funding">ทุน</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-secondary hover:bg-secondary-dark text-white py-2 px-5 rounded-[6px] text-sm font-semibold transition">
                            + เพิ่มหัวข้อ
                        </button>
                    </form>
                </section>

    <?php if ($cvSections === []): ?>
        <div class="cv-edit-stitch-panel rounded-none border-dashed border-slate-200 bg-slate-50/40 py-14 text-center text-slate-500">
            <p class="text-lg font-medium text-slate-700">ยังไม่มีหัวข้อ</p>
            <p class="text-sm mt-2">สร้างหัวข้อด้านบน แล้วเพิ่มรายการในแต่ละหัวข้อ</p>
        </div>
    <?php else: ?>
        <div id="cv-sections-container" class="space-y-4 pt-3 sm:pt-4">
            <?php
            $canReorderSections = count($cvSections) > 1;
            foreach ($cvSections as $section):
                $sid = (int) ($section['id'] ?? 0);
                $entries = $section['entries'] ?? [];
                $canReorderEntries = count($entries) > 1;
                $visPub = !empty($section['visible_on_public']);
                $sectionType = (string) ($section['type'] ?? '');
                $showPublicationType = in_array($sectionType, ['research', 'articles'], true);
            ?>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden cv-section-item"
                     data-section-id="<?= $sid ?>"
                     data-show-pub="<?= $showPublicationType ? '1' : '0' ?>"
                     data-section-type="<?= esc($sectionType, 'attr') ?>"
                     data-section-title="<?= esc($section['title'] ?? '', 'attr') ?>">
                    <div class="cv-section-head flex items-center justify-between py-4 sm:py-5 bg-gradient-to-r from-yellow-50 to-white border-b border-gray-100 cursor-pointer hover:from-yellow-100/50 transition"
                         onclick="toggleCvSection(<?= $sid ?>)">
                        <div class="flex items-center gap-3 min-w-0">
                            <?php if ($canReorderSections): ?>
                            <span class="cv-section-handle cursor-grab text-gray-400 hover:text-gray-600 text-lg select-none" onclick="event.stopPropagation()" title="ลากเพื่อเรียงหัวข้อ">⋮⋮</span>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <span class="font-semibold text-gray-900 text-lg"><?= esc($section['title'] ?? '') ?></span>
                                <span class="text-sm text-gray-500 ml-2">(<?= count($entries) ?> รายการ)</span>
                                <?php if (!$visPub): ?>
                                    <span class="ml-2 text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">ซ่อนจากหน้าสาธารณะ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0" onclick="event.stopPropagation()">
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white/80 px-2 py-1"
                                 title="<?= $visPub ? 'ซ่อนหัวข้อทั้งก้อนจาก CV สาธารณะ และปิดการแสดงสาธารณะของรายการย่อยทุกรายการในหัวข้อนี้' : 'แสดงหัวข้อนี้ใน CV สาธารณะ (รายการย่อยยังต้องเปิดทีละรายการ)' ?>">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 hidden sm:inline">สาธารณะ</span>
                                <button type="button" role="switch" aria-checked="<?= $visPub ? 'true' : 'false' ?>"
                                        aria-label="<?= $visPub ? 'ปิดการแสดงหัวข้อนี้ในหน้าสาธารณะ' : 'เปิดการแสดงหัวข้อนี้ในหน้าสาธารณะ' ?>"
                                        onclick="toggleSectionPublic(<?= $sid ?>, this)"
                                        class="cv-list-vis-switch relative h-7 w-12 shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-yellow-500 <?= $visPub ? 'bg-emerald-500' : 'bg-gray-300' ?>">
                                    <span class="pointer-events-none absolute top-[3px] left-[3px] h-[1.125rem] w-[1.125rem] rounded-full bg-white shadow transition-transform duration-200 ease-out <?= $visPub ? 'translate-x-[1.35rem]' : 'translate-x-0' ?>"></span>
                                </button>
                            </div>
                            <button type="button" onclick="deleteCvSection(<?= $sid ?>, '<?= esc($section['title'] ?? '', 'js') ?>')"
                                    class="text-red-500 hover:text-red-700 text-sm px-2 py-1 rounded-lg hover:bg-red-50">ลบหัวข้อ</button>
                            <span class="text-gray-400 text-lg transition-transform duration-200" id="cv-toggle-<?= $sid ?>">▼</span>
                        </div>
                    </div>

                    <div class="hidden" id="cv-content-<?= $sid ?>">
                        <div class="cv-entries-container py-5 sm:py-6 border-b border-gray-100" id="cv-entries-<?= $sid ?>" data-section-id="<?= $sid ?>">
                            <?php if ($entries !== []): ?>
                                <div class="space-y-1.5">
                                    <?php foreach ($entries as $entry):
                                        $eid    = (int) ($entry['id'] ?? 0);
                                        $eVisPub = !empty($entry['visible_on_public']);
                                        $ePubRaw = (string) ($entry['metadata_array']['rr_publication_type'] ?? '');
                                        $ePubLbl = $ePubRaw !== '' ? \App\Libraries\RrPublicationType::labelTh($ePubRaw) : '';
                                        $eUrl = $entry['metadata_array']['url'] ?? $entry['metadata_array']['legacy_url'] ?? '';
                                        $eUrl = is_string($eUrl) ? trim($eUrl) : '';
                                        $dateSpanBe = cv_format_entry_date_span_be(
                                            ! empty($entry['start_date']) ? (string) $entry['start_date'] : null,
                                            ! empty($entry['end_date']) ? (string) $entry['end_date'] : null,
                                            (int) ($entry['is_current'] ?? 0)
                                        );
                                        $metaBits = [];
                                        if (! empty($entry['organization'])) {
                                            $metaBits[] = (string) $entry['organization'];
                                        }
                                        if (! empty($entry['location'])) {
                                            $metaBits[] = (string) $entry['location'];
                                        }
                                        if ($dateSpanBe !== '') {
                                            $metaBits[] = $dateSpanBe;
                                        }
                                        if ($ePubLbl !== '') {
                                            $metaBits[] = $ePubLbl;
                                        }
                                        $descFlat = preg_replace('/\s+/u', ' ', trim((string) ($entry['description'] ?? '')));
                                        if ($descFlat !== '') {
                                            $metaBits[] = function_exists('mb_strlen') && mb_strlen($descFlat) > 180
                                                ? mb_substr($descFlat, 0, 180) . '…'
                                                : $descFlat;
                                        }
                                        $metaLine = implode(' · ', $metaBits);
                                        $rowTitleAttr = trim(($entry['title'] ?? '') . ($metaLine !== '' ? ' — ' . $metaLine : ''));
                                    ?>
                                        <div class="entry-card cv-entry-row cv-entry-item rounded-lg border border-slate-200/80 bg-white px-2 py-1.5 sm:px-3 sm:py-2 shadow-sm hover:border-slate-300 hover:bg-slate-50/80 transition-colors" data-entry-id="<?= $eid ?>">
                                            <?php if ($canReorderEntries): ?>
                                            <span class="cv-entry-handle cursor-grab text-slate-400 hover:text-slate-600 select-none text-sm leading-none px-0.5 shrink-0 mt-0.5" onclick="event.stopPropagation()" title="ลากเพื่อเรียงรายการ">⋮⋮</span>
                                            <?php endif; ?>
                                            <div class="cv-entry-row__body" title="<?= esc($rowTitleAttr, 'attr') ?>">
                                                <div class="cv-entry-row__title-line"><?= esc($entry['title'] ?? '') ?></div>
                                                <?php if ($metaLine !== ''): ?>
                                                <div class="cv-entry-row__meta-line"><?= esc($metaLine) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($eUrl !== ''): ?>
                                                <a href="<?= esc($eUrl, 'attr') ?>" class="shrink-0 self-center text-secondary hover:text-secondary-dark p-1 rounded hover:bg-primary-light/80" target="_blank" rel="noopener noreferrer" title="เปิดลิงก์" aria-label="เปิดลิงก์" onclick="event.stopPropagation()">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            <?php endif; ?>
                                            <div class="cv-entry-row__actions" onclick="event.stopPropagation()">
                                                <div class="flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5"
                                                     title="<?= $eVisPub ? 'คลิกเพื่อซ่อนรายการนี้จาก CV สาธารณะ' : 'คลิกเพื่อแสดงรายการนี้ใน CV สาธารณะ' ?>">
                                                    <span class="text-[9px] font-semibold uppercase tracking-wide text-slate-500 hidden sm:inline">สาธารณะ</span>
                                                    <button type="button" role="switch" aria-checked="<?= $eVisPub ? 'true' : 'false' ?>"
                                                            aria-label="<?= $eVisPub ? 'ปิดการแสดงรายการนี้ในหน้าสาธารณะ' : 'เปิดการแสดงรายการนี้ในหน้าสาธารณะ' ?>"
                                                            onclick="toggleEntryPublic(<?= $eid ?>, this)"
                                                            class="cv-list-vis-switch relative h-6 w-10 shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-slate-400 <?= $eVisPub ? 'bg-emerald-500' : 'bg-slate-300' ?>">
                                                        <span class="pointer-events-none absolute top-[2px] left-[2px] h-[1.125rem] w-[1.125rem] rounded-full bg-white shadow transition-transform duration-200 ease-out <?= $eVisPub ? 'translate-x-[1.1rem]' : 'translate-x-0' ?>"></span>
                                                    </button>
                                                </div>
                                                <button type="button" onclick="editCvEntry(<?= $sid ?>, <?= $eid ?>)"
                                                        class="text-xs px-2 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">แก้ไข</button>
                                                <button type="button" onclick="deleteCvEntry(<?= $eid ?>, '<?= esc($entry['title'] ?? '', 'js') ?>')"
                                                        class="text-xs px-2 py-1 rounded-md border border-red-200/90 text-red-600 hover:bg-red-50">ลบ</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-400 text-center py-6">ยังไม่มีรายการ</p>
                            <?php endif; ?>
                        </div>

                        <div class="cv-section-foot py-4 sm:py-5 border-t border-gray-100 bg-gray-50/80 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-gray-600">เพิ่มหรือแก้ไขรายการในหัวข้อนี้</p>
                            <button type="button"
                                    onclick="openCvEntryModal(<?= $sid ?>)"
                                    class="inline-flex items-center justify-center gap-1.5 text-sm px-4 py-2.5 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold shadow-sm transition-colors">
                                <span aria-hidden="true">+</span> เพิ่มรายการ
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <?php if (($cv_sections ?? []) !== []): ?>
    <div id="cv-entry-modal" class="fixed inset-0 z-[6000] hidden flex flex-col items-stretch justify-start sm:justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-[2px] p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="cv-entry-modal-title">
        <div class="cv-entry-modal__panel relative bg-white overflow-hidden flex flex-col min-h-0 w-full max-w-[56rem] mx-auto shrink-0" onclick="event.stopPropagation()">
            <button type="button" id="cv-entry-modal-close" class="absolute top-3 right-3 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-600 text-xl leading-none shadow-md ring-1 ring-slate-200/90 hover:bg-slate-50 hover:text-slate-900 transition-colors" aria-label="ปิด">&times;</button>

            <form id="cv-entry-modal-form" class="flex flex-col flex-1 min-h-0 font-sarabun" action="<?= base_url('dashboard/profile/cv/entry/save') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="section_id" id="cv-m-section-id" value="">
                <input type="hidden" name="entry_id" id="cv-m-entry-id" value="">
                <input type="hidden" name="entry_sort_order" id="cv-m-entry-sort" value="0">
                <input type="hidden" name="extra_info" value="">
                <input type="hidden" name="funding_amount" value="">
                <input type="hidden" name="is_current" id="cv-m-current" value="0">

                <div class="flex flex-col flex-1 min-h-0">
                    <div class="cv-entry-modal__body flex-1 min-w-0 bg-white px-5 py-6 pt-12 sm:px-8 sm:py-8 sm:pt-12">
                        <div class="cv-entry-modal__meta mb-6 pb-6 border-b border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">รายการบน CV สาธารณะ</p>
                            <h2 id="cv-entry-modal-title" class="mt-1.5 text-lg sm:text-xl font-bold text-slate-900 leading-snug tracking-tight">รายการ CV</h2>
                            <p id="cv-entry-modal-sub" class="mt-2 text-sm text-slate-600 leading-relaxed"></p>
                            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">การแสดงผล</p>
                            <div class="cv-entry-modal__display-row mt-2">
                                <label class="cv-entry-modal__vis-toggle flex flex-1 min-w-0 items-center justify-between gap-3 cursor-pointer select-none sm:max-w-md">
                                    <span class="text-sm text-slate-700">แสดงในหน้าสาธารณะ</span>
                                    <input type="checkbox" name="visible_on_public" value="1" checked id="cv-m-vis" class="peer sr-only">
                                    <span class="relative inline-block h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-white after:absolute after:left-[3px] after:top-[3px] after:h-[18px] after:w-[18px] after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 after:content-[''] peer-checked:after:translate-x-[1.25rem]" aria-hidden="true"></span>
                                </label>
                            </div>
                        </div>

                        <div class="cv-entry-modal__headstrip">
                            <p class="cv-edit-stitch-kicker">รายละเอียดในตาราง CV</p>
                            <p class="text-sm text-slate-600 leading-relaxed">จัดเรียงเหมือนหน้า <strong class="text-slate-800 font-semibold">ดู CV</strong> — หัวข้อ, หน่วยงาน/สถานที่, ช่วงเวลา, รายละเอียด และลิงก์</p>
                        </div>

                        <div class="cv-entry-modal__formstack">
                            <div>
                                <label for="cv-m-title" class="cv-edit-modal-label">หัวข้อรายการ <span class="text-red-600 normal-case tracking-normal font-semibold">*</span></label>
                                <input type="text" name="entry_title" required maxlength="500" id="cv-m-title" class="cv-edit-modal-input" placeholder="ชื่อรายการที่แสดงในคอลัมน์หัวข้อ">
                            </div>
                            <div class="cv-entry-modal__grid2">
                                <div>
                                    <label for="cv-m-org" class="cv-edit-modal-label">หน่วยงาน / องค์กร</label>
                                    <input type="text" name="organization" maxlength="500" id="cv-m-org" class="cv-edit-modal-input" placeholder="องค์กร / สถาบัน">
                                </div>
                                <div>
                                    <label for="cv-m-loc" class="cv-edit-modal-label">สถานที่</label>
                                    <input type="text" name="location" maxlength="500" id="cv-m-loc" class="cv-edit-modal-input" placeholder="เมือง ประเทศ ฯลฯ">
                                </div>
                            </div>
                            <div class="cv-entry-modal__grid2">
                                <div>
                                    <label for="cv-m-start" class="cv-edit-modal-label">วันเริ่ม</label>
                                    <input type="date" name="start_date" id="cv-m-start" class="cv-edit-modal-input">
                                </div>
                                <div>
                                    <label for="cv-m-end" class="cv-edit-modal-label">วันสิ้นสุด</label>
                                    <input type="date" name="end_date" id="cv-m-end" class="cv-edit-modal-input">
                                </div>
                            </div>
                            <div id="cv-m-pubtype-wrap" class="hidden rounded-lg border border-slate-200 bg-slate-50 p-3 sm:p-4">
                                <label for="cv-m-pubtype" class="cv-edit-modal-label">ประเภทผลงานเผยแพร่</label>
                                <p class="text-xs text-slate-600 mb-2 leading-relaxed">สำหรับหัวข้อ <strong class="text-slate-800">งานวิจัย / บทความ</strong> — รหัสเดียวกับ กบศ (<code class="text-[11px] bg-white px-1.5 py-0.5 rounded border border-slate-200 font-mono">publication_type</code>)</p>
                                <select name="publication_type" id="cv-m-pubtype" disabled class="cv-edit-modal-select disabled:bg-slate-100 disabled:text-slate-500">
                                    <option value="">— ไม่ระบุ —</option>
                                    <?php foreach (\App\Libraries\RrPublicationType::selectOptionGroups() as $groupLabel => $groupOpts): ?>
                                        <optgroup label="<?= esc($groupLabel) ?>">
                                            <?php foreach ($groupOpts as $pubOpt): ?>
                                                <option value="<?= esc($pubOpt['value']) ?>"><?= esc($pubOpt['label']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="cv-m-url" class="cv-edit-modal-label">ลิงก์ (URL)</label>
                                <input type="text" name="entry_url" maxlength="2048" id="cv-m-url" class="cv-edit-modal-input" placeholder="https://...">
                            </div>
                            <div>
                                <label for="cv-m-desc" class="cv-edit-modal-label">รายละเอียด</label>
                                <textarea name="entry_description" rows="4" id="cv-m-desc" class="cv-edit-modal-textarea" placeholder="ข้อความในคอลัมน์รายละเอียดบนหน้า CV"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cv-entry-modal__footer flex flex-wrap items-center justify-end gap-2 sm:gap-3 shrink-0">
                    <button type="button" id="cv-entry-modal-cancel" class="text-sm px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium hover:bg-slate-50 transition-colors min-w-[5.5rem]">ยกเลิก</button>
                    <button type="submit" class="text-sm px-5 py-2.5 rounded-lg bg-secondary hover:bg-secondary-dark text-white font-semibold shadow-sm transition-colors min-w-[6.5rem]">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.sortable-ghost { opacity: 0.45; background: #fef9c3; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<?php if (!empty($cv_photo_supported)): ?>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    var fileInput = document.getElementById('cv-photo-file');
    var form = document.getElementById('cv-photo-form');
    var previewImg = document.getElementById('cv-photo-preview');
    var modal = document.getElementById('cv-photo-crop-modal');
    var cropImg = document.getElementById('cv-photo-crop-image');
    var cropper = null;
    var objectUrl = null;

    function closeModal(clearFile) {
        if (modal) modal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        if (clearFile && fileInput) fileInput.value = '';
    }

    function openModal(file) {
        if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/i)) return;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        cropImg.src = objectUrl;
        if (modal) modal.style.display = 'flex';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        setTimeout(function () {
            if (typeof Cropper !== 'undefined' && cropImg) {
                cropper = new Cropper(cropImg, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true
                });
            }
        }, 100);
    }

    function applyAndSubmit() {
        if (!cropper || !fileInput || !form) return;
        cropper.getCroppedCanvas({
            width: 800,
            height: 800,
            maxWidth: 1024,
            maxHeight: 1024,
            imageSmoothingQuality: 'high'
        }).toBlob(function (blob) {
            if (!blob) return;
            var dt = new DataTransfer();
            dt.items.add(new File([blob], 'cv-profile.jpg', { type: 'image/jpeg' }));
            fileInput.files = dt.files;
            if (previewImg) {
                previewImg.src = URL.createObjectURL(blob);
            }
            closeModal(false);
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }, 'image/jpeg', 0.92);
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var f = this.files[0];
            if (f && f.type.match(/^image\//)) openModal(f);
            else if (f) this.value = '';
        });
    }
    var c = document.getElementById('cvPhotoCropClose');
    var x = document.getElementById('cvPhotoCropCancel');
    var ok = document.getElementById('cvPhotoCropConfirm');
    if (c) c.addEventListener('click', function () { closeModal(true); });
    if (x) x.addEventListener('click', function () { closeModal(true); });
    if (ok) ok.addEventListener('click', applyAndSubmit);
    if (modal && modal.querySelector('.cv-photo-crop-modal__backdrop')) {
        modal.querySelector('.cv-photo-crop-modal__backdrop').addEventListener('click', function () { closeModal(true); });
    }
})();
</script>
<?php endif; ?>
<script>
(function () {
    window.CV_CSRF = { name: '<?= esc($csrfName, 'js') ?>', hash: '<?= esc($csrfHash, 'js') ?>' };

    function csrfBody(extra) {
        var p = new URLSearchParams();
        p.append(CV_CSRF.name, CV_CSRF.hash);
        if (extra) {
            Object.keys(extra).forEach(function (k) { p.append(k, extra[k]); });
        }
        return p;
    }

    window.toggleCvSection = function (id) {
        var c = document.getElementById('cv-content-' + id);
        var t = document.getElementById('cv-toggle-' + id);
        if (!c || !t) return;
        c.classList.toggle('hidden');
        t.textContent = c.classList.contains('hidden') ? '▼' : '▲';
    };

    var CV_SECTION_TYPE_LABELS = {
        research: 'งานวิจัย', articles: 'บทความ', education: 'การศึกษา', work: 'งาน / ตำแหน่ง',
        experience: 'ประสบการณ์', courses: 'รายวิชา', service: 'บริการวิชาการ', funding: 'ทุน', custom: 'กำหนดเอง'
    };

    var pubSelectInitialHtml = null;

    function cvSectionRow(sectionId) {
        return document.querySelector('.cv-section-item[data-section-id="' + sectionId + '"]');
    }

    function setPublicationFieldVisible(show) {
        var wrap = document.getElementById('cv-m-pubtype-wrap');
        var sel = document.getElementById('cv-m-pubtype');
        if (!wrap || !sel) return;
        if (show) {
            wrap.classList.remove('hidden');
            sel.disabled = false;
        } else {
            wrap.classList.add('hidden');
            sel.disabled = true;
            sel.value = '';
        }
    }

    function resetPublicationSelectOptions() {
        var sel = document.getElementById('cv-m-pubtype');
        if (sel && pubSelectInitialHtml !== null) sel.innerHTML = pubSelectInitialHtml;
    }

    function resetCvEntryModalForm() {
        document.getElementById('cv-m-entry-id').value = '';
        document.getElementById('cv-m-entry-sort').value = '0';
        document.getElementById('cv-m-title').value = '';
        document.getElementById('cv-m-org').value = '';
        document.getElementById('cv-m-loc').value = '';
        document.getElementById('cv-m-start').value = '';
        document.getElementById('cv-m-end').value = '';
        document.getElementById('cv-m-url').value = '';
        document.getElementById('cv-m-desc').value = '';
        document.getElementById('cv-m-current').value = '0';
        document.getElementById('cv-m-vis').checked = true;
    }

    function openCvEntryModalShell(sectionId) {
        var row = cvSectionRow(sectionId);
        var showPub = row && row.dataset.showPub === '1';
        var st = row ? (row.dataset.sectionType || '') : '';
        var stitle = row ? (row.dataset.sectionTitle || '') : '';
        var typeLabel = CV_SECTION_TYPE_LABELS[st] || st || '';
        document.getElementById('cv-m-section-id').value = String(sectionId);
        setPublicationFieldVisible(showPub);
        var sub = document.getElementById('cv-entry-modal-sub');
        if (sub) {
            sub.textContent = (stitle ? 'หัวข้อ: ' + stitle : '') + (typeLabel ? ' · ประเภท: ' + typeLabel : '');
        }
        var m = document.getElementById('cv-entry-modal');
        if (m) {
            m.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    window.closeCvEntryModal = function () {
        var m = document.getElementById('cv-entry-modal');
        if (!m) return;
        m.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    window.openCvEntryModal = function (sectionId) {
        resetPublicationSelectOptions();
        resetCvEntryModalForm();
        document.getElementById('cv-entry-modal-title').textContent = 'เพิ่มรายการ';
        openCvEntryModalShell(sectionId);
        document.getElementById('cv-m-title').focus();
    };

    window.editCvEntry = async function (sectionId, entryId) {
        var url = '<?= base_url('dashboard/profile/cv/entry') ?>/' + entryId;
        var res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        var data = await res.json();
        if (!data.success || !data.entry) {
            if (typeof swalAlert === 'function') swalAlert(data.message || 'โหลดไม่สำเร็จ', 'error');
            return;
        }
        resetPublicationSelectOptions();
        resetCvEntryModalForm();
        document.getElementById('cv-entry-modal-title').textContent = 'แก้ไขรายการ';
        openCvEntryModalShell(sectionId);
        var e = data.entry;
        document.getElementById('cv-m-entry-id').value = e.id;
        document.getElementById('cv-m-entry-sort').value = e.sort_order || 0;
        document.getElementById('cv-m-title').value = e.title || '';
        document.getElementById('cv-m-org').value = e.organization || '';
        document.getElementById('cv-m-loc').value = e.location || '';
        document.getElementById('cv-m-start').value = (e.start_date || '').substring(0, 10);
        document.getElementById('cv-m-end').value = (e.end_date || '').substring(0, 10);
        document.getElementById('cv-m-url').value = e.entry_url || (e.metadata_array && e.metadata_array.url) || (e.metadata_array && e.metadata_array.legacy_url) || '';
        document.getElementById('cv-m-desc').value = e.description || '';
        document.getElementById('cv-m-current').value = String(e.is_current) === '1' ? '1' : '0';
        document.getElementById('cv-m-vis').checked = String(e.visible_on_public) !== '0';
        var pubSel = document.getElementById('cv-m-pubtype');
        var row = cvSectionRow(sectionId);
        var showPub = row && row.dataset.showPub === '1';
        setPublicationFieldVisible(showPub);
        if (pubSel && showPub) {
            var pv = (e.publication_type || (e.metadata_array && e.metadata_array.rr_publication_type) || '').toString();
            if (pv && !Array.from(pubSel.options).some(function (o) { return o.value === pv; })) {
                var opt = document.createElement('option');
                opt.value = pv;
                opt.textContent = 'จาก กบศ / ระบบ: ' + pv;
                opt.setAttribute('data-temp-option', '1');
                pubSel.appendChild(opt);
            }
            pubSel.value = pv;
        }
        document.getElementById('cv-m-title').focus();
    };

    window.deleteCvEntry = async function (entryId, title) {
        var ok = true;
        if (typeof swalConfirm === 'function') {
            ok = await swalConfirm({ title: 'ลบรายการ?', text: title, confirmText: 'ลบ', cancelText: 'ยกเลิก' });
        } else {
            ok = window.confirm('ลบ "' + title + '"?');
        }
        if (!ok) return;
        var p = csrfBody();
        await fetch('<?= base_url('dashboard/profile/cv/entry/delete') ?>/' + entryId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: p.toString()
        });
        location.reload();
    };

    window.deleteCvSection = async function (sectionId, title) {
        var ok = true;
        if (typeof swalConfirm === 'function') {
            ok = await swalConfirm({ title: 'ลบหัวข้อและรายการทั้งหมด?', text: title, confirmText: 'ลบทั้งหมด', cancelText: 'ยกเลิก' });
        } else {
            ok = window.confirm('ลบหัวข้อ "' + title + '" และรายการทั้งหมด?');
        }
        if (!ok) return;
        var p = csrfBody();
        await fetch('<?= base_url('dashboard/profile/cv/section/delete') ?>/' + sectionId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: p.toString()
        });
        location.reload();
    };

    window.toggleSectionPublic = async function (sectionId, el) {
        if (el) el.disabled = true;
        try {
            var p = csrfBody();
            var res = await fetch('<?= base_url('dashboard/profile/cv/section/toggle') ?>/' + sectionId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: p.toString()
            });
            var data = await res.json().catch(function () { return {}; });
            if (data.success) {
                location.reload();
                return;
            }
            if (typeof swalAlert === 'function') swalAlert(data.message || 'ผิดพลาด', 'error');
        } finally {
            if (el) el.disabled = false;
        }
    };

    window.toggleEntryPublic = async function (entryId, el) {
        if (el) el.disabled = true;
        try {
            var p = csrfBody();
            var res = await fetch('<?= base_url('dashboard/profile/cv/entry/toggle') ?>/' + entryId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: p.toString()
            });
            var data = await res.json().catch(function () { return {}; });
            if (data.success) {
                location.reload();
                return;
            }
            if (typeof swalAlert === 'function') swalAlert(data.message || 'ผิดพลาด', 'error');
        } finally {
            if (el) el.disabled = false;
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var sc = document.getElementById('cv-sections-container');
        if (sc && typeof Sortable !== 'undefined' && sc.querySelectorAll('.cv-section-item').length >= 2) {
            new Sortable(sc, {
                animation: 150,
                handle: '.cv-section-handle',
                ghostClass: 'sortable-ghost',
                onEnd: async function () {
                    var order = Array.from(sc.querySelectorAll('.cv-section-item')).map(function (el, i) {
                        return { id: el.dataset.sectionId, order: i };
                    });
                    var p = csrfBody({ order: JSON.stringify(order) });
                    await fetch('<?= base_url('dashboard/profile/cv/section/reorder') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: p.toString()
                    });
                }
            });
        }

        document.querySelectorAll('.cv-entries-container').forEach(function (container) {
            if (typeof Sortable === 'undefined') return;
            if (container.querySelectorAll('.cv-entry-item').length < 2) return;
            new Sortable(container, {
                animation: 150,
                handle: '.cv-entry-handle',
                ghostClass: 'sortable-ghost',
                onEnd: async function () {
                    var sectionId = container.dataset.sectionId;
                    var order = Array.from(container.querySelectorAll('.cv-entry-item')).map(function (el, i) {
                        return { id: el.dataset.entryId, order: i };
                    });
                    var p = csrfBody({ section_id: sectionId, order: JSON.stringify(order) });
                    await fetch('<?= base_url('dashboard/profile/cv/entry/reorder') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: p.toString()
                    });
                }
            });
        });

        var pubSelInit = document.getElementById('cv-m-pubtype');
        if (pubSelInit) pubSelectInitialHtml = pubSelInit.innerHTML;

        var entryModal = document.getElementById('cv-entry-modal');
        var entryForm = document.getElementById('cv-entry-modal-form');
        if (entryModal) {
            entryModal.addEventListener('click', function (ev) {
                if (ev.target === entryModal) closeCvEntryModal();
            });
        }
        var btnClose = document.getElementById('cv-entry-modal-close');
        var btnCancel = document.getElementById('cv-entry-modal-cancel');
        if (btnClose) btnClose.addEventListener('click', closeCvEntryModal);
        if (btnCancel) btnCancel.addEventListener('click', closeCvEntryModal);
        document.addEventListener('keydown', function (ev) {
            if (ev.key !== 'Escape') return;
            var m = document.getElementById('cv-entry-modal');
            if (m && !m.classList.contains('hidden')) closeCvEntryModal();
        });

        if (entryForm) {
            entryForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                var fd = new FormData(entryForm);
                var res = await fetch(entryForm.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                var data = await res.json();
                if (data.success) location.reload();
                else if (typeof swalAlert === 'function') swalAlert(data.message || 'บันทึกไม่สำเร็จ', 'error');
                else alert(data.message || 'บันทึกไม่สำเร็จ');
            });
        }
    });

    window.importOrcidCv = async function () {
        var inp = document.getElementById('cv-orcid-input');
        var btn = document.getElementById('cv-orcid-import-btn');
        var id = (inp && inp.value || '').trim();
        if (!id) {
            if (typeof swalAlert === 'function') swalAlert('กรุณากรอก ORCID iD', 'warning');
            else alert('กรุณากรอก ORCID iD');
            return;
        }
        var scopes = [];
        if (document.getElementById('cv-scope-education') && document.getElementById('cv-scope-education').checked) scopes.push('education');
        if (document.getElementById('cv-scope-employment') && document.getElementById('cv-scope-employment').checked) scopes.push('employment');
        if (document.getElementById('cv-scope-works') && document.getElementById('cv-scope-works').checked) scopes.push('works');
        if (scopes.length === 0) {
            if (typeof swalAlert === 'function') swalAlert('เลือกอย่างน้อยหนึ่งประเภทการนำเข้า', 'warning');
            else alert('เลือกอย่างน้อยหนึ่งประเภทการนำเข้า');
            return;
        }
        if (btn) { btn.disabled = true; btn.textContent = 'กำลังดึงข้อมูล…'; }
        try {
            var p = csrfBody({ orcid_id: id, scopes: scopes.join(',') });
            var res = await fetch('<?= base_url('dashboard/profile/cv/orcid/import') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: p.toString()
            });
            var data = await res.json().catch(function () { return {}; });
            if (data.success) {
                var msg = data.message || 'สำเร็จ';
                if (data.education_count != null || data.employment_count != null || data.works_count != null) {
                    msg += ' (การศึกษา ' + (data.education_count || 0) + ', จ้างงาน ' + (data.employment_count || 0) + ', ผลงาน ' + (data.works_count || 0) + ' รายการ)';
                }
                if (typeof swalAlert === 'function') swalAlert(msg, 'success');
                else alert(msg);
                location.reload();
            } else {
                if (typeof swalAlert === 'function') swalAlert(data.message || 'นำเข้าไม่สำเร็จ', 'error');
                else alert(data.message || 'นำเข้าไม่สำเร็จ');
            }
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = 'นำเข้าจาก ORCID'; }
        }
    };
})();
</script>
<?= $this->endSection() ?>
