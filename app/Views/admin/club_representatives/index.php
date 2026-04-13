<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>ตัวแทนนักศึกษาสโมสร</h2>
        <p style="margin: 0.5rem 0 0; color: var(--color-gray-600); font-size: 0.95rem;">
            กำหนดนักศึกษาในหลักสูตรของคุณให้เป็น <strong>ตัวแทนสโมสร (club)</strong> เพื่อเข้าจัดการกิจกรรมแจกบาร์โค้ดได้
        </p>
    </div>
    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (($current_user['role'] ?? '') === 'super_admin' && ! empty($programs)): ?>
            <form method="get" action="<?= base_url('admin/club-representatives') ?>" class="filter-form" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <label for="program_id" style="font-weight: 500;">หลักสูตร</label>
                <select name="program_id" id="program_id" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 8px; min-width: 240px;">
                    <option value="">— เลือกหลักสูตร —</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= (int) ($p['id'] ?? 0) ?>" <?= (int) ($filter_program_id ?? 0) === (int) ($p['id'] ?? 0) ? 'selected' : '' ?>><?= esc(trim(($p['name_th'] ?? '') . ' ' . ($p['name_en'] ?? ''))) ?: ('Program #' . (int) ($p['id'] ?? 0)) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>

        <?php if (empty($filter_program_id)): ?>
            <p class="text-muted"><?= ($current_user['role'] ?? '') === 'super_admin' ? 'กรุณาเลือกหลักสูตรด้านบน' : 'ไม่พบหลักสูตรในโปรไฟล์ของคุณ — ติดต่อผู้ดูแลระบบ' ?></p>
        <?php elseif (empty($students)): ?>
            <p>ยังไม่มีนักศึกษาในหลักสูตรนี้ (หรือยังไม่ได้ผูก program_id)</p>
        <?php else: ?>
            <?php $studentModelForName = new \App\Models\StudentUserModel(); ?>
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--color-gray-200);">
                            <th style="padding: 0.5rem;">อีเมล</th>
                            <th style="padding: 0.5rem;">ชื่อ</th>
                            <th style="padding: 0.5rem;">สถานะ</th>
                            <th style="padding: 0.5rem;">บทบาทปัจจุบัน</th>
                            <th style="padding: 0.5rem;">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                <td style="padding: 0.5rem;"><?= esc($s['email'] ?? '') ?></td>
                                <td style="padding: 0.5rem;"><?= esc($studentModelForName->getFullName($s)) ?></td>
                                <td style="padding: 0.5rem;"><?= esc($s['status'] ?? '') ?></td>
                                <td style="padding: 0.5rem;"><?= esc($s['role'] ?? '') ?></td>
                                <td style="padding: 0.5rem;">
                                    <?php if (($s['status'] ?? '') === 'active'): ?>
                                        <?php if (($s['role'] ?? '') !== 'club'): ?>
                                            <form method="post" action="<?= base_url('admin/club-representatives/set-role') ?>" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="student_user_id" value="<?= (int) ($s['id'] ?? 0) ?>">
                                                <input type="hidden" name="role" value="club">
                                                <button type="submit" class="btn btn-primary btn-sm">ตั้งเป็นตัวแทนสโมสร</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= base_url('admin/club-representatives/set-role') ?>" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="student_user_id" value="<?= (int) ($s['id'] ?? 0) ?>">
                                                <input type="hidden" name="role" value="student">
                                                <button type="submit" class="btn btn-secondary btn-sm">ยกเลิกตัวแทนสโมสร</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: var(--color-gray-500);">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
