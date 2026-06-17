<?php
$isDeleted  = !empty($reg['deleted_at']);
$levelLabel = $comp['levels'][$reg['level_key']] ?? $reg['level_key'];
$mainParts  = array_filter($participants, fn($p) => $p['role'] === 'main');
$resParts   = array_filter($participants, fn($p) => $p['role'] === 'reserve');
$extra      = is_array($reg['extra']) ? $reg['extra'] : ($reg['extra'] ? json_decode($reg['extra'], true) : []);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการสมัคร #<?= $reg['id'] ?></title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>?v=<?= (defined('FCPATH') && is_file(FCPATH . 'assets/css/fonts.css')) ? filemtime(FCPATH . 'assets/css/fonts.css') : '1' ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>body { font-family: 'Sarabun', 'Noto Sans Thai', Tahoma, sans-serif; }</style>
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:860px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>รายละเอียดการสมัคร #<?= $reg['id'] ?></h4>
        <a href="<?= base_url("scienceweek/manage?competition={$reg['competition_key']}&level={$reg['level_key']}") ?>"
           class="btn btn-outline-secondary btn-sm">← กลับรายการ</a>
    </div>

    <?php if ($isDeleted): ?>
        <div class="alert alert-danger">
            ⚠️ ข้อมูลนี้ถูกลบแล้ว เมื่อ <?= esc($reg['deleted_at']) ?>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">ข้อมูลการสมัคร</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small">รายการแข่งขัน</label>
                    <div class="fw-bold"><?= esc($comp['name_th'] ?? $reg['competition_key']) ?></div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">ระดับ</label>
                    <div><?= esc($levelLabel) ?></div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">โรงเรียน / สถาบัน</label>
                    <div class="fw-bold"><?= esc($reg['school_name']) ?></div>
                </div>
                <?php if ($reg['school_address']): ?>
                <div class="col-md-6">
                    <label class="text-muted small">ที่อยู่</label>
                    <div><?= esc($reg['school_address']) ?></div>
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label class="text-muted small">โทรศัพท์สถานศึกษา</label>
                    <div><?= esc($reg['contact_phone']) ?></div>
                </div>
                <?php if ($reg['contact_email']): ?>
                <div class="col-md-4">
                    <label class="text-muted small">อีเมลสถานศึกษา</label>
                    <div><?= esc($reg['contact_email']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($extra['fax'])): ?>
                <div class="col-md-4">
                    <label class="text-muted small">โทรสาร</label>
                    <div><?= esc($extra['fax']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($reg['team_name']): ?>
                <div class="col-md-4">
                    <label class="text-muted small">ชื่อทีม</label>
                    <div><?= esc($reg['team_name']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">อาจารย์ผู้ควบคุม</div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="text-muted small">ชื่อ-สกุล</label>
                    <div><?= esc($reg['coach_name']) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">ตำแหน่ง</label>
                    <div><?= esc($reg['coach_position'] ?? '—') ?></div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">โทรศัพท์</label>
                    <div><?= esc($reg['coach_phone'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">อีเมล</label>
                    <div><?= esc($reg['coach_email'] ?? '—') ?></div>
                </div>
            </div>

            <?php if (!empty($extra['coach2'])): ?>
            <hr>
            <h6 class="text-secondary">อาจารย์คนที่ 2</h6>
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="text-muted small">ชื่อ-สกุล</label>
                    <div><?= esc($extra['coach2']['name'] ?? '—') ?></div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">โทรศัพท์</label>
                    <div><?= esc($extra['coach2']['phone'] ?? '—') ?></div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">อีเมล</label>
                    <div><?= esc($extra['coach2']['email'] ?? '—') ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">รายชื่อผู้เข้าแข่งขัน</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ชั้น / ระดับ</th>
                        <th>Role</th>
                        <th>Game ID</th>
                        <th>อายุ</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($mainParts as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($p['full_name']) ?></td>
                        <td><?= esc($p['level_class'] ?? '—') ?></td>
                        <td><span class="badge bg-primary">หลัก</span></td>
                        <td><?= esc($p['game_id'] ?? '—') ?></td>
                        <td><?= $p['age'] ?? '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($resParts as $i => $p): ?>
                    <tr class="table-secondary">
                        <td>—</td>
                        <td><?= esc($p['full_name']) ?></td>
                        <td><?= esc($p['level_class'] ?? '—') ?></td>
                        <td><span class="badge bg-secondary">สำรอง</span></td>
                        <td><?= esc($p['game_id'] ?? '—') ?></td>
                        <td>—</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-muted small mb-3">
        สมัครเมื่อ <?= esc($reg['created_at']) ?> · IP: <?= esc($reg['ip_address'] ?? '—') ?>
    </div>

    <?php if (!$isDeleted): ?>
        <button type="button" class="btn btn-danger"
                onclick="document.getElementById('delForm').submit()">
            🗑 ลบข้อมูลการสมัครนี้
        </button>
        <form id="delForm" method="post"
              action="<?= base_url('scienceweek/manage/'.$reg['id'].'/delete') ?>">
            <?= csrf_field() ?>
        </form>
        <small class="text-muted ms-2">ข้อมูลจะถูก soft-delete ไม่ได้ลบถาวร</small>
    <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
