<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('program-admin/edit/' . $program['id']) ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                        กลับ
                </a>
                <a href="<?= base_url('program-admin') ?>" class="btn btn-outline btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11H3z" />
                        <polyline points="3 9 12 9 12 22" />
                    </svg>
                    แดชบอร์ด
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem;">จัดการเอกสารดาวน์โหลด</h3>
        <?= view('admin/programs/partials/downloads_panel', [
            'program'              => $program,
            'downloads'            => $downloads ?? [],
            'programDownloadModel' => $programDownloadModel,
            'downloadsContext'     => 'standalone',
        ]) ?>
    </div>
</div>

<style>
.form-row {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .form-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color-gray-700);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-500);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-hint {
    font-size: 0.75rem;
    color: var(--color-gray-500);
    margin-top: 0.25rem;
}

.download-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--color-gray-500);
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: var(--color-primary-600);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-700);
}

.btn-secondary {
    background: var(--color-gray-200);
    color: var(--color-gray-700);
}

.btn-secondary:hover {
    background: var(--color-gray-300);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--color-gray-300);
    color: var(--color-gray-700);
}

.btn-outline:hover {
    background: var(--color-gray-50);
}

.btn-danger {
    background: var(--color-red-600);
    color: white;
}

.btn-danger:hover {
    background: var(--color-red-700);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>

<?= $this->endSection() ?>
