<?php
/**
 * Modal รายการ CV ทั่วไป (การศึกษา งาน ฯลฯ)
 * ผลงานตีพิมพ์ → หน้า /dashboard/profile/cv/publication
 */
$saveUrl = base_url('dashboard/profile/cv/entry/save');
?>
    <span id="cv-ui-build-marker" class="hidden" data-cv-ui="publication-page-v1" aria-hidden="true"></span>
    <div id="cv-entry-modal" class="fixed inset-0 z-[6000] hidden flex flex-col items-stretch justify-start sm:justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-[2px] p-3 sm:p-6 overscroll-contain" role="dialog" aria-modal="true" aria-labelledby="cv-entry-modal-title">
        <div class="cv-entry-modal__panel relative bg-white overflow-hidden flex flex-col min-h-0 w-full max-w-[56rem] mx-auto shrink-0" onclick="event.stopPropagation()">
            <button type="button" id="cv-entry-modal-close" class="absolute top-3 right-3 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-600 text-xl leading-none shadow-md ring-1 ring-slate-200/90 hover:bg-slate-50 hover:text-slate-900 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600" aria-label="ปิด">&times;</button>
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
                            <h2 id="cv-entry-modal-title" class="mt-1.5 text-lg sm:text-xl font-bold text-slate-900 leading-snug tracking-tight text-pretty">เพิ่มรายการ</h2>
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
                                <input type="text" name="entry_title" required maxlength="500" id="cv-g-title" class="cv-edit-modal-input" placeholder="ชื่อรายการที่แสดงในคอลัมน์หัวข้อ…">
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
                                <input type="url" name="entry_url" maxlength="2048" id="cv-g-url" class="cv-edit-modal-input" placeholder="https://…">
                            </div>
                            <div>
                                <label for="cv-g-desc" class="cv-edit-modal-label">รายละเอียด</label>
                                <textarea name="entry_description" rows="4" id="cv-g-desc" class="cv-edit-modal-textarea" placeholder="ข้อความในคอลัมน์รายละเอียดบนหน้า CV"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cv-entry-modal__footer flex flex-wrap items-center justify-end gap-2 sm:gap-3 shrink-0">
                    <button type="button" id="cv-entry-modal-cancel" class="text-sm px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium hover:bg-slate-50 transition-colors min-w-[5.5rem] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">ยกเลิก</button>
                    <button type="submit" class="text-sm px-5 py-2.5 rounded-lg bg-secondary hover:bg-secondary-dark text-white font-semibold shadow-sm transition-colors min-w-[6.5rem] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
