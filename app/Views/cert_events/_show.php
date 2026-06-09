<?php
/**
 * Shared show page partial — recipient-focused layout.
 *
 * @var array<string,mixed>      $event       cert_event row with details
 * @var array<int,array<mixed>>  $recipients  recipient rows (with cert join)
 * @var array<int,array<mixed>>  $students    active students list (for add-recipient)
 * @var array<int,string>        $programs    program dropdown for bulk add
 * @var string                   $cert_base   absolute base URL e.g. /newScience/admin/cert-events
 */
$cb = rtrim((string) ($cert_base ?? ''), '/');
$eid = (int) ($event['id'] ?? 0);

$pending = 0;
$issued  = 0;
$failed  = 0;
$emailSent   = 0;
$emailUnsent = 0;
foreach ($recipients as $r) {
    if (($r['status'] ?? '') === 'pending') { $pending++; }
    elseif (($r['status'] ?? '') === 'issued') { $issued++; }
    elseif (($r['status'] ?? '') === 'failed') { $failed++; }
    if (! empty($r['email_sent_at'])) { $emailSent++; }
    elseif (($r['status'] ?? '') === 'issued') { $emailUnsent++; }
}
$total = count($recipients);

$statusBadge = [
    'draft'  => ['ร่าง', '#6c757d'],
    'open'   => ['เปิด', '#16a34a'],
    'issued' => ['ออก Cert แล้ว', '#2563eb'],
    'closed' => ['ปิด',  '#f59e0b'],
];
$evStatus = $event['status'] ?? 'draft';
$evLabel  = $statusBadge[$evStatus][0] ?? $evStatus;
$evColor  = $statusBadge[$evStatus][1] ?? '#6c757d';
?>
<style>
.csh-wrap {
    max-width: 1200px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    --primary: hsl(239, 84%, 60%);
    --primary-hover: hsl(239, 84%, 55%);
    --primary-bg: hsl(239, 84%, 97%);
    
    --success-color: #047857;
    --success-bg: #ecfdf5;
    --success-border: #a7f3d0;
    
    --warning-color: #b45309;
    --warning-bg: #fffbeb;
    --warning-border: #fde68a;
    
    --danger-color: #c53030;
    --danger-bg: #fff5f5;
    --danger-border: #feb2b2;
    
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --slate-900: #0f172a;
    
    --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
    --card-shadow-hover: 0 10px 30px -4px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.03);
}

/* Custom Scrollbars */
.csh-wrap *::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.csh-wrap *::-webkit-scrollbar-track {
    background: transparent;
}
.csh-wrap *::-webkit-scrollbar-thumb {
    background: var(--slate-300);
    border-radius: 999px;
}
.csh-wrap *::-webkit-scrollbar-thumb:hover {
    background: var(--slate-400);
}

.csh-breadcrumb {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}
.csh-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 13px;
    font-weight: 600;
    color: var(--slate-500);
    text-decoration: none;
    transition: all 0.2s ease;
}
.csh-back-link:hover {
    color: var(--primary);
    transform: translateX(-2px);
}

.csh-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--slate-200);
}
.csh-header h1 {
    margin: 0;
    font-size: 1.65rem;
    font-weight: 800;
    color: var(--slate-900);
    letter-spacing: -0.025em;
    line-height: 1.25;
}
.csh-header .csh-sub {
    margin-top: 0.5rem;
    font-size: 13px;
    color: var(--slate-500);
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}
.csh-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.65rem;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.02em;
}

.csh-info-row {
    display: grid;
    grid-template-columns: minmax(0, 2.4fr) minmax(0, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 768px) {
    .csh-info-row {
        grid-template-columns: 1fr;
    }
}
.csh-card {
    background: #fff;
    border: 1px solid var(--slate-200);
    border-radius: 14px;
    padding: 1.25rem;
    box-shadow: var(--card-shadow);
}
.csh-card-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--slate-400);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 0.6rem;
}

.csh-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.csh-stat-card {
    padding: 1.25rem 1rem;
    border-radius: 14px;
    text-align: center;
    border: 1px solid var(--slate-200);
    background: #fff;
    box-shadow: var(--card-shadow);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.csh-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow-hover);
}
.csh-stat-num {
    font-size: 1.875rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 0.25rem;
}
.csh-stat-label {
    font-size: 12px;
    color: var(--slate-500);
    font-weight: 600;
}

.csh-bulk-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
    background: var(--slate-50);
    border: 1px solid var(--slate-200);
    border-radius: 14px;
    align-items: center;
}
.csh-bulk-actions .csh-bulk-label {
    font-size: 13.5px;
    color: var(--slate-600);
    font-weight: 600;
    margin-right: 0.5rem;
}

details.csh-add-toggle, details.csh-bulk-students {
    background: #fff;
    border: 1px solid var(--slate-200);
    border-radius: 14px;
    margin-bottom: 1.25rem;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
details.csh-add-toggle[open], details.csh-bulk-students[open] {
    box-shadow: var(--card-shadow-hover);
}
details.csh-add-toggle summary, details.csh-bulk-students summary {
    padding: 1rem 1.25rem;
    font-weight: 700;
    color: var(--slate-800);
    font-size: 14.5px;
    cursor: pointer;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s ease;
}
details.csh-add-toggle summary::-webkit-details-marker,
details.csh-bulk-students summary::-webkit-details-marker {
    display: none;
}
details.csh-add-toggle summary::after,
details.csh-bulk-students summary::after {
    content: '';
    display: inline-block;
    width: 18px;
    height: 18px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
    transition: transform 0.25s ease;
}
details.csh-add-toggle[open] summary::after,
details.csh-bulk-students[open] summary::after {
    transform: rotate(180deg);
}
details.csh-add-toggle summary:hover,
details.csh-bulk-students summary:hover {
    background: var(--slate-50);
}
.csh-add-form, details.csh-bulk-students form {
    padding: 1.25rem;
    border-top: 1px solid var(--slate-100);
    background: #fff;
}
.csh-add-form label, .csh-bulk-filters label {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--slate-700);
    display: block;
    margin-bottom: 0.35rem;
}
.csh-add-form input, .csh-add-form select, .csh-bulk-filters input, .csh-bulk-filters select {
    width: 100%;
    padding: 0.55rem 0.75rem;
    font-size: 13.5px;
    border: 1px solid var(--slate-300);
    border-radius: 8px;
    background: #fff;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
    box-sizing: border-box;
    outline: none;
    transition: all 0.2s ease;
}
.csh-add-form input:focus, .csh-add-form select:focus, .csh-bulk-filters input:focus, .csh-bulk-filters select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15), inset 0 1px 2px rgba(0,0,0,0.02);
}

.csh-table-wrap {
    background: #fff;
    border: 1px solid var(--slate-200);
    border-radius: 14px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    margin-bottom: 2rem;
}
.csh-table-head {
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--slate-200);
    background: var(--slate-50);
}
.csh-table-head h2 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: var(--slate-800);
}
.csh-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13.5px;
}
.csh-table th {
    background: var(--slate-50);
    padding: 0.85rem 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--slate-500);
    border-bottom: 1px solid var(--slate-200);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.csh-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--slate-100);
    vertical-align: middle;
}
.csh-table tr:hover td {
    background: var(--slate-50);
}
.csh-table tr:last-child td {
    border-bottom: none;
}
.csh-table th.csh-center, .csh-table td.csh-center {
    text-align: center;
}

.csh-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    border: 1px solid transparent;
}
.csh-status::before {
    content: '';
    display: inline-block;
    width: 5px;
    height: 5px;
    border-radius: 50%;
}

.csh-status-pending {
    background: var(--warning-bg);
    color: var(--warning-color);
    border-color: var(--warning-border);
}
.csh-status-pending::before {
    background: #d97706;
}

.csh-status-issued {
    background: var(--success-bg);
    color: var(--success-color);
    border-color: var(--success-border);
}
.csh-status-issued::before {
    background: #10b981;
}

.csh-status-failed {
    background: var(--danger-bg);
    color: var(--danger-color);
    border-color: var(--danger-border);
}
.csh-status-failed::before {
    background: #ef4444;
}

.csh-mail-sent {
    background: var(--success-bg);
    color: var(--success-color);
    border-color: var(--success-border);
}
.csh-mail-sent::before {
    background: #10b981;
}

.csh-mail-unsent {
    background: var(--slate-100);
    color: var(--slate-600);
    border-color: var(--slate-200);
}
.csh-mail-unsent::before {
    background: var(--slate-400);
}

.csh-mail-fail {
    background: var(--danger-bg);
    color: var(--danger-color);
    border-color: var(--danger-border);
}
.csh-mail-fail::before {
    background: #ef4444;
}

.csh-action-group {
    display: flex;
    gap: 0.35rem;
    flex-wrap: wrap;
    justify-content: center;
}
.csh-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.csh-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
}
.csh-btn:active {
    transform: translateY(0);
}
.csh-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.csh-btn-issue {
    background: var(--primary);
    color: #fff;
}
.csh-btn-issue:hover:not(:disabled) {
    background: var(--primary-hover);
}

.csh-btn-send {
    background: #10b981;
    color: #fff;
}
.csh-btn-send:hover:not(:disabled) {
    background: #059669;
}

.csh-btn-resend {
    background: #f59e0b;
    color: #fff;
}
.csh-btn-resend:hover:not(:disabled) {
    background: #d97706;
}

.csh-btn-reissue {
    background: #8b5cf6;
    color: #fff;
}
.csh-btn-reissue:hover:not(:disabled) {
    background: #7c3aed;
}

.csh-btn-pdf {
    background: #fff;
    color: var(--primary);
    border-color: var(--slate-200);
}
.csh-btn-pdf:hover:not(:disabled) {
    background: var(--primary-bg);
    border-color: var(--slate-300);
}

.csh-btn-remove {
    background: #fff;
    color: #ef4444;
    border-color: var(--slate-200);
}
.csh-btn-remove:hover:not(:disabled) {
    background: #fef2f2;
    border-color: #fca5a5;
}

.csh-btn-primary {
    background: var(--primary);
    color: #fff;
    padding: 0.55rem 1.15rem;
    font-size: 13.5px;
}
.csh-btn-primary:hover:not(:disabled) {
    background: var(--primary-hover);
}

.csh-btn-secondary {
    background: #fff;
    color: var(--slate-700);
    border-color: var(--slate-200);
    padding: 0.55rem 1.15rem;
    font-size: 13.5px;
}
.csh-btn-secondary:hover:not(:disabled) {
    background: var(--slate-50);
    border-color: var(--slate-300);
}

.csh-btn-danger {
    background: #ef4444;
    color: #fff;
    padding: 0.55rem 1.15rem;
    font-size: 13.5px;
}
.csh-btn-danger:hover:not(:disabled) {
    background: #dc2626;
}

.csh-btn-success {
    background: #10b981;
    color: #fff;
    padding: 0.55rem 1.15rem;
    font-size: 13.5px;
}
.csh-btn-success:hover:not(:disabled) {
    background: #059669;
}

.csh-alert {
    padding: 0.85rem 1.15rem;
    border-radius: 10px;
    font-size: 13.5px;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
}
.csh-alert-success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.csh-alert-error {
    background: #fdf2f2;
    color: #9b1c1c;
    border: 1px solid #fde8e8;
}

.csh-bulk-students {
    background: #fff;
    border: 1px solid var(--slate-200);
    border-radius: 14px;
    padding: 0;
    margin-bottom: 1.25rem;
}
.csh-bulk-filters {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
    margin: 0 0 1rem;
    align-items: end;
}
.csh-bulk-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
    margin-top: 0.5rem;
}
.csh-bulk-table th, .csh-bulk-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--slate-100);
    text-align: left;
}
.csh-bulk-table th {
    background: var(--slate-50);
    font-size: 11px;
    font-weight: 700;
    color: var(--slate-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.csh-bulk-table tr:hover td {
    background: var(--slate-50);
}
.csh-bulk-empty {
    font-size: 13px;
    color: var(--slate-400);
    padding: 1.5rem 0;
    text-align: center;
}
.csh-bulk-actions-row {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 1rem;
}
</style>

<div class="csh-wrap">
    <!-- Breadcrumb back link -->
    <div class="csh-breadcrumb">
        <a href="<?= esc($cb) ?>" class="csh-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            กลับหน้ารวมกิจกรรม e-Certificate
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="csh-alert csh-alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <?= esc((string) session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="csh-alert csh-alert-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <?= esc((string) session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="csh-header">
        <div>
            <h1><?= esc((string) ($event['title'] ?? '')) ?></h1>
            <div class="csh-sub">
                <span style="font-weight:600; color:var(--slate-700);">📅 <?= !empty($event['event_date']) ? date('d/m/Y', strtotime((string) $event['event_date'])) : 'ไม่ระบุวันที่' ?></span>
                <span class="csh-pill" style="background:<?= $evColor ?>;"><?= esc($evLabel) ?></span>
                <?php
                $statusDesc = [
                    'draft'  => 'กิจกรรมร่าง (เพิ่ม/นำเข้ารายชื่อได้ แต่ต้องเปลี่ยนเป็นสถานะ "เปิด" จึงจะเริ่มออกใบและส่งอีเมลได้)',
                    'open'   => 'เปิดใช้งาน (พร้อมสำหรับการออกใบรับรองและส่งอีเมล)',
                    'issued' => 'ออกใบประกาศแล้ว (ใบรับรองหลักเสร็จสิ้นแล้ว สามารถออกเพิ่มเติมหรือส่งซ้ำได้)',
                    'closed' => 'ปิดแล้ว (กิจกรรมสิ้นสุด ปิดรับรายชื่อเพิ่มเติม)',
                ];
                $descText = $statusDesc[$evStatus] ?? '';
                ?>
                <span style="color:var(--slate-500); font-weight: 500; font-size: 12.5px;">• <?= esc($descText) ?></span>
                <?php if (! empty($event['signer_name'])): ?>
                    <span style="margin-left:0.5rem; background: var(--slate-100); padding: 0.15rem 0.5rem; border-radius: 6px; font-weight:500;">👤 ผู้ลงนาม: <?= esc((string) $event['signer_name'] . ' ' . (string) ($event['signer_lastname'] ?? '')) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <a href="<?= esc($cb) ?>/<?= $eid ?>/edit" class="csh-btn csh-btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                แก้ไขกิจกรรม
            </a>
            <?php if ($evStatus !== 'issued'): ?>
                <a href="<?= esc($cb) ?>/<?= $eid ?>/delete" class="csh-btn csh-btn-danger" onclick="return confirm('ยืนยันการลบกิจกรรมและข้อมูลผู้รับทั้งหมด?')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    ลบกิจกรรม
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info row: template thumbnail + description -->
    <div class="csh-info-row">
        <div class="csh-card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div class="csh-card-label">รายละเอียดกิจกรรม</div>
                <?php if (! empty($event['description'])): ?>
                    <div style="font-size:14px; color:var(--slate-700); line-height:1.6; white-space: pre-wrap;"><?= esc((string) $event['description']) ?></div>
                <?php else: ?>
                    <span style="color:var(--slate-400); font-style: italic; font-size:13px;">— ไม่มีคำอธิบายรายละเอียดกิจกรรม</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="csh-card" style="padding:0.85rem; display:flex; align-items:center; justify-content:center;">
            <?= view('admin/cert_events/partials/cert_event_background_block', [
                'event'     => $event,
                'cert_base' => $cb,
            ]) ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="csh-stats">
        <div class="csh-stat-card" style="border-top: 4px solid var(--primary);">
            <div class="csh-stat-num" style="color:var(--primary);"><?= $total ?></div>
            <div class="csh-stat-label">ผู้รับทั้งหมด (คน)</div>
        </div>
        <div class="csh-stat-card" style="border-top: 4px solid var(--warning-color);">
            <div class="csh-stat-num" style="color:var(--warning-color);"><?= $pending ?></div>
            <div class="csh-stat-label">รอออกใบรับรอง</div>
        </div>
        <div class="csh-stat-card" style="border-top: 4px solid var(--success-color);">
            <div class="csh-stat-num" style="color:var(--success-color);"><?= $issued ?></div>
            <div class="csh-stat-label">ออกสำเร็จแล้ว</div>
        </div>
        <div class="csh-stat-card" style="border-top: 4px solid var(--danger-color);">
            <div class="csh-stat-num" style="color:var(--danger-color);"><?= $failed ?></div>
            <div class="csh-stat-label">ออกล้มเหลว</div>
        </div>
        <div class="csh-stat-card" style="border-top: 4px solid #8b5cf6;">
            <div class="csh-stat-num" style="color:#8b5cf6;"><?= $emailSent ?> <span style="font-size:14px; font-weight:500; color:var(--slate-400);">/ <?= $emailSent + $emailUnsent ?></span></div>
            <div class="csh-stat-label">ส่งอีเมลแล้ว (คน)</div>
        </div>
    </div>

    <!-- Bulk actions -->
    <div class="csh-bulk-actions">
        <span class="csh-bulk-label">การดำเนินการหลัก:</span>
        <?php if ($evStatus !== 'draft' && $pending > 0): ?>
            <a href="<?= esc($cb) ?>/<?= $eid ?>/issue" class="csh-btn csh-btn-primary"
               onclick="return confirm('ออกใบ + ส่งอีเมลให้ผู้รับที่ยังรออยู่ทั้งหมด <?= $pending ?> คน?')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                ออกใบ + ส่งอีเมล ทั้งหมด (<?= $pending ?> คน)
            </a>
        <?php elseif ($evStatus === 'draft'): ?>
            <button class="csh-btn" disabled style="background:var(--slate-200); color:var(--slate-400); border-color:var(--slate-300);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                ออกใบ + ส่งอีเมล ทั้งหมด
            </button>
            <span style="font-size:12.5px; color:var(--warning-color); font-weight:600; display:inline-flex; align-items:center; gap:0.25rem; background:#fffbeb; padding:0.4rem 0.75rem; border-radius:8px; border:1px solid var(--warning-border);">
                ⚠️ ต้องเปลี่ยนสถานะกิจกรรมเป็น "เปิด" ในหน้าแก้ไขก่อนจึงจะออกใบรับรองได้
            </span>
        <?php else: ?>
            <button class="csh-btn" disabled style="background:var(--slate-200); color:var(--slate-400); border-color:var(--slate-300);">
                ไม่มีรายการผู้รับที่รอการออกใบ
            </button>
        <?php endif; ?>
        <div style="flex-grow: 1;"></div>
        <div style="display:flex; gap:0.5rem;">
            <a href="<?= esc($cb) ?>/<?= $eid ?>/import" class="csh-btn csh-btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                นำเข้าจาก CSV
            </a>
            <?php if ($total > 0): ?>
                <a href="<?= esc($cb) ?>/<?= $eid ?>/export" class="csh-btn csh-btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    ส่งออก CSV
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Recipient (collapsed) -->
    <?php if ($evStatus !== 'closed' && $evStatus !== 'issued'): ?>
        <details class="csh-add-toggle">
            <summary>เพิ่มรายชื่อผู้รับทีละคน</summary>
            <form method="post" action="<?= esc($cb) ?>/<?= $eid ?>/add-recipient" class="csh-add-form">
                <?= csrf_field() ?>
                <div>
                    <label>ชื่อผู้รับ <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="recipient_name" placeholder="ชื่อ-นามสกุล" required>
                </div>
                <div>
                    <label>อีเมล <span style="color:#dc2626;">*</span></label>
                    <input type="email" name="recipient_email" placeholder="example@domain.com" required>
                </div>
                <div>
                    <label>รหัสนักศึกษา</label>
                    <input type="text" name="recipient_id_no" placeholder="รหัสนักศึกษา (ถ้ามี)">
                </div>
                <div>
                    <label>หรือค้นหาจากฐานข้อมูล</label>
                    <select name="student_id">
                        <option value="">— เลือกรายชื่อนักศึกษา —</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) $student['id'] ?>">
                                <?= esc((string) ($student['tf_name'] ?? '') . ' ' . (string) ($student['tl_name'] ?? '')) ?> (<?= esc((string) ($student['login_uid'] ?? '')) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="csh-btn csh-btn-primary" style="width:100%;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        เพิ่มรายชื่อ
                    </button>
                </div>
            </form>
        </details>

        <details class="csh-bulk-students">
            <summary>เพิ่มรายชื่อจากกลุ่มนักศึกษาในคณะ (หลายคน)</summary>
            <div class="csh-bulk-filters" style="padding: 1.25rem; border-top: 1px solid var(--slate-100); background: var(--slate-50);">
                <div>
                    <label for="cshBulkProgram">หลักสูตร / สาขาวิชา</label>
                    <select id="cshBulkProgram">
                        <option value="">— ทุกสาขาวิชา —</option>
                        <?php foreach (($programs ?? []) as $pid => $pname): ?>
                            <option value="<?= (int) $pid ?>"><?= esc((string) $pname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="cshBulkQuery">คำค้นหา (ชื่อ / รหัส / อีเมล)</label>
                    <input type="search" id="cshBulkQuery" placeholder="พิมพ์ชื่อ รหัส หรืออีเมล...">
                </div>
                <div>
                    <button type="button" class="csh-btn csh-btn-secondary" id="cshBulkSearchBtn" style="width:100%;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        ค้นหานักศึกษา
                    </button>
                </div>
            </div>

            <form method="post" action="<?= esc($cb) ?>/<?= $eid ?>/add-students-bulk" id="cshBulkForm" style="padding: 0 1.25rem 1.25rem;">
                <?= csrf_field() ?>
                <div style="overflow-x:auto; max-height:320px; overflow-y:auto; border: 1px solid var(--slate-200); border-radius: 8px; margin-bottom: 1rem;">
                    <table class="csh-bulk-table" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th style="width:38px; text-align:center; padding: 0.5rem;"><input type="checkbox" id="cshBulkSelectAll" title="เลือกทั้งหมด"></th>
                                <th style="padding: 0.5rem 0.75rem;">ชื่อ-นามสกุล</th>
                                <th style="padding: 0.5rem 0.75rem;">รหัสนักศึกษา</th>
                                <th style="padding: 0.5rem 0.75rem;">อีเมล</th>
                            </tr>
                        </thead>
                        <tbody id="cshBulkResults">
                            <tr><td colspan="4" class="csh-bulk-empty">🔍 กรุณากดปุ่ม "ค้นหานักศึกษา" เพื่อแสดงรายชื่อ</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="csh-bulk-actions-row">
                    <button type="submit" class="csh-btn csh-btn-primary" id="cshBulkSubmit" disabled>
                        เพิ่มรายชื่อที่เลือก (0 คน)
                    </button>
                    <span id="cshBulkHint" style="font-size:12.5px; color:var(--slate-500); font-weight: 500;">เลือกนักศึกษาที่เข้าร่วมกิจกรรมนี้</span>
                </div>
            </form>
        </details>
    <?php endif; ?>

    <!-- Recipients table (PRIMARY content) -->
    <div class="csh-table-wrap">
        <div class="csh-table-head">
            <h2 style="display:flex; align-items:center; gap:0.5rem; margin:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary);"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                รายชื่อผู้เข้าร่วมทั้งหมด (<?= $total ?> คน)
            </h2>
            <?php if ($total > 0): ?>
                <span style="font-size:12px; font-weight:600; color:var(--slate-500);">
                    สำเร็จ: <span style="color:var(--success-color); font-weight:700;"><?= $issued ?></span> &nbsp;•&nbsp; 
                    รอดำเนินการ: <span style="color:var(--warning-color); font-weight:700;"><?= $pending ?></span> &nbsp;•&nbsp; 
                    ล้มเหลว: <span style="color:var(--danger-color); font-weight:700;"><?= $failed ?></span>
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($recipients)): ?>
            <div class="csh-alert csh-alert-error" style="margin:1.25rem; background:var(--slate-50); border-color:var(--slate-200); color:var(--slate-600);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                ยังไม่มีรายชื่อผู้รับสำหรับกิจกรรมนี้ สามารถเพิ่มรายชื่อแบบเดี่ยว/กลุ่ม หรือนำเข้าผ่านไฟล์ CSV ด้านบน
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="csh-table">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center; padding: 0.85rem 0.5rem;">#</th>
                            <th>ข้อมูลผู้เข้าร่วม</th>
                            <th>รหัสนักศึกษา</th>
                            <th class="csh-center">สถานะใบประกาศ</th>
                            <th class="csh-center">สถานะอีเมล</th>
                            <th>เลขที่ใบประกาศ</th>
                            <th class="csh-center" style="min-width:180px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipients as $i => $r): ?>
                            <?php
                            $status   = (string) ($r['status'] ?? '');
                            $hasPdf   = ! empty($r['pdf_path']);
                            $emailOk  = ! empty($r['email_sent_at']);
                            $emailErr = ! empty($r['email_error']);
                            ?>
                            <tr>
                                <td style="text-align:center; padding: 1rem 0.5rem; color: var(--slate-400); font-weight:600;"><?= $i + 1 ?></td>
                                <td>
                                    <div style="font-weight:700; color:var(--slate-900); font-size:14px;"><?= esc((string) $r['recipient_name']) ?></div>
                                    <div style="font-size:12px; color:var(--slate-500); margin-top:0.1rem;"><?= esc((string) $r['recipient_email']) ?></div>
                                </td>
                                <td style="font-size:13px; color:var(--slate-600); font-weight:500;"><?= esc((string) ($r['recipient_id_no'] ?? '-')) ?></td>
                                <td class="csh-center">
                                    <?php if ($status === 'pending'): ?>
                                        <span class="csh-status csh-status-pending">รอดำเนินการ</span>
                                    <?php elseif ($status === 'issued'): ?>
                                        <span class="csh-status csh-status-issued">ออกแล้ว</span>
                                    <?php elseif ($status === 'failed'): ?>
                                        <span class="csh-status csh-status-failed">ล้มเหลว</span>
                                        <?php if (! empty($r['error_message'])): ?>
                                            <div style="font-size:11px; color:var(--danger-color); font-weight:500; margin-top:0.25rem;"><?= esc((string) $r['error_message']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="csh-status"><?= esc($status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="csh-center">
                                    <?php if ($emailOk): ?>
                                        <span class="csh-status csh-mail-sent">ส่งอีเมลแล้ว</span>
                                        <div style="font-size:10px; color:var(--slate-400); font-weight:500; margin-top:0.25rem;"><?= esc((string) $r['email_sent_at']) ?></div>
                                    <?php elseif ($status === 'issued' && $emailErr): ?>
                                        <span class="csh-status csh-mail-fail">ส่งไม่สำเร็จ</span>
                                        <div style="font-size:10px; color:var(--danger-color); font-weight:500; margin-top:0.25rem; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin: 0.25rem auto 0;" title="<?= esc((string) $r['email_error']) ?>"><?= esc(mb_substr((string) $r['email_error'], 0, 40)) ?></div>
                                    <?php elseif ($status === 'issued'): ?>
                                        <span class="csh-status csh-mail-unsent">ยังไม่ส่ง</span>
                                    <?php else: ?>
                                        <span style="color:var(--slate-300); font-weight: 500;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (! empty($r['certificate_no'])): ?>
                                        <code style="background:var(--slate-100); color:var(--slate-800); border:1px solid var(--slate-200); padding:0.15rem 0.4rem; border-radius:6px; font-weight:700; font-size:12px;"><?= esc((string) $r['certificate_no']) ?></code>
                                        <?php if (! empty($r['download_count'])): ?>
                                            <div style="font-size:10px; color:var(--slate-400); font-weight:500; margin-top:0.25rem;">ดาวน์โหลดแล้ว <?= (int) $r['download_count'] ?> ครั้ง</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:var(--slate-300); font-weight:500;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="csh-center">
                                    <div class="csh-action-group">
                                        <?php if ($status === 'pending'): ?>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/issue"
                                               class="csh-btn csh-btn-issue"
                                               onclick="return confirm('ยืนยันออกใบรับรองให้คุณ <?= esc((string) $r['recipient_name'], 'js') ?>? (ยังไม่จัดส่งอีเมล)');">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                                ออกใบ
                                            </a>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/remove"
                                               class="csh-btn csh-btn-remove"
                                               onclick="return confirm('ยืนยันลบผู้เข้าร่วมรายนี้ออกจากกิจกรรม?');"
                                               title="ลบรายชื่อ">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </a>
                                        <?php elseif ($status === 'issued'): ?>
                                            <?php if ($hasPdf): ?>
                                                <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/pdf"
                                                   target="_blank" class="csh-btn csh-btn-pdf">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                    PDF
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/issue"
                                               class="csh-btn csh-btn-reissue"
                                               onclick="return confirm('ยืนยันออกใบรับรองใหม่ทับใบเดิม? (เลขที่ใบประกาศเดิม แต่ระบบจะสร้างไฟล์ PDF ใหม่)');"
                                               title="ออกใบใหม่">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
                                            </a>
                                            <?php if (! $emailOk): ?>
                                                <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/send"
                                                   class="csh-btn csh-btn-send"
                                                   onclick="return confirm('ยืนยันส่งอีเมลใบรับรองแนบไฟล์ให้คุณ <?= esc((string) $r['recipient_name'], 'js') ?>?');">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                                    ส่งอีเมล
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/send"
                                                   class="csh-btn csh-btn-resend"
                                                   onclick="return confirm('ยืนยันส่งอีเมลซ้ำอีกครั้ง?');">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
                                                    ส่งซ้ำ
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($status === 'failed'): ?>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/issue"
                                               class="csh-btn csh-btn-issue"
                                               onclick="return confirm('ลองทำรายการออกใบประกาศใหม่อีกครั้ง?');">
                                                ลองใหม่
                                            </a>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/remove"
                                               class="csh-btn csh-btn-remove"
                                               onclick="return confirm('ยืนยันลบรายชื่อผู้รับนี้?');">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($evStatus !== 'closed' && $evStatus !== 'issued'): ?>
<script>
(function () {
    var searchUrl = <?= json_encode($cb . '/students-search', JSON_UNESCAPED_UNICODE) ?>;
    var tbody = document.getElementById('cshBulkResults');
    var selectAll = document.getElementById('cshBulkSelectAll');
    var submitBtn = document.getElementById('cshBulkSubmit');
    var programEl = document.getElementById('cshBulkProgram');
    var queryEl = document.getElementById('cshBulkQuery');
    var searchBtn = document.getElementById('cshBulkSearchBtn');
    if (!tbody || !submitBtn) { return; }

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function updateSubmitLabel() {
        var checked = tbody.querySelectorAll('input[name="student_ids[]"]:checked').length;
        submitBtn.disabled = checked === 0;
        submitBtn.textContent = 'เพิ่มที่เลือก (' + checked + ' คน)';
    }

    function renderRows(students) {
        if (!students.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="csh-bulk-empty">ไม่พบนักศึกษา — ลองเปลี่ยนคำค้นหาหรือหลักสูตร</td></tr>';
            if (selectAll) { selectAll.checked = false; selectAll.disabled = true; }
            updateSubmitLabel();
            return;
        }
        var html = '';
        students.forEach(function (s) {
            html += '<tr>'
                + '<td><input type="checkbox" name="student_ids[]" value="' + escapeHtml(s.id) + '"></td>'
                + '<td>' + escapeHtml(s.name) + '</td>'
                + '<td>' + escapeHtml(s.login_uid || '—') + '</td>'
                + '<td style="font-size:12px;">' + escapeHtml(s.email || '—') + '</td>'
                + '</tr>';
        });
        tbody.innerHTML = html;
        if (selectAll) {
            selectAll.disabled = false;
            selectAll.checked = false;
        }
        tbody.querySelectorAll('input[name="student_ids[]"]').forEach(function (cb) {
            cb.addEventListener('change', updateSubmitLabel);
        });
        updateSubmitLabel();
    }

    function runSearch() {
        var params = new URLSearchParams();
        var q = (queryEl && queryEl.value) ? queryEl.value.trim() : '';
        var pid = programEl ? programEl.value : '';
        if (q) { params.set('q', q); }
        if (pid) { params.set('program_id', pid); }
        tbody.innerHTML = '<tr><td colspan="4" class="csh-bulk-empty">กำลังค้นหา…</td></tr>';
        fetch(searchUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { renderRows(data.students || []); })
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="4" class="csh-bulk-empty" style="color:#b91c1c;">ค้นหาไม่สำเร็จ</td></tr>';
            });
    }

    if (searchBtn) { searchBtn.addEventListener('click', runSearch); }
    if (queryEl) {
        queryEl.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter') { ev.preventDefault(); runSearch(); }
        });
    }
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            tbody.querySelectorAll('input[name="student_ids[]"]').forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
            updateSubmitLabel();
        });
    }
})();
</script>
<?php endif; ?>
