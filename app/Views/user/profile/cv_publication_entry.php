<?php
helper(['form', 'security']);
$section = $section ?? [];
$entry = $entry ?? null;
$isEdit = ! empty($is_edit);
$sectionId = (int) ($section_id ?? 0);
$entryId = (int) ($entry_id ?? 0);
$sectionTitle = (string) ($section['title'] ?? '');
$aiReady = ! empty($ai_cv_publication_enabled);
$openAi = ! empty($open_ai_panel);
$backUrl = base_url('dashboard/profile/cv?tab=sections');
$saveUrl = base_url('dashboard/profile/cv/entry/save');

$val = static function (string $key, string $default = '') use ($entry): string {
    if (! is_array($entry)) {
        return $default;
    }

    return (string) ($entry[$key] ?? $default);
};
$visChecked = ! is_array($entry) || (string) ($entry['visible_on_public'] ?? '1') !== '0';
$authorsJson = '[]';
if (is_array($entry) && ! empty($entry['publication_authors']) && is_array($entry['publication_authors'])) {
    $authorsJson = json_encode($entry['publication_authors'], JSON_UNESCAPED_UNICODE) ?: '[]';
}
?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('styles') ?>
<style>
.cv-pub-page { font-family: 'Sarabun', 'Noto Sans Thai', sans-serif; }
.cv-pub-page input:focus-visible,
.cv-pub-page select:focus-visible,
.cv-pub-page textarea:focus-visible,
.cv-pub-page button:focus-visible {
    outline: 2px solid #7c3aed;
    outline-offset: 2px;
}
.cv-pub-field {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.6rem 0.85rem;
    font-size: 0.9375rem;
    line-height: 1.5;
    color: #1e293b;
    background: #fff;
}
.cv-pub-field:focus { border-color: #7c3aed; }
.cv-pub-field--invalid {
    border-color: #dc2626;
    background: #fef2f2;
}
.cv-pub-field-error {
    display: block;
    margin-top: 0.35rem;
    font-size: 0.8125rem;
    color: #b91c1c;
}
#cv-pub-form-errors:not(:empty) {
    display: block;
}
.cv-pub-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.35rem;
}
.cv-pub-section {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    padding: 1.25rem 1.5rem;
}
.cv-pub-section legend {
    font-size: 0.875rem;
    font-weight: 700;
    color: #0f172a;
    padding: 0 0.35rem;
}
@media (prefers-reduced-motion: reduce) {
    .cv-pub-page * { transition: none !important; }
}
.cv-author-search-dropdown {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
    max-height: 14rem;
    overflow-y: auto;
}
.cv-author-search-item {
    display: block;
    width: 100%;
    padding: 0.55rem 0.75rem;
    text-align: left;
    border: 0;
    background: transparent;
    cursor: pointer;
}
.cv-author-search-item:hover { background: #f8fafc; }
.cv-author-search-name {
    display: block;
    color: #0f172a;
    font-weight: 600;
    font-size: 0.875rem;
}
.cv-author-search-email {
    display: block;
    color: #64748b;
    font-size: 0.75rem;
}
.cv-author-search-empty {
    padding: 0.55rem 0.75rem;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="cv-pub-page max-w-6xl mx-auto px-3 sm:px-6 py-5 sm:py-8">
    <nav class="text-sm text-slate-600 mb-4" aria-label="breadcrumb">
        <a href="<?= esc($backUrl, 'attr') ?>" class="text-secondary hover:underline">จัดการ CV</a>
        <span class="mx-2 text-slate-400" aria-hidden="true">/</span>
        <span class="text-slate-800 font-medium"><?= $isEdit ? 'แก้ไขผลงาน' : 'เพิ่มผลงาน' ?></span>
    </nav>

    <?php if (session()->getFlashdata('success')): ?>
        <p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status"><?= esc(session()->getFlashdata('success')) ?></p>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <p class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert"><?= esc(session()->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 text-pretty"><?= $isEdit ? 'แก้ไขผลงานตีพิมพ์' : 'เพิ่มผลงานตีพิมพ์' ?></h1>
        <p class="mt-2 text-slate-600">หัวข้อ: <strong class="text-slate-800"><?= esc($sectionTitle) ?></strong></p>
    </header>

    <form id="cv-pub-form"
          method="post"
          action="<?= esc($saveUrl, 'attr') ?>"
          class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start"
          novalidate
          data-section-id="<?= $sectionId ?>"
          data-open-ai="<?= $openAi ? '1' : '0' ?>">
        <?= csrf_field() ?>
        <div id="cv-pub-form-errors"
             class="lg:col-span-3 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
             role="alert"
             aria-live="assertive"></div>
        <input type="hidden" name="cv_publication_page" value="1">
        <input type="hidden" name="section_id" id="cv-p-section-id" value="<?= $sectionId ?>">
        <input type="hidden" name="entry_id" id="cv-p-entry-id" value="<?= $isEdit ? $entryId : '' ?>">
        <input type="hidden" name="entry_sort_order" id="cv-p-entry-sort" value="<?= esc($val('sort_order', '0')) ?>">
        <input type="hidden" name="extra_info" value="">
        <input type="hidden" name="funding_amount" value="">
        <input type="hidden" name="is_current" id="cv-p-current" value="0">
        <input type="hidden" name="entry_metadata_source" id="cv-p-meta-src" value="">

        <div class="lg:col-span-2 space-y-6 min-w-0">
            <fieldset class="cv-pub-section">
                <legend>ข้อมูลหลัก</legend>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="cv-p-title" class="cv-pub-label">ชื่อผลงาน <span class="text-red-600">*</span></label>
                        <input type="text" name="entry_title" id="cv-p-title" required maxlength="500" class="cv-pub-field" placeholder="ชื่อบทความ / หนังสือ / ผลงาน…" value="<?= esc($val('title')) ?>">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cv-p-org" class="cv-pub-label">แหล่งเผยแพร่ <span class="text-red-600">*</span></label>
                            <input type="text" name="organization" id="cv-p-org" required maxlength="500" class="cv-pub-field" placeholder="วารสาร / สำนักพิมพ์…" value="<?= esc($val('organization')) ?>">
                        </div>
                        <div>
                            <label for="cv-p-pubtype" class="cv-pub-label">ประเภทผลงาน <span class="text-red-600">*</span></label>
                            <select name="publication_type" id="cv-p-pubtype" required class="cv-pub-field">
                                <?php $ptypeCur = $val('publication_type'); ?>
                                <?php foreach (\App\Libraries\RrPublicationType::selectOptionGroups() as $groupLabel => $groupOpts): ?>
                                    <optgroup label="<?= esc($groupLabel) ?>">
                                        <?php foreach ($groupOpts as $pubOpt): ?>
                                            <?php
                                            $selected = $ptypeCur === $pubOpt['value']
                                                || ($ptypeCur === '' && $pubOpt['value'] === 'journal' && ! $isEdit);
                                            ?>
                                            <option value="<?= esc($pubOpt['value']) ?>" <?= $selected ? 'selected' : '' ?>><?= esc($pubOpt['label']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cv-p-year-be" class="cv-pub-label">ปีที่เผยแพร่ (พ.ศ.) <span class="text-red-600">*</span></label>
                            <input type="number" name="publication_year_be" id="cv-p-year-be" required class="cv-pub-field" min="2400" max="2700" inputmode="numeric" placeholder="เช่น 2567" value="<?= esc($val('publication_year_be') !== '' ? $val('publication_year_be') : '2567') ?>">
                        </div>
                        <div>
                            <label for="cv-p-month" class="cv-pub-label">เดือน</label>
                            <select name="publication_month" id="cv-p-month" class="cv-pub-field">
                                <option value="">— ไม่ระบุ —</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $val('publication_month') === (string) $m ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cv-p-doi" class="cv-pub-label">DOI</label>
                            <input type="text" name="entry_doi" id="cv-p-doi" class="cv-pub-field" maxlength="255" autocomplete="off" spellcheck="false" placeholder="10.1234/example…" value="<?= esc($val('entry_doi')) ?>">
                        </div>
                        <div>
                            <label for="cv-p-url" class="cv-pub-label">ลิงก์ (URL)</label>
                            <input type="url" name="entry_url" id="cv-p-url" class="cv-pub-field" maxlength="2048" placeholder="https://doi.org/…" value="<?= esc($val('entry_url')) ?>">
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="cv-pub-section">
                <legend>รายละเอียดวารสาร</legend>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="cv-p-volume" class="cv-pub-label">เล่ม (Volume)</label>
                        <input type="text" name="volume" id="cv-p-volume" class="cv-pub-field" maxlength="100" value="<?= esc($val('volume')) ?>">
                    </div>
                    <div>
                        <label for="cv-p-pages" class="cv-pub-label">หน้า (Pages)</label>
                        <input type="text" name="pages" id="cv-p-pages" class="cv-pub-field" maxlength="100" value="<?= esc($val('pages')) ?>">
                    </div>
                    <div>
                        <label for="cv-p-isbn" class="cv-pub-label">ISBN</label>
                        <input type="text" name="isbn" id="cv-p-isbn" class="cv-pub-field" maxlength="100" value="<?= esc($val('isbn')) ?>">
                    </div>
                    <div>
                        <label for="cv-p-loc" class="cv-pub-label">สถานที่</label>
                        <input type="text" name="location" id="cv-p-loc" class="cv-pub-field" maxlength="500" value="<?= esc($val('location')) ?>">
                    </div>
                </div>
                <div class="mt-4">
                    <label for="cv-p-abstract" class="cv-pub-label">บทคัดย่อ</label>
                    <textarea name="abstract" id="cv-p-abstract" rows="4" class="cv-pub-field" placeholder="บทคัดย่อผลงาน…"><?= esc($val('abstract')) ?></textarea>
                </div>
                <div class="mt-4">
                    <label for="cv-p-keywords" class="cv-pub-label">คำสำคัญ</label>
                    <input type="text" name="keywords" id="cv-p-keywords" class="cv-pub-field" maxlength="500" placeholder="คั่นด้วยจุลภาค…" value="<?= esc($val('keywords')) ?>">
                </div>
            </fieldset>

            <fieldset class="cv-pub-section">
                <legend>ผู้แต่ง</legend>
                <div class="mt-4">
                    <input type="hidden" name="publication_authors" id="cv-p-authors-json" value="">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <p class="text-sm text-slate-600">ผู้แต่ง / ผู้ร่วมวิจัย — * = ผู้ติดต่อหลัก</p>
                        <button type="button" id="cv-p-add-author" class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600">+ เพิ่มผู้แต่ง</button>
                    </div>
                    <div id="cv-p-authors-list" class="space-y-2 min-w-0"></div>
                </div>
            </fieldset>

            <fieldset class="cv-pub-section">
                <legend>เพิ่มเติม</legend>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="cv-p-rrid" class="cv-pub-label">รหัสผลงานใน กบศ</label>
                        <input type="text" name="publication_rr_id" id="cv-p-rrid" class="cv-pub-field" maxlength="12" inputmode="numeric" pattern="[0-9]*" autocomplete="off" spellcheck="false" value="<?= esc($val('publication_rr_id')) ?>">
                    </div>
                    <div>
                        <label for="cv-p-desc" class="cv-pub-label">รายละเอียดบน CV</label>
                        <textarea name="entry_description" id="cv-p-desc" rows="3" class="cv-pub-field" placeholder="ข้อความเสริมบนหน้า CV (ถ้ามี)…"><?= esc($val('description')) ?></textarea>
                    </div>
                </div>
            </fieldset>
        </div>

        <aside class="lg:col-span-1 space-y-4 lg:sticky lg:top-4">
            <section id="cv-pub-ai-panel" class="rounded-xl border-2 border-violet-300 bg-gradient-to-b from-violet-50 to-white p-4 sm:p-5 shadow-sm" aria-labelledby="cv-pub-ai-heading">
                <h2 id="cv-pub-ai-heading" class="text-base font-bold text-violet-950 flex items-center gap-2">
                    <span aria-hidden="true">✦</span> ช่วยเติมด้วย AI
                </h2>
                <p class="text-sm text-violet-900/90 mt-2 leading-relaxed">อัปโหลด PDF/รูป หรือใส่ DOI — ระบบกรอกฟอร์มให้ (แบบ กบศ)</p>
                <?php if (! $aiReady): ?>
                    <p class="text-xs text-amber-800 mt-2 rounded-md bg-amber-50 border border-amber-200 px-2 py-1.5">ยังไม่เปิดใช้บนเซิร์ฟเวอร์ — ตั้ง <code class="text-[11px]">AI_CV_N8N_URL</code></p>
                <?php endif; ?>
                <div class="mt-4 space-y-3">
                    <div>
                        <label for="cv-pub-ai-file" class="cv-pub-label">ไฟล์</label>
                        <input type="file" id="cv-pub-ai-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt" class="cv-pub-field text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-violet-100 file:text-violet-900 file:font-semibold">
                        <p id="cv-pub-ai-file-status" class="text-xs text-slate-600 mt-1 min-h-[1rem]" aria-live="polite"></p>
                    </div>
                    <div>
                        <label for="cv-pub-ai-url" class="cv-pub-label">URL / DOI</label>
                        <input type="url" id="cv-pub-ai-url" class="cv-pub-field" placeholder="https://doi.org/10.xxxx/…" autocomplete="off" spellcheck="false">
                    </div>
                    <div>
                        <label for="cv-pub-ai-text" class="cv-pub-label">ข้อความ / BibTeX</label>
                        <textarea id="cv-pub-ai-text" rows="4" class="cv-pub-field font-mono text-sm" placeholder="@article{ … }"></textarea>
                    </div>
                </div>
                <p id="cv-pub-ai-status" class="text-sm text-slate-600 mt-3 min-h-[1.25rem]" aria-live="polite"></p>
                <button type="button" id="cv-pub-ai-run" class="mt-3 w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-lg bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-700 disabled:opacity-60" <?= $aiReady ? '' : 'disabled' ?>>
                    วิเคราะห์และกรอกฟอร์ม
                </button>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <label class="flex items-center justify-between gap-3 cursor-pointer">
                    <span class="text-sm font-medium text-slate-800">แสดงในหน้า CV สาธารณะ</span>
                    <input type="checkbox" name="visible_on_public" value="1" id="cv-p-vis" class="sr-only peer" <?= $visChecked ? 'checked' : '' ?>>
                    <span class="relative inline-block h-7 w-12 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-violet-500 peer-focus-visible:ring-offset-2 after:absolute after:left-[3px] after:top-[3px] after:h-[1.125rem] after:w-[1.125rem] after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-[1.35rem]" aria-hidden="true"></span>
                </label>
            </section>

            <div class="flex flex-col gap-2">
                <button type="submit" id="cv-pub-submit" class="w-full px-5 py-3 rounded-lg bg-secondary hover:bg-secondary-dark text-white font-semibold text-sm shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    บันทึกผลงาน
                </button>
                <a href="<?= esc($backUrl, 'attr') ?>" class="w-full text-center px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">
                    ยกเลิก
                </a>
            </div>
        </aside>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.CV_PUB_PAGE = {
    validation: {
        yearBeMin: <?= (int) \App\Libraries\PublicationResearchFields::PUBLICATION_YEAR_BE_MIN ?>,
        yearBeMax: <?= (int) \App\Libraries\PublicationResearchFields::PUBLICATION_YEAR_BE_MAX ?>,
        fieldIds: <?= json_encode(\App\Libraries\PublicationResearchFields::publicationPageFieldElementIds(), JSON_UNESCAPED_UNICODE) ?>,
        validPubTypeCodes: <?= json_encode(array_values(array_unique(array_map(static fn (array $o): string => $o['value'], \App\Libraries\RrPublicationType::selectOptions()))), JSON_UNESCAPED_UNICODE) ?>
    },
    csrf: { name: <?= json_encode(csrf_token()) ?>, hash: <?= json_encode(csrf_hash()) ?> },
    endpoints: {
        upload: <?= json_encode(base_url('dashboard/profile/cv/ai-publication-upload'), JSON_UNESCAPED_SLASHES) ?>,
        preview: <?= json_encode(base_url('dashboard/profile/cv/ai-publication-preview'), JSON_UNESCAPED_SLASHES) ?>,
        name: <?= json_encode(base_url('dashboard/profile/cv/search-personnel-names'), JSON_UNESCAPED_SLASHES) ?>,
        names: <?= json_encode(base_url('dashboard/profile/cv/search-personnel-names'), JSON_UNESCAPED_SLASHES) ?>,
        email: <?= json_encode(base_url('dashboard/profile/cv/search-personnel-email'), JSON_UNESCAPED_SLASHES) ?>
    },
    owner: { email: <?= json_encode((string) ($cv_owner_email ?? ''), JSON_UNESCAPED_UNICODE) ?>, name: <?= json_encode((string) ($cv_owner_name ?? ''), JSON_UNESCAPED_UNICODE) ?> },
    authorsInitial: <?= $authorsJson ?>,
    aiReady: <?= $aiReady ? 'true' : 'false' ?>
};
window.CV_AUTHOR_SEARCH_ENDPOINTS = window.CV_PUB_PAGE.endpoints;
</script>
<script src="<?= base_url('assets/js/cv-publication-author-search.js') ?>"></script>
<script src="<?= base_url('assets/js/cv-publication-entry-page.js') ?>"></script>
<?= $this->endSection() ?>
