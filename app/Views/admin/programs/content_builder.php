<?= $this->extend($layout ?? 'admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem; color: var(--color-primary-600);">
                        <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                        <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                        <path d="M2 2l7.586 7.586"></path>
                        <circle cx="11" cy="11" r="2"></circle>
                    </svg>
                    Content Builder
                </h2>
                <p style="margin: 0.25rem 0 0 0; color: var(--color-gray-600);">
                    <?= esc($program['name_th'] ?? $program['name_en']) ?>
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="<?= base_url('program-admin') ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    กลับ
                </a>
                <a href="<?= base_url('program-admin/live-preview/' . $program['id']) ?>" class="btn btn-outline btn-sm" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    ดูตัวอย่าง
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    สร้างบล็อกใหม่
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; padding: 1.5rem; background: var(--color-gray-50); border-bottom: 1px solid var(--color-gray-200);">
        <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px; border: 1px solid var(--color-gray-200);">
            <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary-600);"><?= $total_blocks ?></div>
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">บล็อกทั้งหมด</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px; border: 1px solid var(--color-gray-200);">
            <div style="font-size: 2rem; font-weight: 700; color: var(--color-green-600);"><?= $published_blocks ?></div>
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">เผยแพร่แล้ว</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px; border: 1px solid var(--color-gray-200);">
            <div style="font-size: 2rem; font-weight: 700; color: var(--color-blue-600);"><?= $active_blocks ?></div>
            <div style="font-size: 0.875rem; color: var(--color-gray-600);">เปิดใช้งาน</div>
        </div>
    </div>

    <!-- Blocks List -->
    <div style="padding: 1.5rem;">
        <?php if (empty($blocks)): ?>
            <div style="text-align: center; padding: 3rem 1rem; color: var(--color-gray-500);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <h3 style="margin: 0 0 0.5rem 0; font-weight: 500;">ยังไม่มีบล็อกเนื้อหา</h3>
                <p style="margin: 0 0 1rem 0; color: var(--color-gray-500);">เริ่มต้นสร้างบล็อกแรกของคุณ</p>
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    สร้างบล็อกใหม่
                </button>
            </div>
        <?php else: ?>
            <div id="blocks-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($blocks as $block): ?>
                    <div class="block-item" data-block-id="<?= $block['id'] ?>" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border: 1px solid var(--color-gray-200); border-radius: 8px; <?= !$block['is_active'] ? 'opacity: 0.6;' : '' ?>">
                        <!-- Drag Handle -->
                        <div class="drag-handle" style="cursor: grab; color: var(--color-gray-400); padding: 0.5rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="12" r="1"></circle>
                                <circle cx="9" cy="5" r="1"></circle>
                                <circle cx="9" cy="19" r="1"></circle>
                                <circle cx="15" cy="12" r="1"></circle>
                                <circle cx="15" cy="5" r="1"></circle>
                                <circle cx="15" cy="19" r="1"></circle>
                            </svg>
                        </div>

                        <!-- Block Info -->
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <h4 style="margin: 0; font-weight: 600; font-size: 1rem;"><?= esc($block['title']) ?></h4>
                                <span class="badge badge-<?= $block['block_type'] ?>">
                                    <?= match($block['block_type']) {
                                        'html' => 'HTML',
                                        'css' => 'CSS',
                                        'js' => 'JS',
                                        'wysiwyg' => 'WYSIWYG',
                                        'markdown' => 'MD',
                                        default => strtoupper($block['block_type'])
                                    } ?>
                                </span>
                                <?php if ($block['is_published']): ?>
                                    <span style="font-size: 0.75rem; background: var(--color-green-100); color: var(--color-green-700); padding: 0.125rem 0.5rem; border-radius: 4px;">เผยแพร่</span>
                                <?php endif; ?>
                                <?php if (!$block['is_active']): ?>
                                    <span style="font-size: 0.75rem; background: var(--color-gray-100); color: var(--color-gray-600); padding: 0.125rem 0.5rem; border-radius: 4px;">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-500); display: flex; gap: 1rem; flex-wrap: wrap;">
                                <span>Key: <code><?= esc($block['block_key']) ?></code></span>
                                <span>อัปเดต: <?= date('d/m/Y H:i', strtotime($block['updated_at'])) ?></span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="<?= base_url('program-admin/content-builder/block/' . $block['id'] . '/edit') ?>" class="btn btn-outline btn-sm" title="แก้ไข">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            
                            <form action="<?= base_url('program-admin/content-builder/block/' . $block['id'] . '/toggle') ?>" method="post" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm <?= $block['is_active'] ? 'btn-secondary' : 'btn-outline' ?>" title="<?= $block['is_active'] ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <?php if ($block['is_active']): ?>
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        <?php else: ?>
                                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        <?php endif; ?>
                                    </svg>
                                </button>
                            </form>

                            <form action="<?= base_url('program-admin/content-builder/block/' . $block['id'] . '/publish') ?>" method="post" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm <?= $block['is_published'] ? 'btn-success' : 'btn-outline' ?>" title="<?= $block['is_published'] ? 'ยกเลิกเผยแพร่' : 'เผยแพร่' ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                </button>
                            </form>

                            <form action="<?= base_url('program-admin/content-builder/block/' . $block['id'] . '/duplicate') ?>" method="post" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm" title="คัดลอก">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
                                    </svg>
                                </button>
                            </form>

                            <form action="<?= base_url('program-admin/content-builder/block/' . $block['id'] . '/delete') ?>" method="post" style="display: inline;" onsubmit="return confirm('คุณแน่ใจว่าต้องการลบบล็อกนี้?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm" title="ลบ">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Block Modal -->
<div id="create-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 500px; max-height: 90vh; overflow: auto;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
            <h3 style="margin: 0; font-size: 1.25rem;">สร้างบล็อกใหม่</h3>
        </div>
        <form action="<?= base_url('program-admin/content-builder/' . $program['id'] . '/blocks') ?>" method="post" style="padding: 1.5rem;">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">ชื่อบล็อก *</label>
                <input type="text" name="title" required class="form-control" placeholder="เช่น: แบนเนอร์หน้าแรก, เกี่ยวกับเรา, สไตล์ CSS">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">ประเภทบล็อก *</label>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="block-type-option">
                        <input type="radio" name="block_type" value="wysiwyg" checked style="margin: 0;">
                        <div>
                            <div style="font-weight: 500;">WYSIWYG</div>
                            <div style="font-size: 0.75rem; color: var(--color-gray-500);">ตัวแก้ไขแบบเห็นหน้าตา</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="block-type-option">
                        <input type="radio" name="block_type" value="html" style="margin: 0;">
                        <div>
                            <div style="font-weight: 500;">HTML</div>
                            <div style="font-size: 0.75rem; color: var(--color-gray-500);">โค้ด HTML</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="block-type-option">
                        <input type="radio" name="block_type" value="css" style="margin: 0;">
                        <div>
                            <div style="font-weight: 500;">CSS</div>
                            <div style="font-size: 0.75rem; color: var(--color-gray-500);">สไตล์ CSS</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="block-type-option">
                        <input type="radio" name="block_type" value="js" style="margin: 0;">
                        <div>
                            <div style="font-weight: 500;">JavaScript</div>
                            <div style="font-size: 0.75rem; color: var(--color-gray-500);">สคริปต์ JS</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="block-type-option">
                        <input type="radio" name="block_type" value="markdown" style="margin: 0;">
                        <div>
                            <div style="font-weight: 500;">Markdown</div>
                            <div style="font-size: 0.75rem; color: var(--color-gray-500);">โค้ด Markdown</div>
                        </div>
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">สร้างบล็อก</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge {
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}
.badge-html { background: var(--color-orange-100); color: var(--color-orange-700); }
.badge-css { background: var(--color-blue-100); color: var(--color-blue-700); }
.badge-js { background: var(--color-yellow-100); color: var(--color-yellow-700); }
.badge-wysiwyg { background: var(--color-green-100); color: var(--color-green-700); }
.badge-markdown { background: var(--color-purple-100); color: var(--color-purple-700); }

.block-type-option:hover {
    border-color: var(--color-primary-400) !important;
    background: var(--color-primary-50);
}

.block-type-option:has(input:checked) {
    border-color: var(--color-primary-500) !important;
    background: var(--color-primary-100);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-500);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.drag-handle:active {
    cursor: grabbing;
}

.block-item.dragging {
    opacity: 0.5;
    background: var(--color-primary-50);
    border-color: var(--color-primary-400);
}
</style>

<script>
function openCreateModal() {
    document.getElementById('create-modal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('create-modal').style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('create-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateModal();
    }
});

// Drag and drop functionality
let draggedItem = null;

document.querySelectorAll('.drag-handle').forEach(handle => {
    handle.addEventListener('mousedown', function(e) {
        draggedItem = this.closest('.block-item');
        draggedItem.classList.add('dragging');
    });
});

document.addEventListener('mouseup', function() {
    if (draggedItem) {
        draggedItem.classList.remove('dragging');
        draggedItem = null;
        saveOrder();
    }
});

document.addEventListener('mousemove', function(e) {
    if (!draggedItem) return;
    
    const container = document.getElementById('blocks-list');
    const items = [...container.querySelectorAll('.block-item:not(.dragging)')];
    
    const afterElement = items.find(item => {
        const rect = item.getBoundingClientRect();
        const midY = rect.top + rect.height / 2;
        return e.clientY < midY;
    });
    
    if (afterElement) {
        container.insertBefore(draggedItem, afterElement);
    } else {
        container.appendChild(draggedItem);
    }
});

function saveOrder() {
    const container = document.getElementById('blocks-list');
    const blockIds = [...container.querySelectorAll('.block-item')].map(item => item.dataset.blockId);
    
    fetch('<?= base_url('program-admin/content-builder/' . $program['id'] . '/reorder') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>&block_ids=' + JSON.stringify(blockIds)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to save order:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?= $this->endSection() ?>
