<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <p class="form-hint" style="margin: 0.25rem 0 0 0;">จัดการหมวดหมู่และเอกสารดาวน์โหลดของหน้าเว็บคณะ (แบบฟอร์มดาวน์โหลด, คำสั่ง/ประกาศ/ระเบียบ ฯลฯ)</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" style="margin: 1rem 1.5rem; padding: 0.75rem 1rem; background: #d3f9d8; border-radius: 8px; color: #2b8a3e;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" style="margin: 1rem 1.5rem; padding: 0.75rem 1rem; background: #ffe3e3; border-radius: 8px; color: #c92a2a;">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card-body" style="padding: 1.5rem;">
        <ul class="admin-tabs" role="tablist">
            <?php foreach ($page_types as $pt => $label): ?>
                <li class="admin-tabs__item">
                    <button type="button" class="admin-tab-btn <?= ($active_tab ?? 'support') === $pt ? 'active' : '' ?>"
                        data-tab="<?= esc($pt) ?>"
                        role="tab"
                        aria-selected="<?= ($active_tab ?? 'support') === $pt ? 'true' : 'false' ?>">
                        <?= esc($label) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php foreach ($page_types as $pt => $label): ?>
            <div class="tab-pane" id="tab-<?= esc($pt) ?>" style="display: none;">
                <?php $cats = $categories_by_page[$pt] ?? []; ?>

                <!-- Add category form -->
                <div class="add-category-form" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--color-gray-50); border-radius: 8px;">
                    <h3 style="margin: 0 0 1rem 0; font-size: 1rem;">เพิ่มหมวดหมู่</h3>
                    <form action="<?= base_url('admin/downloads/store-category') ?>" method="post" style="display: grid; gap: 1rem; grid-template-columns: 1fr auto auto auto;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="page_type" value="<?= esc($pt) ?>">
                        <div>
                            <label for="name-<?= $pt ?>" class="form-label">ชื่อหมวด *</label>
                            <input type="text" id="name-<?= $pt ?>" name="name" class="form-control" required placeholder="เช่น งานบริหารทั่วไป">
                        </div>
                        <div>
                            <label for="icon-<?= $pt ?>" class="form-label">ไอคอน</label>
                            <input type="text" id="icon-<?= $pt ?>" name="icon" class="form-control" value="folder" placeholder="folder">
                        </div>
                        <div>
                            <label for="sort_order-<?= $pt ?>" class="form-label">ลำดับ</label>
                            <input type="number" id="sort_order-<?= $pt ?>" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div style="align-self: end;">
                            <button type="submit" class="btn btn-primary btn-save-download" name="submit" value="1">เพิ่มหมวด</button>
                        </div>
                    </form>
                </div>

                <!-- Category list -->
                <div class="category-list">
                    <?php if (empty($cats)): ?>
                        <p style="color: var(--color-gray-500);">ยังไม่มีหมวดในหน้านี้</p>
                    <?php else: ?>
                        <div style="display: grid; gap: 1rem;">
                            <?php foreach ($cats as $cat): ?>
                                <div class="category-card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                    <div>
                                        <strong><?= esc($cat['name']) ?></strong>
                                        <span style="color: var(--color-gray-500); font-size: 0.875rem;"> (รหัส <?= (int)$cat['id'] ?>)</span>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <a href="<?= base_url('admin/downloads/documents/' . $cat['id']) ?>" class="btn btn-primary btn-sm">จัดการเอกสาร</a>
                                        <button type="button" class="btn btn-outline btn-sm btn-edit-cat" data-id="<?= $cat['id'] ?>" data-name="<?= esc($cat['name']) ?>" data-icon="<?= esc($cat['icon']) ?>" data-sort="<?= (int)$cat['sort_order'] ?>">แก้ไข</button>
                                        <a href="<?= base_url('admin/downloads/delete-category/' . $cat['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบหมวดนี้และเอกสารทั้งหมดในหมวดหรือไม่?');">ลบ</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Edit category modal -->
<div id="editCategoryModal" class="download-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="download-modal-box" style="background: white; padding: 1.5rem; border-radius: 12px; max-width: 420px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h3 style="margin: 0 0 1rem 0;">แก้ไขหมวดหมู่</h3>
        <form id="editCategoryForm" method="post" action="">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">ชื่อหมวด *</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">ไอคอน</label>
                <input type="text" name="icon" id="edit_icon" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">ลำดับ</label>
                <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="0">
            </div>
            <div class="download-modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--color-gray-200); margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editCategoryModal').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-save-download">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var tabs = document.querySelectorAll('.admin-tab-btn');
    var panes = document.querySelectorAll('.tab-pane');
    var firstTab = document.querySelector('.admin-tab-btn[data-tab="support"]');
    var firstPane = document.getElementById('tab-support');
    if (firstTab && firstPane) {
        firstTab.classList.add('active');
        firstPane.style.display = 'block';
    }
    tabs.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var t = this.getAttribute('data-tab');
            tabs.forEach(function(b) { b.classList.remove('active'); });
            panes.forEach(function(p) { p.style.display = 'none'; });
            this.classList.add('active');
            var pane = document.getElementById('tab-' + t);
            if (pane) pane.style.display = 'block';
        });
    });

    document.querySelectorAll('.btn-edit-cat').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            var icon = this.getAttribute('data-icon');
            var sort = this.getAttribute('data-sort');
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_icon').value = icon || 'folder';
            document.getElementById('edit_sort_order').value = sort || 0;
            document.getElementById('editCategoryForm').action = '<?= base_url('admin/downloads/update-category/') ?>' + id;
            document.getElementById('editCategoryModal').style.display = 'flex';
        });
    });
})();
</script>

<style>
.form-control { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 6px; }
.form-label { display: block; margin-bottom: 0.25rem; font-weight: 500; font-size: 0.875rem; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; border: none; font-size: 0.875rem; }
.btn-primary { background: var(--color-primary-600, #2563eb); color: #fff; }
.btn-primary:hover { background: var(--color-primary-700, #1d4ed8); }
.btn-secondary { background: var(--color-gray-200); color: var(--color-gray-700); }
.btn-outline { background: transparent; border: 1px solid var(--color-gray-300); color: var(--color-gray-700); }
.btn-danger { background: #e03131; color: white; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
/* Tab bar: ไม่มีจุด (list-style none), Tab ชัดเจนว่า Active */
.admin-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--color-gray-200); margin: 0 0 1.5rem 0; padding: 0; list-style: none; flex-wrap: wrap; }
.admin-tabs__item { margin: 0; padding: 0; }
.admin-tab-btn { padding: 0.75rem 1.25rem; border: none; border-bottom: 3px solid transparent; margin-bottom: -2px; background: transparent; cursor: pointer; font-weight: 500; color: var(--color-gray-600); font-size: 0.9375rem; transition: color 0.15s, background 0.15s, border-color 0.15s; }
.admin-tab-btn:hover { color: var(--color-gray-800); background: var(--color-gray-50); }
.admin-tab-btn.active { color: #1d4ed8; font-weight: 600; border-bottom-color: #2563eb !important; background: rgba(37, 99, 235, 0.06); }
/* Modal overlay: ใช้ flex เพื่อจัดปุ่มและเนื้อหาให้เห็นชัด */
.download-modal-overlay[style*="flex"] { display: flex !important; }
.download-modal-actions .btn-save-download { min-width: 100px; background: #2563eb !important; color: #fff !important; cursor: pointer; }
.download-modal-actions .btn-save-download:hover { background: #1d4ed8 !important; }
</style>

<?= $this->endSection() ?>
