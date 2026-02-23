<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0;">เทมเพลตใบรับรอง</h2>
            <p style="margin: 0; color: #6b7280;">จัดการไฟล์พื้นหลังและตำแหน่งข้อมูลสำหรับการออก E-Certificate</p>
        </div>
        <a href="<?= base_url('admin/cert-templates/create') ?>" class="btn btn-primary">+ เพิ่มเทมเพลต</a>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($templates)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                    <circle cx="8" cy="6" r="1"/>
                    <circle cx="12" cy="6" r="1"/>
                    <circle cx="16" cy="6" r="1"/>
                </svg>
                <h3>ยังไม่มีเทมเพลต</h3>
                <p>กดปุ่ม "เพิ่มเทมเพลต" เพื่อสร้างไฟล์พื้นหลังและกำหนดตำแหน่งตัวแปร</p>
                <a href="<?= base_url('admin/cert-templates/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">เพิ่มเทมเพลต</a>
            </div>
        <?php else: ?>
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>ชื่อเทมเพลต</th>
                        <th style="width: 120px;">ระดับ</th>
                        <th style="width: 140px;">สถานะ</th>
                        <th style="width: 200px;">ไฟล์</th>
                        <th style="width: 160px;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $index => $template): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= esc($template['name_th']) ?></strong><br>
                                <small style="color: #6b7280;"><?= esc($template['name_en'] ?: '-') ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $template['level'] === 'program' ? 'badge-info' : 'badge-warning' ?>">
                                    <?= $template['level'] === 'program' ? 'ระดับหลักสูตร' : 'ระดับคณะ' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $template['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $template['status'] === 'active' ? 'ใช้งาน' : 'ปิดใช้งาน' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($template['template_file']): ?>
                                    <a href="<?= base_url($template['template_file']) ?>" target="_blank">ดูไฟล์</a>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= base_url('admin/cert-templates/edit/' . $template['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                    <a href="<?= base_url('admin/cert-templates/delete/' . $template['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบเทมเพลตนี้?');">ลบ</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
