<?= $this->extend('scienceweek/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold text-primary mb-0">ตรวจสอบรายชื่อผู้สมัคร</h3>
        <p class="text-muted small mb-0">ค้นหาชื่อของท่านในรายการด้านล่าง</p>
    </div>
    <a href="<?= base_url('scienceweek') ?>" class="btn btn-outline-secondary btn-sm">← สมัครใหม่</a>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('scienceweek/verify') ?>" class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">รายการแข่งขัน</label>
                <select name="competition" class="form-select" onchange="this.form.submit()">
                    <option value="">— เลือกรายการ —</option>
                    <?php foreach ($competitions as $key => $comp): ?>
                        <option value="<?= esc($key) ?>" <?= $compKey === $key ? 'selected' : '' ?>>
                            <?= esc($comp['name_th']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selectedComp !== null): ?>
            <div class="col-md-4">
                <label class="form-label fw-semibold">ระดับ</label>
                <select name="level" class="form-select" onchange="this.form.submit()">
                    <option value="">— ทุกระดับ —</option>
                    <?php foreach ($selectedComp['levels'] as $lk => $lv): ?>
                        <option value="<?= esc($lk) ?>" <?= $levelKey === $lk ? 'selected' : '' ?>>
                            <?= esc($lv) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">แสดงรายชื่อ</button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedComp === null): ?>
    <div class="text-center text-muted py-5">กรุณาเลือกรายการแข่งขัน</div>

<?php elseif (empty($registrations)): ?>
    <div class="alert alert-info">ยังไม่มีผู้สมัครในรายการนี้</div>

<?php else: ?>
    <?php
    // จัดกลุ่มตาม level สำหรับแสดง
    $byLevel = [];
    foreach ($registrations as $reg) {
        $byLevel[$reg['level_key']][] = $reg;
    }
    ?>
    <p class="text-muted">พบ <?= count($registrations) ?> ทีม/คน</p>

    <?php foreach ($byLevel as $lk => $regs): ?>
        <h5 class="text-primary mt-4">
            <?= esc($selectedComp['levels'][$lk] ?? $lk) ?>
            <span class="badge bg-secondary"><?= count($regs) ?> ทีม</span>
        </h5>

        <?php foreach ($regs as $no => $reg): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold">
                        #<?= $no + 1 ?>
                        <?= $reg['team_name'] ? esc($reg['team_name']).' — ' : '' ?>
                        <?= esc($reg['school_name']) ?>
                    </span>
                    <small class="text-muted">สมัครวันที่ <?= date('d/m/Y H:i', strtotime($reg['created_at'])) ?></small>
                </div>
                <div class="card-body py-2">
                    <?php $parts = $participants[$reg['id']] ?? [] ?>
                    <?php $mainParts = array_filter($parts, fn($p) => $p['role'] === 'main') ?>
                    <?php $resvParts = array_filter($parts, fn($p) => $p['role'] === 'reserve') ?>

                    <div class="row">
                        <div class="col-md-8">
                            <strong>ผู้เข้าแข่งขัน:</strong>
                            <ol class="mb-1">
                                <?php foreach ($mainParts as $p): ?>
                                    <li>
                                        <?= esc($p['full_name']) ?>
                                        <?= $p['level_class'] ? '<span class="text-muted small">('.esc($p['level_class']).')</span>' : '' ?>
                                        <?= $p['game_id'] ? '<span class="badge bg-secondary ms-1">'.esc($p['game_id']).'</span>' : '' ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                            <?php if (!empty($resvParts)): ?>
                                <strong class="text-muted small">ตัวสำรอง:</strong>
                                <ol class="mb-0">
                                    <?php foreach ($resvParts as $p): ?>
                                        <li class="text-muted small">
                                            <?= esc($p['full_name']) ?>
                                            <?= $p['game_id'] ? '<span class="badge bg-light text-secondary">'.esc($p['game_id']).'</span>' : '' ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-muted small">
                            <div>อาจารย์ผู้ควบคุม: <?= esc($reg['coach_name']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
