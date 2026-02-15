<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
$siteName = $site_info['site_name_th'] ?? $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี';
/** รูป staff ทุกหน้าใช้ thumbnail โหลดไว (fallback เป็นรูปเต็มถ้าไม่มี thumb) */
$getImageUrl = function ($person) {
  $img = isset($person['image']) ? trim((string) $person['image']) : '';
  if ($img === '') return '';
  if (strpos($img, 'http') === 0) return $img;
  return base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $img)));
};
$getDisplayName = function ($person) {
  $name = trim($person['name'] ?? '');
  $title = trim($person['academic_title'] ?? '');
  return $title !== '' ? $title . ' ' . $name : $name;
};
$getDisplayNameEn = function ($person) {
  $name = trim($person['name_en'] ?? '');
  if ($name === '') return $getDisplayName($person);
  $title = trim($person['academic_title_en'] ?? '');
  return $title !== '' ? $title . ' ' . $name : $name;
};
$organizationSections = $organization_sections ?? [];
$posLabelFn = function ($p) {
  $pos = trim($p['position'] ?? '');
  $posDetail = trim($p['position_detail'] ?? '');
  return $pos !== '' ? $pos . ($posDetail !== '' ? ' ' . $posDetail : '') : 'อาจารย์';
};
?>
<div class="personnel-page">
  <header class="personnel-header">
    <h1 class="personnel-header__title">บุคลากรตามหลักสูตร / หน่วยงาน</h1>
    <p class="personnel-header__subtitle"><?= esc($siteName) ?></p>
  </header>

  <nav class="personnel-unit-icons" role="navigation" aria-label="เลือกหน่วยงาน">
    <?php foreach ($organizationSections as $index => $sec):
      $unit = $sec['unit'] ?? [];
      $unitId = (int)($unit['id'] ?? 0);
      $unitName = $unit['name_th'] ?? 'หน่วยงาน';
      $code = $unit['code'] ?? '';
      $isFirst = $index === 0;
    ?>
      <button type="button" class="personnel-unit-icon <?= $isFirst ? 'personnel-unit-icon--active' : '' ?>" data-unit-code="<?= esc($code) ?>" data-section-id="personnel-section-<?= $unitId ?>" aria-pressed="<?= $isFirst ? 'true' : 'false' ?>">
        <span class="personnel-unit-icon__icon" aria-hidden="true">
          <?php if ($code === 'curriculum'): ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8"/><path d="M8 11h8"/></svg>
          <?php elseif ($code === 'research'): ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
          <?php else: ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <?php endif; ?>
        </span>
        <span class="personnel-unit-icon__label"><?= esc($unitName) ?></span>
      </button>
    <?php endforeach; ?>
  </nav>

  <?php foreach ($organizationSections as $sec):
    $unit = $sec['unit'] ?? [];
    $unitId = (int)($unit['id'] ?? 0);
    $unitName = $unit['name_th'] ?? 'หน่วยงาน';
    $code = $unit['code'] ?? '';
    $personnelList = $sec['personnel'] ?? [];
    $programsInSection = $sec['programs'] ?? [];
  ?>
  <section id="personnel-section-<?= $unitId ?>" class="personnel-dept-section<?= $code !== 'curriculum' ? ' personnel-dept-section--hidden' : '' ?>" data-dept-id="<?= $unitId ?>" data-unit-code="<?= esc($code) ?>" aria-labelledby="org-title-<?= $unitId ?>" aria-hidden="<?= $code === 'curriculum' ? 'false' : 'true' ?>">
    <h2 id="org-title-<?= $unitId ?>" class="personnel-dept-section__title"><?= esc($unitName) ?></h2>

    <?php if (!empty($personnelList)): ?>
      <?php
      $showHeadLikeChair = ($code === 'research' || $code === 'office') && !empty($personnelList);
      $headPerson = null;
      $restList = $personnelList;
      if ($showHeadLikeChair) {
        $first = $personnelList[0];
        $pos = $first['position'] ?? '';
        $isHead = ($code === 'research' && mb_strpos($pos, 'หัวหน้าหน่วยจัดการงานวิจัย') !== false)
          || ($code === 'office' && mb_strpos($pos, 'หัวหน้าสำนักงาน') !== false);
        if ($isHead) {
          $headPerson = $first;
          $restList = array_slice($personnelList, 1);
        }
      }
      $headLabel = $code === 'research' ? 'หัวหน้าหน่วยการจัดการงานวิจัย' : ($code === 'office' ? 'หัวหน้าสำนักงานคณบดี' : '');
      ?>
      <div class="personnel-grid-4col">
        <?php if ($headPerson): ?>
          <div class="personnel-grid-cell personnel-grid-cell--chair">
            <?php $headName = $getDisplayName($headPerson); $headImg = $getImageUrl($headPerson); ?>
            <div class="team-card animate-on-scroll">
              <div class="team-card__image-wrap">
                <?php if ($headImg): ?>
                  <img src="<?= esc($headImg) ?>" alt="<?= esc($headName) ?>" class="team-card__image" loading="lazy" onerror="this.style.display='none';var n=this.nextElementSibling;if(n)n.style.display='flex';">
                  <div class="team-card__placeholder" style="display:none;"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                <?php else: ?>
                  <div class="team-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                <?php endif; ?>
                <div class="team-card__label">
                  <span class="team-card__label-name"><?= esc($headName) ?></span>
                  <span class="team-card__label-role"><?= esc($headLabel) ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <?php foreach ($restList as $i => $p):
          $imgUrl = $getImageUrl($p);
          $displayName = $getDisplayName($p);
          $posLabel = $posLabelFn($p);
          if ($posLabel === '') $posLabel = $unitName;
          $row = $headPerson ? (2 + (int) floor($i / 4)) : 1;
          $col = $headPerson ? (($i % 4) + 1) : ($i % 4) + 1;
        ?>
          <div class="personnel-grid-cell"<?= $headPerson ? ' style="grid-column: ' . $col . '; grid-row: ' . $row . ';"' : '' ?>>
            <div class="team-card animate-on-scroll">
              <div class="team-card__image-wrap">
                <?php if ($imgUrl): ?>
                  <img src="<?= esc($imgUrl) ?>" alt="<?= esc($displayName) ?>" class="team-card__image" loading="lazy" onerror="this.style.display='none';var n=this.nextElementSibling;if(n)n.style.display='flex';">
                  <div class="team-card__placeholder" style="display:none;"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                <?php else: ?>
                  <div class="team-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                <?php endif; ?>
                <div class="team-card__label">
                  <span class="team-card__label-name"><?= esc($displayName) ?></span>
                  <span class="team-card__label-role"><?= esc($posLabel) ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php elseif (!empty($programsInSection)): ?>
      <?php $programsInDept = $programsInSection; ?>
      <?php if (count($programsInDept) > 1): ?>
        <nav class="program-tabs program-tabs--names" role="tablist" aria-label="เลือกสาขาใน<?= esc($unitName) ?>">
          <?php foreach ($programsInDept as $index => $block):
            $prog = $block['program'] ?? [];
            $progId = (int)($prog['id'] ?? 0);
            $nameTh = $prog['name_th'] ?? $prog['name_en'] ?? 'หลักสูตร';
            $isFirst = $index === 0;
          ?>
            <button type="button" id="program-tab-<?= $progId ?>" class="program-tab program-tab--name <?= $isFirst ? 'active' : '' ?>" data-program-id="<?= $progId ?>" data-index="<?= $index ?>" role="tab" aria-selected="<?= $isFirst ? 'true' : 'false' ?>" aria-controls="program-panel-<?= $progId ?>">
              <span class="program-tab__label"><?= esc($nameTh) ?></span>
            </button>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>

      <?php foreach ($programsInDept as $index => $block):
        $prog = $block['program'] ?? [];
        $progId = (int)($prog['id'] ?? 0);
        $chair = $block['chair'] ?? null;
        $personnelList = $block['personnel'] ?? [];
        $nameTh = $prog['name_th'] ?? $prog['name_en'] ?? 'หลักสูตร';
        $isFirst = $index === 0;
        if (count($programsInDept) === 1) { $isFirst = true; }
      ?>
        <section class="program-content <?= $isFirst ? 'active' : '' ?>" id="program-panel-<?= $progId ?>" data-program-id="<?= $progId ?>" role="tabpanel" aria-labelledby="program-tab-<?= $progId ?>">
          <h3 class="program-content__title"><?= esc($nameTh) ?></h3>
          <?php $list = $personnelList; ?>
          <div class="personnel-grid-4col">
            <div class="personnel-grid-cell personnel-grid-cell--chair">
              <?php if ($chair): ?>
                <?php $chairName = $getDisplayName($chair); $imgUrl = $getImageUrl($chair); ?>
                <div class="team-card animate-on-scroll">
                  <div class="team-card__image-wrap">
                    <?php if ($imgUrl): ?>
                      <img src="<?= esc($imgUrl) ?>" alt="<?= esc($chairName) ?>" class="team-card__image" loading="lazy" onerror="this.style.display='none';var n=this.nextElementSibling;if(n)n.style.display='flex';">
                      <div class="team-card__placeholder" style="display:none;"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                    <?php else: ?>
                      <div class="team-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                    <?php endif; ?>
                    <div class="team-card__label">
                      <span class="team-card__label-name"><?= esc($chairName) ?></span>
                      <span class="team-card__label-role">ประธานหลักสูตร</span>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <div class="team-card animate-on-scroll" style="opacity:0.8;">
                  <div class="team-card__image-wrap">
                    <div class="team-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                    <div class="team-card__label">
                      <span class="team-card__label-name">ยังไม่มีประธานหลักสูตร</span>
                      <span class="team-card__label-role">—</span>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            <?php if (empty($list)): ?>
              <p class="personnel-empty-hint personnel-grid-cell personnel-grid-cell--full" style="grid-row: 2; grid-column: 1 / -1;">ยังไม่มีรายชื่อบุคลากรในหลักสูตรนี้</p>
            <?php else:
              foreach ($list as $i => $p):
                $row = 2 + (int) floor($i / 4);
                $col = ($i % 4) + 1;
                $imgUrl = $getImageUrl($p);
                $displayName = $getDisplayName($p);
            ?>
              <div class="personnel-grid-cell" style="grid-column: <?= $col ?>; grid-row: <?= $row ?>;">
                <div class="team-card animate-on-scroll">
                  <div class="team-card__image-wrap">
                    <?php if ($imgUrl): ?>
                      <img src="<?= esc($imgUrl) ?>" alt="<?= esc($displayName) ?>" class="team-card__image" loading="lazy" onerror="this.style.display='none';var n=this.nextElementSibling;if(n)n.style.display='flex';">
                      <div class="team-card__placeholder" style="display:none;"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                    <?php else: ?>
                      <div class="team-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                    <?php endif; ?>
                    <div class="team-card__label">
                      <span class="team-card__label-name"><?= esc($displayName) ?></span>
                      <span class="team-card__label-role"><?= esc($posLabelFn($p)) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="personnel-empty-hint">ยังไม่มีข้อมูล</p>
    <?php endif; ?>
  </section>
  <?php endforeach; ?>

  <?php if (empty($organizationSections)): ?>
    <p style="text-align:center;padding:4rem;color:#94a3b8;">ยังไม่มีข้อมูลหน่วยงาน กรุณารัน migration ตาราง organization_units</p>
  <?php endif; ?>

  <footer class="personnel-footer"><?= esc($siteName) ?> © <?= date('Y') + 543 ?></footer>
</div>

<script>
  (function() {
    document.querySelectorAll('.personnel-unit-icon').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var sectionId = this.getAttribute('data-section-id');
        document.querySelectorAll('.personnel-unit-icon').forEach(function(b) {
          b.classList.remove('personnel-unit-icon--active');
          b.setAttribute('aria-pressed', 'false');
        });
        this.classList.add('personnel-unit-icon--active');
        this.setAttribute('aria-pressed', 'true');
        document.querySelectorAll('.personnel-dept-section').forEach(function(section) {
          var show = section.id === sectionId;
          section.classList.toggle('personnel-dept-section--hidden', !show);
          section.setAttribute('aria-hidden', show ? 'false' : 'true');
        });
      });
    });
    document.querySelectorAll('.program-tab').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var programId = this.getAttribute('data-program-id');
        var section = this.closest('.personnel-dept-section');
        var tabs = section ? section.querySelectorAll('.program-tab') : document.querySelectorAll('.program-tab');
        var panels = section ? section.querySelectorAll('.program-content') : document.querySelectorAll('.program-content');
        tabs.forEach(function(b) {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');
        panels.forEach(function(panel) {
          panel.classList.toggle('active', panel.getAttribute('data-program-id') === programId);
        });
      });
    });
  })();
</script>
<?= $this->endSection() ?>