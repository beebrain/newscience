<?php
helper(['form', 'security']);
?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('styles') ?>
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
$research_sync_configured   = $research_sync_configured ?? false;
$rr_sync_notice             = $rr_sync_notice ?? null;
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-6">

    <?php if (is_array($rr_sync_notice) && ! empty($rr_sync_notice['text'])): ?>
        <div class="rounded-xl px-4 py-3 text-sm border <?= ($rr_sync_notice['type'] ?? '') === 'success' ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-amber-50 text-amber-900 border-amber-200' ?>">
            <p class="font-semibold"><?= esc($rr_sync_notice['text']) ?></p>
            <?php if (! empty($rr_sync_notice['detail'])): ?>
                <p class="mt-1 text-xs opacity-90"><?= esc($rr_sync_notice['detail']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="w-1 h-6 bg-yellow-400 rounded-full"></div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">จัดการ CV</h1>
            </div>
            <p class="text-sm text-gray-500 ml-3">แบบเดียวกับ Research Record — หัวข้อกำหนดเอง ลากเรียงได้ รายการมีองค์กร สถานที่ วันที่</p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <a href="<?= base_url('dashboard/profile') ?>" class="text-sm px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">โปรไฟล์</a>
            <?php if ($research_sync_configured): ?>
                <form method="post" action="<?= base_url('dashboard/profile/cv/sync-from-rr') ?>" class="inline"
                      onsubmit="return confirm('ดึง CV และผลงานจาก Research Record มาแทนที่ข้อมูลใน newScience ตอนนี้?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-emerald-700 text-white font-semibold hover:bg-emerald-800">
                        ดึงจาก RR ตอนนี้
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= base_url('dashboard/profile/research-record-sync') ?>" class="text-sm px-3 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">เปรียบเทียบ / ดึงแบบละเอียด</a>
            <?php
            $pubEmail = '';
            $p = $person ?? [];
            if (!empty($p['user_email'])) {
                $pubEmail = \App\Libraries\CvProfile::normalizeEmail((string) $p['user_email']);
            } elseif (!empty($p['email'])) {
                $pubEmail = \App\Libraries\CvProfile::normalizeEmail((string) $p['email']);
            }
            if ($pubEmail !== ''):
            ?>
                <a href="<?= base_url('personnel-cv/' . rawurlencode($pubEmail)) ?>" target="_blank" rel="noopener noreferrer"
                   class="text-sm px-3 py-2 rounded-lg border border-secondary text-secondary-dark hover:bg-green-50">ดู CV สาธารณะ</a>
            <?php endif; ?>
            <a href="<?= base_url('dashboard') ?>" class="text-sm px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">หน้าหลัก</a>
        </div>
    </div>

    <?php
    $cvPhotoOk = $cv_photo_supported ?? false;
    $p = $person ?? [];
    $cvImgPath = trim((string) ($p['cv_profile_image'] ?? ''));
    $cvImgPreview = '';
    if ($cvImgPath !== '') {
        $cvImgPreview = strpos($cvImgPath, 'http') === 0
            ? $cvImgPath
            : base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $cvImgPath)));
    }
    $cvEmptyPreview = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="112" height="112"><circle cx="56" cy="56" r="56" fill="#e2e8f0"/></svg>');
    ?>
    <!-- รูปประกอบ CV สาธารณะ (แยกจากรูปบัญชีระบบ) -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-amber-50/60">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">รูปประกอบ CV</p>
            <p class="text-sm text-gray-600 mt-1">แสดงเฉพาะหน้า CV สาธารณะ — ไม่เปลี่ยนรูปโปรไฟล์บัญชีผู้ใช้ (เช่นในหน้าอื่นหรือรายการบุคลากร)</p>
        </div>
        <div class="p-5 flex flex-col sm:flex-row gap-6 sm:items-start">
            <div class="shrink-0">
                <?php if (!$cvPhotoOk): ?>
                    <div class="w-28 h-28 rounded-full bg-gray-100 border border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-500 text-center px-2">
                        รัน migrate เพื่อเปิดใช้
                    </div>
                <?php else: ?>
                    <img id="cv-photo-preview" src="<?= esc($cvImgPreview !== '' ? $cvImgPreview : $cvEmptyPreview, 'attr') ?>" alt="" width="112" height="112"
                         class="w-28 h-28 rounded-full object-cover border border-gray-200 shadow-sm bg-slate-100">
                <?php endif; ?>
            </div>
            <div class="flex-1 space-y-4 min-w-0">
                <?php if ($cvPhotoOk): ?>
                    <form id="cv-photo-form" action="<?= base_url('dashboard/profile/cv/photo') ?>" method="post" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                        <?= csrf_field() ?>
                        <div class="flex-1 min-w-[200px]">
                            <label for="cv-photo-file" class="block text-xs font-medium text-gray-600 mb-1">อัปโหลดรูปใหม่</label>
                            <input type="file" name="image" id="cv-photo-file" accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-yellow-50 file:text-yellow-900 hover:file:bg-yellow-100 border border-gray-200 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF หรือ WebP — เลือกแล้วจะเปิดตัดภาพเป็นสี่เหลี่ยมจัตุรัส แล้วอัปโหลดอัตโนมัติ (สูงสุด 20MB)</p>
                        </div>
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-5 rounded-lg text-sm font-semibold transition shrink-0">
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
        </div>
    </div>

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

    <!-- นำเข้าจาก ORCID (Public API) -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">ORCID</p>
            <p class="text-sm text-gray-600 mt-1">ดึงรายการที่ตั้งเป็น<strong class="text-gray-800">สาธารณะ</strong>จาก <a href="https://orcid.org" target="_blank" rel="noopener noreferrer" class="text-secondary-dark underline">orcid.org</a> แล้วเพิ่ม/อัปเดตใน CV — เลือกประเภทด้านล่าง (จับคู่รายการเดิมด้วย <code class="text-xs bg-white px-1 rounded border">put-code</code> หรือคีย์สำรองเมื่อไม่มี put-code)</p>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-700">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="cv-scope-education" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-400" checked>
                    การศึกษา
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="cv-scope-employment" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-400" checked>
                    การจ้างงาน
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="cv-scope-works" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-400" checked>
                    ผลงานตีพิมพ์ (works)
                </label>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label for="cv-orcid-input" class="block text-xs font-medium text-gray-600 mb-1">ORCID iD</label>
                    <input type="text" id="cv-orcid-input" maxlength="19" placeholder="0000-0002-1825-0097"
                           value="<?= esc($person['orcid_id'] ?? '') ?>"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 font-mono focus:ring-2 focus:ring-yellow-300 focus:border-yellow-400">
                </div>
                <button type="button" id="cv-orcid-import-btn" onclick="importOrcidCv()"
                        class="bg-slate-700 hover:bg-slate-800 text-white py-2 px-5 rounded-lg text-sm font-semibold transition shrink-0">
                    นำเข้าจาก ORCID
                </button>
            </div>
        </div>
    </div>

    <!-- สร้างหัวข้อ -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">สรุป</p>
                <div class="flex gap-6 mt-1 text-sm">
                    <span><strong class="text-gray-900"><?= count($cvSections) ?></strong> หัวข้อ</span>
                    <?php
                    $totalE = 0;
                    foreach ($cvSections as $sx) {
                        $totalE += count($sx['entries'] ?? []);
                    }
                    ?>
                    <span><strong class="text-gray-900"><?= $totalE ?></strong> รายการ</span>
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100 bg-white">
            <form action="<?= base_url('dashboard/profile/cv/section/save') ?>" method="post" class="flex flex-wrap items-end gap-3">
                <?= csrf_field() ?>
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">ชื่อหัวข้อ</label>
                    <input type="text" name="title" required maxlength="255" placeholder="เช่น ประวัติการศึกษา งานวิจัย วิทยากร..."
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300 focus:border-yellow-400">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">ประเภท</label>
                    <select name="type" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300">
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
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 py-2 px-5 rounded-lg text-sm font-semibold transition">
                    + เพิ่มหัวข้อ
                </button>
            </form>
        </div>
    </div>

    <?php if ($cvSections === []): ?>
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 py-16 text-center text-gray-500">
            <p class="text-lg font-medium text-gray-700">ยังไม่มีหัวข้อ</p>
            <p class="text-sm mt-2">สร้างหัวข้อด้านบน แล้วเพิ่มรายการในแต่ละหัวข้อ</p>
        </div>
    <?php else: ?>
        <div id="cv-sections-container" class="space-y-4">
            <?php foreach ($cvSections as $section):
                $sid = (int) ($section['id'] ?? 0);
                $entries = $section['entries'] ?? [];
                $visPub = !empty($section['visible_on_public']);
            ?>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden cv-section-item" data-section-id="<?= $sid ?>">
                    <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-yellow-50 to-white border-b border-gray-100 cursor-pointer hover:from-yellow-100/50 transition"
                         onclick="toggleCvSection(<?= $sid ?>)">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="cv-section-handle cursor-grab text-gray-400 hover:text-gray-600 text-lg select-none" onclick="event.stopPropagation()">⋮⋮</span>
                            <div class="min-w-0">
                                <span class="font-semibold text-gray-900 text-lg"><?= esc($section['title'] ?? '') ?></span>
                                <span class="text-sm text-gray-500 ml-2">(<?= count($entries) ?> รายการ)</span>
                                <?php if (!$visPub): ?>
                                    <span class="ml-2 text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">ซ่อนจากหน้าสาธารณะ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0" onclick="event.stopPropagation()">
                            <button type="button" onclick="toggleSectionPublic(<?= $sid ?>, <?= $visPub ? 'true' : 'false' ?>)"
                                    class="text-xs px-2 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                                <?= $visPub ? 'ซ่อนจากสาธารณะ' : 'แสดงในสาธารณะ' ?>
                            </button>
                            <button type="button" onclick="deleteCvSection(<?= $sid ?>, '<?= esc($section['title'] ?? '', 'js') ?>')"
                                    class="text-red-500 hover:text-red-700 text-sm px-2 py-1 rounded-lg hover:bg-red-50">ลบหัวข้อ</button>
                            <span class="text-gray-400 text-lg transition-transform duration-200" id="cv-toggle-<?= $sid ?>">▼</span>
                        </div>
                    </div>

                    <div class="hidden" id="cv-content-<?= $sid ?>">
                        <div class="p-5 cv-entries-container border-b border-gray-100" id="cv-entries-<?= $sid ?>" data-section-id="<?= $sid ?>">
                            <?php if ($entries !== []): ?>
                                <div class="space-y-2">
                                    <?php foreach ($entries as $entry):
                                        $eid = (int) ($entry['id'] ?? 0);
                                    ?>
                                        <div class="entry-card flex items-start gap-3 p-4 bg-gray-50 rounded-xl cv-entry-item border border-transparent hover:border-gray-200" data-entry-id="<?= $eid ?>">
                                            <span class="cv-entry-handle cursor-grab text-gray-400 hover:text-gray-600 mt-1 select-none">⋮⋮</span>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-baseline gap-2">
                                                    <h4 class="font-medium text-gray-900"><?= esc($entry['title'] ?? '') ?></h4>
                                                    <?php if (!empty($entry['organization'])): ?>
                                                        <span class="text-sm text-gray-600">• <?= esc($entry['organization']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($entry['location']) || !empty($entry['start_date'])): ?>
                                                    <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-500">
                                                        <?php if (!empty($entry['location'])): ?>
                                                            <span><?= esc($entry['location']) ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($entry['start_date'])): ?>
                                                            <span>
                                                                <?= esc(substr((string) $entry['start_date'], 0, 10)) ?>
                                                                <?php if (!empty($entry['end_date'])): ?>
                                                                    – <?= esc(substr((string) $entry['end_date'], 0, 10)) ?>
                                                                <?php elseif (!empty($entry['is_current'])): ?>
                                                                    – ปัจจุบัน
                                                                <?php endif; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($entry['description'])): ?>
                                                    <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?= esc($entry['description']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($entry['metadata_array']['url']) || !empty($entry['metadata_array']['legacy_url'])): ?>
                                                    <?php $u = $entry['metadata_array']['url'] ?? $entry['metadata_array']['legacy_url']; ?>
                                                    <a href="<?= esc($u, 'attr') ?>" class="text-xs text-blue-600 hover:underline mt-1 inline-block" target="_blank" rel="noopener noreferrer">ลิงก์</a>
                                                <?php endif; ?>
                                                <?php if (empty($entry['visible_on_public'])): ?>
                                                    <span class="inline-block mt-1 text-xs text-amber-700">ไม่แสดงในหน้าสาธารณะ</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col gap-1 shrink-0">
                                                <button type="button" onclick="editCvEntry(<?= $sid ?>, <?= $eid ?>)"
                                                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-white">แก้ไข</button>
                                                <button type="button" onclick="deleteCvEntry(<?= $eid ?>, '<?= esc($entry['title'] ?? '', 'js') ?>')"
                                                        class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">ลบ</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-400 text-center py-6">ยังไม่มีรายการ</p>
                            <?php endif; ?>
                        </div>

                        <div class="p-5 bg-gray-50/80">
                            <p class="text-xs text-gray-500 uppercase mb-3 font-semibold">เพิ่ม / แก้ไขรายการ</p>
                            <form class="cv-entry-form space-y-3" action="<?= base_url('dashboard/profile/cv/entry/save') ?>" method="post" data-section-id="<?= $sid ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="section_id" value="<?= $sid ?>">
                                <input type="hidden" name="entry_id" value="" id="cv-entry-id-<?= $sid ?>">
                                <input type="hidden" name="entry_sort_order" value="0" id="cv-entry-sort-<?= $sid ?>">

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">หัวข้อรายการ <span class="text-red-500">*</span></label>
                                    <input type="text" name="entry_title" required maxlength="500" id="cv-entry-title-<?= $sid ?>"
                                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">หน่วยงาน / องค์กร</label>
                                        <input type="text" name="organization" maxlength="500" id="cv-entry-org-<?= $sid ?>"
                                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">สถานที่</label>
                                        <input type="text" name="location" maxlength="500" id="cv-entry-loc-<?= $sid ?>"
                                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">วันเริ่ม</label>
                                        <input type="date" name="start_date" id="cv-entry-start-<?= $sid ?>"
                                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">วันสิ้นสุด</label>
                                        <input type="date" name="end_date" id="cv-entry-end-<?= $sid ?>"
                                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">ลิงก์ (URL)</label>
                                    <input type="text" name="entry_url" maxlength="2048" id="cv-entry-url-<?= $sid ?>"
                                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300" placeholder="https://...">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">รายละเอียด</label>
                                    <textarea name="entry_description" rows="3" id="cv-entry-desc-<?= $sid ?>"
                                              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300"></textarea>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="is_current" value="1" id="cv-entry-current-<?= $sid ?>" class="rounded border-gray-300 text-yellow-500 focus:ring-yellow-400">
                                        <span>ปัจจุบัน</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="visible_on_public" value="1" checked id="cv-entry-vis-<?= $sid ?>" class="rounded border-gray-300 text-yellow-500 focus:ring-yellow-400">
                                        <span>แสดงในหน้าสาธารณะ</span>
                                    </label>
                                </div>
                                <input type="hidden" name="extra_info" value="">
                                <input type="hidden" name="funding_amount" value="">
                                <div class="flex flex-wrap gap-2 pt-2">
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 text-sm font-semibold rounded-lg px-5 py-2">บันทึก</button>
                                    <button type="button" onclick="resetCvEntryForm(<?= $sid ?>)" class="border border-gray-200 text-gray-600 text-sm rounded-lg px-4 py-2 hover:bg-gray-50">ล้างฟอร์ม</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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

    window.resetCvEntryForm = function (sectionId) {
        document.getElementById('cv-entry-id-' + sectionId).value = '';
        document.getElementById('cv-entry-sort-' + sectionId).value = '0';
        document.getElementById('cv-entry-title-' + sectionId).value = '';
        document.getElementById('cv-entry-org-' + sectionId).value = '';
        document.getElementById('cv-entry-loc-' + sectionId).value = '';
        document.getElementById('cv-entry-start-' + sectionId).value = '';
        document.getElementById('cv-entry-end-' + sectionId).value = '';
        document.getElementById('cv-entry-url-' + sectionId).value = '';
        document.getElementById('cv-entry-desc-' + sectionId).value = '';
        document.getElementById('cv-entry-current-' + sectionId).checked = false;
        document.getElementById('cv-entry-vis-' + sectionId).checked = true;
    };

    window.editCvEntry = async function (sectionId, entryId) {
        var url = '<?= base_url('dashboard/profile/cv/entry') ?>/' + entryId;
        var res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        var data = await res.json();
        if (!data.success || !data.entry) {
            if (typeof swalAlert === 'function') swalAlert(data.message || 'โหลดไม่สำเร็จ', 'error');
            return;
        }
        var e = data.entry;
        document.getElementById('cv-entry-id-' + sectionId).value = e.id;
        document.getElementById('cv-entry-sort-' + sectionId).value = e.sort_order || 0;
        document.getElementById('cv-entry-title-' + sectionId).value = e.title || '';
        document.getElementById('cv-entry-org-' + sectionId).value = e.organization || '';
        document.getElementById('cv-entry-loc-' + sectionId).value = e.location || '';
        document.getElementById('cv-entry-start-' + sectionId).value = (e.start_date || '').substring(0, 10);
        document.getElementById('cv-entry-end-' + sectionId).value = (e.end_date || '').substring(0, 10);
        document.getElementById('cv-entry-url-' + sectionId).value = e.entry_url || (e.metadata_array && e.metadata_array.url) || (e.metadata_array && e.metadata_array.legacy_url) || '';
        document.getElementById('cv-entry-desc-' + sectionId).value = e.description || '';
        document.getElementById('cv-entry-current-' + sectionId).checked = String(e.is_current) === '1';
        document.getElementById('cv-entry-vis-' + sectionId).checked = String(e.visible_on_public) !== '0';
        var content = document.getElementById('cv-content-' + sectionId);
        var toggle = document.getElementById('cv-toggle-' + sectionId);
        if (content) content.classList.remove('hidden');
        if (toggle) toggle.textContent = '▲';
        document.getElementById('cv-entry-title-' + sectionId).focus();
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

    window.toggleSectionPublic = async function (sectionId, currentlyVisible) {
        var p = csrfBody();
        var res = await fetch('<?= base_url('dashboard/profile/cv/section/toggle') ?>/' + sectionId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: p.toString()
        });
        var data = await res.json().catch(function () { return {}; });
        if (data.success) location.reload();
        else if (typeof swalAlert === 'function') swalAlert(data.message || 'ผิดพลาด', 'error');
    };

    document.addEventListener('DOMContentLoaded', function () {
        var sc = document.getElementById('cv-sections-container');
        if (sc && typeof Sortable !== 'undefined') {
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

        document.querySelectorAll('.cv-entry-form').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                var fd = new FormData(form);
                var res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                var data = await res.json();
                if (data.success) location.reload();
                else if (typeof swalAlert === 'function') swalAlert(data.message || 'บันทึกไม่สำเร็จ', 'error');
                else alert(data.message || 'บันทึกไม่สำเร็จ');
            });
        });
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
