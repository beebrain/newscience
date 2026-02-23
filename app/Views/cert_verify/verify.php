<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - คณะวิทยาศาสตร์</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .verify-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .status-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-icon.valid { background: #dcfce7; color: #166534; }
        .status-icon.invalid { background: #fee2e2; color: #991b1b; }
        .status-icon svg { width: 32px; height: 32px; }
        h1 { margin: 0 0 0.5rem; font-size: 1.5rem; color: #1f2937; }
        .cert-info { margin-top: 1.5rem; text-align: left; }
        .cert-info-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb; }
        .cert-info-label { color: #6b7280; }
        .cert-info-value { font-weight: 500; color: #1f2937; }
        .revoked-banner {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="status-icon <?= $is_valid ? 'valid' : 'invalid' ?>">
            <?php if ($is_valid): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <?php endif; ?>
        </div>
        <h1><?= $is_valid ? 'ใบรับรองถูกต้อง' : 'ใบรับรองไม่ถูกต้อง' ?></h1>
        <p class="text-muted">ตรวจสอบโดยระบบ E-Certificate คณะวิทยาศาสตร์</p>

        <?php if ($is_valid): ?>
            <div class="cert-info">
                <div class="cert-info-row"><span class="cert-info-label">เลขที่ใบรับรอง</span><span class="cert-info-value"><?= esc($certificate['certificate_no']) ?></span></div>
                <div class="cert-info-row"><span class="cert-info-label">ประเภท</span><span class="cert-info-value"><?= esc($template['name_th'] ?? '-') ?></span></div>
                <div class="cert-info-row"><span class="cert-info-label">วันที่ออก</span><span class="cert-info-value"><?= esc(date('d/m/Y', strtotime($certificate['issued_date']))) ?></span></div>
                <?php if ($signer): ?>
                    <div class="cert-info-row"><span class="cert-info-label">ผู้ลงนาม</span><span class="cert-info-value"><?= esc(($signer['th_name'] ?? '') . ' ' . ($signer['thai_lastname'] ?? '')) ?></span></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="revoked-banner">ใบรับรองฉบับนี้ถูกเพิกถอน</div>
        <?php endif; ?>
    </div>
</body>
</html>
