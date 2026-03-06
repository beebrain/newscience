<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header — Documents page modern style -->
<section class="page-header page-header--documents">
    <div class="container">
        <h1 class="page-header__title"><?= esc($page_title) ?></h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span class="page-header__sep">/</span>
            <span>บริการด้านเอกสาร</span>
        </div>
    </div>
</section>

<?php
$document_sections = $document_sections ?? [];
$section_labels = [
    'support'  => 'แบบฟอร์มดาวน์โหลด',
    'official' => 'คำสั่ง/ประกาศ/ระเบียบ',
    'promotion'=> 'เกณฑ์การประเมินบุคคล',
    'internal' => 'เอกสารภายในมหาวิทยาลัย',
];
$doc_tabs = [
    'support'  => ['label' => 'แบบฟอร์ม', 'icon' => 'folder'],
    'official' => ['label' => 'คำสั่ง/ประกาศ', 'icon' => 'document'],
    'promotion'=> ['label' => 'เกณฑ์ประเมิน', 'icon' => 'academic'],
    'internal' => ['label' => 'เอกสารภายใน', 'icon' => 'lock'],
];
?>

<!-- Tab Navigation (หมวดเอกสาร — เข้าถึงง่าย) -->
<nav class="doc-tabs-wrap" aria-label="เลือกหมวดเอกสาร">
    <div class="container">
        <ul class="doc-tabs" role="tablist">
            <?php $first_tab = true; foreach ($doc_tabs as $tab_key => $tab): ?>
            <li class="doc-tabs__item" role="presentation">
                <button type="button" id="tab-<?= esc($tab_key) ?>" class="doc-tab <?= $first_tab ? 'doc-tab--active' : '' ?>" data-section="section-<?= esc($tab_key) ?>" role="tab" aria-selected="<?= $first_tab ? 'true' : 'false' ?>" aria-controls="section-<?= esc($tab_key) ?>">
                    <span class="doc-tab__icon" aria-hidden="true">
                        <?php if (($tab['icon'] ?? '') === 'document'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        <?php elseif (($tab['icon'] ?? '') === 'academic'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <?php elseif (($tab['icon'] ?? '') === 'lock'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                        <?php endif; ?>
                    </span>
                    <span class="doc-tab__label"><?= esc($tab['label']) ?></span>
                </button>
            </li>
            <?php $first_tab = false; endforeach; ?>
        </ul>
    </div>
</nav>

<!-- Documents Sections — แท็บซ่อน: แสดงเฉพาะหมวดที่เลือก -->

<?php $first_section = true; foreach ($document_sections as $section_key => $section): ?>
<?php
    $categories = $section['categories'] ?? [];
    $has_any = false;
    foreach ($categories as $cat) {
        if (!empty($cat['documents'])) {
            $has_any = true;
            break;
        }
    }
    $is_first = $first_section;
    $first_section = false;
?>
<section class="section documents-page-section doc-tab-panel <?= $is_first ? 'is-active' : '' ?>" id="section-<?= esc($section_key) ?>" role="tabpanel" aria-labelledby="tab-<?= esc($section_key) ?>" <?= $is_first ? '' : 'hidden' ?>>
    <div class="container">
        <div class="doc-section-header">
            <h2 class="doc-section-header__title"><?= esc($section['title'] ?? $section_labels[$section_key] ?? $section_key) ?></h2>
            <p class="doc-section-header__description"><?= esc($section['description'] ?? '') ?></p>
        </div>

        <?php if (!$has_any): ?>
        <div class="documents-empty" role="status">ยังไม่มีเอกสารในหมวดนี้</div>
        <?php else: ?>
        <div class="documents-grid">
            <?php foreach ($categories as $category): ?>
                <?php if (empty($category['documents'])) { continue; } ?>
            <div class="card animate-on-scroll">
                <div class="card__header">
                    <div class="card__icon">
                        <?php $icon = $category['icon'] ?? 'folder'; ?>
                        <?php if ($icon === 'banknotes'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                        <?php elseif ($icon === 'academic-cap'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                        <?php elseif ($icon === 'beaker'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <?php elseif ($icon === 'cube'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                        <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="card__title"><?= esc($category['title']) ?></h3>
                </div>
                <div class="document-list">
                    <?php foreach ($category['documents'] as $doc): ?>
                    <?php if (empty($doc['url'])) { continue; } ?>
                    <a href="<?= esc($doc['url']) ?>" target="_blank" rel="noopener" class="document-item">
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
                            <span class="document-item__type"><?= strtoupper(esc((string)($doc['type'] ?? ''))) ?></span>
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
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>

<style>
/* --- Documents page: modern header --- */
.page-header--documents {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #0c1222 100%);
    position: relative;
    overflow: hidden;
}
.page-header--documents::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(251, 191, 36, 0.12), transparent),
                radial-gradient(ellipse 60% 40% at 100% 0%, rgba(148, 163, 184, 0.08), transparent);
    pointer-events: none;
}
.page-header--documents .page-header__title {
    color: #fff;
    font-weight: 700;
    letter-spacing: -0.02em;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
.page-header--documents .page-header__breadcrumb {
    color: rgba(255,255,255,0.75);
}
.page-header--documents .page-header__breadcrumb a {
    color: rgba(255,255,255,0.9);
}
.page-header--documents .page-header__breadcrumb a:hover {
    color: #fbbf24;
}
.page-header--documents .page-header__sep {
    opacity: 0.6;
    margin: 0 0.25rem;
}

/* --- Tab bar — sticky, modern pill style --- */
.doc-tabs-wrap {
    position: sticky;
    top: 0;
    z-index: 30;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
}

.doc-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    list-style: none;
    margin: 0;
    padding: 1rem 0;
    justify-content: center;
}

.doc-tabs__item { margin: 0; }

.doc-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.65rem 1.25rem;
    border-radius: 999px;
    border: 1px solid transparent;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9375rem;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.25s ease, color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.doc-tab:focus-visible {
    outline: 2px solid var(--color-primary, #f59e0b);
    outline-offset: 2px;
}

.doc-tab:hover {
    background: #f1f5f9;
    color: #334155;
}

.doc-tab--active {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.1));
    color: #b45309;
    border-color: rgba(251, 191, 36, 0.4);
    box-shadow: 0 2px 12px rgba(251, 191, 36, 0.15);
}

.doc-tab__icon {
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.doc-tab__icon svg {
    width: 20px;
    height: 20px;
}

.doc-tab--active .doc-tab__icon {
    color: var(--color-primary, #f59e0b);
}

.doc-tab__label { white-space: nowrap; }

@media (max-width: 768px) {
    .doc-tabs {
        overflow-x: auto;
        justify-content: flex-start;
        padding-bottom: 0.5rem;
        -webkit-overflow-scrolling: touch;
    }
    .doc-tab__label { font-size: 0.875rem; }
}

/* --- Tab panels --- */
.doc-tab-panel {
    display: none;
    padding-top: 2rem;
    padding-bottom: 3rem;
}

.doc-tab-panel.is-active {
    display: block;
    animation: docPanelFade 0.3s ease;
}

@keyframes docPanelFade {
    from { opacity: 0; }
    to { opacity: 1; }
}

.documents-page-section { padding-top: 0; padding-bottom: 0; }
.documents-page-section:first-of-type { padding-top: 0; }

/* --- Section header --- */
.doc-section-header {
    margin-bottom: 2rem;
    text-align: center;
}

.doc-section-header__title {
    margin: 0 0 0.5rem;
    font-size: 1.75rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.doc-section-header__description {
    margin: 0;
    font-size: 1rem;
    color: #64748b;
    line-height: 1.5;
    max-width: 42rem;
    margin-left: auto;
    margin-right: auto;
}

.documents-empty {
    color: #64748b;
    padding: 2rem 1.5rem;
    text-align: center;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    border: 1px dashed #cbd5e1;
    font-size: 0.9375rem;
}

/* --- Cards grid --- */
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: 1.75rem;
}

.card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: transform 0.3s ease, box-shadow 0.35s ease, border-color 0.3s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1), 0 8px 16px rgba(251, 191, 36, 0.08);
    border-color: rgba(251, 191, 36, 0.25);
}

.card__header {
    padding: 1.5rem 1.5rem 1.25rem;
    background: linear-gradient(160deg, #fefce8 0%, #fef9c3 30%, #fef3c7 100%);
    border-bottom: 1px solid rgba(251, 191, 36, 0.2);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card__icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary, #d97706);
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);
    flex-shrink: 0;
}

.card__icon svg {
    width: 26px;
    height: 26px;
}

.card__title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.01em;
    line-height: 1.3;
}

.document-list {
    padding: 1rem 1.25rem 1.25rem;
    flex: 1;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    text-decoration: none;
    color: #1e293b;
    transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid transparent;
    margin-bottom: 0.5rem;
}

.document-item:hover {
    background: #f8fafc;
    transform: translateX(6px);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}

.document-item__icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
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
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #dc2626;
}

.document-item__icon.type-doc,
.document-item__icon.type-docx {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #2563eb;
}

.document-item__icon.type-link {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    color: #475569;
}

.document-item__content {
    flex: 1;
    min-width: 0;
}

.document-item__name {
    display: block;
    font-weight: 600;
    font-size: 0.9375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-item__type {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.2rem;
}

.document-item__action {
    color: var(--color-primary, #f59e0b);
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.document-item:hover .document-item__action {
    opacity: 1;
    transform: translateY(-2px);
}

.document-item__action svg {
    width: 20px;
    height: 20px;
}

@media (max-width: 640px) {
    .documents-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .card__header { padding: 1.25rem; }
    .doc-section-header__title { font-size: 1.5rem; }
}
</style>

<script>
(function() {
    var tabs = document.querySelectorAll('.doc-tab');
    var panels = document.querySelectorAll('.doc-tab-panel');
    if (!tabs.length || !panels.length) return;

    function switchToPanel(sectionId) {
        tabs.forEach(function(t) {
            var isActive = t.getAttribute('data-section') === sectionId;
            t.classList.toggle('doc-tab--active', isActive);
            t.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        panels.forEach(function(p) {
            var isActive = p.id === sectionId;
            p.classList.toggle('is-active', isActive);
            p.hidden = !isActive;
        });
        history.replaceState(null, '', '#' + sectionId);
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var id = tab.getAttribute('data-section');
            if (document.getElementById(id)) {
                switchToPanel(id);
            }
        });
    });

    if (location.hash) {
        var target = document.getElementById(location.hash.slice(1));
        if (target && target.classList.contains('doc-tab-panel')) {
            switchToPanel(target.id);
        }
    }
})();
</script>

<?= $this->endSection() ?>
