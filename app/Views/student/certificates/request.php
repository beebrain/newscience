<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<div class="portal-card">
    <div class="portal-card-header">
        <div>
            <h2>ขอใบรับรอง</h2>
            <p class="text-muted">เลือกประเภทเอกสาร ระบุวัตถุประสงค์ และส่งคำขอถึงเจ้าหน้าที่</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul style="margin:0; padding-left:1.25rem;">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('student/certificates/request') ?>" class="form-grid">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>ประเภทใบรับรอง <span class="text-danger">*</span></label>
            <select name="template_id" class="form-control" required>
                <option value="">-- เลือกเทมเพลต --</option>
                <?php foreach ($templates as $tpl): ?>
                    <option value="<?= $tpl['id'] ?>" <?= old('template_id') == $tpl['id'] ? 'selected' : '' ?>>
                        <?= esc($tpl['name_th']) ?> (<?= $tpl['level'] === 'program' ? 'ระดับหลักสูตร' : 'ระดับคณะ' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>วัตถุประสงค์ <span class="text-danger">*</span></label>
            <textarea name="purpose" class="form-control" rows="3" required><?= esc(old('purpose')) ?></textarea>
            <small class="form-text text-muted">อธิบายว่าต้องการใช้ใบรับรองเพื่ออะไร</small>
        </div>

        <div class="form-group">
            <label>จำนวนฉบับ</label>
            <input type="number" name="copies" class="form-control" min="1" max="5" value="<?= esc(old('copies', 1)) ?>" required>
        </div>

        <div class="form-group">
            <label>หมายเหตุเพิ่มเติม</label>
            <textarea name="note" class="form-control" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติมถ้ามี"><?= esc(old('note')) ?></textarea>
        </div>

        <div class="form-actions" style="display:flex; gap:1rem;">
            <a href="<?= base_url('student/certificates') ?>" class="btn btn-secondary">ยกเลิก</a>
            <button type="submit" class="btn btn-primary">ส่งคำขอ</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
