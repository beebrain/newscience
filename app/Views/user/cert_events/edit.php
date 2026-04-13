<?php $cb = $cert_base ?? rtrim(base_url('dashboard/cert-events'), '/'); ?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">แก้ไขกิจกรรม</h2>
    </div>

    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= esc($cb) ?>/<?= (int) $event['id'] ?>/update" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>ชื่อกิจกรรม/หัวข้ออบรม <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= esc(old('title', $event['title'])) ?>" required>
            </div>

            <div class="form-group">
                <label>รายละเอียด</label>
                <textarea name="description" class="form-control" rows="4"><?= esc(old('description', $event['description'])) ?></textarea>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>วันที่จัดกิจกรรม</label>
                    <input type="date" name="event_date" class="form-control" value="<?= esc(old('event_date', $event['event_date'])) ?>">
                </div>

                <div class="form-group">
                    <label>สถานะ</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= old('status', $event['status']) === 'draft' ? 'selected' : '' ?>>ร่าง</option>
                        <option value="open" <?= old('status', $event['status']) === 'open' ? 'selected' : '' ?>>เปิด</option>
                        <option value="issued" <?= old('status', $event['status']) === 'issued' ? 'selected' : '' ?>>ออก Cert แล้ว</option>
                        <option value="closed" <?= old('status', $event['status']) === 'closed' ? 'selected' : '' ?>>ปิด</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>ผู้ลงนาม</label>
                <select name="signer_id" class="form-control">
                    <option value="">-- ไม่ระบุ --</option>
                    <?php foreach ($signers as $signer): ?>
                        <option value="<?= $signer['uid'] ?>" <?= old('signer_id', $event['signer_id']) == $signer['uid'] ? 'selected' : '' ?>>
                            <?= esc(($signer['tf_name'] ?? $signer['gf_name']) . ' ' . ($signer['tl_name'] ?? $signer['gl_name'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>ไฟล์ใบรับรองของกิจกรรม (PDF / JPG / PNG)</label>
                <input type="file" name="background_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text text-muted">จำเป็นต้องมีไฟล์นี้ก่อนกดออกใบ</small>
                <?php if (! empty($event['background_file'])): ?>
                    <div style="margin-top:0.5rem;font-size:12px;">ปัจจุบัน: <?= esc($event['background_kind'] ?? '') ?> — <?= esc($event['background_file']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>layout_json (ทับตำแหน่งฟิลด์ — ไม่บังคับ)</label>
                <textarea name="layout_json" class="form-control" rows="6" placeholder='{"field_mapping":{"student_name":{"x":100,"y":140,"font_size":22}},...}'><?= esc(old('layout_json', $event['layout_json'] ?? '')) ?></textarea>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="<?= esc($cb) ?>/<?= (int) $event['id'] ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
