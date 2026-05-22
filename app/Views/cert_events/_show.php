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
.csh-wrap { max-width: 1200px; margin: 0 auto; }
.csh-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;
}
.csh-header h1 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #0f172a; }
.csh-header .csh-sub { margin: 0.35rem 0 0; font-size: 13px; color: #64748b; display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
.csh-pill { display:inline-block; padding:0.15rem 0.55rem; border-radius:9999px; font-size:11px; font-weight:700; color:#fff; }

.csh-info-row { display:grid; grid-template-columns: minmax(0, 2.4fr) minmax(0, 1fr); gap: 1rem; margin-bottom: 1rem; }
@media (max-width: 720px) { .csh-info-row { grid-template-columns: 1fr; } }
.csh-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:0.85rem 1rem; }
.csh-card-label { font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.02em; text-transform:uppercase; margin-bottom:0.35rem; }

.csh-stats { display:grid; grid-template-columns: repeat(auto-fit, minmax(120px,1fr)); gap:0.5rem; margin-bottom: 1rem; }
.csh-stat-card { padding:0.75rem; border-radius:8px; text-align:center; border:1px solid rgba(15,23,42,0.06); }
.csh-stat-num { font-size:1.5rem; font-weight:800; line-height:1.2; }
.csh-stat-label { font-size:11px; color:#475569; font-weight:600; margin-top:0.2rem; }

.csh-bulk-actions { display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom: 1rem; padding: 0.75rem; background: #f8fafc; border:1px solid #e2e8f0; border-radius:8px; align-items:center; }
.csh-bulk-actions .csh-bulk-label { font-size:13px; color:#475569; font-weight:600; margin-right:0.25rem; }

.csh-add-toggle { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:0.65rem 0.9rem; margin-bottom: 1rem; }
.csh-add-toggle summary { cursor:pointer; font-weight:600; color:#1d4ed8; font-size:14px; }
.csh-add-form { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:0.5rem; align-items:end; margin-top: 0.75rem; }
.csh-add-form label { font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:0.2rem; }
.csh-add-form input, .csh-add-form select { width:100%; padding:0.4rem 0.55rem; font-size:13px; border:1px solid #d1d5db; border-radius:5px; box-sizing:border-box; }

.csh-table-wrap { background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }
.csh-table-head { padding:0.75rem 1rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
.csh-table-head h2 { margin:0; font-size:14px; font-weight:700; color:#0f172a; }
.csh-table { width:100%; border-collapse:collapse; font-size:13px; }
.csh-table th { background:#f8fafc; padding:0.6rem 0.5rem; text-align:left; font-weight:700; color:#475569; border-bottom:1px solid #e5e7eb; font-size:12px; }
.csh-table td { padding:0.6rem 0.5rem; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.csh-table tr:hover td { background:#fafbfc; }
.csh-table tr:last-child td { border-bottom:none; }
.csh-table th.csh-center, .csh-table td.csh-center { text-align:center; }

.csh-status { display:inline-block; padding:0.2rem 0.55rem; border-radius:4px; font-size:11px; font-weight:700; }
.csh-status-pending { background:#fef3c7; color:#92400e; }
.csh-status-issued  { background:#dcfce7; color:#15803d; }
.csh-status-failed  { background:#fee2e2; color:#b91c1c; }
.csh-mail-sent { background:#dcfce7; color:#15803d; }
.csh-mail-unsent { background:#fef3c7; color:#92400e; }
.csh-mail-fail { background:#fee2e2; color:#b91c1c; }

.csh-action-group { display:flex; gap:0.25rem; flex-wrap:wrap; justify-content:center; }
.csh-btn { display:inline-block; padding:0.3rem 0.6rem; border-radius:4px; font-size:11px; font-weight:600; text-decoration:none; border:1px solid transparent; cursor:pointer; white-space:nowrap; }
.csh-btn-issue   { background:#2563eb; color:#fff; }
.csh-btn-issue:hover { background:#1d4ed8; }
.csh-btn-send    { background:#16a34a; color:#fff; }
.csh-btn-send:hover { background:#15803d; }
.csh-btn-resend  { background:#f59e0b; color:#fff; }
.csh-btn-resend:hover { background:#d97706; }
.csh-btn-reissue { background:#7c3aed; color:#fff; }
.csh-btn-reissue:hover { background:#6d28d9; }
.csh-btn-pdf     { background:#fff; color:#1d4ed8; border-color:#bfdbfe; }
.csh-btn-pdf:hover { background:#eff6ff; }
.csh-btn-remove  { background:#fff; color:#b91c1c; border-color:#fecaca; }
.csh-btn-remove:hover { background:#fef2f2; }
.csh-btn-primary { background:#2563eb; color:#fff; padding:0.45rem 1rem; font-size:13px; }
.csh-btn-primary:hover { background:#1d4ed8; }
.csh-btn-secondary { background:#e5e7eb; color:#374151; padding:0.45rem 1rem; font-size:13px; }
.csh-btn-secondary:hover { background:#d1d5db; }
.csh-btn-danger  { background:#dc2626; color:#fff; padding:0.45rem 1rem; font-size:13px; }
.csh-btn-danger:hover { background:#b91c1c; }
.csh-btn-success { background:#16a34a; color:#fff; padding:0.45rem 1rem; font-size:13px; }
.csh-btn-success:hover { background:#15803d; }

.csh-alert { padding:0.7rem 1rem; border-radius:8px; font-size:13px; margin-bottom:1rem; }
.csh-alert-success { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
.csh-alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.csh-bulk-students { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:0.85rem 1rem; margin-bottom:1rem; }
.csh-bulk-students summary { cursor:pointer; font-weight:600; color:#1d4ed8; font-size:14px; }
.csh-bulk-filters { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:0.5rem; margin:0.75rem 0; align-items:end; }
.csh-bulk-filters label { font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:0.2rem; }
.csh-bulk-filters input, .csh-bulk-filters select { width:100%; padding:0.4rem 0.55rem; font-size:13px; border:1px solid #d1d5db; border-radius:5px; box-sizing:border-box; }
.csh-bulk-table { width:100%; border-collapse:collapse; font-size:13px; margin-top:0.5rem; }
.csh-bulk-table th, .csh-bulk-table td { padding:0.45rem 0.5rem; border-bottom:1px solid #f1f5f9; text-align:left; }
.csh-bulk-table th { background:#f8fafc; font-size:12px; color:#475569; }
.csh-bulk-empty { font-size:13px; color:#64748b; padding:0.75rem 0; }
.csh-bulk-actions-row { display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center; margin-top:0.75rem; }
</style>

<div class="csh-wrap">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="csh-alert csh-alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="csh-alert csh-alert-error"><?= esc((string) session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div class="csh-header">
        <div>
            <h1><?= esc((string) ($event['title'] ?? '')) ?></h1>
            <div class="csh-sub">
                <span><?= !empty($event['event_date']) ? date('d/m/Y', strtotime((string) $event['event_date'])) : 'ไม่ระบุวันที่' ?></span>
                <span class="csh-pill" style="background:<?= $evColor ?>;"><?= esc($evLabel) ?></span>
                <?php if (! empty($event['signer_name'])): ?>
                    <span>👤 <?= esc((string) $event['signer_name'] . ' ' . (string) ($event['signer_lastname'] ?? '')) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <a href="<?= esc($cb) ?>/<?= $eid ?>/edit" class="csh-btn csh-btn-secondary">แก้ไข</a>
            <?php if ($evStatus !== 'issued'): ?>
                <a href="<?= esc($cb) ?>/<?= $eid ?>/delete" class="csh-btn csh-btn-danger" onclick="return confirm('ยืนยันการลบ?')">ลบ</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info row: template thumbnail + description -->
    <div class="csh-info-row">
        <div class="csh-card">
            <div class="csh-card-label">รายละเอียดกิจกรรม</div>
            <?php if (! empty($event['description'])): ?>
                <div style="font-size:14px; color:#334155; line-height:1.55;"><?= nl2br(esc((string) $event['description'])) ?></div>
            <?php else: ?>
                <span style="color:#94a3b8; font-size:13px;">— ไม่มีรายละเอียด</span>
            <?php endif; ?>
        </div>
        <div>
            <?= view('admin/cert_events/partials/cert_event_background_block', [
                'event'     => $event,
                'cert_base' => $cb,
            ]) ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="csh-stats">
        <div class="csh-stat-card" style="background:linear-gradient(180deg,#eff6ff,#dbeafe);">
            <div class="csh-stat-num" style="color:#1d4ed8;"><?= $total ?></div>
            <div class="csh-stat-label">ผู้รับทั้งหมด</div>
        </div>
        <div class="csh-stat-card" style="background:linear-gradient(180deg,#fffbeb,#fef3c7);">
            <div class="csh-stat-num" style="color:#b45309;"><?= $pending ?></div>
            <div class="csh-stat-label">รอออกใบ</div>
        </div>
        <div class="csh-stat-card" style="background:linear-gradient(180deg,#f0fdf4,#dcfce7);">
            <div class="csh-stat-num" style="color:#15803d;"><?= $issued ?></div>
            <div class="csh-stat-label">ออกแล้ว</div>
        </div>
        <div class="csh-stat-card" style="background:linear-gradient(180deg,#fef2f2,#fecaca);">
            <div class="csh-stat-num" style="color:#b91c1c;"><?= $failed ?></div>
            <div class="csh-stat-label">ไม่สำเร็จ</div>
        </div>
        <div class="csh-stat-card" style="background:linear-gradient(180deg,#f5f3ff,#ddd6fe);">
            <div class="csh-stat-num" style="color:#6d28d9;"><?= $emailSent ?> / <?= $emailSent + $emailUnsent ?></div>
            <div class="csh-stat-label">อีเมลที่ส่งแล้ว</div>
        </div>
    </div>

    <!-- Bulk actions -->
    <div class="csh-bulk-actions">
        <span class="csh-bulk-label">ทำงานทั้งหมด:</span>
        <?php if ($evStatus !== 'draft' && $pending > 0): ?>
            <a href="<?= esc($cb) ?>/<?= $eid ?>/issue" class="csh-btn csh-btn-primary"
               onclick="return confirm('ออกใบ + ส่งอีเมลให้ผู้รับที่ยังรอ <?= $pending ?> คน?')">
                ออกใบ + ส่งอีเมล ทั้งหมด (<?= $pending ?> คน)
            </a>
        <?php elseif ($evStatus === 'draft'): ?>
            <span style="font-size:12px; color:#b45309;">⚠ กิจกรรมยังเป็นร่าง — แก้ไขสถานะเป็น "เปิด" ก่อนออกใบ</span>
        <?php endif; ?>
        <a href="<?= esc($cb) ?>/<?= $eid ?>/import" class="csh-btn csh-btn-secondary">นำเข้า CSV</a>
        <?php if ($total > 0): ?>
            <a href="<?= esc($cb) ?>/<?= $eid ?>/export" class="csh-btn csh-btn-secondary">ส่งออก CSV</a>
        <?php endif; ?>
    </div>

    <!-- Add Recipient (collapsed) -->
    <?php if ($evStatus !== 'closed' && $evStatus !== 'issued'): ?>
        <details class="csh-add-toggle">
            <summary>+ เพิ่มผู้รับใหม่</summary>
            <form method="post" action="<?= esc($cb) ?>/<?= $eid ?>/add-recipient" class="csh-add-form">
                <?= csrf_field() ?>
                <div>
                    <label>ชื่อผู้รับ <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="recipient_name" required>
                </div>
                <div>
                    <label>อีเมล <span style="color:#dc2626;">*</span></label>
                    <input type="email" name="recipient_email" required>
                </div>
                <div>
                    <label>รหัสนักศึกษา</label>
                    <input type="text" name="recipient_id_no">
                </div>
                <div>
                    <label>หรือเลือกจากระบบ</label>
                    <select name="student_id">
                        <option value="">— เลือก —</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) $student['id'] ?>">
                                <?= esc((string) ($student['tf_name'] ?? '') . ' ' . (string) ($student['tl_name'] ?? '')) ?> (<?= esc((string) ($student['login_uid'] ?? '')) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="csh-btn csh-btn-primary" style="width:100%;">เพิ่ม</button>
                </div>
            </form>
        </details>

        <details class="csh-bulk-students">
            <summary>+ เพิ่มจากรายชื่อนักศึกษาในคณะ (หลายคน)</summary>
            <div class="csh-bulk-filters">
                <div>
                    <label for="cshBulkProgram">หลักสูตร</label>
                    <select id="cshBulkProgram">
                        <option value="">— ทุกหลักสูตร —</option>
                        <?php foreach (($programs ?? []) as $pid => $pname): ?>
                            <option value="<?= (int) $pid ?>"><?= esc((string) $pname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="cshBulkQuery">ค้นหา (ชื่อ / รหัส / อีเมล)</label>
                    <input type="search" id="cshBulkQuery" placeholder="พิมพ์เพื่อค้นหา…">
                </div>
                <div>
                    <button type="button" class="csh-btn csh-btn-secondary" id="cshBulkSearchBtn" style="width:100%;">ค้นหา</button>
                </div>
            </div>

            <form method="post" action="<?= esc($cb) ?>/<?= $eid ?>/add-students-bulk" id="cshBulkForm">
                <?= csrf_field() ?>
                <div style="overflow-x:auto; max-height:320px; overflow-y:auto;">
                    <table class="csh-bulk-table">
                        <thead>
                            <tr>
                                <th style="width:36px;"><input type="checkbox" id="cshBulkSelectAll" title="เลือกทั้งหมด"></th>
                                <th>ชื่อ</th>
                                <th>รหัส</th>
                                <th>อีเมล</th>
                            </tr>
                        </thead>
                        <tbody id="cshBulkResults">
                            <tr><td colspan="4" class="csh-bulk-empty">กด "ค้นหา" เพื่อแสดงรายชื่อนักศึกษา</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="csh-bulk-actions-row">
                    <button type="submit" class="csh-btn csh-btn-primary" id="cshBulkSubmit" disabled>
                        เพิ่มที่เลือก (0 คน)
                    </button>
                    <span id="cshBulkHint" style="font-size:12px; color:#64748b;">เลือกนักศึกษาที่เข้าร่วมกิจกรรมนี้</span>
                </div>
            </form>
        </details>
    <?php endif; ?>

    <!-- Recipients table (PRIMARY content) -->
    <div class="csh-table-wrap">
        <div class="csh-table-head">
            <h2>👥 รายชื่อผู้เข้าร่วม (<?= $total ?> คน)</h2>
            <?php if ($total > 0): ?>
                <span style="font-size:11px; color:#64748b;">ออก: <?= $issued ?> · รอ: <?= $pending ?> · ไม่สำเร็จ: <?= $failed ?></span>
            <?php endif; ?>
        </div>

        <?php if (empty($recipients)): ?>
            <div class="csh-alert csh-alert-info" style="margin:1rem;">ยังไม่มีผู้รับ — เพิ่มทีละคนด้านบน หรือนำเข้าจาก CSV</div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="csh-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>ชื่อ / อีเมล</th>
                            <th>รหัส</th>
                            <th class="csh-center">สถานะใบ</th>
                            <th class="csh-center">อีเมล</th>
                            <th>เลขที่ Cert</th>
                            <th class="csh-center" style="min-width:180px;">การจัดการ</th>
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
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;"><?= esc((string) $r['recipient_name']) ?></div>
                                    <div style="font-size:11px; color:#64748b;"><?= esc((string) $r['recipient_email']) ?></div>
                                </td>
                                <td style="font-size:12px; color:#475569;"><?= esc((string) ($r['recipient_id_no'] ?? '-')) ?></td>
                                <td class="csh-center">
                                    <?php if ($status === 'pending'): ?>
                                        <span class="csh-status csh-status-pending">⏳ รอออก</span>
                                    <?php elseif ($status === 'issued'): ?>
                                        <span class="csh-status csh-status-issued">✅ ออกแล้ว</span>
                                    <?php elseif ($status === 'failed'): ?>
                                        <span class="csh-status csh-status-failed">❌ ไม่สำเร็จ</span>
                                        <?php if (! empty($r['error_message'])): ?>
                                            <div style="font-size:11px; color:#b91c1c; margin-top:0.2rem;"><?= esc((string) $r['error_message']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="csh-status"><?= esc($status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="csh-center">
                                    <?php if ($emailOk): ?>
                                        <span class="csh-status csh-mail-sent">✉ ส่งแล้ว</span>
                                        <div style="font-size:10px; color:#64748b; margin-top:0.2rem;"><?= esc((string) $r['email_sent_at']) ?></div>
                                    <?php elseif ($status === 'issued' && $emailErr): ?>
                                        <span class="csh-status csh-mail-fail">⚠ ส่งไม่ได้</span>
                                        <div style="font-size:10px; color:#b91c1c; margin-top:0.2rem;"><?= esc(mb_substr((string) $r['email_error'], 0, 60)) ?></div>
                                    <?php elseif ($status === 'issued'): ?>
                                        <span class="csh-status csh-mail-unsent">ยังไม่ส่ง</span>
                                    <?php else: ?>
                                        <span style="color:#cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:11px;">
                                    <?php if (! empty($r['certificate_no'])): ?>
                                        <code style="background:#f1f5f9; padding:0.1rem 0.3rem; border-radius:3px;"><?= esc((string) $r['certificate_no']) ?></code>
                                        <?php if (! empty($r['download_count'])): ?>
                                            <div style="font-size:10px; color:#64748b; margin-top:0.2rem;">↓ <?= (int) $r['download_count'] ?> ครั้ง</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="csh-center">
                                    <div class="csh-action-group">
                                        <?php if ($status === 'pending'): ?>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/issue"
                                               class="csh-btn csh-btn-issue"
                                               onclick="return confirm('ออกใบรับรองให้ <?= esc((string) $r['recipient_name'], 'js') ?>? (ยังไม่ส่งอีเมล)');">
                                                ออกใบ
                                            </a>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/remove"
                                               class="csh-btn csh-btn-remove"
                                               onclick="return confirm('ลบผู้รับนี้?');">
                                                ลบ
                                            </a>
                                        <?php elseif ($status === 'issued'): ?>
                                            <?php if ($hasPdf): ?>
                                                <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/pdf"
                                                   target="_blank" class="csh-btn csh-btn-pdf">PDF</a>
                                            <?php endif; ?>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/issue"
                                               class="csh-btn csh-btn-reissue"
                                               onclick="return confirm('ออกใบใหม่ทับใบเดิม? (เลขที่และ token เดิม, สร้างไฟล์ PDF ใหม่)');">
                                                ออกใหม่
                                            </a>
                                            <?php if (! $emailOk): ?>
                                                <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/send"
                                                   class="csh-btn csh-btn-send"
                                                   onclick="return confirm('ส่งอีเมลใบรับรองให้ <?= esc((string) $r['recipient_email'], 'js') ?>?');">
                                                    ส่งอีเมล
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/send"
                                                   class="csh-btn csh-btn-resend"
                                                   onclick="return confirm('ส่งอีเมลซ้ำให้ <?= esc((string) $r['recipient_email'], 'js') ?>?');">
                                                    ส่งซ้ำ
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($status === 'failed'): ?>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/issue"
                                               class="csh-btn csh-btn-issue"
                                               onclick="return confirm('ลองออกใบใหม่?');">
                                                ลองใหม่
                                            </a>
                                            <a href="<?= esc($cb) ?>/recipient/<?= (int) $r['id'] ?>/remove"
                                               class="csh-btn csh-btn-remove"
                                               onclick="return confirm('ลบผู้รับนี้?');">
                                                ลบ
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
