<?php
/**
 * Base Modal Component for Admin Views
 * 
 * Usage:
 * <?= view('admin/components/modal_base', [
 *     'modal_id' => 'createEventModal',
 *     'title' => 'สร้างกิจกรรมใหม่',
 *     'content' => view('admin/cert_events/_form', ['event' => null]),
 *     'size' => 'lg', // sm, md, lg, xl
 *     'footer' => '<button type="button" class="btn btn-primary" onclick="submitModalForm()">บันทึก</button>'
 * ]) ?>
 */
?>
<div id="<?= $modal_id ?>" class="modal" role="dialog" aria-modal="true" aria-labelledby="<?= $modal_id ?>_title" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal('<?= $modal_id ?>')"></div>
    <div class="modal-dialog modal-<?= $size ?? 'md' ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="<?= $modal_id ?>_title" class="modal-title"><?= esc($title) ?></h3>
                <button type="button" class="modal-close" onclick="closeModal('<?= $modal_id ?>')" aria-label="ปิด">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <?= $content ?? '' ?>
            </div>
            <?php if (isset($footer)): ?>
            <div class="modal-footer">
                <?= $footer ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: -1;
}

.modal-dialog {
    background: white;
    border-radius: 8px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    width: 100%;
}

.modal-sm { max-width: 400px; }
.modal-md { max-width: 600px; }
.modal-lg { max-width: 800px; }
.modal-xl { max-width: 1140px; }

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: #6b7280;
    border-radius: 4px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Animations */
.modal.show {
    animation: fadeIn 0.2s ease-out;
}

.modal.show .modal-dialog {
    animation: slideIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Loading state */
.modal-loading .modal-body {
    position: relative;
}

.modal-loading .modal-body::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Mobile responsive */
@media (max-width: 640px) {
    .modal-dialog {
        max-width: 100%;
        max-height: 95vh;
    }
    
    .modal-sm,
    .modal-md,
    .modal-lg,
    .modal-xl {
        max-width: 100%;
    }
}
</style>

<script>
// Global modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Reset form if exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Clear validation errors
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            closeModal(openModal.id);
        }
    }
});
</script>
