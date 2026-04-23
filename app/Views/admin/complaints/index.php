<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$selectedId = (int) ($selectedComplaint['id'] ?? 0);
$buildQuery = static function (array $overrides = []) use ($currentStatus, $search) {
    $params = array_merge([
        'status' => $currentStatus,
        'search' => $search,
    ], $overrides);

    $params = array_filter($params, static fn($value) => $value !== '' && $value !== null);
    $query = http_build_query($params);

    return base_url('admin/complaints' . ($query !== '' ? '?' . $query : ''));
};
$statusClassMap = [
    'new' => 'complaint-status--new',
    'in_progress' => 'complaint-status--progress',
    'closed' => 'complaint-status--closed',
];
?>

<div class="card">
    <div class="card-header card-header--with-context">
        <div>
            <h2>รายการร้องเรียน</h2>
            <p class="card-header__context">ดูรายการเรื่องร้องเรียนล่าสุดและอัปเดตสถานะโดย `Super Admin` เท่านั้น</p>
        </div>
    </div>

    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form method="get" action="<?= base_url('admin/complaints') ?>" class="complaint-filter">
            <div>
                <label for="status" class="form-label">สถานะ</label>
                <select name="status" id="status" class="form-control">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= $currentStatus === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="search" class="form-label">ค้นหา</label>
                <input type="text" id="search" name="search" class="form-control" value="<?= esc($search) ?>" placeholder="ชื่อ, อีเมล, หัวข้อ">
            </div>
            <div class="complaint-filter__actions">
                <button type="submit" class="btn btn-primary btn-sm">กรองรายการ</button>
                <a href="<?= base_url('admin/complaints') ?>" class="btn btn-secondary btn-sm">ล้างตัวกรอง</a>
            </div>
        </form>

        <div class="complaint-admin-layout">
            <div class="complaint-list-panel">
                <?php if ($complaints === []): ?>
                    <div class="complaint-empty">ยังไม่มีรายการร้องเรียนตามเงื่อนไขที่เลือก</div>
                <?php else: ?>
                    <div class="complaint-list">
                        <?php foreach ($complaints as $complaint): ?>
                            <?php
                            $rowStatus = (string) ($complaint['status'] ?? 'new');
                            $rowUrl = $buildQuery(['selected' => (int) $complaint['id']]);
                            ?>
                            <a href="<?= esc($rowUrl) ?>" class="complaint-list__item <?= $selectedId === (int) $complaint['id'] ? 'is-active' : '' ?>">
                                <div class="complaint-list__top">
                                    <strong>#<?= (int) $complaint['id'] ?> <?= esc($complaint['subject'] ?? '-') ?></strong>
                                    <span class="complaint-status <?= esc($statusClassMap[$rowStatus] ?? 'complaint-status--new') ?>">
                                        <?= esc($statusOptions[$rowStatus] ?? $rowStatus) ?>
                                    </span>
                                </div>
                                <div class="complaint-list__meta">
                                    <span><?= esc($complaint['complainant_name'] ?? '-') ?></span>
                                    <span><?= esc($complaint['complainant_email'] ?? '-') ?></span>
                                </div>
                                <div class="complaint-list__date"><?= esc($complaint['created_at'] ?? '-') ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if (isset($pager) && $pager): ?>
                        <div class="complaint-pagination">
                            <?= $pager->links() ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="complaint-detail-panel">
                <?php if (!$selectedComplaint): ?>
                    <div class="complaint-empty">เลือกรายการร้องเรียนเพื่อดูรายละเอียด</div>
                <?php else: ?>
                    <?php $selectedStatus = (string) ($selectedComplaint['status'] ?? 'new'); ?>
                    <div class="complaint-detail-card">
                        <div class="complaint-detail-card__header">
                            <div>
                                <h3>#<?= (int) $selectedComplaint['id'] ?> <?= esc($selectedComplaint['subject'] ?? '-') ?></h3>
                                <p>ส่งเมื่อ <?= esc($selectedComplaint['created_at'] ?? '-') ?></p>
                            </div>
                            <span class="complaint-status <?= esc($statusClassMap[$selectedStatus] ?? 'complaint-status--new') ?>">
                                <?= esc($statusOptions[$selectedStatus] ?? $selectedStatus) ?>
                            </span>
                        </div>

                        <div class="complaint-detail-grid">
                            <div>
                                <span class="complaint-label">ผู้ร้องเรียน</span>
                                <div><?= esc($selectedComplaint['complainant_name'] ?? '-') ?></div>
                            </div>
                            <div>
                                <span class="complaint-label">อีเมล</span>
                                <div><a href="mailto:<?= esc($selectedComplaint['complainant_email'] ?? '') ?>"><?= esc($selectedComplaint['complainant_email'] ?? '-') ?></a></div>
                            </div>
                            <div>
                                <span class="complaint-label">โทรศัพท์</span>
                                <div><?= esc($selectedComplaint['complainant_phone'] ?? '-') ?></div>
                            </div>
                            <div>
                                <span class="complaint-label">IP Address</span>
                                <div><?= esc($selectedComplaint['ip_address'] ?? '-') ?></div>
                            </div>
                        </div>

                        <div class="complaint-section">
                            <span class="complaint-label">รายละเอียด</span>
                            <div class="complaint-detail-text"><?= nl2br(esc((string) ($selectedComplaint['detail'] ?? '-'))) ?></div>
                        </div>

                        <?php if (!empty($selectedComplaint['attachment_path'])): ?>
                            <div class="complaint-section">
                                <span class="complaint-label">ไฟล์แนบ</span>
                                <div>
                                    <a href="<?= base_url('serve/uploads/' . ltrim((string) $selectedComplaint['attachment_path'], '/')) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                                        เปิดไฟล์แนบ
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="complaint-section">
                            <span class="complaint-label">อัปเดตสถานะ</span>
                            <form method="post" action="<?= base_url('admin/complaints/update-status/' . (int) $selectedComplaint['id']) . '?' . http_build_query(array_filter(['selected' => $selectedId, 'status' => $currentStatus, 'search' => $search, 'page' => service('request')->getGet('page')])) ?>">
                                <?= csrf_field() ?>
                                <div class="complaint-status-form">
                                    <select name="status" class="form-control" style="min-width: 220px;">
                                        <?php foreach ($statusOptions as $value => $label): ?>
                                            <option value="<?= esc($value) ?>" <?= $selectedStatus === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">บันทึกสถานะ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.complaint-filter {
    display: grid;
    grid-template-columns: 220px minmax(220px, 1fr) auto;
    gap: 1rem;
    align-items: end;
    margin-bottom: 1.5rem;
}

.complaint-filter__actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.complaint-admin-layout {
    display: grid;
    grid-template-columns: minmax(300px, 0.95fr) minmax(360px, 1.25fr);
    gap: 1.5rem;
    align-items: start;
}

.complaint-list,
.complaint-detail-card,
.complaint-empty {
    background: #fff;
    border: 1px solid var(--color-gray-200);
    border-radius: 14px;
}

.complaint-list {
    display: grid;
    overflow: hidden;
}

.complaint-list__item {
    display: block;
    padding: 1rem;
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid var(--color-gray-200);
}

.complaint-list__item:last-child {
    border-bottom: none;
}

.complaint-list__item.is-active {
    background: rgba(59, 130, 246, 0.08);
}

.complaint-list__top,
.complaint-list__meta {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.complaint-list__meta,
.complaint-list__date,
.complaint-detail-card__header p,
.complaint-label {
    color: var(--color-gray-600);
    font-size: 0.9rem;
}

.complaint-list__date {
    margin-top: 0.5rem;
}

.complaint-detail-card {
    padding: 1.25rem;
}

.complaint-detail-card__header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: start;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--color-gray-200);
}

.complaint-detail-card__header h3 {
    margin: 0 0 0.25rem 0;
}

.complaint-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.complaint-section {
    margin-top: 1.25rem;
}

.complaint-label {
    display: block;
    margin-bottom: 0.35rem;
    font-weight: 600;
}

.complaint-detail-text {
    padding: 1rem;
    border-radius: 12px;
    background: var(--color-gray-50);
    border: 1px solid var(--color-gray-200);
    line-height: 1.65;
}

.complaint-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.complaint-status--new {
    background: rgba(37, 99, 235, 0.14);
    color: #1d4ed8;
}

.complaint-status--progress {
    background: rgba(245, 158, 11, 0.16);
    color: #b45309;
}

.complaint-status--closed {
    background: rgba(34, 197, 94, 0.16);
    color: #15803d;
}

.complaint-status-form {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}

.complaint-empty {
    padding: 2rem 1.25rem;
    text-align: center;
    color: var(--color-gray-600);
}

.complaint-pagination {
    margin-top: 1rem;
}

@media (max-width: 1100px) {
    .complaint-admin-layout,
    .complaint-filter,
    .complaint-detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?= $this->endSection() ?>
