<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>โครงสร้างองค์กรของคณะ</h2>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= base_url('admin/organization/create') ?>" class="btn btn-primary">เพิ่มบุคลากร</a>
            <a href="<?= base_url('personnel') ?>" target="_blank" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                <polyline points="15 3 21 3 21 9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            ดูหน้าโครงสร้าง (หน้าสาธารณะ)
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <p style="color: var(--color-gray-600); margin-bottom: 1rem;">
            จัดการตำแหน่งและลำดับการแสดงผลในหน้าโครงสร้างองค์กร (ผู้บริหาร / บุคลากร) คลิก <strong>แก้ไข</strong> เพื่อเปลี่ยนตำแหน่งหรือลำดับ
        </p>

        <form method="get" action="<?= base_url('admin/organization') ?>" class="filter-form" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <label for="name" style="font-weight: 500; color: var(--color-gray-700);">ชื่อ</label>
            <input type="text" name="name" id="name" value="<?= esc($filter_name ?? '') ?>" placeholder="ค้นหาชื่อ-นามสกุล" style="padding: 0.5rem 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 8px; font-size: 0.9rem; min-width: 180px;">
            <label for="position" style="font-weight: 500; color: var(--color-gray-700);">ตำแหน่ง</label>
            <select name="position" id="position" onchange="this.form.submit()" style="padding: 0.5rem 2rem 0.5rem 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 8px; font-size: 0.9rem; min-width: 200px;">
                <?php foreach ($position_filter_options ?? [] as $value => $label): ?>
                    <option value="<?= $value === '' ? '' : (int)$value ?>" <?= (string)($filter_position ?? '') === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
            <?php if (($filter_position ?? '') !== '' || ($filter_name ?? '') !== ''): ?>
                <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary btn-sm">ล้างตัวกรอง</a>
            <?php endif; ?>
        </form>

        <?php foreach ($groups as $tier => $group):
            $personnel = $group['personnel'];
            $label = $group['label_th'];
        ?>
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--color-gray-700); margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--color-primary);">
                    <?= esc($label) ?> (<?= count($personnel) ?> คน)
                </h3>
                <?php if (empty($personnel)): ?>
                    <p style="color: var(--color-gray-500); font-size: 0.9rem;">ยังไม่มีบุคลากรในระดับนี้</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ลำดับ</th>
                                <th style="width: 80px;">รูป</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ตำแหน่ง</th>
                                <th>หลักสูตร/สังกัด</th>
                                <th style="width: 160px;">กำหนดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $p): ?>
                                <?php
                                $fullName = trim(($p['title'] ?? '') . ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                                $img = $p['image'] ?? '';
                                if ($img && strpos($img, 'http') !== 0) {
                                    $img = str_replace('\\', '/', $img);
                                    if (strpos($img, 'uploads/') !== 0) {
                                        $img = (strpos($img, 'staff/') === 0 ? 'uploads/' : 'uploads/personnel/') . ltrim($img, '/');
                                    }
                                    $img = base_url($img);
                                }
                                ?>
                                <tr>
                                    <td><?= (int)($p['sort_order'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($img): ?>
                                            <img src="<?= esc($img) ?>" alt="" class="table-image" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--color-gray-200); display: flex; align-items: center; justify-content: center; color: var(--color-gray-500); font-size: 0.75rem;">—</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= esc($fullName) ?></strong></td>
                                    <td><?= esc($p['position'] ?? $p['position_en'] ?? '—') ?></td>
                                    <td>
                                        <?php
                                        $dept = $p['department_name_th'] ?? $p['department_name_en'] ?? '';
                                        $tags = $p['programs_list_tags'] ?? [];
                                        ?>
                                        <?php if ($dept): ?><span style="color: var(--color-gray-600);"><?= esc($dept) ?></span><?php endif; ?>
                                        <?php if (!empty($tags)): ?>
                                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; margin-top: 0.25rem;">
                                                <?php foreach ($tags as $t): ?>
                                                    <span class="badge" style="background: var(--color-primary); color: #fff; font-size: 0.75rem; font-weight: 500; padding: 0.2rem 0.5rem; border-radius: 999px;">
                                                        <?= esc($t['name']) ?><?= !empty($t['role']) ? ' <span style="opacity: 0.9;">(' . esc($t['role']) . ')</span>' : '' ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif (!empty($p['programs_list'])): ?>
                                            <span class="badge" style="background: var(--color-primary); color: #fff; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 999px;"><?= esc($p['programs_list']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!$dept && empty($tags) && empty($p['programs_list'])): ?>—<?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('admin/organization/edit/' . $p['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                        <a href="<?= base_url('admin/organization/delete/' . $p['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('ต้องการลบบุคลากรนี้ออกจากโครงสร้างองค์กรหรือไม่?\n(จะไม่แสดงบนหน้าสาธารณะ แต่ข้อมูลยังอยู่ในระบบ)');">ลบ</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?= $this->endSection() ?>
