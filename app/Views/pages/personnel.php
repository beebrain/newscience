<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
$siteName = $site_info['site_name_th'] ?? $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี';
$siteUrl = $site_info['website'] ?? $settings['website'] ?? '';
$tier1 = $personnel_by_position_tier[1]['personnel'] ?? [];
$tier2 = $personnel_by_position_tier[2]['personnel'] ?? [];
$tier3 = $personnel_by_position_tier[3]['personnel'] ?? [];
$getImageUrl = function ($person) {
    $img = isset($person['image']) ? trim((string) $person['image']) : '';
    if ($img === '') return '';
    if (strpos($img, 'http') === 0) return $img;
    $img = str_replace('\\', '/', $img);
    if (strpos($img, 'uploads/') !== 0) {
        $img = (strpos($img, 'staff/') === 0 ? 'uploads/' : 'uploads/personnel/') . ltrim($img, '/');
    }
    return rtrim(base_url(), '/') . '/' . ltrim($img, '/');
};
$accent = '#e07c5e'; // orange/coral
?>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  .org-chart-page { box-sizing: border-box; font-family: 'Source Sans Pro', sans-serif; background: #fafafa; min-height: 100vh; }
  .org-chart-page .org-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding: 1.5rem 2rem; background: #fff; border-bottom: 1px solid #eee; }
  .org-chart-page .org-title { font-size: 1.75rem; font-weight: 700; margin: 0; }
  .org-chart-page .org-title .org-title-accent { color: <?= $accent ?>; }
  .org-chart-page .org-brand { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; font-weight: 600; color: #333; }
  .org-chart-page .org-brand-logo { width: 32px; height: 32px; border: 2px solid <?= $accent ?>; border-radius: 4px; background: #fff; }
  .org-chart-page .org-tree { position: relative; max-width: 900px; margin: 0 auto; padding: 2rem 1rem 4rem; }
  .org-chart-page .org-row { display: flex; justify-content: center; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap; position: relative; z-index: 2; }
  .org-chart-page .org-row--tier1 { margin-bottom: 0; }
  .org-chart-page .org-row--tier2 { margin-top: 1.5rem; margin-bottom: 1rem; }
  .org-chart-page .org-row--tier3 { margin-top: 1rem; }
  .org-chart-page .org-connectors { position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1; }
  .org-chart-page .org-connectors svg { width: 100%; height: 100%; }
  .org-chart-page .org-card { text-align: center; transition: transform 0.2s, box-shadow 0.2s; box-sizing: border-box; flex-shrink: 0; }
  .org-chart-page .org-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
  /* คณบดี: ใหญ่สุด */
  .org-chart-page .org-card--tier1 { width: 280px; min-width: 280px; min-height: 260px; background: <?= $accent ?>; border-radius: 20px 20px 12px 12px; padding: 1.5rem 1.5rem 1.25rem; color: #fff; box-shadow: 0 4px 20px rgba(224,124,94,0.35); }
  .org-chart-page .org-card--tier1 .org-card__arch { width: 100%; height: 24px; margin: -1.5rem -1.5rem 0.75rem -1.5rem; background: <?= $accent ?>; border-radius: 20px 20px 0 0; }
  .org-chart-page .org-card--tier1 .org-card__photo-wrap { width: 100px; height: 100px; margin: 0 auto 0.75rem; border-radius: 50%; overflow: hidden; border: 3px solid rgba(255,255,255,0.9); background: rgba(255,255,255,0.2); }
  .org-chart-page .org-card--tier1 .org-card__photo { width: 100%; height: 100%; object-fit: cover; }
  .org-chart-page .org-card--tier1 .org-card__name { font-size: 1.15rem; font-weight: 700; margin: 0 0 0.25rem; color: #fff; }
  .org-chart-page .org-card--tier1 .org-card__title { font-size: 0.8rem; margin: 0; color: rgba(255,255,255,0.95); line-height: 1.4; }
  /* รองคณบดี: ขนาดเท่ากันทุกการ์ด */
  .org-chart-page .org-card--tier2 { width: 200px; min-width: 200px; min-height: 180px; background: <?= $accent ?>; border-radius: 14px; padding: 1rem 1rem 0.9rem; color: #fff; box-shadow: 0 4px 16px rgba(224,124,94,0.3); }
  .org-chart-page .org-card--tier2 .org-card__photo-wrap { width: 64px; height: 64px; margin: 0 auto 0.5rem; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255,255,255,0.9); background: rgba(255,255,255,0.2); }
  .org-chart-page .org-card--tier2 .org-card__photo { width: 100%; height: 100%; object-fit: cover; }
  .org-chart-page .org-card--tier2 .org-card__name { font-size: 0.95rem; font-weight: 700; margin: 0 0 0.15rem; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
  .org-chart-page .org-card--tier2 .org-card__title { font-size: 0.7rem; margin: 0; color: rgba(255,255,255,0.95); line-height: 1.35; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
  /* ผู้ช่วยคณบดี: ขนาดเท่ากันทุกการ์ด */
  .org-chart-page .org-card--tier3 { width: 180px; min-width: 180px; min-height: 160px; background: #fff; border-radius: 12px; padding: 0.9rem 0.75rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #eee; }
  .org-chart-page .org-card--tier3 .org-card__photo-wrap { width: 52px; height: 52px; margin: 0 auto 0.5rem; border-radius: 50%; overflow: hidden; background: #f1f5f9; }
  .org-chart-page .org-card--tier3 .org-card__photo { width: 100%; height: 100%; object-fit: cover; }
  .org-chart-page .org-card--tier3 .org-card__name { font-size: 0.85rem; font-weight: 700; margin: 0 0 0.1rem; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
  .org-chart-page .org-card--tier3 .org-card__title { font-size: 0.65rem; margin: 0; color: #64748b; line-height: 1.35; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
  .org-chart-page .org-card__photo-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); }
  .org-chart-page .org-card--tier3 .org-card__photo-placeholder { background: #e2e8f0; }
  .org-chart-page .org-footer { text-align: center; padding: 1rem; font-size: 0.8rem; color: #94a3b8; }
  .org-chart-page .org-footer a { color: #64748b; text-decoration: none; }
  .org-chart-page .org-card--placeholder { opacity: 0.85; }
  .org-chart-page .org-card--placeholder .org-card__title { font-style: italic; }
  @media (max-width: 768px) {
    .org-chart-page .org-header { padding: 1rem; }
    .org-chart-page .org-title { font-size: 1.35rem; }
    .org-chart-page .org-card--tier1 { width: 260px; min-width: 260px; min-height: 240px; }
    .org-chart-page .org-card--tier2 { width: 160px; min-width: 160px; min-height: 160px; }
    .org-chart-page .org-card--tier3 { width: 150px; min-width: 150px; min-height: 145px; }
  }
</style>

<div id="personnel-page" class="org-chart-page">
  <header class="org-header">
    <h1 class="org-title"><span class="org-title-accent">โครงสร้าง</span> องค์กร</h1>
    <div class="org-brand">
      <div class="org-brand-logo"></div>
      <span><?= esc($siteName) ?></span>
    </div>
  </header>

  <div class="org-tree">
    <!-- Dotted connector lines (tree structure) -->
    <div class="org-connectors" aria-hidden="true">
      <svg viewBox="0 0 900 520" preserveAspectRatio="xMidYMid meet">
        <?php
        $n1 = count($tier1);
        $n2 = count($tier2);
        $n3 = count($tier3);
        $cx0 = 450;
        $cy2 = 280;
        $step = $n2 > 0 ? 280 : 0;
        $start = $n2 > 2 ? max(120, 450 - ($n2 - 1) * $step / 2) : 0;
        if ($n1 >= 1) {
          echo '<path d="M' . $cx0 . ' 200 L' . $cx0 . ' ' . $cy2 . '" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
          if ($n2 >= 1) {
            if ($n2 === 1) {
              echo '<path d="M450 ' . $cy2 . ' L450 310" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
            } elseif ($n2 === 2) {
              echo '<path d="M450 ' . $cy2 . ' L250 310" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
              echo '<path d="M450 ' . $cy2 . ' L650 310" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
            } else {
              for ($i = 0; $i < $n2; $i++) {
                $x = $start + $i * $step;
                if ($x > 100 && $x < 800) echo '<path d="M450 ' . $cy2 . ' L' . round($x) . ' 310" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
              }
            }
            if ($n3 >= 1) {
              $t3step = 200;
              $t3start = max(120, 450 - (min($n3, 4) - 1) * $t3step / 2);
              for ($i = 0; $i < min($n3, 4); $i++) {
                $x3 = $t3start + $i * $t3step;
                $parentIdx = $n2 === 1 ? 0 : (int)floor(($i / max(1, min($n3, 4))) * $n2);
                $px = $n2 === 1 ? 450 : ($n2 === 2 ? ($parentIdx === 0 ? 250 : 650) : $start + $parentIdx * $step);
                if ($x3 > 80 && $x3 < 820) echo '<path d="M' . round($px) . ' 380 L' . round($x3) . ' 450" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
              }
            }
          } else {
            echo '<path d="M450 ' . $cy2 . ' L450 310" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
            echo '<path d="M450 380 L450 450" stroke="#d1d5db" stroke-width="1.5" stroke-dasharray="6 4" fill="none"/>';
          }
        }
        ?>
      </svg>
    </div>

    <!-- Tier 1: คณบดี (หนึ่งคน กลาง) -->
    <div class="org-row org-row--tier1">
      <?php if (!empty($tier1)): ?>
        <?php $p = $tier1[0]; $fullName = trim(($p['title'] ?? '') . ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); $imgUrl = $getImageUrl($p); ?>
        <div class="org-card org-card--tier1">
          <div class="org-card__arch"></div>
          <div class="org-card__photo-wrap">
            <?php if ($imgUrl): ?>
              <img src="<?= esc($imgUrl) ?>" alt="<?= esc($fullName) ?>" class="org-card__photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <div class="org-card__photo-placeholder" style="display:none;"><svg width="40" height="40" viewBox="0 0 24 24" fill="rgba(255,255,255,0.8)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
            <?php else: ?>
              <div class="org-card__photo-placeholder"><svg width="40" height="40" viewBox="0 0 24 24" fill="rgba(255,255,255,0.8)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
            <?php endif; ?>
          </div>
          <p class="org-card__name"><?= esc($fullName) ?></p>
          <p class="org-card__title"><?= esc($p['position'] ?? 'คณบดี') ?></p>
          <?php
          $chairPrograms = array_filter($p['programs_list_tags'] ?? [], fn($t) => ($t['role'] ?? '') === 'ประธานหลักสูตร');
          if (!empty($chairPrograms)): $chairNames = array_column($chairPrograms, 'name'); ?>
            <p class="org-card__subtitle" style="font-size:0.75rem;margin:0.35rem 0 0;color:rgba(255,255,255,0.9);">ประธานของหลักสูตร: <?= esc(implode(', ', $chairNames)) ?></p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="org-card org-card--tier1">
          <div class="org-card__arch"></div>
          <div class="org-card__photo-wrap"><div class="org-card__photo-placeholder"><svg width="40" height="40" viewBox="0 0 24 24" fill="rgba(255,255,255,0.8)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"/></svg></div></div>
          <p class="org-card__name">คณบดี</p>
          <p class="org-card__title">—</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tier 2: รองคณบดี (แถวกลาง สีส้ม) - แสดงแถวเสมอ -->
    <div class="org-row org-row--tier2">
      <?php if (!empty($tier2)): ?>
        <?php foreach ($tier2 as $p): ?>
          <?php $fullName = trim(($p['title'] ?? '') . ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); $imgUrl = $getImageUrl($p); ?>
          <div class="org-card org-card--tier2">
            <div class="org-card__photo-wrap">
              <?php if ($imgUrl): ?>
                <img src="<?= esc($imgUrl) ?>" alt="<?= esc($fullName) ?>" class="org-card__photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="org-card__photo-placeholder" style="display:none;"><svg width="28" height="28" viewBox="0 0 24 24" fill="rgba(255,255,255,0.9)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
              <?php else: ?>
                <div class="org-card__photo-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="rgba(255,255,255,0.9)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
              <?php endif; ?>
            </div>
            <p class="org-card__name"><?= esc($fullName) ?></p>
            <p class="org-card__title"><?= esc($p['position'] ?? 'รองคณบดี') ?></p>
            <?php
            $chairPrograms = array_filter($p['programs_list_tags'] ?? [], fn($t) => ($t['role'] ?? '') === 'ประธานหลักสูตร');
            if (!empty($chairPrograms)): $chairNames = array_column($chairPrograms, 'name'); ?>
              <p class="org-card__subtitle" style="font-size:0.65rem;margin:0.25rem 0 0;color:rgba(255,255,255,0.9);">ประธานของหลักสูตร: <?= esc(implode(', ', $chairNames)) ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="org-card org-card--tier2 org-card--placeholder">
          <div class="org-card__photo-wrap">
            <div class="org-card__photo-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="rgba(255,255,255,0.9)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
          </div>
          <p class="org-card__name">รองคณบดี</p>
          <p class="org-card__title">ยังไม่มีข้อมูล</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tier 3: ผู้ช่วยคณบดี (แถวล่าง การ์ดขาว) - แสดงแถวเสมอ -->
    <div class="org-row org-row--tier3">
      <?php if (!empty($tier3)): ?>
        <?php foreach ($tier3 as $p): ?>
          <?php $fullName = trim(($p['title'] ?? '') . ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); $imgUrl = $getImageUrl($p); ?>
          <div class="org-card org-card--tier3">
            <div class="org-card__photo-wrap">
              <?php if ($imgUrl): ?>
                <img src="<?= esc($imgUrl) ?>" alt="<?= esc($fullName) ?>" class="org-card__photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="org-card__photo-placeholder" style="display:none;"><svg width="24" height="24" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
              <?php else: ?>
                <div class="org-card__photo-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
              <?php endif; ?>
            </div>
            <p class="org-card__name"><?= esc($fullName) ?></p>
            <p class="org-card__title"><?= esc($p['position'] ?? 'ผู้ช่วยคณบดี') ?></p>
            <?php
            $chairPrograms = array_filter($p['programs_list_tags'] ?? [], fn($t) => ($t['role'] ?? '') === 'ประธานหลักสูตร');
            if (!empty($chairPrograms)): $chairNames = array_column($chairPrograms, 'name'); ?>
              <p class="org-card__subtitle" style="font-size:0.6rem;margin:0.2rem 0 0;color:#64748b;">ประธานของหลักสูตร: <?= esc(implode(', ', $chairNames)) ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="org-card org-card--tier3 org-card--placeholder">
          <div class="org-card__photo-wrap">
            <div class="org-card__photo-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
          </div>
          <p class="org-card__name">ผู้ช่วยคณบดี</p>
          <p class="org-card__title">ยังไม่มีข้อมูล</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($tier1) && empty($tier2) && empty($tier3)): ?>
    <p class="text-center py-8 text-gray-500">ยังไม่มีข้อมูลบุคลากร</p>
  <?php endif; ?>

  <footer class="org-footer">
    <?php if ($siteUrl): ?><a href="<?= esc($siteUrl) ?>" target="_blank" rel="noopener"><?= esc(parse_url($siteUrl, PHP_URL_HOST) ?: $siteUrl) ?></a><?php else: ?><?= esc($siteName) ?> &copy; <?= date('Y') + 543 ?><?php endif; ?>
  </footer>
</div>
<?= $this->endSection() ?>
