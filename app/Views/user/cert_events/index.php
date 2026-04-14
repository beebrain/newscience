<?= $this->extend('layouts/user_layout') ?>

<?php
$certFacultyView = \App\Libraries\CertOrganizerAccess::isFacultyWideViewer();
?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">กิจกรรมใบรับรอง</h1>
            <p class="text-sm text-gray-500 mt-1">
                <?php if ($certFacultyView): ?>
                    มุมมองระดับคณะ — แสดงกิจกรรมจากผู้จัดทุกคนในคณะ (กรองผู้สร้างได้)
                <?php else: ?>
                    สร้างกิจกรรม แนบรูปหรือ PDF แม่แบบใบรับรอง นำเข้าผู้รับ และออกใบได้จากที่นี่หลังเข้าสู่ระบบ
                <?php endif; ?>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <?php if ($certFacultyView): ?>
                <a href="<?= esc($cert_base) ?>/issued-report"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    รายงานใบที่ออกแล้ว
                </a>
            <?php endif; ?>
            <a href="<?= esc($cert_base) ?>/create"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600">
                + สร้างกิจกรรม
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
        <form method="get" class="flex flex-wrap items-center gap-3">
            <?php if ($certFacultyView): ?>
                <label class="text-sm text-gray-600">ผู้สร้าง (uid)</label>
                <input type="number" name="created_by" value="<?= esc($filter_creator ?? '') ?>" placeholder="ทั้งหมด"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-32">
                <button type="submit" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">กรอง</button>
            <?php endif; ?>
            <label class="text-sm text-gray-600">สถานะ</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">ทั้งหมด</option>
                <option value="draft" <?= ($filter_status ?? '') === 'draft' ? 'selected' : '' ?>>ร่าง</option>
                <option value="open" <?= ($filter_status ?? '') === 'open' ? 'selected' : '' ?>>เปิด</option>
                <option value="issued" <?= ($filter_status ?? '') === 'issued' ? 'selected' : '' ?>>ออกแล้ว</option>
                <option value="closed" <?= ($filter_status ?? '') === 'closed' ? 'selected' : '' ?>>ปิด</option>
            </select>
        </form>
    </div>

    <?php if (empty($events)): ?>
        <div class="bg-blue-50 text-blue-900 rounded-xl p-6 text-center">ยังไม่มีกิจกรรม</div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">กิจกรรม</th>
                        <?php if ($certFacultyView): ?>
                            <th class="text-left px-4 py-3">ผู้สร้าง</th>
                        <?php endif; ?>
                        <th class="text-center px-4 py-3">วันที่</th>
                        <th class="text-center px-4 py-3">สถานะ</th>
                        <th class="text-center px-4 py-3">ผู้รับ</th>
                        <th class="text-center px-4 py-3">ออกแล้ว</th>
                        <th class="text-center px-4 py-3">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?= esc($event['title']) ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php if (! empty($event['background_file'])): ?>
                                        <?= esc(($event['background_kind'] ?? '') . ' · มีไฟล์ใบรับรอง') ?>
                                    <?php else: ?>
                                        <span class="text-amber-700">ยังไม่มีไฟล์ใบรับรอง</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if ($certFacultyView): ?>
                                <td class="px-4 py-3 text-gray-700 text-xs">
                                    <?php
                                    $cn = trim(($event['creator_tf_name'] ?? '') . ' ' . ($event['creator_tl_name'] ?? ''));
                                    echo esc($cn !== '' ? $cn : ($event['creator_email'] ?? '-'));
                                    ?>
                                    <?php if (! empty($event['created_by'])): ?>
                                        <br><span class="text-gray-400">uid <?= (int) $event['created_by'] ?></span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-4 py-3 text-center text-gray-700">
                                <?= $event['event_date'] ? date('d/m/Y', strtotime($event['event_date'])) : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><?= esc($event['status']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center"><?= (int) ($event['total_recipients'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-center"><?= (int) ($event['issued_count'] ?? 0) ?> / <?= (int) ($event['total_recipients'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?= esc($cert_base) ?>/<?= (int) $event['id'] ?>" class="text-yellow-700 font-medium hover:underline">ดู</a>
                                <span class="text-gray-300">|</span>
                                <a href="<?= esc($cert_base) ?>/<?= (int) $event['id'] ?>/edit" class="text-gray-700 hover:underline">แก้ไข</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
