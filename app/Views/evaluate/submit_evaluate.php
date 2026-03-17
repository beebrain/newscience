<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
helper('url');
$infoUser = $infoUser ?? [];
$userSubmissions = $userSubmissions ?? [];
$submittedPositions = $submittedPositions ?? [];
$availablePositions = $availablePositions ?? [];
$canSubmitNewRequest = $canSubmitNewRequest ?? false;
$selectedCurriculum = $selectedCurriculum ?? '';
$noRightsMessage = $noRightsMessage ?? null;
$canManageEvaluate = $can_manage_evaluate ?? false;
$cooldownInfo = $cooldownInfo ?? [];
$userName = ($infoUser['gf_name'] ?? $infoUser['tf_name'] ?? '') . ' ' . ($infoUser['gl_name'] ?? $infoUser['tl_name'] ?? '');
$saveUrl = base_url('evaluate/lecture-evaluate/save');
$base = rtrim(base_url(), '/');

use App\Models\Evaluate\TeachingEvaluationModel;

$allPositions = ['ผู้ช่วยศาสตราจารย์', 'รองศาสตราจารย์', 'ศาสตราจารย์'];
$submissionCount = count($submittedPositions);
$availableCount = count($availablePositions);
$cooldownCount = count($cooldownInfo);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    :root {
        --eval-primary: #2563eb;
        --eval-success: #10b981;
        --eval-warning: #f59e0b;
        --eval-danger: #ef4444;
        --eval-bg: #f8fafc;
        --eval-card-bg: #ffffff;
        --eval-text: #1e293b;
        --eval-text-muted: #64748b;
        --eval-border: #e2e8f0;
    }

    .eval-container {
        font-family: var(--font-primary);
        max-width: 1440px;
        margin: 0 auto;
        padding: 1.5rem 1.25rem;
    }

    .eval-container,
    .eval-container input,
    .eval-container textarea,
    .eval-container select,
    .eval-container button,
    .eval-container .btn,
    .eval-container .badge,
    .eval-container .modal-content {
        font-family: var(--font-primary);
    }

    .eval-container {
        font-size: 1.05rem;
    }

    .eval-container .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.98rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .eval-container .btn-primary {
        background: #339af0;
        border-color: #339af0;
    }

    .eval-container .btn-primary:hover {
        background: #228be6;
        border-color: #228be6;
    }

    .eval-container .btn-outline-primary {
        color: #228be6;
        border-color: #a5d8ff;
        background: #f8fbff;
    }

    .eval-container .btn-outline-secondary {
        color: var(--color-gray-700);
        border-color: var(--color-gray-300);
        background: #fff;
    }

    .eval-container .form-control,
    .eval-container .form-select {
        border-radius: 8px;
        border: 1px solid var(--color-gray-300);
        min-height: 42px;
        box-shadow: none;
    }

    .eval-container .form-control:focus,
    .eval-container .form-select:focus {
        border-color: #4dabf7;
        box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.12);
    }

    .eval-hero {
        background: #ffffff;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .eval-hero-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--color-gray-900);
        margin-bottom: 0.35rem;
    }

    .eval-hero-subtitle {
        font-size: 1.2rem;
        color: var(--color-gray-600);
        margin-bottom: 0;
    }

    .eval-hero-actions {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .eval-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.25rem;
    }

    .eval-stat-card {
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .eval-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        background: #339af0;
        flex-shrink: 0;
    }

    .eval-stat-card.success .eval-stat-icon {
        background: #2f9e44;
    }

    .eval-stat-card.warning .eval-stat-icon {
        background: #f08c00;
    }

    .eval-stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        color: var(--color-gray-900);
    }

    .eval-stat-label {
        font-size: 0.95rem;
        color: var(--color-gray-600);
        margin-top: 0.2rem;
    }

    .eval-card {
        background: var(--eval-card-bg);
        border: 1px solid var(--eval-border);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }

    .eval-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .sidebar-card,
    .content-card {
        background: var(--eval-card-bg);
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.5rem;
        padding: 1.25rem;
    }

    .card-section-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 1.12rem;
        font-weight: 700;
        color: var(--color-gray-900);
        margin-bottom: 0.25rem;
    }

    .card-section-title i {
        color: var(--color-blue-600);
    }

    .user-profile-card {
        background: #f8fafc;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 1rem;
    }

    .user-profile-name {
        font-size: 1.12rem;
        font-weight: 700;
        color: var(--color-gray-900);
        margin-bottom: 0.2rem;
    }

    .user-profile-email {
        font-size: 0.98rem;
        color: var(--color-gray-600);
        word-break: break-word;
    }

    .eval-card-header {
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Custom Modal */
    .ns-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 1100;
    }

    .ns-modal-backdrop.is-open {
        display: flex;
    }

    .ns-modal-box {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 25px 60px rgba(3, 105, 161, 0.22);
        width: min(100%, 860px);
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        animation: nsModalIn 0.25s ease-out;
        overflow: hidden;
    }

    @keyframes nsModalIn {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .ns-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        flex-shrink: 0;
    }

    .ns-modal-header h5 {
        margin: 0;
        font-size: 1.0625rem;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ns-modal-close {
        border: none;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .ns-modal-close:hover {
        background: rgba(255, 255, 255, 0.28);
    }

    .ns-modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
        background: #f8fafc;
    }

    .ns-modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #fff;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    /* Form sections */
    .ns-form-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .ns-form-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #0369a1;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .ns-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.875rem;
    }

    .ns-form-grid.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .ns-form-grid.full {
        grid-template-columns: 1fr;
    }

    @media (max-width: 600px) {

        .ns-form-grid,
        .ns-form-grid.cols-3 {
            grid-template-columns: 1fr;
        }

        .ns-modal-box {
            max-height: 100vh;
            border-radius: 0;
        }
    }

    .ns-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.375rem;
    }

    .ns-label .req {
        color: #ef4444;
    }

    .eval-card-header i {
        font-size: 1.25rem;
    }

    .eval-card-body {
        padding: 1.5rem;
    }

    .eval-submission-card {
        background: white;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .eval-submission-card:hover {
        border-color: #a5d8ff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .eval-submission-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .eval-submission-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .eval-submission-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem 1rem;
    }

    .eval-data-chip {
        background: #f8f9fa;
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        padding: 0.85rem 0.95rem;
    }

    .eval-data-label {
        display: block;
        font-size: 0.88rem;
        color: var(--color-gray-600);
        margin-bottom: 0.2rem;
    }

    .eval-data-value {
        font-size: 1rem;
        color: var(--color-gray-900);
        font-weight: 600;
        word-break: break-word;
    }

    .eval-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .eval-badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .eval-badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .eval-badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .eval-submission-title {
        font-size: 1.08rem;
        font-weight: 600;
        color: var(--eval-text);
        margin-bottom: 0.5rem;
    }

    .eval-submission-meta {
        font-size: 0.92rem;
        color: var(--eval-text-muted);
        margin-top: 0.25rem;
    }

    .eval-container .form-label,
    .eval-container .modal-title,
    .eval-container .badge,
    .eval-container .text-muted,
    .eval-container small,
    .eval-container .small {
        font-size: 0.96rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .eval-container {
            padding: 1rem;
        }

        .eval-stat-grid {
            grid-template-columns: 1fr;
        }

        .eval-submission-grid {
            grid-template-columns: 1fr;
        }

        .eval-hero-actions {
            justify-content: flex-start;
        }

        .eval-submission-top {
            flex-direction: column;
        }

        .eval-card-header {
            padding: 1rem;
        }

        .eval-card-body {
            padding: 1rem;
        }
    }
</style>

<!-- Header -->
<div class="eval-container">
    <div class="eval-hero">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="eval-hero-title"><i class="bi bi-clipboard-check me-2"></i>ระบบการประเมินการสอน</div>
                <p class="eval-hero-subtitle">จัดการคำร้อง เอกสารแนบ และติดตามสถานะการประเมินได้จากหน้าเดียวในรูปแบบการ์ดที่อ่านง่าย</p>
            </div>
            <div class="col-lg-5">
                <div class="eval-hero-actions">
                    <a href="<?= esc(base_url('evaluate/card')) ?>" class="btn btn-outline-secondary"><i class="bi bi-person-lines-fill me-1"></i>แบบประเมินของตนเอง</a>
                    <?php if ($canManageEvaluate): ?>
                        <a href="<?= esc(base_url('evaluate/admin')) ?>" class="btn btn-outline-primary"><i class="bi bi-gear me-1"></i>จัดการระบบประเมิน</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="eval-stat-grid">
            <div class="eval-stat-card">
                <div class="eval-stat-icon"><i class="bi bi-journal-check"></i></div>
                <div>
                    <div class="eval-stat-value"><?= esc((string) $submissionCount) ?></div>
                    <div class="eval-stat-label">คำร้องที่ส่งแล้ว</div>
                </div>
            </div>
            <div class="eval-stat-card success">
                <div class="eval-stat-icon"><i class="bi bi-send-check"></i></div>
                <div>
                    <div class="eval-stat-value"><?= esc((string) $availableCount) ?></div>
                    <div class="eval-stat-label">ตำแหน่งที่ยังส่งได้</div>
                </div>
            </div>
            <div class="eval-stat-card warning">
                <div class="eval-stat-icon"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="eval-stat-value"><?= esc((string) $cooldownCount) ?></div>
                    <div class="eval-stat-label">ตำแหน่งที่อยู่ในช่วงรอ</div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($noRightsMessage): ?>
        <div class="alert alert-warning"><?= esc($noRightsMessage) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sidebar-card">
                <div class="card-section-title"><i class="bi bi-person-circle"></i><span>ข้อมูลผู้ใช้</span></div>
                <div class="user-profile-card">
                    <div class="user-profile-name"><?= esc($userName ?: 'ผู้ใช้') ?></div>
                    <?php if (!empty($infoUser['email'])): ?>
                        <div class="user-profile-email"><?= esc($infoUser['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-card">
                <div class="card-section-title"><i class="bi bi-award"></i><span>สถานะการประเมินตำแหน่ง</span></div>
                <?php
                $byPos = [];
                foreach ($userSubmissions as $s) {
                    if (!empty($s['position'])) $byPos[$s['position']] = $s;
                }
                foreach ($allPositions as $pos):
                    $sub = $byPos[$pos] ?? null;
                    $status = (int) ($sub['status'] ?? -1);
                    $isCooldown = isset($cooldownInfo[$pos]);
                    if ($isCooldown) {
                        $cls = 'cooldown';
                    } elseif ($sub) {
                        $cls = match ($status) {
                            TeachingEvaluationModel::STATUS_APPROVED => 'completed',
                            TeachingEvaluationModel::STATUS_REJECTED, TeachingEvaluationModel::STATUS_EXPIRED => 'rejected',
                            default => 'pending',
                        };
                    } else {
                        $cls = '';
                    }
                ?>
                    <div class="status-item <?= $cls ?>">
                        <span class="status-title"><?= esc($pos) ?></span>
                        <small class="d-block text-muted">
                            <?php if ($isCooldown): ?>
                                <i class="bi bi-hourglass-split me-1"></i>อยู่ระหว่างดำเนินการ — ส่งใหม่ได้เมื่อคำร้องสิ้นสุด
                            <?php elseif ($sub): ?>
                                <?php
                                $statusText = match ($status) {
                                    TeachingEvaluationModel::STATUS_APPROVED => 'อนุมัติแล้ว',
                                    TeachingEvaluationModel::STATUS_REJECTED => 'ไม่ผ่านการประเมิน',
                                    TeachingEvaluationModel::STATUS_EXPIRED  => 'หมดอายุ',
                                    default => 'รอการประเมิน',
                                };
                                ?>
                                <?= $statusText ?> · วันที่ส่ง: <?= esc($sub['submit_date'] ?? '-') ?>
                            <?php else: ?>
                                ยังไม่ได้ส่งคำร้อง
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
                <div class="summary-note small">
                    <strong>สรุป:</strong> ส่งแล้ว <?= count($submittedPositions) ?>/<?= count($allPositions) ?> ตำแหน่ง
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-8">
            <?php if ($canSubmitNewRequest): ?>
                <div class="content-card">
                    <h6 class="text-primary mb-3"><i class="bi bi-send me-2"></i>ตำแหน่งที่สามารถส่งได้</h6>
                    <p class="text-muted small"><?= implode(', ', array_map('esc', $availablePositions)) ?></p>
                    <button type="button" class="btn btn-primary" onclick="openSubmitModal()">
                        <i class="bi bi-send me-1"></i>ส่งคำร้องใหม่
                    </button>
                </div>
            <?php else: ?>
                <div class="content-card text-center py-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <h6 class="mt-2">ส่งครบทุกตำแหน่งแล้ว</h6>
                    <small class="text-muted">คุณได้ส่งคำร้องขอประเมินครบทุกตำแหน่งแล้ว หรืออยู่ระหว่างรอ cooldown</small>
                </div>
            <?php endif; ?>

            <?php if ($userSubmissions === []): ?>
                <div class="content-card">
                    <div class="eval-empty-state">
                        <i class="bi bi-inbox"></i>
                        <div class="fw-semibold mb-2 text-dark">ยังไม่มีประวัติการส่งคำร้อง</div>
                        <div>เมื่อคุณส่งคำร้องแล้ว รายการจะปรากฏในรูปแบบการ์ดที่นี่</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($userSubmissions as $idx => $sub): ?>
                <div class="eval-submission-card">
                    <div class="eval-submission-top">
                        <div>
                            <div class="eval-submission-title"><i class="bi bi-file-earmark-text text-primary me-2"></i>คำร้องที่ <?= $idx + 1 ?></div>
                            <div class="eval-submission-meta">วันที่ส่ง: <?= esc($sub['submit_date'] ?? '-') ?> · ตำแหน่ง: <?= esc($sub['position'] ?? '-') ?></div>
                        </div>
                        <div class="eval-submission-actions">
                            <button type="button" class="btn btn-outline-danger btn-sm genpdf-btn" data-submission='<?= htmlspecialchars(json_encode($sub), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="bi bi-file-pdf me-1"></i>สร้าง PDF
                            </button>
                            <?php if (!empty($sub['file_doc'])): ?>
                                <a href="<?= esc(base_url('serve/uploads/documents/' . $sub['file_doc'])) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>เอกสาร</a>
                            <?php endif; ?>
                            <?php if (!empty($sub['link_video'])): ?>
                                <a href="<?= esc($sub['link_video']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-play-circle me-1"></i>วิดีโอ</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="eval-submission-grid">
                        <div class="eval-data-chip">
                            <span class="eval-data-label">สาขา</span>
                            <span class="eval-data-value"><?= esc($sub['position_major'] ?? '-') ?></span>
                        </div>
                        <div class="eval-data-chip">
                            <span class="eval-data-label">รายวิชา</span>
                            <span class="eval-data-value"><?= esc($sub['subject_name'] ?? '-') ?></span>
                        </div>
                        <div class="eval-data-chip">
                            <span class="eval-data-label">รหัสวิชา</span>
                            <span class="eval-data-value"><?= esc($sub['subject_id'] ?? '-') ?></span>
                        </div>
                        <div class="eval-data-chip">
                            <span class="eval-data-label">สถานะ</span>
                            <span class="eval-data-value">
                                <?php
                                $st = (int)($sub['status'] ?? 0);
                                $badgeCls = match ($st) {
                                    TeachingEvaluationModel::STATUS_APPROVED => 'bg-success',
                                    TeachingEvaluationModel::STATUS_REJECTED => 'bg-danger',
                                    TeachingEvaluationModel::STATUS_EXPIRED  => 'bg-secondary',
                                    default => 'bg-warning',
                                };
                                $badgeText = match ($st) {
                                    TeachingEvaluationModel::STATUS_APPROVED => 'อนุมัติแล้ว',
                                    TeachingEvaluationModel::STATUS_REJECTED => 'ไม่ผ่าน',
                                    TeachingEvaluationModel::STATUS_EXPIRED  => 'หมดอายุ',
                                    default => 'รอการอนุมัติ',
                                };
                                ?>
                                <span class="badge <?= $badgeCls ?>"><?= $badgeText ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Custom Submit Modal -->
<div class="ns-modal-backdrop" id="submitModalBackdrop" onclick="if(event.target===this)closeSubmitModal()">
    <div class="ns-modal-box">
        <div class="ns-modal-header">
            <h5><i class="bi bi-clipboard-list"></i> แบบฟอร์มขอส่งการประเมินการสอน</h5>
            <button type="button" class="ns-modal-close" onclick="closeSubmitModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="ns-modal-body">
            <form id="evaluationForm" enctype="multipart/form-data">
                <input type="hidden" name="position" id="formPosition" value="">

                <!-- ส่วนที่ 1: ข้อมูลผู้ขอ -->
                <div class="ns-form-section">
                    <div class="ns-form-section-title"><i class="bi bi-person-badge"></i> ข้อมูลผู้ขอรับการประเมิน</div>
                    <div class="ns-form-grid cols-3">
                        <div>
                            <label class="ns-label">ชื่อ <span class="req">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div>
                            <label class="ns-label">นามสกุล <span class="req">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div>
                            <label class="ns-label">ยศ / คำนำหน้า</label>
                            <input type="text" class="form-control" name="title_thai">
                        </div>
                    </div>
                    <div class="ns-form-grid" style="margin-top:0.875rem;">
                        <div>
                            <label class="ns-label">หลักสูตร</label>
                            <input type="text" class="form-control" name="curriculum_name" value="<?= esc($selectedCurriculum ?? '') ?>">
                        </div>
                        <div>
                            <label class="ns-label">ตำแหน่งที่ขอรับการประเมิน <span class="req">*</span></label>
                            <select class="form-select" name="position_select" id="positionSelect" onchange="document.getElementById('formPosition').value=this.value;">
                                <option value="">เลือกตำแหน่ง</option>
                                <?php foreach ($availablePositions as $aPos): ?>
                                    <option value="<?= esc($aPos) ?>"><?= esc($aPos) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="ns-label">สาขาที่เสนอ</label>
                            <input type="text" class="form-control" name="position_major">
                        </div>
                        <div>
                            <label class="ns-label">รหัสสาขา</label>
                            <input type="text" class="form-control" name="position_major_id">
                        </div>
                    </div>
                </div>

                <!-- ส่วนที่ 2: ข้อมูลรายวิชา -->
                <div class="ns-form-section">
                    <div class="ns-form-section-title"><i class="bi bi-journal-text"></i> ข้อมูลรายวิชา</div>
                    <div class="ns-form-grid cols-3">
                        <div>
                            <label class="ns-label">วันที่เริ่มเข้าทำงาน</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div>
                            <label class="ns-label">รหัสวิชา</label>
                            <input type="text" class="form-control" name="subject_id">
                        </div>
                        <div>
                            <label class="ns-label">ชื่อรายวิชา</label>
                            <input type="text" class="form-control" name="subject_name">
                        </div>
                        <div>
                            <label class="ns-label">หน่วยกิต</label>
                            <input type="text" class="form-control" name="subject_credit">
                        </div>
                        <div>
                            <label class="ns-label">ผู้สอนร่วม</label>
                            <input type="text" class="form-control" name="subject_teacher" value="-">
                        </div>
                        <div>
                            <label class="ns-label">ลิงก์วิดีโอ</label>
                            <input type="url" class="form-control" name="link_video" id="link_video" placeholder="https://...">
                        </div>
                    </div>
                    <div id="videoStatusWrap" style="margin-top:0.5rem;">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="checkVideoBtn" style="font-size:0.8rem;">
                            <i class="bi bi-globe"></i> ตรวจสอบลิงก์วิดีโอ
                        </button>
                    </div>
                    <div style="margin-top:0.875rem;">
                        <label class="ns-label">คำอธิบายรายวิชา</label>
                        <textarea class="form-control" name="subject_detail" rows="2"></textarea>
                    </div>
                </div>

                <!-- ส่วนที่ 3: เอกสารแนบ -->
                <div class="ns-form-section">
                    <div class="ns-form-section-title"><i class="bi bi-paperclip"></i> เอกสารแนบ</div>
                    <label class="ns-label">ไฟล์เอกสารประกอบ <span class="req">*</span></label>
                    <input type="file" class="form-control" id="filedoc" name="filedoc" accept=".pdf,.doc,.docx" required>
                </div>
            </form>
        </div>
        <div class="ns-modal-footer">
            <button type="button" class="btn btn-outline-secondary" onclick="closeSubmitModal()">
                <i class="bi bi-x"></i> ยกเลิก
            </button>
            <button type="submit" form="evaluationForm" class="btn btn-primary" id="submitBtn">
                <i class="bi bi-send"></i> ส่งคำร้อง
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>pdfmake/build/pdfmake.min.js"></script>
<script src="<?= base_url() ?>pdfmake/build/vfs_fonts.js"></script>
<script src="<?= base_url() ?>pdfmake/scriptgen.js"></script>
<script src="<?= base_url() ?>js/evaluate-validator.js"></script>
<script>
    var saveUrl = <?= json_encode($saveUrl) ?>;
    var availablePositions = <?= json_encode($availablePositions) ?>;

    async function runVideoAccessibilityCheck() {
        const videoInput = document.getElementById('link_video');
        if (!videoInput) {
            return;
        }

        const url = videoInput.value.trim();
        if (!url) {
            alert('กรุณากรอกลิงก์วิดีโอก่อนตรวจสอบ');
            return;
        }

        const parent = videoInput.parentNode;
        const statusDiv = document.getElementById('videoStatus') || document.createElement('div');
        statusDiv.id = 'videoStatus';
        statusDiv.className = 'eval-status';
        parent.appendChild(statusDiv);

        const oldPreview = document.getElementById('videoPreview');
        if (oldPreview) {
            oldPreview.remove();
        }

        statusDiv.innerHTML = '<span class="eval-status-icon"><i class="bi bi-hourglass-split"></i></span><span>กำลังตรวจสอบการเข้าถึงแบบ Public...</span>';

        const result = await EvaluateValidator.checkUrlAccessibility(url);

        if (result.accessible) {
            statusDiv.className = 'eval-status eval-status-success';
            statusDiv.innerHTML = '<span class="eval-status-icon"><i class="bi bi-check-circle"></i></span><span>ลิงก์วิดีโอสามารถเข้าถึงได้แบบ Public' + (result.title ? ' - ' + result.title : '') + '</span>';

            if (result.warning) {
                statusDiv.innerHTML += '<div class="small mt-1">' + result.warning + '</div>';
            }

            if (result.type === 'youtube') {
                const previewDiv = document.createElement('div');
                previewDiv.id = 'videoPreview';
                previewDiv.className = 'eval-video-preview';
                const videoId = EvaluateValidator.extractYouTubeId(url);
                previewDiv.innerHTML = '<iframe src="https://www.youtube.com/embed/' + videoId + '" allowfullscreen></iframe>';
                parent.appendChild(previewDiv);
                setTimeout(() => previewDiv.classList.add('active'), 100);
            }
        } else {
            statusDiv.className = 'eval-status eval-status-error';
            statusDiv.innerHTML = '<span class="eval-status-icon"><i class="bi bi-x-circle"></i></span><span>' + (result.error || 'ไม่สามารถเข้าถึงวิดีโอได้แบบ Public') + '</span>';
        }
    }

    document.getElementById('checkVideoBtn')?.addEventListener('click', runVideoAccessibilityCheck);
    document.getElementById('link_video')?.addEventListener('blur', function() {
        if (this.value.trim()) {
            runVideoAccessibilityCheck();
        }
    });

    // File Upload Validation
    document.getElementById('filedoc')?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const result = EvaluateValidator.checkUploadedFile(file);
        const uploadDiv = this.closest('.eval-file-upload');
        const statusDiv = document.getElementById('fileStatus') || document.createElement('div');
        statusDiv.id = 'fileStatus';

        if (!result.valid) {
            statusDiv.className = 'eval-status eval-status-error';
            statusDiv.innerHTML = '<span class="eval-status-icon"><i class="bi bi-x-circle"></i></span><span>' + result.error + '</span>';
            this.value = '';
            uploadDiv?.classList.remove('has-file');
        } else {
            uploadDiv?.classList.add('has-file');

            if (result.isLargeFile) {
                statusDiv.className = 'eval-status eval-status-warning';
                statusDiv.innerHTML = '<span class="eval-status-icon"><i class="bi bi-exclamation-triangle"></i></span><span>ไฟล์ขนาด ' + result.sizeMB + ' MB เกิน 50 MB - ควรให้เป็นลิงก์แทน</span>';

                // Add link option
                const linkOption = document.createElement('div');
                linkOption.className = 'mt-2';
                linkOption.innerHTML = '<input type="url" class="eval-form-control" name="file_link" placeholder="หรือใส่ลิงก์ไฟล์ (Google Drive, OneDrive, etc.)">\n<p class="text-muted small mt-1"><i class="bi bi-info-circle"></i> แนะนำให้อัปโหลดไฟล์ไปยัง Google Drive และตั้งค่า "Anyone with the link can view"</p>';
                this.parentNode.appendChild(linkOption);
            } else {
                statusDiv.className = 'eval-status eval-status-success';
                statusDiv.innerHTML = '<span class="eval-status-icon"><i class="bi bi-check-circle"></i></span><span>ไฟล์ ' + result.name + ' (' + result.sizeMB + ' MB) พร้อมใช้งาน</span>';
            }
        }

        this.parentNode.appendChild(statusDiv);
    });

    function openSubmitModal() {
        if (availablePositions.length) {
            document.getElementById('formPosition').value = availablePositions[0];
            document.getElementById('positionSelect').value = availablePositions[0];
        }
        document.getElementById('submitModalBackdrop').classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSubmitModal() {
        document.getElementById('submitModalBackdrop').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    document.getElementById('evaluationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var pos = document.getElementById('formPosition').value;
        if (!pos) {
            alert('กรุณาเลือกตำแหน่ง');
            return;
        }
        var fd = new FormData(this);
        fd.set('position', pos);
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...';
        fetch(saveUrl, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(function(res) {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message || 'เกิดข้อผิดพลาด');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send"></i> ส่งคำร้อง';
                }
            })
            .catch(function() {
                alert('เกิดข้อผิดพลาด');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i> ส่งคำร้อง';
            });
    });

    $('.genpdf-btn').on('click', function() {
        var data = $(this).data('submission');
        if (typeof makepdf === 'function' && data) {
            var dataname = [(data.title_thai || '') + ' ' + (data.first_name || '') + ' ' + (data.last_name || ''), data.curriculum || '', data.start_date || '', data.position || '', data.position_major || '', data.position_major_id || '', 'ผู้ช่วยศาสตราจารย์'];
            var datasubject = [data.subject_id || '', data.subject_name || '', data.subject_credit || '', data.subject_teacher || '', data.subject_detail || ''];
            makepdf(dataname, datasubject, []);
        } else alert('ไม่พบฟังก์ชันสร้าง PDF');
    });
</script>
<?= $this->endSection() ?>