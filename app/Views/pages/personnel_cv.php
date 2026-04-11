<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
// Full-page CV — ฟอนต์/โทนสีตาม theme.css เดียวกับเว็บ newScience
$siteName = $site_info['site_name_th'] ?? $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี';
$p = $person ?? [];
$name = $display_name ?? '';
$nameEn = $display_name_en ?? '';
$img = $profile_image ?? '';
$email = trim($p['email'] ?? '');
$phone = trim($p['phone'] ?? '');
$position = trim($p['position'] ?? '');
$posDetail = trim($p['position_detail'] ?? '');
$posLabel = $position !== '' ? $position . ($posDetail !== '' ? ' ' . $posDetail : '') : 'อาจารย์';
$bio = trim($p['bio'] ?? '');
$education = trim($p['education'] ?? '');
$expertise = trim($p['expertise'] ?? '');
?>
<style>
  /* Full-page CV — สีอ้างอิง theme.css (เหลือง primary + เขียว secondary) */
  .personnel-cv-doc {
    --cv-ink: var(--color-gray-900, #1f2937);
    --cv-muted: var(--text-secondary, #64748b);
    --cv-line: var(--color-gray-300, #e5e7eb);
    --cv-accent: var(--secondary, #2d7d46);
    --cv-accent-text: var(--text-primary, #1e5c32);
    --cv-highlight: var(--primary, #eab308);
    --cv-sidebar-bg-1: var(--secondary-dark, #1e5c32);
    --cv-sidebar-bg-2: var(--secondary, #2d7d46);
    --cv-sidebar-soft: rgba(255, 255, 255, 0.14);
    --cv-page-pad-x: clamp(0.5rem, 2.5vw, 2rem);
    --cv-page-pad-y: clamp(0.5rem, 1.5vw, 1rem);
    box-sizing: border-box;
    width: 100%;
    max-width: none;
    margin: 0;
    padding: var(--cv-page-pad-y) var(--cv-page-pad-x) clamp(1rem, 2vw, 1.5rem);
    min-height: calc(100dvh - var(--header-height, 80px) - 0.75rem);
    font-family: var(--font-primary, 'Sarabun', 'Noto Sans Thai', sans-serif);
    font-size: var(--text-base, 1rem);
    line-height: 1.6;
    color: var(--cv-ink);
  }
  .personnel-cv-doc .cv-back {
    display: inline-block;
    margin-bottom: 0.65rem;
    font-size: var(--text-sm, 0.875rem);
    color: var(--cv-accent);
    font-weight: 600;
    text-decoration: none;
  }
  .personnel-cv-doc .cv-back:hover {
    color: var(--secondary-dark, #1e5c32);
    text-decoration: underline;
  }
  .personnel-cv-doc .cv-sheet {
    display: grid;
    grid-template-columns: minmax(14rem, 22vw) minmax(0, 1fr);
    grid-template-rows: 1fr auto;
    align-items: stretch;
    min-height: calc(100dvh - var(--header-height, 80px) - 3.25rem);
    background: var(--color-white, #fff);
    border-radius: var(--radius-md, 0.5rem);
    overflow: hidden;
    box-shadow: var(--shadow-md, 0 4px 6px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.06));
    border: 1px solid var(--cv-line);
  }
  .personnel-cv-doc .cv-sidebar {
    grid-column: 1;
    grid-row: 1;
    background: linear-gradient(165deg, var(--cv-sidebar-bg-1) 0%, var(--cv-sidebar-bg-2) 50%, var(--secondary-light, #3da55d) 100%);
    color: var(--color-white, #fff);
    padding: clamp(1.25rem, 3vw, 2.25rem) clamp(1rem, 2vw, 1.75rem);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1.25rem;
    min-height: 100%;
  }
  .personnel-cv-doc .cv-photo-wrap {
    width: clamp(7.5rem, 14vw, 11rem);
    height: clamp(7.5rem, 14vw, 11rem);
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(253, 224, 71, 0.55);
    flex-shrink: 0;
    background: var(--secondary-dark, #1e5c32);
  }
  .personnel-cv-doc .cv-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .personnel-cv-doc .cv-sidebar-name {
    font-size: clamp(var(--text-base, 1rem), 1.1vw, var(--text-lg, 1.125rem));
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: 0.02em;
  }
  .personnel-cv-doc .cv-sidebar-role {
    font-size: var(--text-sm, 0.875rem);
    opacity: 0.85;
    line-height: 1.4;
  }
  .personnel-cv-doc .cv-contact-block {
    width: 100%;
    text-align: left;
    font-size: var(--text-sm, 0.875rem);
    border-top: 1px solid var(--cv-sidebar-soft);
    padding-top: 1rem;
    margin-top: 0.25rem;
  }
  .personnel-cv-doc .cv-contact-row {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
    word-break: break-word;
    opacity: 0.92;
  }
  .personnel-cv-doc .cv-contact-row:last-child { margin-bottom: 0; }
  .personnel-cv-doc .cv-contact-row svg {
    flex-shrink: 0;
    margin-top: 0.1rem;
    opacity: 0.75;
  }
  .personnel-cv-doc .cv-sidebar-section-title {
    width: 100%;
    text-align: left;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    opacity: 0.55;
    margin-bottom: 0.5rem;
  }
  .personnel-cv-doc .cv-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    justify-content: flex-start;
    width: 100%;
  }
  .personnel-cv-doc .cv-tag {
    font-size: var(--text-xs, 0.75rem);
    padding: 0.2rem 0.55rem;
    border-radius: 9999px;
    border: 1px solid var(--cv-sidebar-soft);
    background: rgba(255, 255, 255, 0.06);
  }
  .personnel-cv-doc .cv-sidebar-expertise {
    width: 100%;
    margin-top: auto;
    padding-top: 0.5rem;
  }
  .personnel-cv-doc .cv-main {
    grid-column: 2;
    grid-row: 1;
    padding: clamp(1.25rem, 2.8vw, 2.5rem) clamp(1.25rem, 3.5vw, 3rem) clamp(1.5rem, 3vw, 2.75rem);
    min-width: 0;
  }
  .personnel-cv-doc .cv-main-head {
    border-bottom: 2px solid var(--cv-accent);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
  }
  .personnel-cv-doc .cv-main-head h1 {
    margin: 0;
    font-size: clamp(var(--text-xl, 1.25rem), 2.2vw, var(--text-3xl, 1.875rem));
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.2;
    color: var(--text-primary, #1e5c32);
  }
  .personnel-cv-doc .cv-main-head .cv-name-en {
    margin: 0.35rem 0 0;
    font-size: clamp(var(--text-sm, 0.875rem), 1.3vw, var(--text-lg, 1.125rem));
    color: var(--cv-muted);
    font-weight: 500;
  }
  .personnel-cv-doc .cv-main-head .cv-position {
    margin: 0.5rem 0 0;
    font-size: var(--text-base, 1rem);
    color: var(--cv-accent-text);
    font-weight: 600;
  }
  .personnel-cv-doc .cv-block { margin-bottom: 1.5rem; }
  .personnel-cv-doc .cv-block:last-child { margin-bottom: 0; }
  .personnel-cv-doc .cv-block h2 {
    margin: 0 0 0.6rem;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cv-accent-text);
  }
  .personnel-cv-doc .cv-block-body {
    font-size: var(--text-base, 1rem);
    line-height: 1.65;
    color: var(--color-gray-800, #374151);
    white-space: pre-line;
    border-left: 3px solid var(--cv-highlight, #eab308);
    padding-left: 1rem;
  }
  .personnel-cv-doc .cv-section-block { margin-top: 1.75rem; }
  .personnel-cv-doc .cv-section-block h2 {
    margin: 0 0 0.65rem;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cv-accent-text);
  }
  .personnel-cv-doc .cv-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--cv-line);
    border-radius: 6px;
  }
  .personnel-cv-doc table.cv-data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--text-sm, 0.875rem);
    color: var(--color-gray-800, #374151);
  }
  .personnel-cv-doc table.cv-data-table thead th {
    text-align: left;
    padding: 0.55rem 0.65rem;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--cv-accent-text);
    background: var(--color-secondary-light, #e8f5e9);
    border-bottom: 1px solid var(--cv-line);
    white-space: nowrap;
  }
  .personnel-cv-doc table.cv-data-table tbody td {
    padding: 0.6rem 0.65rem;
    border-bottom: 1px solid var(--color-gray-200, #f3f4f6);
    vertical-align: top;
    line-height: 1.45;
  }
  .personnel-cv-doc table.cv-data-table tbody tr:last-child td { border-bottom: none; }
  .personnel-cv-doc table.cv-data-table .cv-num {
    width: 2rem;
    text-align: center;
    color: var(--color-gray-500, #9ca3af);
    font-variant-numeric: tabular-nums;
  }
  .personnel-cv-doc table.cv-data-table .cv-title-cell { font-weight: 600; color: var(--cv-ink); }
  .personnel-cv-doc table.cv-data-table a {
    color: var(--cv-accent);
    font-weight: 500;
  }
  .personnel-cv-doc table.cv-data-table a:hover {
    color: var(--secondary-dark, #1e5c32);
    text-decoration: underline;
  }
  .personnel-cv-doc .cv-dash { color: var(--color-gray-400, #d1d5db); }
  .personnel-cv-doc .cv-sheet-foot {
    grid-column: 1 / -1;
    grid-row: 2;
    padding: 0.85rem 1.25rem;
    text-align: center;
    font-size: var(--text-xs, 0.75rem);
    color: var(--cv-muted);
    background: var(--color-gray-100, #f9fafb);
    border-top: 1px solid var(--cv-line);
  }
  @media (max-width: 52rem) {
    .personnel-cv-doc {
      min-height: 0;
    }
    .personnel-cv-doc .cv-sheet {
      grid-template-columns: 1fr;
      grid-template-rows: auto auto auto;
      min-height: auto;
    }
    .personnel-cv-doc .cv-sidebar {
      grid-column: 1;
      grid-row: 1;
      flex-direction: row;
      flex-wrap: wrap;
      justify-content: center;
      text-align: left;
      align-items: flex-start;
    }
    .personnel-cv-doc .cv-photo-wrap { width: 6.5rem; height: 6.5rem; }
    .personnel-cv-doc .cv-sidebar .cv-sidebar-text {
      flex: 1;
      min-width: 12rem;
      text-align: left;
    }
    .personnel-cv-doc .cv-sidebar-name { text-align: left; }
    .personnel-cv-doc .cv-sidebar-role { text-align: left; }
    .personnel-cv-doc .cv-contact-block { border-top: none; padding-top: 0; margin-top: 0; }
    .personnel-cv-doc .cv-sidebar-section-title { margin-top: 0.5rem; }
    .personnel-cv-doc .cv-sidebar-expertise {
      margin-top: 0.5rem;
      padding-top: 0;
    }
    .personnel-cv-doc .cv-main {
      grid-column: 1;
      grid-row: 2;
      padding: 1.5rem 1.25rem 1.75rem;
    }
    .personnel-cv-doc .cv-sheet-foot { grid-row: 3; }
  }
  @media print {
    .personnel-cv-doc { margin: 0; padding: 0; max-width: none; min-height: 0; }
    .personnel-cv-doc .cv-back { display: none !important; }
    .personnel-cv-doc .cv-sheet {
      box-shadow: none;
      border: none;
      border-radius: 0;
      min-height: 0;
    }
  }
</style>

<div class="personnel-cv-doc">
  <a class="cv-back" href="<?= base_url('personnel') ?>">← กลับหน้าบุคลากร</a>

  <article class="cv-sheet">
    <aside class="cv-sidebar" aria-label="ข้อมูลติดต่อและความเชี่ยวชาญ">
      <div class="cv-photo-wrap">
        <?php if ($img): ?>
          <img src="<?= esc($img, 'attr') ?>" alt="<?= esc($name, 'attr') ?>" width="152" height="152" loading="lazy" onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;\'><svg width=40 height=40 viewBox=\'0 0 24 24\' fill=\'#94a3b8\'><path d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/></svg></div>';">
        <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          </div>
        <?php endif; ?>
      </div>
      <div class="cv-sidebar-text">
        <div class="cv-sidebar-name"><?= esc($name) ?></div>
        <?php if ($nameEn): ?>
          <div class="cv-sidebar-role"><?= esc($nameEn) ?></div>
        <?php endif; ?>
        <div class="cv-sidebar-role" style="margin-top:0.35rem;"><?= esc($posLabel) ?></div>
      </div>

      <?php if ($email || $phone): ?>
        <div class="cv-contact-block">
          <?php if ($email): ?>
            <div class="cv-contact-row">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span><?= esc($email) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($phone): ?>
            <div class="cv-contact-row">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              <span><?= esc($phone) ?></span>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($expertise): ?>
        <div class="cv-sidebar-expertise">
          <div class="cv-sidebar-section-title">ความเชี่ยวชาญส่วนบุคคล</div>
          <div class="cv-tag-row">
            <?php foreach (preg_split('/[,;、]\s*/', $expertise) as $tag):
                $tag = trim($tag);
                if ($tag === '') {
                    continue;
                }
                ?>
              <span class="cv-tag"><?= esc($tag) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </aside>

    <main class="cv-main">
      <header class="cv-main-head">
        <h1><?= esc($name) ?></h1>
        <?php if ($nameEn): ?>
          <p class="cv-name-en"><?= esc($nameEn) ?></p>
        <?php endif; ?>
        <p class="cv-position"><?= esc($posLabel) ?></p>
      </header>

      <?php if ($education): ?>
        <section class="cv-block">
          <h2>การศึกษา</h2>
          <div class="cv-block-body"><?= esc($education) ?></div>
        </section>
      <?php endif; ?>

      <?php if ($bio): ?>
        <section class="cv-block">
          <h2>การแนะนำข้อมูล</h2>
          <div class="cv-block-body"><?= esc($bio) ?></div>
        </section>
      <?php endif; ?>

      <?php
        $cvSections = $cv_sections ?? [];
        foreach ($cvSections as $block):
            $blabel = $block['title'] ?? '';
            $bitems = $block['entries'] ?? [];
            $showPubTypeCol = in_array((string) ($block['type'] ?? ''), ['research', 'articles'], true);
            ?>
        <section class="cv-section-block">
          <h2><?= esc($blabel) ?></h2>
          <div class="cv-table-wrap">
            <table class="cv-data-table">
              <thead>
                <tr>
                  <th class="cv-num">#</th>
                  <th>หัวข้อ</th>
                  <?php if ($showPubTypeCol): ?>
                    <th>ประเภทการเผยแพร่</th>
                  <?php endif; ?>
                  <th>หน่วยงาน / สถานที่</th>
                  <th>ช่วงเวลา</th>
                  <th>รายละเอียด</th>
                  <th>ลิงก์</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $ti = 0;
            foreach ($bitems as $it):
                $ti++;
                $meta = $it['metadata_array'] ?? [];
                $link = (string) ($meta['url'] ?? $meta['legacy_url'] ?? '');
                $sd = !empty($it['start_date']) ? substr((string) $it['start_date'], 0, 10) : '';
                $ed = !empty($it['end_date']) ? substr((string) $it['end_date'], 0, 10) : '';
                $period = cv_format_entry_date_span_be(
                    $sd !== '' ? $sd : null,
                    $ed !== '' ? $ed : null,
                    (int) ($it['is_current'] ?? 0)
                );
                $org = trim((string) ($it['organization'] ?? ''));
                $loc = trim((string) ($it['location'] ?? ''));
                $orgLoc = $org === '' ? $loc : ($loc === '' ? $org : $org . ' · ' . $loc);
                $pubCode = (string) (($it['metadata_array'] ?? [])['rr_publication_type'] ?? '');
                $pubLabel = $pubCode !== '' ? \App\Libraries\RrPublicationType::labelTh($pubCode) : '';
                ?>
                <tr>
                  <td class="cv-num"><?= $ti ?></td>
                  <td class="cv-title-cell"><?= esc($it['title'] ?? '') ?></td>
                  <?php if ($showPubTypeCol): ?>
                    <td><?= $pubLabel !== '' ? esc($pubLabel) : '<span class="cv-dash">—</span>' ?></td>
                  <?php endif; ?>
                  <td><?= $orgLoc !== '' ? esc($orgLoc) : '<span class="cv-dash">—</span>' ?></td>
                  <td><?= $period !== '' ? esc($period) : '<span class="cv-dash">—</span>' ?></td>
                  <td style="white-space: pre-line;"><?= !empty($it['description']) ? esc($it['description']) : '<span class="cv-dash">—</span>' ?></td>
                  <td style="word-break: break-all;">
                    <?php if ($link !== ''): ?>
                      <a href="<?= esc($link, 'attr') ?>" target="_blank" rel="noopener noreferrer">เปิด</a>
                    <?php else: ?>
                      <span class="cv-dash">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
        <?php endforeach; ?>
    </main>

    <footer class="cv-sheet-foot"><?= esc($siteName) ?></footer>
  </article>
</div>
<?= $this->endSection() ?>
