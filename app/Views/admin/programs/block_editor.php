<?= $this->extend($layout ?? 'admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; flex-direction: column; height: calc(100vh - 100px);">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-gray-200); background: white;">
        <div>
            <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">
                <?= esc($block['title']) ?>
            </h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem;">
                <span class="badge badge-<?= $block['block_type'] ?>">
                    <?= match($block['block_type']) {
                        'html' => 'HTML',
                        'css' => 'CSS',
                        'js' => 'JS',
                        'wysiwyg' => 'WYSIWYG',
                        'markdown' => 'Markdown',
                        default => strtoupper($block['block_type'])
                    } ?>
                </span>
                <?php if ($block['is_published']): ?>
                    <span style="font-size: 0.75rem; background: var(--color-green-100); color: var(--color-green-700); padding: 0.125rem 0.5rem; border-radius: 4px;">เผยแพร่</span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= base_url('program-admin/content-builder/' . $program['id']) ?>" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                กลับ
            </a>
            <button type="button" class="btn btn-outline btn-sm" onclick="togglePreview()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span id="preview-btn-text">แสดงตัวอย่าง</span>
            </button>
            <button type="submit" form="block-form" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                บันทึก
            </button>
        </div>
    </div>

    <!-- Editor Container -->
    <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr; overflow: hidden;">
        <!-- Editor -->
        <div style="display: flex; flex-direction: column; border-right: 1px solid var(--color-gray-200);">
            <!-- Tabs -->
            <div style="display: flex; border-bottom: 1px solid var(--color-gray-200); background: var(--color-gray-50);">
                <button type="button" class="editor-tab active" data-tab="content" onclick="switchEditorTab('content')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.25rem;">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    เนื้อหา
                </button>
                <?php if (in_array($block['block_type'], ['html', 'wysiwyg', 'markdown'])): ?>
                <button type="button" class="editor-tab" data-tab="css" onclick="switchEditorTab('css')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.25rem;">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    CSS เฉพาะบล็อก
                </button>
                <button type="button" class="editor-tab" data-tab="js" onclick="switchEditorTab('js')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.25rem;">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg>
                    JS เฉพาะบล็อก
                </button>
                <?php endif; ?>
            </div>

            <!-- Form -->
            <form id="block-form" action="<?= base_url('program-admin/content-builder/block/' . $block['id'] . '/update') ?>" method="post" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
                <?= csrf_field() ?>
                
                <!-- Title Field -->
                <div style="padding: 1rem; border-bottom: 1px solid var(--color-gray-200);">
                    <label style="display: block; margin-bottom: 0.25rem; font-weight: 500; font-size: 0.875rem;">ชื่อบล็อก</label>
                    <input type="text" name="title" value="<?= esc($block['title']) ?>" required class="form-control" style="font-size: 0.875rem;">
                </div>

                <!-- Content Tab -->
                <div id="tab-content" class="tab-panel active" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
                    <textarea id="editor-content" name="content" style="flex: 1; width: 100%; padding: 1rem; border: none; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 14px; line-height: 1.6; resize: none;" placeholder="<?= $block['block_type'] === 'css' ? '/* Enter your CSS here */' : ($block['block_type'] === 'js' ? '// Enter your JavaScript here' : 'Enter your content here...') ?>"><?= esc($block['content'] ?? '') ?></textarea>
                </div>

                <!-- CSS Tab -->
                <?php if (in_array($block['block_type'], ['html', 'wysiwyg', 'markdown'])): ?>
                <div id="tab-css" class="tab-panel" style="flex: 1; display: none; flex-direction: column; overflow: hidden;">
                    <textarea id="editor-css" name="custom_css" style="flex: 1; width: 100%; padding: 1rem; border: none; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 14px; line-height: 1.6; resize: none;" placeholder="/* Enter custom CSS for this block */"><?= esc($block['custom_css'] ?? '') ?></textarea>
                </div>

                <!-- JS Tab -->
                <div id="tab-js" class="tab-panel" style="flex: 1; display: none; flex-direction: column; overflow: hidden;">
                    <textarea id="editor-js" name="custom_js" style="flex: 1; width: 100%; padding: 1rem; border: none; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 14px; line-height: 1.6; resize: none;" placeholder="// Enter custom JavaScript for this block"><?= esc($block['custom_js'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Preview -->
        <div id="preview-panel" style="display: none; flex-direction: column; background: white;">
            <div style="padding: 0.5rem 1rem; background: var(--color-gray-50); border-bottom: 1px solid var(--color-gray-200); font-size: 0.875rem; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                <span>ตัวอย่าง</span>
                <div style="display: flex; gap: 0.5rem;">
                    <select id="preview-width" onchange="updatePreviewWidth()" style="font-size: 0.75rem; padding: 0.25rem;">
                        <option value="100%">Full</option>
                        <option value="768px">Tablet (768px)</option>
                        <option value="375px">Mobile (375px)</option>
                    </select>
                </div>
            </div>
            <iframe id="preview-frame" style="flex: 1; width: 100%; border: none; background: white;"></iframe>
        </div>
    </div>
</div>

<style>
.editor-tab {
    padding: 0.75rem 1rem;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-gray-600);
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.editor-tab:hover {
    color: var(--color-gray-900);
    background: var(--color-gray-100);
}

.editor-tab.active {
    color: var(--color-primary-600);
    border-bottom-color: var(--color-primary-600);
    background: white;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-500);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

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

#editor-content:focus,
#editor-css:focus,
#editor-js:focus {
    outline: none;
}

.btn-success {
    background: var(--color-green-600);
    color: white;
}

.btn-success:hover {
    background: var(--color-green-700);
}
</style>

<script>
const blockType = '<?= $block['block_type'] ?>';
const programId = <?= $program['id'] ?>;
let previewVisible = false;

function switchEditorTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.editor-tab').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    
    // Update tab panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.style.display = panel.id === 'tab-' + tab ? 'flex' : 'none';
    });
}

function togglePreview() {
    previewVisible = !previewVisible;
    const previewPanel = document.getElementById('preview-panel');
    const btnText = document.getElementById('preview-btn-text');
    
    if (previewVisible) {
        previewPanel.style.display = 'flex';
        btnText.textContent = 'ซ่อนตัวอย่าง';
        updatePreview();
    } else {
        previewPanel.style.display = 'none';
        btnText.textContent = 'แสดงตัวอย่าง';
    }
}

function updatePreview() {
    if (!previewVisible) return;
    
    const content = document.getElementById('editor-content').value;
    const css = document.getElementById('editor-css')?.value || '';
    const js = document.getElementById('editor-js')?.value || '';
    
    const iframe = document.getElementById('preview-frame');
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    
    let processedContent = content;
    
    // Process markdown if needed
    if (blockType === 'markdown' && typeof marked !== 'undefined') {
        processedContent = marked.parse(content);
    }
    
    const html = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            padding: 1rem;
            margin: 0;
        }
        ${blockType === 'css' ? content : css}
    </style>
</head>
<body>
    ${blockType === 'css' ? '<div style="padding:2rem;color:#666">CSS block - styles applied to page</div>' : processedContent}
    ${blockType === 'js' ? content : ''}
    <script>${js}<\/script>
</body>
</html>`;
    
    doc.open();
    doc.write(html);
    doc.close();
}

function updatePreviewWidth() {
    const width = document.getElementById('preview-width').value;
    const iframe = document.getElementById('preview-frame');
    iframe.style.maxWidth = width;
    iframe.style.margin = '0 auto';
}

// Auto-save functionality
let autoSaveTimer;
const editors = ['editor-content', 'editor-css', 'editor-js'];

editors.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(updatePreview, 500);
        });
    }
});

// Initial preview update
if (previewVisible) {
    updatePreview();
}

// Handle Ctrl+S
 document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('block-form').submit();
    }
});
</script>

<?= $this->endSection() ?>
