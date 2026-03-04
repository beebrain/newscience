<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title"><?= esc($page_title) ?></h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>เกณฑ์การประเมินบุคคล</span>
        </div>
    </div>
</section>

<!-- Content Section (ข้อมูลจากฐานข้อมูล: download_categories + download_documents, page_type = promotion) -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-header__title">สายสนับสนุน</h2>
            <p class="section-header__description">หลักเกณฑ์และวิธีการประเมินค่างานเพื่อกำหนดระดับตำแหน่งที่สูงขึ้น</p>
        </div>

        <?php
        $document_categories = $document_categories ?? [];
        $has_any = false;
        foreach ($document_categories as $category) {
            if (!empty($category['documents'])) {
                $has_any = true;
                break;
            }
        }
        ?>

        <?php if (!$has_any): ?>
        <div class="content-box animate-on-scroll">
            <p class="document-empty-message">ยังไม่มีเอกสารในหมวดเกณฑ์การประเมินบุคคล คุณสามารถเพิ่มได้ที่ <a href="<?= base_url('admin/downloads') ?>">จัดการดาวน์โหลดคณะ</a> (แท็บเกณฑ์การประเมิน)</p>
        </div>
        <?php else: ?>
        <?php foreach ($document_categories as $category): ?>
            <?php if (empty($category['documents'])) { continue; } ?>
        <div class="content-box animate-on-scroll promotion-category-block">
            <h3 class="promotion-category-title"><?= esc($category['title']) ?></h3>
            <ul class="document-list-vertical">
                <?php foreach ($category['documents'] as $doc): ?>
                    <?php if (empty($doc['url'])) { continue; } ?>
                <li>
                    <a href="<?= esc($doc['url']) ?>" target="_blank" rel="noopener" class="document-list-vertical__link">
                        <div class="document-list-vertical__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <div class="document-list-vertical__info">
                            <span class="document-list-vertical__title"><?= esc($doc['name']) ?></span>
                            <span class="document-list-vertical__meta"><?= esc($category['title']) ?></span>
                        </div>
                        <div class="document-list-vertical__action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.content-box {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    max-width: 900px;
    margin: 0 auto 1.5rem auto;
}

.content-box:last-of-type {
    margin-bottom: 0;
}

.promotion-category-block:first-child .promotion-category-title {
    margin-top: 0;
}

.promotion-category-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.document-empty-message {
    margin: 0;
    color: #64748b;
}

.document-empty-message a {
    color: var(--color-primary);
}

.document-list-vertical {
    list-style: none;
    padding: 0;
    margin: 0;
}

.document-list-vertical li {
    margin-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 1rem;
}

.document-list-vertical li:last-child {
    margin-bottom: 0;
    border-bottom: none;
    padding-bottom: 0;
}

.document-list-vertical__link {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    text-decoration: none;
    color: var(--text-primary);
    padding: 1rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.document-list-vertical__link:hover {
    background: #f8fafc;
}

.document-list-vertical__icon {
    width: 48px;
    height: 48px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary);
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.document-list-vertical__link:hover .document-list-vertical__icon {
    background: var(--color-primary);
    color: white;
}

.document-list-vertical__icon svg {
    width: 24px;
    height: 24px;
}

.document-list-vertical__info {
    flex: 1;
    min-width: 0;
}

.document-list-vertical__title {
    display: block;
    font-weight: 500;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.document-list-vertical__meta {
    display: block;
    font-size: 0.85rem;
    color: #64748b;
}

.document-list-vertical__action {
    color: #cbd5e1;
    transition: all 0.2s ease;
}

.document-list-vertical__link:hover .document-list-vertical__action {
    color: var(--color-primary);
    transform: translateX(4px);
}

.document-list-vertical__action svg {
    width: 20px;
    height: 20px;
}

@media (max-width: 640px) {
    .content-box {
        padding: 1rem;
    }

    .document-list-vertical__link {
        padding: 0.75rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .document-list-vertical__action {
        display: none;
    }

    .document-list-vertical__icon {
        width: 40px;
        height: 40px;
    }

    .document-list-vertical__icon svg {
        width: 20px;
        height: 20px;
    }
}
</style>

<?= $this->endSection() ?>
