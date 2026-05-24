<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
$queryBase = [];
if ($search !== '') {
    $queryBase['search'] = $search;
}
$pageUrl = static function (int $page) use ($queryBase): string {
    return base_url('admin/impersonation?' . http_build_query(array_merge($queryBase, ['page' => $page])));
};
?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="text-wrap: balance;">Login As บุคลากร</h2>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">
                    เลือกบุคลากร active เพื่อเข้าใช้งานในสิทธิ์ของบัญชีนั้น พร้อมบันทึก audit log
                </p>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <?php if ($isImpersonating): ?>
            <div class="alert alert-error" role="status" aria-live="polite" style="margin-bottom: 1rem;">
                <span>คุณกำลัง Login As อยู่ กรุณาหยุด session ปัจจุบันก่อนเริ่มรายการใหม่</span>
            </div>
        <?php endif; ?>

        <form method="get" action="<?= base_url('admin/impersonation') ?>" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; margin-bottom: 1.5rem; padding: 1rem; background: var(--color-gray-50); border-radius: 8px;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 240px;">
                <label class="form-label" for="impersonationSearch">ค้นหาบุคลากร</label>
                <input
                    type="search"
                    id="impersonationSearch"
                    name="search"
                    value="<?= esc($search, 'attr') ?>"
                    class="form-control"
                    placeholder="ค้นหาชื่อ, อีเมล, login_uid…"
                    autocomplete="off"
                    spellcheck="false"
                >
            </div>
            <button type="submit" class="btn btn-primary">ค้นหา</button>
            <?php if ($search !== ''): ?>
                <a href="<?= base_url('admin/impersonation') ?>" class="btn btn-outline">ล้างตัวกรอง</a>
            <?php endif; ?>
        </form>

        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem;">
            <p class="form-hint" style="margin: 0; font-variant-numeric: tabular-nums;">
                พบ <?= (int) $total ?> รายการ
            </p>
            <p class="form-hint" style="margin: 0;">ทุกครั้งต้องระบุเหตุผลก่อนเริ่ม Login As</p>
        </div>

        <?php if (empty($targets)): ?>
            <div class="empty-state empty-state--news">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <h3>ไม่พบบุคลากรที่เข้าเงื่อนไข</h3>
                <p>ลองเปลี่ยนคำค้นหา หรือยืนยันว่าบุคลากรมีบัญชี active และเชื่อมกับรายชื่อบุคลากรแล้ว</p>
            </div>
        <?php else: ?>
            <div class="news-table-wrap">
                <table class="table" role="table" aria-label="รายการบุคลากรสำหรับ Login As">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อ-นามสกุล</th>
                            <th scope="col">อีเมล</th>
                            <th scope="col" style="width: 120px;">บทบาท</th>
                            <th scope="col" style="width: 110px;">สถานะ</th>
                            <th scope="col" style="width: 360px;">Login As</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($targets as $target): ?>
                            <?php
                            $uid = (int) ($target['uid'] ?? 0);
                            $displayName = $target['display_name'] ?: $target['normalized_email'];
                            $reasonId = 'reason-' . $uid;
                            ?>
                            <tr>
                                <td class="news-title-cell">
                                    <span style="font-weight: 600;"><?= esc($displayName) ?></span>
                                    <div style="font-size: 0.75rem; color: var(--color-gray-500);" translate="no">
                                        <?= esc($target['login_uid'] ?: 'ไม่มี login_uid') ?>
                                    </div>
                                </td>
                                <td style="word-break: break-word;" translate="no"><?= esc($target['normalized_email']) ?></td>
                                <td>
                                    <span class="badge" style="background: var(--color-gray-200); color: var(--color-gray-700);">
                                        <?= esc($target['role'] ?? 'user') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">ใช้งาน</span>
                                </td>
                                <td>
                                    <form
                                        method="post"
                                        action="<?= base_url('admin/impersonation/start/' . $uid) ?>"
                                        class="impersonation-start-form"
                                        data-target-name="<?= esc($displayName, 'attr') ?>"
                                    >
                                        <?= csrf_field() ?>
                                        <label class="form-label" for="<?= esc($reasonId, 'attr') ?>" style="font-size: 0.75rem;">เหตุผล</label>
                                        <textarea
                                            id="<?= esc($reasonId, 'attr') ?>"
                                            name="reason"
                                            class="form-control"
                                            rows="2"
                                            minlength="10"
                                            maxlength="1000"
                                            required
                                            autocomplete="off"
                                            placeholder="เช่น ช่วยตรวจสอบข้อมูลโปรไฟล์ตามคำร้องขอ…"
                                            style="resize: vertical; margin-bottom: 0.5rem;"
                                        ></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary" <?= $isImpersonating ? 'disabled' : '' ?>>
                                            เข้าใช้งานในฐานะนี้
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="แบ่งหน้ารายชื่อบุคลากร" style="margin-top: 1rem;">
                    <?php if ($hasPreviousPage): ?>
                        <a class="btn btn-outline btn-sm" href="<?= esc($pageUrl($currentPage - 1)) ?>">ก่อนหน้า</a>
                    <?php endif; ?>
                    <span style="font-size: 0.875rem; color: var(--color-gray-600); font-variant-numeric: tabular-nums;">
                        หน้า <?= (int) $currentPage ?> / <?= (int) $totalPages ?>
                    </span>
                    <?php if ($hasNextPage): ?>
                        <a class="btn btn-outline btn-sm" href="<?= esc($pageUrl($currentPage + 1)) ?>">ถัดไป</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.querySelectorAll('.impersonation-start-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            var targetName = form.getAttribute('data-target-name') || 'ผู้ใช้นี้';
            var reason = form.querySelector('textarea[name="reason"]');
            if (reason && reason.value.trim().length < 10) {
                event.preventDefault();
                reason.focus();
                return;
            }
            if (!window.confirm('ยืนยัน Login As: ' + targetName + '\nระบบจะบันทึก audit log พร้อมเหตุผลที่ระบุ')) {
                event.preventDefault();
            }
        });
    });
</script>
<?= $this->endSection() ?>
