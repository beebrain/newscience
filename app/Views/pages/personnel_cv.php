<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
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
<div class="personnel-cv-page" style="max-width: 900px; margin: 2rem auto; padding: 0 1rem;">

  <div style="margin-bottom: 1.5rem;">
    <a href="<?= base_url('personnel') ?>" style="color: var(--color-primary, #2563eb); text-decoration: none; font-size: 0.9rem;">
      ← กลับหน้าบุคลากร
    </a>
  </div>

  <div style="background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">

    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); padding: 2rem; color: #fff; display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
      <?php if ($img): ?>
        <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
          <img src="<?= esc($img) ?>" alt="<?= esc($name) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#475569;\'><svg width=48 height=48 viewBox=\'0 0 24 24\' fill=\'#94a3b8\'><path d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/></svg></div>';">
        </div>
      <?php else: ?>
        <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0; background: #475569; display: flex; align-items: center; justify-content: center;">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
      <?php endif; ?>
      <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0;"><?= esc($name) ?></h1>
        <?php if ($nameEn): ?>
          <p style="font-size: 1rem; opacity: 0.8; margin: 0.25rem 0 0;"><?= esc($nameEn) ?></p>
        <?php endif; ?>
        <p style="font-size: 0.95rem; opacity: 0.9; margin: 0.5rem 0 0;"><?= esc($posLabel) ?></p>
        <?php if ($email): ?>
          <p style="font-size: 0.85rem; opacity: 0.7; margin: 0.5rem 0 0;">
            <svg style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <?= esc($email) ?>
          </p>
        <?php endif; ?>
        <?php if ($phone): ?>
          <p style="font-size: 0.85rem; opacity: 0.7; margin: 0.25rem 0 0;">
            <svg style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <?= esc($phone) ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Content -->
    <div style="padding: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

      <?php if ($education): ?>
      <div>
        <h2 style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">การศึกษา</h2>
        <div style="font-size: 0.9rem; color: #334155; line-height: 1.6; white-space: pre-line;"><?= esc($education) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($expertise): ?>
      <div>
        <h2 style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">ความเชี่ยวชาญ</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
          <?php foreach (preg_split('/[,;、]\s*/', $expertise) as $tag):
            $tag = trim($tag);
            if ($tag === '') continue;
          ?>
            <span style="padding: 0.25rem 0.75rem; background: #eff6ff; color: #1e40af; font-size: 0.8rem; border-radius: 9999px; border: 1px solid #bfdbfe;"><?= esc($tag) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($bio): ?>
      <div style="grid-column: 1 / -1;">
        <h2 style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">ประวัติ</h2>
        <div style="font-size: 0.9rem; color: #334155; line-height: 1.6; white-space: pre-line;"><?= esc($bio) ?></div>
      </div>
      <?php endif; ?>

      <?php
      $cvSections = $cv_sections ?? [];
      foreach ($cvSections as $block):
          $blabel = $block['title'] ?? '';
          $bitems = $block['entries'] ?? [];
      ?>
      <div style="grid-column: 1 / -1;">
        <h2 style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;"><?= esc($blabel) ?></h2>
        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; color: #334155;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <th style="text-align: center; padding: 0.65rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; width: 2.5rem;">#</th>
                <th style="text-align: left; padding: 0.65rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b;">หัวข้อ</th>
                <th style="text-align: left; padding: 0.65rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; min-width: 8rem;">หน่วยงาน / สถานที่</th>
                <th style="text-align: left; padding: 0.65rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; min-width: 6rem;">ช่วงเวลา</th>
                <th style="text-align: left; padding: 0.65rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b;">รายละเอียด</th>
                <th style="text-align: left; padding: 0.65rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; min-width: 4rem;">ลิงก์</th>
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
                  $period = '';
                  if ($sd !== '') {
                      $period = $sd;
                      if ($ed !== '') {
                          $period .= ' – ' . $ed;
                      } elseif (!empty($it['is_current'])) {
                          $period .= ' – ปัจจุบัน';
                      }
                  }
                  $org = trim((string) ($it['organization'] ?? ''));
                  $loc = trim((string) ($it['location'] ?? ''));
                  $orgLoc = $org === '' ? $loc : ($loc === '' ? $org : $org . ' · ' . $loc);
              ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                  <td style="text-align: center; padding: 0.65rem 0.75rem; color: #94a3b8; vertical-align: top;"><?= $ti ?></td>
                  <td style="padding: 0.65rem 0.75rem; font-weight: 600; vertical-align: top;"><?= esc($it['title'] ?? '') ?></td>
                  <td style="padding: 0.65rem 0.75rem; vertical-align: top; font-size: 0.85rem;"><?= $orgLoc !== '' ? esc($orgLoc) : '<span style="color:#cbd5e1;">—</span>' ?></td>
                  <td style="padding: 0.65rem 0.75rem; vertical-align: top; font-size: 0.85rem;"><?= $period !== '' ? esc($period) : '<span style="color:#cbd5e1;">—</span>' ?></td>
                  <td style="padding: 0.65rem 0.75rem; line-height: 1.5; white-space: pre-line; vertical-align: top;"><?= !empty($it['description']) ? esc($it['description']) : '<span style="color:#cbd5e1;">—</span>' ?></td>
                  <td style="padding: 0.65rem 0.75rem; vertical-align: top; word-break: break-all;">
                    <?php if ($link !== ''): ?>
                      <a href="<?= esc($link, 'attr') ?>" style="font-size: 0.85rem; color: #2563eb;" target="_blank" rel="noopener noreferrer">ลิงก์</a>
                    <?php else: ?>
                      <span style="color: #cbd5e1;">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endforeach; ?>

    </div>

    <!-- Footer -->
    <div style="padding: 1rem 2rem; text-align: center; border-top: 1px solid #e2e8f0;">
      <p style="font-size: 0.75rem; color: #94a3b8;"><?= esc($siteName) ?></p>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
