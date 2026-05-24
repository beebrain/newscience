<?php
/**
 * Modal รายการ CV — แยกทั่วไป vs เผยแพร่ผลงาน (AI เฉพาะผลงาน)
 *
 * @var bool   $ai_cv_publication_enabled
 * @var string $csrfName
 * @var string $csrfHash
 */
$saveUrl = base_url('dashboard/profile/cv/entry/save');
?>
    <!-- NS-CV-UI: split-modals-v2 -->
    <span id="cv-ui-build-marker" class="hidden" data-cv-ui="split-modals-v2" aria-hidden="true"></span>
    <!-- Modal รายการทั่วไป (การศึกษา งาน ฯลฯ — ไม่มี AI) -->
    <div id="cv-entry-modal" class="fixed inset-0 z-[6000] hidden flex flex-col items-stretch justify-start sm:justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-[2px] p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="cv-entry-modal-title">
        <div class="cv-entry-modal__panel relative bg-white overflow-hidden flex flex-col min-h-0 w-full max-w-[56rem] mx-auto shrink-0" onclick="event.stopPropagation()">
            <button type="button" id="cv-entry-modal-close" class="absolute top-3 right-3 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-600 text-xl leading-none shadow-md ring-1 ring-slate-200/90 hover:bg-slate-50 hover:text-slate-900 transition-colors" aria-label="ปิด">&times;</button>
            <form id="cv-entry-modal-form" class="flex flex-col flex-1 min-h-0 font-sarabun" action="<?= $saveUrl ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="section_id" id="cv-g-section-id" value="">
                <input type="hidden" name="entry_id" id="cv-g-entry-id" value="">
                <input type="hidden" name="entry_sort_order" id="cv-g-entry-sort" value="0">
                <input type="hidden" name="extra_info" value="">
                <input type="hidden" name="funding_amount" value="">
                <input type="hidden" name="is_current" id="cv-g-current" value="0">
                <input type="hidden" name="entry_metadata_source" id="cv-g-meta-src" value="">
                <div class="flex flex-col flex-1 min-h-0">
                    <div class="cv-entry-modal__body flex-1 min-w-0 bg-white px-5 py-6 pt-12 sm:px-8 sm:py-8 sm:pt-12">
                        <div class="cv-entry-modal__meta mb-6 pb-6 border-b border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">รายการบน CV สาธารณะ</p>
                            <h2 id="cv-entry-modal-title" class="mt-1.5 text-lg sm:text-xl font-bold text-slate-900 leading-snug tracking-tight">เพิ่มรายการ</h2>
                            <p id="cv-g-entry-modal-sub" class="mt-2 text-sm text-slate-600 leading-relaxed"></p>
                            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">การแสดงผล</p>
                            <div class="cv-entry-modal__display-row mt-2">
                                <label class="cv-entry-modal__vis-toggle flex flex-1 min-w-0 items-center justify-between gap-3 cursor-pointer select-none sm:max-w-md">
                                    <span class="text-sm text-slate-700">แสดงในหน้าสาธารณะ</span>
                                    <input type="checkbox" name="visible_on_public" value="1" checked id="cv-g-vis" class="peer sr-only">
                                    <span class="relative inline-block h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-white after:absolute after:left-[3px] after:top-[3px] after:h-[18px] after:w-[18px] after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 after:content-[''] peer-checked:after:translate-x-[1.25rem]" aria-hidden="true"></span>
                                </label>
                            </div>
                        </div>
                        <div class="cv-entry-modal__headstrip">
                            <p class="cv-edit-stitch-kicker">รายละเอียดในตาราง CV</p>
                            <p class="text-sm text-slate-600 leading-relaxed">หัวข้อ, หน่วยงาน/สถานที่, ช่วงเวลา, รายละเอียด และลิงก์</p>
                        </div>
                        <div class="cv-entry-modal__formstack">
                            <div>
                                <label for="cv-g-title" class="cv-edit-modal-label">หัวข้อรายการ <span class="text-red-600 normal-case tracking-normal font-semibold">*</span></label>
                                <input type="text" name="entry_title" required maxlength="500" id="cv-g-title" class="cv-edit-modal-input" placeholder="ชื่อรายการที่แสดงในคอลัมน์หัวข้อ">
                            </div>
                            <div class="cv-entry-modal__grid2">
                                <div>
                                    <label for="cv-g-org" class="cv-edit-modal-label">หน่วยงาน / องค์กร</label>
                                    <input type="text" name="organization" maxlength="500" id="cv-g-org" class="cv-edit-modal-input" placeholder="องค์กร / สถาบัน">
                                </div>
                                <div>
                                    <label for="cv-g-loc" class="cv-edit-modal-label">สถานที่</label>
                                    <input type="text" name="location" maxlength="500" id="cv-g-loc" class="cv-edit-modal-input" placeholder="เมือง ประเทศ ฯลฯ">
                                </div>
                            </div>
                            <div class="cv-entry-modal__grid2">
                                <div>
                                    <label for="cv-g-start" class="cv-edit-modal-label">วันเริ่ม</label>
                                    <input type="date" name="start_date" id="cv-g-start" class="cv-edit-modal-input">
                                </div>
                                <div>
                                    <label for="cv-g-end" class="cv-edit-modal-label">วันสิ้นสุด</label>
                                    <input type="date" name="end_date" id="cv-g-end" class="cv-edit-modal-input">
                                </div>
                            </div>
                            <div>
                                <label for="cv-g-url" class="cv-edit-modal-label">ลิงก์ (URL)</label>
                                <input type="text" name="entry_url" maxlength="2048" id="cv-g-url" class="cv-edit-modal-input" placeholder="https://...">
                            </div>
                            <div>
                                <label for="cv-g-desc" class="cv-edit-modal-label">รายละเอียด</label>
                                <textarea name="entry_description" rows="4" id="cv-g-desc" class="cv-edit-modal-textarea" placeholder="ข้อความในคอลัมน์รายละเอียดบนหน้า CV"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cv-entry-modal__footer flex flex-wrap items-center justify-end gap-2 sm:gap-3 shrink-0">
                    <button type="button" id="cv-entry-modal-cancel" class="text-sm px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium hover:bg-slate-50 transition-colors min-w-[5.5rem]">ยกเลิก</button>
                    <button type="submit" class="text-sm px-5 py-2.5 rounded-lg bg-secondary hover:bg-secondary-dark text-white font-semibold shadow-sm transition-colors min-w-[6.5rem]">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal เผยแพร่ผลงาน (ฟิลด์ กบศ + ช่วยเติมด้วย AI) -->
    <div id="cv-pub-entry-modal" class="fixed inset-0 z-[6000] hidden flex flex-col items-stretch justify-start sm:justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-[2px] p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="cv-pub-entry-modal-title">
        <div class="cv-entry-modal__panel relative bg-white overflow-hidden flex flex-col min-h-0 w-full max-w-[56rem] mx-auto shrink-0" onclick="event.stopPropagation()">
            <button type="button" id="cv-pub-entry-modal-close" class="absolute top-3 right-3 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-600 text-xl leading-none shadow-md ring-1 ring-slate-200/90 hover:bg-slate-50 hover:text-slate-900 transition-colors" aria-label="ปิด">&times;</button>
            <form id="cv-pub-entry-modal-form" class="flex flex-col flex-1 min-h-0 font-sarabun" action="<?= $saveUrl ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="section_id" id="cv-p-section-id" value="">
                <input type="hidden" name="entry_id" id="cv-p-entry-id" value="">
                <input type="hidden" name="entry_sort_order" id="cv-p-entry-sort" value="0">
                <input type="hidden" name="extra_info" value="">
                <input type="hidden" name="funding_amount" value="">
                <input type="hidden" name="is_current" id="cv-p-current" value="0">
                <input type="hidden" name="entry_metadata_source" id="cv-p-meta-src" value="">
                <div class="flex flex-col flex-1 min-h-0">
                    <div class="cv-entry-modal__body flex-1 min-w-0 bg-white px-5 py-6 pt-12 sm:px-8 sm:py-8 sm:pt-12">
                        <div class="cv-entry-modal__meta mb-6 pb-6 border-b border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">ผลงานเผยแพร่บน CV สาธารณะ</p>
                            <h2 id="cv-pub-entry-modal-title" class="mt-1.5 text-lg sm:text-xl font-bold text-slate-900 leading-snug tracking-tight">เพิ่มผลงานเผยแพร่</h2>
                            <p id="cv-p-entry-modal-sub" class="mt-2 text-sm text-slate-600 leading-relaxed"></p>
                            <div class="mt-4 rounded-xl border border-violet-200 bg-violet-50/80 p-3 sm:p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <p class="text-sm text-violet-950 leading-relaxed">
                                        <strong>ช่วยเติมด้วย AI</strong> — อัปโหลด PDF/รูป หรือ DOI แล้วให้ระบบกรอกฟอร์มนี้ (แบบ กบศ)
                                    </p>
                                    <button type="button"
                                            onclick="launchCvAiFromPubEntryModal()"
                                            class="inline-flex shrink-0 items-center justify-center gap-1.5 text-sm px-4 py-2.5 rounded-lg border border-violet-400 bg-violet-600 text-white font-semibold hover:bg-violet-700 shadow-sm transition-colors">
                                        <span aria-hidden="true">✦</span> ช่วยเติมด้วย AI
                                    </button>
                                </div>
                                <?php if (! ($ai_cv_publication_enabled ?? false)): ?>
                                <p class="text-xs text-amber-800 mt-2">ยังไม่เปิดใช้บนเซิร์ฟเวอร์ — ตั้ง <code class="text-[11px] bg-amber-100 px-1 rounded">AI_CV_N8N_URL</code> ใน .env</p>
                                <?php endif; ?>
                            </div>
                            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">การแสดงผล</p>
                            <div class="cv-entry-modal__display-row mt-2">
                                <label class="cv-entry-modal__vis-toggle flex flex-1 min-w-0 items-center justify-between gap-3 cursor-pointer select-none sm:max-w-md">
                                    <span class="text-sm text-slate-700">แสดงในหน้าสาธารณะ</span>
                                    <input type="checkbox" name="visible_on_public" value="1" checked id="cv-p-vis" class="peer sr-only">
                                    <span class="relative inline-block h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-white after:absolute after:left-[3px] after:top-[3px] after:h-[18px] after:w-[18px] after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 after:content-[''] peer-checked:after:translate-x-[1.25rem]" aria-hidden="true"></span>
                                </label>
                            </div>
                        </div>
                        <div class="cv-entry-modal__headstrip">
                            <p class="cv-edit-stitch-kicker">รายละเอียดผลงาน (ตรงกับ กบศ)</p>
                            <p class="text-sm text-slate-600 leading-relaxed">ชื่อผลงาน, แหล่งเผยแพร่, DOI, ปี/เดือน, ผู้แต่ง และข้อมูลวารสาร</p>
                        </div>
                        <div class="cv-entry-modal__formstack">
                            <div>
                                <label for="cv-p-title" class="cv-edit-modal-label">ชื่อผลงาน <span class="text-red-600 normal-case tracking-normal font-semibold">*</span></label>
                                <input type="text" name="entry_title" required maxlength="500" id="cv-p-title" class="cv-edit-modal-input" placeholder="ชื่อบทความ / หนังสือ / ผลงาน">
                            </div>
                            <div class="cv-entry-modal__grid2">
                                <div>
                                    <label for="cv-p-org" class="cv-edit-modal-label">แหล่งเผยแพร่ (source) <span class="text-red-600 normal-case tracking-normal font-semibold">*</span></label>
                                    <input type="text" name="organization" maxlength="500" id="cv-p-org" class="cv-edit-modal-input" placeholder="วารสาร / สำนักพิมพ์ / การประชุม">
                                </div>
                                <div>
                                    <label for="cv-p-loc" class="cv-edit-modal-label">สถานที่</label>
                                    <input type="text" name="location" maxlength="500" id="cv-p-loc" class="cv-edit-modal-input" placeholder="เมือง ประเทศ ฯลฯ">
                                </div>
                            </div>
                            <div class="cv-entry-modal__grid2">
                                <div>
                                    <label for="cv-p-start" class="cv-edit-modal-label">วันเริ่ม</label>
                                    <input type="date" name="start_date" id="cv-p-start" class="cv-edit-modal-input">
                                </div>
                                <div>
                                    <label for="cv-p-end" class="cv-edit-modal-label">วันสิ้นสุด</label>
                                    <input type="date" name="end_date" id="cv-p-end" class="cv-edit-modal-input">
                                </div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 sm:p-4">
                                <label for="cv-p-pubtype" class="cv-edit-modal-label">ประเภทผลงานเผยแพร่</label>
                                <p class="text-xs text-slate-600 mb-2 leading-relaxed">รหัสเดียวกับ กบศ (<code class="text-[11px] bg-white px-1.5 py-0.5 rounded border border-slate-200 font-mono">publication_type</code>)</p>
                                <select name="publication_type" id="cv-p-pubtype" class="cv-edit-modal-select">
                                    <option value="">— ไม่ระบุ —</option>
                                    <?php foreach (\App\Libraries\RrPublicationType::selectOptionGroups() as $groupLabel => $groupOpts): ?>
                                        <optgroup label="<?= esc($groupLabel) ?>">
                                            <?php foreach ($groupOpts as $pubOpt): ?>
                                                <option value="<?= esc($pubOpt['value']) ?>"><?= esc($pubOpt['label']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50/90 p-3 sm:p-4 space-y-3">
                                <div class="cv-entry-modal__grid2">
                                    <div>
                                        <label for="cv-p-doi" class="cv-edit-modal-label">DOI</label>
                                        <input type="text" name="entry_doi" id="cv-p-doi" class="cv-edit-modal-input" maxlength="255" placeholder="เช่น 10.1234/example" autocomplete="off">
                                    </div>
                                    <div>
                                        <label for="cv-p-rrid" class="cv-edit-modal-label">รหัสผลงานใน กบศ</label>
                                        <input type="text" name="publication_rr_id" id="cv-p-rrid" class="cv-edit-modal-input" maxlength="12" inputmode="numeric" pattern="[0-9]*" placeholder="ถ้าทราบ (ตัวเลข)" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50/90 p-3 sm:p-4 space-y-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">ข้อมูลวารสาร / หนังสือ</p>
                                <div class="cv-entry-modal__grid2">
                                    <div>
                                        <label for="cv-p-year-be" class="cv-edit-modal-label">ปีที่เผยแพร่ (พ.ศ.)</label>
                                        <input type="number" name="publication_year_be" id="cv-p-year-be" class="cv-edit-modal-input" min="2400" max="2700" placeholder="เช่น 2567">
                                    </div>
                                    <div>
                                        <label for="cv-p-month" class="cv-edit-modal-label">เดือน</label>
                                        <select name="publication_month" id="cv-p-month" class="cv-edit-modal-select">
                                            <option value="">— ไม่ระบุ —</option>
                                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?= $m ?>"><?= $m ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="cv-entry-modal__grid2">
                                    <div>
                                        <label for="cv-p-volume" class="cv-edit-modal-label">เล่ม (Volume)</label>
                                        <input type="text" name="volume" id="cv-p-volume" class="cv-edit-modal-input" maxlength="100">
                                    </div>
                                    <div>
                                        <label for="cv-p-pages" class="cv-edit-modal-label">หน้า (Pages)</label>
                                        <input type="text" name="pages" id="cv-p-pages" class="cv-edit-modal-input" maxlength="100">
                                    </div>
                                </div>
                                <div>
                                    <label for="cv-p-isbn" class="cv-edit-modal-label">ISBN</label>
                                    <input type="text" name="isbn" id="cv-p-isbn" class="cv-edit-modal-input" maxlength="100">
                                </div>
                                <div>
                                    <label for="cv-p-abstract" class="cv-edit-modal-label">บทคัดย่อ (Abstract)</label>
                                    <textarea name="abstract" rows="3" id="cv-p-abstract" class="cv-edit-modal-textarea" placeholder="บทคัดย่อผลงาน"></textarea>
                                </div>
                                <div>
                                    <label for="cv-p-keywords" class="cv-edit-modal-label">คำสำคัญ (Keywords)</label>
                                    <input type="text" name="keywords" id="cv-p-keywords" class="cv-edit-modal-input" maxlength="500" placeholder="คั่นด้วยจุลภาค">
                                </div>
                                <div>
                                    <label for="cv-p-notes" class="cv-edit-modal-label">หมายเหตุ (Notes)</label>
                                    <textarea name="notes" rows="2" id="cv-p-notes" class="cv-edit-modal-textarea"></textarea>
                                </div>
                                <div>
                                    <label for="cv-p-ref-url" class="cv-edit-modal-label">ลิงก์อ้างอิง (ref_url)</label>
                                    <input type="text" name="ref_url" id="cv-p-ref-url" class="cv-edit-modal-input" maxlength="2048" placeholder="https://doi.org/…">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <label class="cv-edit-modal-label mb-0">ผู้แต่ง / ผู้ร่วมวิจัย</label>
                                        <button type="button" id="cv-p-add-author" class="text-xs px-2.5 py-1 rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">+ เพิ่มผู้แต่ง</button>
                                    </div>
                                    <input type="hidden" name="publication_authors" id="cv-p-authors-json" value="">
                                    <div id="cv-p-authors-list" class="space-y-2"></div>
                                    <p class="text-xs text-slate-500 mt-2">* = ผู้รับผิดชอบการติดต่อ (corresponding author)</p>
                                </div>
                            </div>
                            <div>
                                <label for="cv-p-url" class="cv-edit-modal-label">ลิงก์ (URL)</label>
                                <input type="text" name="entry_url" maxlength="2048" id="cv-p-url" class="cv-edit-modal-input" placeholder="https://...">
                            </div>
                            <div>
                                <label for="cv-p-desc" class="cv-edit-modal-label">รายละเอียดเพิ่มเติม</label>
                                <textarea name="entry_description" rows="3" id="cv-p-desc" class="cv-edit-modal-textarea" placeholder="ข้อความเสริมบนหน้า CV (ถ้ามี)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cv-entry-modal__footer flex flex-wrap items-center justify-end gap-2 sm:gap-3 shrink-0">
                    <button type="button" id="cv-pub-entry-modal-cancel" class="text-sm px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium hover:bg-slate-50 transition-colors min-w-[5.5rem]">ยกเลิก</button>
                    <button type="submit" class="text-sm px-5 py-2.5 rounded-lg bg-secondary hover:bg-secondary-dark text-white font-semibold shadow-sm transition-colors min-w-[6.5rem]">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
