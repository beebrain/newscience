<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('styles') ?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { 'sarabun': ['Sarabun', 'Noto Sans Thai', 'sans-serif'] },
                colors: {
                    primary: { DEFAULT: '#eab308', dark: '#ca8a04', light: '#fef9c3' },
                }
            }
        }
    }
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$p = $profile ?? [];
$role = $p['role'] ?? 'user';
$canManageEvaluate = $can_manage_evaluate ?? false;
?>

<div class="font-sarabun" style="margin: -1rem; padding: 1.5rem;">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 rounded-2xl p-6 mb-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-yellow-400"></div>
            <div class="absolute -left-5 -bottom-5 w-24 h-24 rounded-full bg-yellow-400"></div>
        </div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-2xl font-bold text-gray-900 shadow-lg">
                <?= strtoupper(mb_substr($p['name_en'] ?? 'A', 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold">สวัสดี, <?= esc($p['name_th'] ?: ($p['name_en'] ?? 'Admin')) ?></h1>
                <div class="flex flex-wrap items-center gap-2 mt-1 text-gray-300 text-sm">
                    <span><?= esc($p['email'] ?? '') ?></span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-400/20 text-yellow-300 border border-yellow-400/30">
                        <?= esc(ucfirst($role)) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">E-Document</div>
                    <a href="<?= base_url('edoc') ?>" class="text-sm font-semibold text-gray-800 hover:text-blue-600 transition-colors">เข้าสู่ระบบ &rarr;</a>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">ตารางคุมสอบ</div>
                    <a href="<?= base_url('exam') ?>" class="text-sm font-semibold text-gray-800 hover:text-orange-600 transition-colors">ดูตาราง &rarr;</a>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">ประเมินการสอน</div>
                    <a href="<?= base_url('evaluate') ?>" class="text-sm font-semibold text-gray-800 hover:text-green-600 transition-colors">เข้าสู่ระบบ &rarr;</a>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">ปฏิทินนัดหมาย</div>
                    <a href="<?= base_url('dashboard/calendar') ?>" class="text-sm font-semibold text-gray-800 hover:text-yellow-600 transition-colors">จัดการ &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Quick Actions Grid -->
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            เครื่องมือจัดการ
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <?php
            $tools = [
                ['url' => 'admin/news', 'label' => 'จัดการข่าว', 'color' => 'blue', 'icon' => '<path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>'],
                ['url' => 'admin/organization', 'label' => 'โครงสร้างองค์กร', 'color' => 'purple', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/>'],
                ['url' => 'admin/programs', 'label' => 'หลักสูตร', 'color' => 'indigo', 'icon' => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>'],
                ['url' => 'admin/hero-slides', 'label' => 'ภาพสไลด์', 'color' => 'pink', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'],
                ['url' => 'admin/events', 'label' => 'กิจกรรม', 'color' => 'amber', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
                ['url' => 'admin/downloads', 'label' => 'ดาวน์โหลด', 'color' => 'cyan', 'icon' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>'],
                ['url' => 'student-admin/barcode-events', 'label' => 'บาร์โค้ด', 'color' => 'teal', 'icon' => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>'],
                ['url' => 'admin/cert-events', 'label' => 'E-Certificate', 'color' => 'emerald', 'icon' => '<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>'],
            ];
            $colorMap = [
                'blue' => ['bg-blue-50', 'text-blue-600'],
                'purple' => ['bg-purple-50', 'text-purple-600'],
                'indigo' => ['bg-indigo-50', 'text-indigo-600'],
                'pink' => ['bg-pink-50', 'text-pink-600'],
                'amber' => ['bg-amber-50', 'text-amber-600'],
                'cyan' => ['bg-cyan-50', 'text-cyan-600'],
                'teal' => ['bg-teal-50', 'text-teal-600'],
                'emerald' => ['bg-emerald-50', 'text-emerald-600'],
            ];
            foreach ($tools as $tool):
                $c = $colorMap[$tool['color']] ?? ['bg-gray-50', 'text-gray-600'];
            ?>
                <a href="<?= base_url($tool['url']) ?>" class="group flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="w-10 h-10 rounded-lg <?= $c[0] ?> <?= $c[1] ?> flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $tool['icon'] ?></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900"><?= $tool['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- System Admin Section -->
    <?php if ($role === 'super_admin'): ?>
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            ผู้ดูแลระบบ
            <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Super Admin</span>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php
            $sysTools = [
                ['url' => 'admin/users', 'label' => 'จัดการผู้ใช้', 'desc' => 'เพิ่ม แก้ไข ลบ ผู้ใช้ในระบบ', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>'],
                ['url' => 'admin/user-faculty', 'label' => 'จัดการคณะผู้ใช้', 'desc' => 'กำหนดคณะและสังกัดผู้ใช้', 'icon' => '<path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
                ['url' => 'admin/settings', 'label' => 'ตั้งค่าเว็บไซต์', 'desc' => 'ปรับแต่งค่าต่างๆ ของเว็บไซต์', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>'],
                ['url' => 'admin/executive-dashboard', 'label' => 'Dashboard ผู้บริหาร', 'desc' => 'ภาพรวมข้อมูลสำหรับผู้บริหาร', 'icon' => '<path d="M3 3v18h18"/><path d="M18 9v4"/><path d="M13 3v12"/><path d="M8 7v8"/>'],
                ['url' => 'utility/import-data', 'label' => 'Import Data', 'desc' => 'นำเข้าข้อมูลจากไฟล์ภายนอก', 'icon' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>'],
                ['url' => 'utility/categorize-news', 'label' => 'จัดหมวดข่าว', 'desc' => 'จัดหมวดหมู่ข่าวอัตโนมัติ', 'icon' => '<path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7"/>'],
            ];
            foreach ($sysTools as $st):
            ?>
                <a href="<?= base_url($st['url']) ?>" class="group bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md hover:border-red-200 hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $st['icon'] ?></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-800 group-hover:text-red-600 transition-colors"><?= $st['label'] ?></div>
                            <div class="text-xs text-gray-500 mt-0.5"><?= $st['desc'] ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ระบบภายนอก -->
    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
            ลิงก์ภายนอก
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <?php
            $edocSso = config(\Config\EdocSso::class);
            $edocLegacyUrl = ($edocSso->baseUrl !== '') ? rtrim($edocSso->baseUrl, '/') : 'https://edoc.sci.uru.ac.th';
            ?>
            <a href="<?= esc($edocLegacyUrl) ?>" target="_blank" rel="noopener" class="group flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-700">e-Doc เดิม</div>
                    <div class="text-xs text-gray-400">edoc.sci.uru.ac.th</div>
                </div>
            </a>
            <?php
            $researchSso = config(\Config\ResearchRecordSso::class);
            if ($researchSso->enabled && $researchSso->baseUrl !== ''):
            ?>
                <a href="<?= esc(rtrim($researchSso->baseUrl, '/') . '/index.php/dashboard') ?>" target="_blank" rel="noopener" class="group flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">Research Record</div>
                        <div class="text-xs text-gray-400">จัดการงานวิจัย</div>
                    </div>
                </a>
            <?php endif; ?>
            <a href="<?= base_url() ?>" target="_blank" class="group flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-700">เว็บไซต์คณะ</div>
                    <div class="text-xs text-gray-400">sci.uru.ac.th</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Profile Info -->
    <div class="mt-8 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-700">ข้อมูลส่วนตัว</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php
            $rows = [
                ['label' => 'ชื่อ (ไทย)', 'value' => $p['name_th'] ?? '—'],
                ['label' => 'ชื่อ (อังกฤษ)', 'value' => $p['name_en'] ?? '—'],
                ['label' => 'อีเมล', 'value' => $p['email'] ?? '—'],
                ['label' => 'Login UID', 'value' => $p['login_uid'] ?? '—'],
                ['label' => 'บทบาท', 'value' => ucfirst($p['role'] ?? 'user')],
            ];
            foreach ($rows as $r):
            ?>
                <div class="flex px-5 py-2.5">
                    <div class="w-40 text-xs font-medium text-gray-500"><?= $r['label'] ?></div>
                    <div class="text-sm text-gray-800"><?= esc($r['value']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
