<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Dev Student Test') ?></title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 42rem; margin: 2rem auto; padding: 0 1rem; line-height: 1.5; }
        h1 { font-size: 1.25rem; }
        .ok { background: #ecfdf5; border: 1px solid #6ee7b7; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
        .err { background: #fef2f2; border: 1px solid #fca5a5; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
        ul { padding-left: 1.25rem; }
        a { color: #0d9488; }
        code { background: #f1f5f9; padding: 0.1rem 0.35rem; border-radius: 4px; font-size: 0.9em; }
    </style>
</head>

<body>
    <h1><?= esc($page_title ?? 'Dev') ?></h1>
    <p>ใช้ได้เฉพาะ <code>ENVIRONMENT=development</code> — ห้ามเปิดใน production</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="ok"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="err"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <h2>บัญชีทดสอบ (รหัสผ่านเดียวกัน)</h2>
    <ul>
        <li><strong>สโมสร:</strong> <code><?= esc($dummy_club_email ?? 'u59@live.uru.ac.th') ?></code> — login_uid <code>u59</code> — role <code>club</code></li>
        <li><strong>นักศึกษาปกติ:</strong> <code><?= esc($dummy_plain_email ?? 'u69@live.uru.ac.th') ?></code> — login_uid <code>u69</code> — role <code>student</code></li>
        <li>รหัสผ่าน (หน้า <a href="<?= base_url('student/login') ?>">/student/login</a>): <code><?= esc($dummy_password ?? '') ?></code></li>
    </ul>

    <h2>ลิงก์ด่วน (แบบเดียวกับ <code>login-as-admin?email=</code>)</h2>
    <ul>
        <li><a href="<?= base_url('dev/seed-student-dummies') ?>">สร้าง/อัปเดต dummy ในฐานข้อมูล</a> แล้วกลับมาหน้านี้</li>
        <li><a href="<?= base_url('dev/login-as-student?email=' . rawurlencode($dummy_club_email ?? 'u59@live.uru.ac.th')) ?>"><code>login-as-student?email=</code> สโมสร (u59)</a> → ระบบเช็ค role=club แล้วพาไป student-admin</li>
        <li><a href="<?= base_url('dev/login-as-student?email=' . rawurlencode($dummy_plain_email ?? 'u69@live.uru.ac.th')) ?>"><code>login-as-student?email=</code> นักศึกษาปกติ (u69)</a> → พาไป student portal</li>
        <li><a href="<?= base_url('dev/login-as-student') ?>"><code>login-as-student</code> ไม่ส่งพารามิเตอร์</a> → ลองหา club active ก่อน ถ้าไม่มีค่อยใช้แถวแรกในตาราง</li>
        <li><a href="<?= base_url('dev/login-as-student-admin') ?>"><code>login-as-student-admin</code></a> → redirect ไป <code>login-as-student?email=</code> ของสโมสรคนแรก</li>
        <li>ทดสอบ OAuth แบบกำหนดเอง: <a href="<?= base_url('dev/mock-oauth-student?uid=u69&email=u69@live.uru.ac.th') ?>">mock-oauth-student</a> ·
            <a href="<?= base_url('dev/login-dummy-club') ?>">login-dummy-club</a> ·
            <a href="<?= base_url('dev/login-dummy-student') ?>">login-dummy-student</a></li>
    </ul>
</body>

</html>
