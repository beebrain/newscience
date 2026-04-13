<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<?php
$p = $profile ?? [];
$role = $p['role'] ?? 'user';
$isAdmin = in_array($role, ['admin', 'editor', 'super_admin', 'faculty_admin'], true);
$canManageEvaluate = $can_manage_evaluate ?? false;
$dashUid = (int) ($p['uid'] ?? 0);
$showProgramAdminQuick = $dashUid > 0 && \App\Libraries\AccessControl::hasAccess($dashUid, 'program_admin');
$showCertQuick         = \App\Libraries\CertOrganizerAccess::currentMayOrganize();
$quickSystemsGridClass = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-8">

    <!-- Welcome Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-400 via-yellow-500 to-amber-500 h-24 sm:h-28"></div>
        <div class="px-6 pb-6 -mt-10 sm:-mt-12">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white shadow-lg border-4 border-white flex items-center justify-center text-2xl sm:text-3xl font-bold text-yellow-600 bg-gradient-to-br from-yellow-50 to-yellow-100">
                    <?= strtoupper(mb_substr($p['name_en'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-1 pb-1">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">สวัสดี, <?= esc($p['name_th'] ?: ($p['name_en'] ?? 'User')) ?></h1>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-sm text-gray-500">
                        <span><?= esc($p['email'] ?? '') ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            <?php if ($role === 'super_admin'): ?>bg-red-100 text-red-700
                            <?php elseif (in_array($role, ['admin', 'faculty_admin'])): ?>bg-blue-100 text-blue-700
                            <?php elseif ($role === 'editor'): ?>bg-purple-100 text-purple-700
                            <?php else: ?>bg-gray-100 text-gray-600<?php endif; ?>">
                            <?= esc(ucfirst($role)) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access: ระบบภายในเว็บไซต์ -->
    <section>
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-yellow-400 rounded-full"></div>
            <h2 class="text-lg font-bold text-gray-800">ระบบในเว็บไซต์</h2>
        </div>

        <div class="<?= esc($quickSystemsGridClass, 'attr') ?>">
            <!-- โปรไฟล์ / จัดการ CV -->
            <a href="<?= base_url('dashboard/profile') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-teal-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-teal-600 transition-colors">โปรไฟล์ / ประวัติ</div>
                <div class="text-sm text-gray-500 mt-1">จัดการ CV สาธารณะ</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-teal-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <?php if ($showCertQuick): ?>
            <!-- E-Certificate กิจกรรม / ออกใบ -->
            <a href="<?= base_url('dashboard/cert-events') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-amber-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-amber-600 transition-colors">ใบรับรอง (E-Cert)</div>
                <div class="text-sm text-gray-500 mt-1">สร้างกิจกรรมและออกใบให้ผู้เข้าร่วม</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-amber-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <?php endif; ?>

            <!-- E-Document -->
            <a href="<?= base_url('edoc') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-blue-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">งานสารบรรณ (e-Doc)</div>
                <div class="text-sm text-gray-500 mt-1">รับ-ส่งหนังสือราชการ</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-blue-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <?php if ($showProgramAdminQuick): ?>
            <!-- แก้ไขเว็บหลักสูตร (สไตล์เดียวกับ e-Doc) -->
            <a href="<?= base_url('program-admin') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-violet-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-violet-600 transition-colors">แก้ไขเว็บหลักสูตร</div>
                <div class="text-sm text-gray-500 mt-1">จัดการเนื้อหาเว็บหลักสูตร</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-violet-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <?php endif; ?>

            <!-- ตารางคุมสอบ -->
            <a href="<?= base_url('exam') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-orange-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-orange-600 transition-colors">ตารางคุมสอบ</div>
                <div class="text-sm text-gray-500 mt-1">ตรวจสอบกำหนดการสอบ</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-orange-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <!-- ประเมินผลการสอน -->
            <a href="<?= base_url('evaluate') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-green-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-green-600 transition-colors">ประเมินผลการสอน</div>
                <div class="text-sm text-gray-500 mt-1">ส่งคำร้องขอประเมิน</div>
                <?php if ($canManageEvaluate): ?>
                    <span class="inline-block mt-2 text-xs text-green-600 font-medium">+ จัดการระบบประเมิน</span>
                <?php endif; ?>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-green-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <!-- ปฏิทินนัดหมาย -->
            <a href="<?= base_url('dashboard/calendar') ?>" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-yellow-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-yellow-600 transition-colors">ปฏิทินนัดหมาย</div>
                <div class="text-sm text-gray-500 mt-1">จัดการตารางนัดหมาย</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </section>

    <!-- ระบบภายนอก -->
    <section>
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-gray-400 rounded-full"></div>
            <h2 class="text-lg font-bold text-gray-800">ระบบภายนอก</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $edocSso = config(\Config\EdocSso::class);
            $edocLegacyUrl = ($edocSso->baseUrl !== '') ? rtrim($edocSso->baseUrl, '/') : 'https://edoc.sci.uru.ac.th';
            ?>
            <a href="<?= esc($edocLegacyUrl) ?>" target="_blank" rel="noopener" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-gray-300 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-gray-600 transition-colors">e-Doc เดิม</div>
                <div class="text-sm text-gray-500 mt-1">edoc.sci.uru.ac.th</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-gray-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <?php
            $researchSso = config(\Config\ResearchRecordSso::class);
            if ($researchSso->enabled && $researchSso->baseUrl !== ''):
            ?>
                <a href="<?= esc(rtrim($researchSso->baseUrl, '/') . '/index.php/dashboard') ?>" target="_blank" rel="noopener" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-emerald-200 hover:-translate-y-1 transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="font-semibold text-gray-800 group-hover:text-emerald-600 transition-colors">จัดการงานวิจัย</div>
                    <div class="text-sm text-gray-500 mt-1">Research Record</div>
                    <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            <?php endif; ?>

            <a href="<?= base_url() ?>" target="_blank" class="group relative bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-yellow-200 hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                </div>
                <div class="font-semibold text-gray-800 group-hover:text-yellow-600 transition-colors">เว็บไซต์คณะ</div>
                <div class="text-sm text-gray-500 mt-1">sci.uru.ac.th</div>
                <svg class="absolute top-5 right-5 w-5 h-5 text-gray-300 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </section>

    <!-- Admin Quick Access (สำหรับ admin/super_admin) -->
    <?php if ($isAdmin): ?>
    <section>
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-red-400 rounded-full"></div>
            <h2 class="text-lg font-bold text-gray-800">เครื่องมือผู้ดูแล</h2>
            <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Admin</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php
                $adminLinks = [
                    ['url' => 'admin/news', 'label' => 'จัดการข่าว', 'icon' => '<path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>'],
                    ['url' => 'admin/organization', 'label' => 'โครงสร้างองค์กร', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>'],
                    ['url' => 'admin/programs', 'label' => 'หลักสูตร', 'icon' => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>'],
                    ['url' => 'admin/users', 'label' => 'ผู้ใช้งาน', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
                    ['url' => 'admin/hero-slides', 'label' => 'ภาพสไลด์', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'],
                    ['url' => 'admin/events', 'label' => 'กิจกรรม', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
                    ['url' => 'student-admin/barcode-events', 'label' => 'บาร์โค้ด', 'icon' => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>'],
                    ['url' => 'admin/settings', 'label' => 'ตั้งค่าเว็บ', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
                ];
                foreach ($adminLinks as $link):
                ?>
                    <a href="<?= base_url($link['url']) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $link['icon'] ?></svg>
                        </div>
                        <?= $link['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ข้อมูลส่วนตัว -->
    <section>
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-blue-400 rounded-full"></div>
            <h2 class="text-lg font-bold text-gray-800">ข้อมูลส่วนตัว</h2>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="divide-y divide-gray-100">
                <?php
                $infoRows = [
                    ['label' => 'ชื่อ-นามสกุล (ไทย)', 'value' => $p['name_th'] ?? '—'],
                    ['label' => 'ชื่อ-นามสกุล (อังกฤษ)', 'value' => $p['name_en'] ?? '—'],
                    ['label' => 'อีเมล', 'value' => $p['email'] ?? '—'],
                    ['label' => 'Login UID', 'value' => $p['login_uid'] ?? '—'],
                    ['label' => 'บทบาท', 'value' => ucfirst($p['role'] ?? 'user')],
                    ['label' => 'สถานะ', 'value' => ($p['status'] ?? '') === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน', 'badge' => ($p['status'] ?? '') === 'active' ? 'green' : 'gray'],
                    ['label' => 'วันที่สมัคร', 'value' => !empty($p['created_at']) ? date('d/m/', strtotime($p['created_at'])) . (date('Y', strtotime($p['created_at'])) + 543) . date(' H:i', strtotime($p['created_at'])) : '—'],
                ];
                foreach ($infoRows as $row):
                ?>
                    <div class="flex flex-col sm:flex-row sm:items-center px-6 py-3.5">
                        <div class="sm:w-48 text-sm font-medium text-gray-500 mb-0.5 sm:mb-0"><?= $row['label'] ?></div>
                        <div class="text-sm text-gray-900">
                            <?php if (!empty($row['badge'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    <?= $row['badge'] === 'green' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                                    <?= esc($row['value']) ?>
                                </span>
                            <?php else: ?>
                                <?= esc($row['value']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</div>

<?= $this->endSection() ?>
