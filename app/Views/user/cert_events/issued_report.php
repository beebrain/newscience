<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">รายงานใบรับรองที่ออกแล้ว</h1>
            <p class="text-sm text-gray-500 mt-1">ระดับคณะ — แสดงสูงสุด <?= count($rows ?? []) ?> รายการล่าสุด</p>
        </div>
        <a href="<?= esc($cert_base) ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            ← กลับรายการกิจกรรม
        </a>
    </div>

    <?php if (empty($rows)): ?>
        <div class="bg-blue-50 text-blue-900 rounded-xl p-6 text-center">ยังไม่มีรายการที่ออกใบแล้ว</div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">กิจกรรม</th>
                        <th class="text-left px-4 py-3">ผู้รับ</th>
                        <th class="text-left px-4 py-3">อีเมล</th>
                        <th class="text-left px-4 py-3">เลขที่</th>
                        <th class="text-left px-4 py-3">วันที่ออก</th>
                        <th class="text-left px-4 py-3">ผู้สร้างกิจกรรม</th>
                        <th class="text-left px-4 py-3">ส่งอีเมล</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-900"><?= esc($r['event_title'] ?? '') ?></td>
                            <td class="px-4 py-3"><?= esc($r['recipient_name'] ?? '') ?></td>
                            <td class="px-4 py-3 text-xs"><?= esc($r['recipient_email'] ?? '') ?></td>
                            <td class="px-4 py-3"><code class="text-xs bg-gray-100 px-1 rounded"><?= esc($r['certificate_no'] ?? '') ?></code></td>
                            <td class="px-4 py-3"><?= esc($r['issued_date'] ?? '') ?></td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <?= esc(trim(($r['creator_tf_name'] ?? '') . ' ' . ($r['creator_tl_name'] ?? '')) ?: ($r['creator_email'] ?? '-')) ?>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <?php if (! empty($r['email_sent_at'])): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">ส่งแล้ว</span>
                                    <div class="text-gray-500 mt-0.5"><?= esc($r['email_sent_at']) ?></div>
                                <?php elseif (! empty($r['email_error'])): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">ล้มเหลว</span>
                                    <div class="text-red-700 mt-0.5"><?= esc($r['email_error']) ?></div>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
