<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>แก้ไขหลักสูตร</h2>
        <a href="<?= base_url('admin/programs') ?>" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
    <div class="card-body">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc(is_array($e) ? implode(', ', $e) : $e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php $program = $program ?? []; ?>
        <form action="<?= base_url('admin/programs/update/' . ($program['id'] ?? 0)) ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name_th">ชื่อหลักสูตร (ไทย) <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="name_th" id="name_th" class="form-control" value="<?= esc(old('name_th', $program['name_th'] ?? '')) ?>" required placeholder="เช่น คณิตศาสตร์ประยุกต์">
                </div>
                <div class="form-group">
                    <label class="form-label" for="name_en">ชื่อหลักสูตร (อังกฤษ)</label>
                    <input type="text" name="name_en" id="name_en" class="form-control" value="<?= esc(old('name_en', $program['name_en'] ?? '')) ?>" placeholder="Applied Mathematics">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="degree_th">ชื่อปริญญา (ไทย)</label>
                    <input type="text" name="degree_th" id="degree_th" class="form-control" value="<?= esc(old('degree_th', $program['degree_th'] ?? '')) ?>" placeholder="วิทยาศาสตรบัณฑิต">
                </div>
                <div class="form-group">
                    <label class="form-label" for="degree_en">ชื่อปริญญา (อังกฤษ)</label>
                    <input type="text" name="degree_en" id="degree_en" class="form-control" value="<?= esc(old('degree_en', $program['degree_en'] ?? '')) ?>" placeholder="Bachelor of Science">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="level">ระดับ <span style="color: var(--color-danger);">*</span></label>
                    <select name="level" id="level" class="form-control" required>
                        <option value="bachelor" <?= (old('level') ?: ($program['level'] ?? '')) === 'bachelor' ? 'selected' : '' ?>>ปริญญาตรี</option>
                        <option value="master" <?= (old('level') ?: ($program['level'] ?? '')) === 'master' ? 'selected' : '' ?>>ปริญญาโท</option>
                        <option value="doctorate" <?= (old('level') ?: ($program['level'] ?? '')) === 'doctorate' ? 'selected' : '' ?>>ปริญญาเอก</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="organization_unit_id">หน่วยงาน (หลักสูตร)</label>
                    <select name="organization_unit_id" id="organization_unit_id" class="form-control">
                        <option value="">-- เลือกตามระดับ (ป.ตรี/บัณฑิต) --</option>
                        <?php foreach ($departments ?? [] as $d): ?>
                            <option value="<?= (int)$d['id'] ?>" <?= (string)(old('organization_unit_id') ?: ($program['organization_unit_id'] ?? '')) === (string)$d['id'] ? 'selected' : '' ?>><?= esc($d['name_th']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ประธานหลักสูตร</label>
                <?php
                $programPersonnel = $program_personnel ?? [];
                $currentCoordinatorId = $current_coordinator_id ?? null;
                $coordinatorName = null;
                foreach ($programPersonnel as $p) {
                    if ((int) $p['id'] === $currentCoordinatorId) {
                        $coordinatorName = $p['name'];
                        break;
                    }
                }
                ?>
                <?php if (empty($programPersonnel)): ?>
                    <p style="color: var(--color-gray-500); font-size: 0.9rem; margin: 0.25rem 0 0;">ยังไม่มีอาจารย์ในหลักสูตรนี้ ให้เพิ่มอาจารย์จากหน้า <a href="<?= base_url('admin/organization') ?>">โครงสร้างองค์กร</a> โดยเลือกหลักสูตรนี้ให้อาจารย์ก่อน</p>
                <?php elseif ($coordinatorName): ?>
                    <p style="font-size: 0.95rem; margin: 0.25rem 0;">
                        <strong><?= esc($coordinatorName) ?></strong>
                        <span style="color: var(--color-gray-500); font-size: 0.85rem; margin-left: 0.5rem;">
                            (ตั้งค่าจากหน้า <a href="<?= base_url('admin/organization') ?>">โครงสร้างองค์กร</a> โดยเลือกบทบาท "ประธานหลักสูตร" ให้อาจารย์)
                        </span>
                    </p>
                <?php else: ?>
                    <p style="color: var(--color-gray-500); font-size: 0.9rem; margin: 0.25rem 0 0;">
                        ยังไม่ได้กำหนดประธานหลักสูตร - ตั้งค่าจากหน้า <a href="<?= base_url('admin/organization') ?>">โครงสร้างองค์กร</a> โดยเลือกบทบาท "ประธานหลักสูตร" ให้อาจารย์ในหลักสูตรนี้
                    </p>
                <?php endif; ?>
                <?php if (!empty($programPersonnel)): ?>
                <details style="margin-top: 0.5rem;">
                    <summary style="cursor: pointer; color: var(--color-primary); font-size: 0.85rem;">ดูอาจารย์ในหลักสูตร (<?= count($programPersonnel) ?> คน)</summary>
                    <ul style="margin: 0.5rem 0 0 1rem; padding: 0; list-style: disc;">
                        <?php foreach ($programPersonnel as $person): ?>
                            <li style="font-size: 0.85rem; color: var(--color-gray-600);">
                                <?= esc($person['name']) ?>
                                <?php if (!empty($person['role'])): ?>
                                    <span style="color: var(--color-gray-400);">- <?= esc($person['role']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="duration">ระยะเวลาเรียน</label>
                    <input type="text" name="duration" id="duration" class="form-control" value="<?= esc(old('duration', $program['duration'] ?? '')) ?>" placeholder="4 ปี">
                </div>
                <div class="form-group">
                    <label class="form-label" for="credits">หน่วยกิต</label>
                    <input type="number" name="credits" id="credits" class="form-control" value="<?= esc(old('credits', $program['credits'] ?? '')) ?>" placeholder="ไม่ระบุ" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="sort_order">ลำดับการแสดง</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= esc(old('sort_order', $program['sort_order'] ?? 0)) ?>" min="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="status">สถานะ <span style="color: var(--color-danger);">*</span></label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active" <?= (old('status') ?: ($program['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= (old('status') ?: ($program['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="website">เว็บไซต์หลักสูตร</label>
                <input type="url" name="website" id="website" class="form-control" value="<?= esc(old('website', $program['website'] ?? '')) ?>" placeholder="https://...">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">รายละเอียด (ไทย)</label>
                <textarea name="description" id="description" class="form-control" rows="3" placeholder="คำอธิบายหลักสูตร"><?= esc(old('description', $program['description'] ?? '')) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="description_en">รายละเอียด (อังกฤษ)</label>
                <textarea name="description_en" id="description_en" class="form-control" rows="3" placeholder="Program description"><?= esc(old('description_en', $program['description_en'] ?? '')) ?></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="<?= base_url('admin/programs') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
