<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2 style="margin:0;"><?= esc($page_title) ?></h2>
            <p class="form-text text-muted" style="margin: 0.4rem 0 0; font-size: 0.9rem; max-width: 48rem;">
                กรอกเนื้อหาแยก <strong>section</strong> สำหรับหน้าเว็บแบบ one page — แสดงต่อสาธารณะที่
                <a href="<?= esc($onepage_public_url) ?>" target="_blank" rel="noopener"><?= esc($onepage_public_url) ?></a>
                (เฉพาะ section ที่มีเนื้อหาและไม่ถูกซ่อน) รองรับ HTML ง่ายๆ ในช่องเนื้อหา
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                <a href="<?= base_url('program-admin') ?>" class="btn btn-secondary btn-sm">กลับแดชบอร์ด</a>
                <a href="<?= base_url('program-admin/edit/' . $program['id']) ?>" class="btn btn-secondary btn-sm">แก้ไขเนื้อหาเว็บ (เดิม)</a>
                <a href="<?= esc($onepage_public_url) ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">เปิดดู Onepage</a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <?php if (session()->getFlashdata('success')): ?>
            <p class="alert alert-success" style="padding:0.6rem 1rem; border-radius:8px; margin-bottom:1rem; background:var(--color-green-50); color: var(--color-green-800);">
                <?= esc(session()->getFlashdata('success')) ?>
            </p>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <p class="alert alert-error" style="padding:0.6rem 1rem; border-radius:8px; margin-bottom:1rem; background:#fef2f2; color: #b91c1c;">
                <?= esc(session()->getFlashdata('error')) ?>
            </p>
        <?php endif; ?>

        <form action="<?= base_url('program-admin/onepage/' . (int) $program['id']) ?>" method="post">
            <?= csrf_field() ?>

            <p class="form-text text-muted" style="font-size: 0.85rem; margin-bottom: 1.25rem;">
                ต่อยอด: เพิ่มหัวข้อ section ได้ที่ <code>app/Config/ProgramOnepage.php</code> แล้ว deploy — โครงจะ merge อัตโนมัติ
            </p>

            <div class="onepage-sections" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($sections as $i => $s) : ?>
                    <?php
                    $sid = $s['id'];
                    $chk = ! empty($s['hidden']) ? ' checked' : '';
                    ?>
                    <details class="onepage-acc" style="border:1px solid var(--color-gray-200); border-radius:8px; background: #fff; overflow: hidden;" <?= $i === 0 ? ' open' : '' ?>>
                        <summary style="padding:0.75rem 1rem; cursor:pointer; font-weight:600; list-style: none; display:flex; align-items:center; justify-content: space-between; gap: 0.5rem; background: var(--color-gray-50);">
                            <span><?= esc($s['title']) ?><?php if (! empty($s['aun_hint']) && $s['aun_hint'] !== '—') : ?><span class="text-muted" style="font-size:0.8rem; font-weight:400; margin-left:0.5rem">(AUN <?= esc($s['aun_hint']) ?>)</span><?php endif; ?></span>
                            <label class="form-label" style="margin:0; font-size:0.8rem; font-weight:500;" onclick="event.stopPropagation()">
                                <input type="checkbox" name="sections[<?= esc($sid) ?>][hidden]" value="1"<?= $chk ?>> ซ่อน section นี้
                            </label>
                        </summary>
                        <div style="padding: 1rem 1rem 1.25rem; border-top:1px solid var(--color-gray-200);">
                            <p class="form-text text-muted" style="font-size:0.8125rem; margin: 0 0 0.75rem;"><?= esc($s['description'] ?? '') ?></p>
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <label class="form-label" for="t_<?= esc($sid) ?>">หัวข้อแสดงบนเว็บ (ไม่บังคับ ว่าง = ใช้ชื่อ default)</label>
                                <input type="text" class="form-control" id="t_<?= esc($sid) ?>" name="sections[<?= esc($sid) ?>][title_override]" value="<?= esc($s['title_override'] ?? '') ?>" placeholder="<?= esc($s['default_title'] ?? '') ?>">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="b_<?= esc($sid) ?>">เนื้อหา (HTML ได้)</label>
                                <textarea class="form-control" id="b_<?= esc($sid) ?>" name="sections[<?= esc($sid) ?>][body]" rows="10" style="font-family: inherit; font-size:0.9rem;"><?= esc($s['body'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>

            <div class="form-actions" style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--color-gray-200);">
                <button type="submit" class="btn btn-primary">บันทึกหน้า Onepage</button>
            </div>
        </form>
    </div>
</div>
<style>details.onepage-acc summary::-webkit-details-marker { display: none; }</style>
<?= $this->endSection() ?>
