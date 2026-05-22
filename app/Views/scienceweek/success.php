<?= $this->extend('scienceweek/layout') ?>
<?= $this->section('content') ?>

<?php
$levelLabel = $comp['levels'][$reg['level_key']] ?? $reg['level_key'];
$mainParts  = array_filter($participants, fn($p) => $p['role'] === 'main');
$reserves   = array_filter($participants, fn($p) => $p['role'] === 'reserve');
?>

<div class="card border-success mb-4">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">✅ ส่งใบสมัครสำเร็จแล้ว!</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>หมายเหตุ:</strong> กรุณาตรวจสอบรายชื่อในหน้า
            <a href="<?= base_url('scienceweek/verify?competition='.$reg['competition_key'].'&level='.$reg['level_key']) ?>">ตรวจสอบรายชื่อผู้สมัคร</a>
            ภายใน 1–2 วันทำการ หากไม่พบรายชื่อกรุณาติดต่อผู้จัดการแข่งขัน
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-muted">รายการแข่งขัน</h6>
                <p class="fw-bold"><?= esc($comp['name_th'] ?? '') ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">ระดับ</h6>
                <p class="fw-bold"><?= esc($levelLabel) ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">โรงเรียน / สถานศึกษา</h6>
                <p><?= esc($reg['school_name']) ?></p>
            </div>
            <?php if ($reg['team_name']): ?>
            <div class="col-md-6">
                <h6 class="text-muted">ชื่อทีม</h6>
                <p><?= esc($reg['team_name']) ?></p>
            </div>
            <?php endif; ?>
            <div class="col-md-6">
                <h6 class="text-muted">อาจารย์ผู้ควบคุม</h6>
                <p><?= esc($reg['coach_name']) ?></p>
            </div>
        </div>

        <hr>

        <h6 class="text-primary">รายชื่อผู้เข้าแข่งขัน (หลัก)</h6>
        <ol>
            <?php foreach ($mainParts as $p): ?>
                <li><?= esc($p['full_name']) ?>
                    <?= $p['level_class'] ? ' — '.esc($p['level_class']) : '' ?>
                    <?= $p['game_id'] ? ' <span class="badge bg-secondary">'.esc($p['game_id']).'</span>' : '' ?>
                </li>
            <?php endforeach; ?>
        </ol>

        <?php if (!empty($reserves)): ?>
            <h6 class="text-secondary">ตัวสำรอง</h6>
            <ol>
                <?php foreach ($reserves as $p): ?>
                    <li><?= esc($p['full_name']) ?>
                        <?= $p['game_id'] ? ' <span class="badge bg-secondary">'.esc($p['game_id']).'</span>' : '' ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <div class="mt-4 d-flex gap-2">
            <a href="<?= base_url('scienceweek/verify?competition='.$reg['competition_key'].'&level='.$reg['level_key']) ?>"
               class="btn btn-outline-primary">
                🔍 ตรวจสอบรายชื่อผู้สมัคร
            </a>
            <a href="<?= base_url('scienceweek') ?>" class="btn btn-outline-secondary">สมัครรายการอื่น</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
