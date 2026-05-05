<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<?php
$person = $person ?? null;
$publicCvUrl = $public_cv_url ?? null;
$accountUser = $account_user ?? null;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-8">

    <div class="flex items-center gap-2 mb-2">
        <div class="w-1 h-6 bg-yellow-400 rounded-full"></div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">โปรไฟล์และประวัติ</h1>
    </div>

    <?php if ($accountUser !== null): ?>
        <?php
        $um = new \App\Models\UserModel();
        $displayFromAccount = $um->getFullName($accountUser);
        ?>
        <?php if ($person !== null): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">ชื่อและคำนำหน้า (หลักใน personnel)</h2>
                <p class="text-xs text-gray-500 mb-4">แก้ไขได้ที่ <strong>จัดการ CV</strong> — บันทึกลง <strong>personnel</strong> สำหรับหน้าเว็บบุคลากรและ CV สาธารณะ และซิงก์ <strong>user</strong></p>
                <?php
                $pm = new \App\Models\PersonnelModel();
                $displayPersonnel = $pm->getFullName($person);
                ?>
                <p class="text-sm text-gray-700 mb-4">ชื่อที่ใช้แสดง (บุคลากร): <strong class="text-gray-900"><?= esc($displayPersonnel !== '' ? $displayPersonnel : ($person['name'] ?? '—')) ?></strong></p>
                <p class="text-sm text-gray-600 mb-4">ชื่อบัญชีล่าสุด: <strong class="text-gray-800"><?= esc($displayFromAccount !== '' ? $displayFromAccount : '—') ?></strong></p>
                <a href="<?= base_url('dashboard/profile/cv?tab=identity') ?>"
                   class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-yellow-500 hover:bg-yellow-600 text-gray-900 text-sm font-semibold transition-colors">
                    แก้ไขชื่อและคำนำหน้าใน CV
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">ชื่อและคำนำหน้า (บัญชี)</h2>
                <p class="text-xs text-gray-500 mb-4">ยังไม่เชื่อมกับระบบบุคลากร — บันทึกลงตาราง <strong>user</strong> เท่านั้น เมื่อเชื่อมอีเมลกับ <strong>personnel</strong> แล้ว ให้แก้ชื่อจากหน้าจัดการ CV</p>
                <p class="text-sm text-gray-700 mb-4">ชื่อที่แสดงขณะนี้: <strong class="text-gray-900"><?= esc($displayFromAccount !== '' ? $displayFromAccount : '—') ?></strong></p>
                <?= $this->include('user/profile/partials/identity_form', [
                    'account_user' => $accountUser,
                    'person' => null,
                    'id_prefix' => 'profile-unlinked',
                ]) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($person === null): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-amber-900">
            <p class="font-semibold mb-2">ยังไม่พบข้อมูลบุคลากรที่ผูกกับบัญชีของคุณ</p>
            <p class="text-sm leading-relaxed">
                ระบบใช้อีเมล <strong><?= esc($session_email ?? '') ?></strong> ในการเชื่อมกับตารางบุคลากร
                (<code class="bg-amber-100 px-1 rounded">personnel.user_email</code>) หากคุณควรมีหน้าประวัติสาธารณะ
                กรุณาติดต่อเจ้าหน้าที่เพื่อบันทึกหรือแก้ไขการเชื่อมอีเมลในระบบ
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">ข้อมูลที่เชื่อมแล้ว</h2>
                <?php
                $pm = new \App\Models\PersonnelModel();
                $displayName = $pm->getFullName($person);
                ?>
                <p class="text-lg font-bold text-gray-900"><?= esc($displayName !== '' ? $displayName : ($person['name'] ?? '')) ?></p>
                <p class="text-sm text-gray-600 mt-2">อีเมลบัญชี: <?= esc($session_email ?? '') ?></p>
                <?php if (!empty($person['user_email'])): ?>
                    <p class="text-sm text-gray-600">อีเมลในระบบบุคลากร: <?= esc($person['user_email']) ?></p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">การดำเนินการ</h2>
                <a href="<?= base_url('dashboard/profile/research-record-sync') ?>"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-gray-200 text-gray-800 font-semibold hover:bg-gray-50 transition-colors mb-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    ดึง CV จาก กบศ → ฐานข้อมูลคณะ
                </a>
                <a href="<?= base_url('dashboard/profile/cv') ?>"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold transition-colors mb-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    จัดการประวัติ / CV (รายวิชา ผลงาน ฯลฯ)
                </a>
                <?php if ($publicCvUrl): ?>
                    <a href="<?= esc($publicCvUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-secondary text-secondary-dark font-semibold hover:bg-green-50 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        ดูหน้าประวัติสาธารณะ (เปิดแท็บใหม่)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="<?= base_url('dashboard') ?>" class="text-sm text-gray-500 hover:text-yellow-700">← กลับหน้าหลัก</a>
    </div>
</div>
<?= $this->endSection() ?>
