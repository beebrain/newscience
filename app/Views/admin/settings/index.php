<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>ตั้งค่าเว็บไซต์</h2>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">จัดการการตั้งค่าต่างๆ ของเว็บไซต์</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="<?= base_url('admin/settings/init-defaults') ?>" class="btn btn-secondary btn-sm" onclick="return confirm('สร้างค่าเริ่มต้นสำหรับการตั้งค่าที่จำเป็น?')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <polyline points="2 17 12 12 22 17"/>
                        <polyline points="2 12 12 7 22 12"/>
                    </svg>
                    สร้างค่าเริ่มต้น
                </a>
                <a href="<?= base_url('admin/settings/create') ?>" class="btn btn-primary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    เพิ่มการตั้งค่า
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($groupedSettings)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 64px; height: 64px; color: var(--color-gray-400);">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                </svg>
                <h3>ยังไม่มีการตั้งค่า</h3>
                <p>เริ่มต้นโดยการสร้างค่าเริ่มต้นหรือเพิ่มการตั้งค่าใหม่</p>
                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                    <a href="<?= base_url('admin/settings/init-defaults') ?>" class="btn btn-primary">สร้างค่าเริ่มต้น</a>
                    <a href="<?= base_url('admin/settings/create') ?>" class="btn btn-secondary">เพิ่มการตั้งค่า</a>
                </div>
            </div>
        <?php else: ?>
            <form action="<?= base_url('admin/settings/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <?php foreach ($groupedSettings as $category => $settings): ?>
                    <div class="settings-category" style="border-bottom: 1px solid var(--color-gray-200);">
                        <div class="category-header" style="padding: 1rem 1.5rem; background: var(--color-gray-50); border-bottom: 1px solid var(--color-gray-100);">
                            <h3 style="margin: 0; font-size: 1rem; color: var(--color-gray-800);">
                                <?= esc($categoryLabels[$category] ?? ucfirst($category)) ?>
                                <span class="badge badge-secondary" style="margin-left: 0.5rem;"><?= count($settings) ?></span>
                            </h3>
                        </div>
                        
                        <div class="settings-list" style="padding: 1rem 1.5rem;">
                            <?php foreach ($settings as $setting): ?>
                                <div class="setting-item" style="display: grid; grid-template-columns: 200px 1fr auto; gap: 1rem; align-items: start; padding: 1rem 0; border-bottom: 1px solid var(--color-gray-100);">
                                    <div class="setting-info">
                                        <label class="form-label" style="margin: 0; font-weight: 500;">
                                            <?= esc($setting['setting_key']) ?>
                                        </label>
                                        <?php if (!empty($setting['description'])): ?>
                                            <p class="form-hint" style="margin: 0.25rem 0 0 0; font-size: 0.75rem;">
                                                <?= esc($setting['description']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <span class="badge badge-secondary" style="margin-top: 0.5rem; font-size: 0.65rem;">
                                            <?= esc($setting['setting_type']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="setting-input">
                                        <?php switch ($setting['setting_type']):
                                            case 'textarea': ?>
                                                <textarea name="settings[<?= $setting['id'] ?>]" 
                                                          class="form-control" 
                                                          rows="3"
                                                          style="resize: vertical;"><?= esc($setting['setting_value']) ?></textarea>
                                                <?php break; ?>
                                                
                                            <?php case 'boolean': ?>
                                                <label class="toggle-switch" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                    <input type="hidden" name="settings[<?= $setting['id'] ?>]" value="0">
                                                    <input type="checkbox" 
                                                           name="settings[<?= $setting['id'] ?>]" 
                                                           value="1"
                                                           <?= $setting['setting_value'] ? 'checked' : '' ?>
                                                           style="width: 20px; height: 20px;">
                                                    <span style="font-size: 0.875rem; color: var(--color-gray-600);">
                                                        <?= $setting['setting_value'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                                                    </span>
                                                </label>
                                                <?php break; ?>
                                                
                                            <?php case 'image': ?>
                                                <div class="image-input-wrapper">
                                                    <?php if ($setting['setting_value']): ?>
                                                        <div class="current-image" style="margin-bottom: 0.5rem;">
                                                            <img src="<?= base_url($setting['setting_value']) ?>" 
                                                                 alt="Current" 
                                                                 style="max-width: 200px; max-height: 100px; border-radius: 4px; border: 1px solid var(--color-gray-200);">
                                                            <p class="form-hint" style="margin: 0.25rem 0 0 0; font-size: 0.75rem;">
                                                                <?= esc($setting['setting_value']) ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" 
                                                           name="settings_files[<?= $setting['id'] ?>]" 
                                                           class="form-control"
                                                           accept="image/*">
                                                    <input type="hidden" name="settings[<?= $setting['id'] ?>]" value="<?= esc($setting['setting_value']) ?>">
                                                </div>
                                                <?php break; ?>
                                                
                                            <?php case 'json': ?>
                                                <textarea name="settings[<?= $setting['id'] ?>]" 
                                                          class="form-control" 
                                                          rows="5"
                                                          style="resize: vertical; font-family: monospace; font-size: 0.875rem;"
                                                          placeholder="Valid JSON"><?= esc($setting['setting_value']) ?></textarea>
                                                <p class="form-hint" style="margin: 0.25rem 0 0 0; font-size: 0.75rem;">รูปแบบ JSON</p>
                                                <?php break; ?>
                                                
                                            <?php default: // text ?>
                                                <input type="text" 
                                                       name="settings[<?= $setting['id'] ?>]" 
                                                       class="form-control"
                                                       value="<?= esc($setting['setting_value']) ?>">
                                                <?php break; ?>
                                        <?php endswitch; ?>
                                    </div>
                                    
                                    <div class="setting-actions" style="text-align: right;">
                                        <a href="<?= base_url('admin/settings/delete/' . $setting['id']) ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('ต้องการลบการตั้งค่า <?= esc($setting['setting_key']) ?>?')"
                                           style="padding: 0.375rem 0.5rem;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="form-actions" style="padding: 1.5rem; background: var(--color-gray-50); border-top: 1px solid var(--color-gray-200);">
                    <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        บันทึกการตั้งค่า
                    </button>
                    <a href="<?= base_url('admin') ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">ยกเลิก</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.settings-category:last-child {
    border-bottom: none !important;
}

.setting-item:last-child {
    border-bottom: none !important;
}

@media (max-width: 768px) {
    .setting-item {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }
    
    .setting-actions {
        text-align: left !important;
    }
}
</style>

<?= $this->endSection() ?>
