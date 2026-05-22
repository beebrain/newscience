<?= $this->extend('scienceweek/layout') ?>
<?= $this->section('content') ?>

<?php
$levelLabels = [
    'primary'         => 'ประถมศึกษา',
    'lower_secondary' => 'ม.ต้น',
    'primary_lower'   => 'ประถม–ม.ต้น',
    'lower_higher'    => 'ม.ต้น–อุดมศึกษา',
    'secondary'       => 'มัธยมศึกษา',
    'higher'          => 'อุดมศึกษา',
    'primary_upper'   => 'ประถมปลาย',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-0" style="font-size:1.35rem;color:var(--sw-indigo);">เลือกรายการแข่งขัน</h2>
        <p class="text-muted mb-0" style="font-size:.85rem;">กรอกข้อมูลให้ครบถ้วนและตรวจสอบก่อนส่งใบสมัคร</p>
    </div>
    <a href="<?= base_url('scienceweek/verify') ?>"
       style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border:1.5px solid var(--sw-border);border-radius:9px;font-size:.83rem;font-weight:600;color:var(--sw-muted);background:#fff;text-decoration:none;transition:border-color .15s,color .15s;"
       onmouseover="this.style.borderColor='var(--sw-blue)';this.style.color='var(--sw-blue)';"
       onmouseout="this.style.borderColor='var(--sw-border)';this.style.color='var(--sw-muted)';">
        🔍 ตรวจสอบรายชื่อผู้สมัคร
    </a>
</div>

<div class="row g-3">
<?php foreach ($competitions as $key => $comp):
    $totalCap   = $comp['cap_total'];
    $totalCount = array_sum($caps[$key]);
    $isFull     = $totalCap !== null && $totalCount >= $totalCap;
    $hasDocs    = !empty($comp['docs']);
?>
    <div class="col-md-6">
        <div class="comp-card h-100 d-flex flex-column <?= $isFull ? 'border-danger' : '' ?>"
             style="<?= $isFull ? 'opacity:.75;cursor:default;' : '' ?>"
             <?= !$isFull ? "onclick=\"window.location='".base_url('scienceweek/register/'.$key)."'\"" : '' ?>>
            <div class="p-3 flex-grow-1">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <h5 class="mb-0" style="font-size:.97rem;font-weight:700;color:var(--sw-indigo);line-height:1.35;">
                        <?= esc($comp['name_th']) ?>
                    </h5>
                    <?php if ($isFull): ?>
                        <span class="pill" style="background:#fef2f2;color:#dc2626;flex-shrink:0;font-size:.7rem;">เต็มแล้ว</span>
                    <?php endif; ?>
                </div>
                <p style="font-size:.78rem;color:var(--sw-muted);margin-bottom:.75rem;"><?= esc($comp['name_en']) ?></p>

                <div class="d-flex flex-wrap gap-1 mb-2">
                    <?php foreach ($comp['levels'] as $lk => $lv):
                        $cnt = $caps[$key][$lk];
                        $cap = $comp['cap_per_level'];
                        $lvFull = $cap !== null && $cnt >= $cap;
                    ?>
                        <span class="badge-cap <?= $lvFull ? 'full' : '' ?>">
                            <?= esc($lv) ?>
                            <span style="opacity:.7;margin-left:.2rem;"><?= $cap !== null ? "{$cnt}/{$cap}" : "{$cnt} ทีม" ?></span>
                        </span>
                    <?php endforeach; ?>
                    <?php if ($totalCap !== null): ?>
                        <span class="badge-cap <?= $isFull ? 'full' : '' ?>">
                            รวม <?= $totalCount ?>/<?= $totalCap ?> ทีม
                        </span>
                    <?php endif; ?>
                </div>

                <div style="font-size:.78rem;color:var(--sw-muted);">
                    <?php if ($comp['team_min'] === $comp['team_max']): ?>
                        👥 <?= $comp['team_min'] ?> คน/ทีม
                    <?php else: ?>
                        👥 <?= $comp['team_min'] ?>–<?= $comp['team_max'] ?> คน/ทีม
                    <?php endif; ?>
                    <?php if ($comp['has_reserve']): ?>
                        · มีตัวสำรอง <?= $comp['reserve_max'] ?> คน
                    <?php endif; ?>
                    <?php if ($comp['deadline']): ?>
                        · ⏰ ปิดรับ <?= esc($comp['deadline']) ?>
                    <?php endif; ?>
                </div>

                <?php if ($comp['notes']): ?>
                    <div style="font-size:.78rem;color:var(--sw-teal);margin-top:.4rem;">ℹ️ <?= esc($comp['notes']) ?></div>
                <?php endif; ?>
            </div>

            <div class="px-3 pb-3 pt-2 d-flex align-items-center gap-2 flex-wrap"
                 style="border-top:1px solid var(--sw-border);">
                <?php if ($isFull): ?>
                    <span style="font-size:.82rem;font-weight:700;color:#dc2626;">🔒 ปิดรับสมัครแล้ว</span>
                <?php else: ?>
                    <a href="<?= base_url('scienceweek/register/'.$key) ?>"
                       class="btn-sw-submit" style="padding:.4rem 1.2rem;font-size:.85rem;border-radius:8px;box-shadow:none;"
                       onclick="event.stopPropagation()">
                        สมัครเลย →
                    </a>
                <?php endif; ?>

                <?php foreach ($comp['docs'] ?? [] as $doc): ?>
                    <a href="<?= base_url('scienceweek/docs/'.rawurlencode($doc)) ?>"
                       target="_blank"
                       class="btn-doc" onclick="event.stopPropagation()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1L14 5.5z"/>
                            <path d="M4 11h8v1H4zm0-2h8v1H4zm0-2h3v1H4z"/>
                        </svg>
                        เอกสาร
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<style>
.btn-sw-submit {
    background: linear-gradient(135deg, var(--sw-teal) 0%, var(--sw-teal-l) 100%);
    color: #fff !important;
    border: none;
    font-weight: 700;
    letter-spacing: .02em;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: transform .15s, box-shadow .15s;
}
.btn-sw-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,150,136,.35); }
</style>

<?= $this->endSection() ?>
