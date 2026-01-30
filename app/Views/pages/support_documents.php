<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title"><?= esc($page_title) ?></h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>เอกสารสายสนับสนุน</span>
        </div>
    </div>
</section>

<!-- Documents Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">ดาวน์โหลด</span>
            <h2 class="section-header__title">แบบฟอร์มและเอกสาร</h2>
            <p class="section-header__description">รวมแบบฟอร์มและเอกสารราชการสำหรับบุคลากรสายสนับสนุน</p>
        </div>
        
        <div class="documents-grid">
            <?php foreach ($document_categories as $key => $category): ?>
            <div class="card animate-on-scroll">
                <div class="card__header">
                    <div class="card__icon">
                        <?php if ($key === 'general'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <?php elseif ($key === 'finance'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                        <?php elseif ($key === 'academic'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                        <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="card__title"><?= esc($category['title']) ?></h3>
                </div>
                
                <div class="document-list">
                    <?php foreach ($category['documents'] as $doc): ?>
                    <a href="<?= esc($doc['url']) ?>" target="_blank" class="document-item">
                        <div class="document-item__icon type-<?= esc($doc['type']) ?>">
                            <?php if ($doc['type'] === 'pdf'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <path d="M9 15l3 3 3-3"/>
                                <path d="M12 18V12"/>
                            </svg>
                            <?php elseif ($doc['type'] === 'doc' || $doc['type'] === 'docx'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                            <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <?php endif; ?>
                        </div>
                        <div class="document-item__content">
                            <span class="document-item__name"><?= esc($doc['name']) ?></span>
                            <span class="document-item__type"><?= strtoupper(esc($doc['type'])) ?></span>
                        </div>
                        <div class="document-item__action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.card__header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #edf2f7);
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card__icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card__icon svg {
    width: 24px;
    height: 24px;
}

.card__title {
    margin: 0;
    font-size: 1.25rem;
    color: var(--text-primary);
}

.document-list {
    padding: 1rem;
    flex: 1;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.2s ease;
    border: 1px solid transparent;
    margin-bottom: 0.5rem;
}

.document-item:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    transform: translateX(4px);
}

.document-item__icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.document-item__icon svg {
    width: 20px;
    height: 20px;
}

.document-item__icon.type-pdf {
    background: #fee2e2;
    color: #dc2626;
}

.document-item__icon.type-doc,
.document-item__icon.type-docx {
    background: #dbeafe;
    color: #2563eb;
}

.document-item__icon.type-link {
    background: #f3f4f6;
    color: #4b5563;
}

.document-item__content {
    flex: 1;
    overflow: hidden;
}

.document-item__name {
    display: block;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-item__type {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.125rem;
}

.document-item__action {
    color: var(--text-secondary);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.document-item:hover .document-item__action {
    opacity: 1;
}

.document-item__action svg {
    width: 20px;
    height: 20px;
}

@media (max-width: 640px) {
    .documents-grid {
        grid-template-columns: 1fr;
    }
    
    .card__header {
        padding: 1.25rem;
    }
}
</style>

<?= $this->endSection() ?>
