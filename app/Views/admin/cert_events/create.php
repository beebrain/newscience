<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">สร้างกิจกรรมใบรับรอง</h2>
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

        <form method="post" action="<?= base_url('admin/cert-events/store') ?>" class="form-grid">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>ชื่อกิจกรรม/หัวข้ออบรม <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= esc(old('title')) ?>" required>
                <small class="form-text text-muted">เช่น "อบรมหลักสูตร Python 2024", "การประชุมวิชาการ"</small>
            </div>

            <div class="form-group">
                <label>รายละเอียด</label>
                <textarea name="description" class="form-control" rows="4"><?= esc(old('description')) ?></textarea>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>วันที่จัดกิจกรรม</label>
                    <input type="date" name="event_date" class="form-control" value="<?= esc(old('event_date')) ?>">
                </div>

                <div class="form-group">
                    <label>สถานะ</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>ร่าง (ยังไม่เปิด)</option>
                        <option value="open" <?= old('status', 'open') === 'open' ? 'selected' : '' ?>>เปิด (พร้อมเพิ่มรายชื่อ)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>เทมเพลตใบรับรอง <span class="text-danger">*</span></label>
                <select name="template_id" class="form-control" required>
                    <option value="">-- เลือกเทมเพลต --</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= $template['id'] ?>" <?= old('template_id') == $template['id'] ? 'selected' : '' ?>>
                            <?= esc($template['name_th']) ?> (<?= $template['level'] === 'program' ? 'ระดับหลักสูตร' : 'ระดับคณะ' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                    <a href="<?= base_url('admin/cert-templates/create') ?>" target="_blank">+ สร้างเทมเพลตใหม่</a>
                </small>
            </div>

            <div class="form-group">
                <label>ผู้ลงนาม</label>
                <select name="signer_id" class="form-control">
                    <option value="">-- ไม่ระบุ (ไม่มีลายเซ็น) --</option>
                    <?php foreach ($signers as $signer): ?>
                        <option value="<?= $signer['uid'] ?>" <?= old('signer_id') == $signer['uid'] ? 'selected' : '' ?>>
                            <?= esc(($signer['thai_name'] ?? $signer['gf_name']) . ' ' . ($signer['thai_lastname'] ?? $signer['gl_name'])) ?> (<?= $signer['role'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="<?= base_url('admin/cert-events') ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">สร้างกิจกรรม</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
