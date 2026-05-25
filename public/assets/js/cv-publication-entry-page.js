(function () {
    'use strict';

    var cfg = window.CV_PUB_PAGE || {};
    var csrf = cfg.csrf || {};

    function el(id) {
        return document.getElementById(id);
    }

    function cvEl(name) {
        return el('cv-p-' + name);
    }

    function defaultAuthors() {
        var o = cfg.owner || {};
        if (!o.email && !o.name) return [];
        return [{
            name: o.name || '',
            email: o.email || '',
            affiliation: 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            corresponding: 1,
            order: 1
        }];
    }

    function mergeAuthor(a, b) {
        var pick = function (x, y) {
            x = (x || '').trim();
            y = (y || '').trim();
            if (!x) return y;
            if (!y) return x;
            return x.length >= y.length ? x : y;
        };
        return {
            name: pick(a.name, b.name),
            email: (a.email || b.email || '').trim().toLowerCase(),
            affiliation: pick(a.affiliation, b.affiliation) || 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            corresponding: (a.corresponding || b.corresponding) ? 1 : 0,
            order: a.order || b.order || 0
        };
    }

    function dedupeAuthors(authors) {
        if (!Array.isArray(authors)) return [];
        var byEmail = {};
        var byName = {};
        authors.forEach(function (a) {
            if (!a) return;
            var name = (a.name || '').trim();
            var email = (a.email || '').trim().toLowerCase();
            if (!name && !email) return;
            var entry = {
                name: name,
                email: email,
                affiliation: (a.affiliation || '').trim() || 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
                corresponding: a.corresponding ? 1 : 0,
                order: 0
            };
            if (email) {
                byEmail[email] = byEmail[email] ? mergeAuthor(byEmail[email], entry) : entry;
            } else {
                var key = name.toLowerCase().replace(/\s+/g, ' ');
                byName[key] = byName[key] ? mergeAuthor(byName[key], entry) : entry;
            }
        });
        var merged = [];
        Object.keys(byEmail).forEach(function (k) { merged.push(byEmail[k]); });
        Object.keys(byName).forEach(function (k) { merged.push(byName[k]); });
        merged.forEach(function (a, i) { a.order = i + 1; });
        return merged;
    }

    function syncAuthorsHidden() {
        var hidden = el('cv-p-authors-json');
        var list = el('cv-p-authors-list');
        if (!hidden || !list) return;
        var out = [];
        list.querySelectorAll('[data-author-index]').forEach(function (row, i) {
            var name = (row.querySelector('.cv-author-name') || {}).value || '';
            var email = (row.querySelector('.cv-author-email') || {}).value || '';
            name = name.trim();
            email = email.trim().toLowerCase();
            if (!name && !email) return;
            out.push({
                name: name,
                email: email,
                affiliation: ((row.querySelector('.cv-author-aff') || {}).value || '').trim(),
                corresponding: (row.querySelector('.cv-author-corr') || {}).checked ? 1 : 0,
                order: i + 1
            });
        });
        hidden.value = JSON.stringify(out);
    }

    function renderAuthors(authors) {
        var list = el('cv-p-authors-list');
        if (!list) return;
        list.innerHTML = '';
        var rows = Array.isArray(authors) && authors.length ? dedupeAuthors(authors) : defaultAuthors();
        rows.forEach(function (a, idx) {
            var row = document.createElement('div');
            row.className = 'rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-2 min-w-0';
            row.dataset.authorIndex = String(idx);
            row.innerHTML =
                '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">' +
                '<input type="text" class="cv-pub-field cv-author-name" placeholder="ชื่อ-นามสกุล" value="' + (a.name || '').replace(/"/g, '&quot;') + '">' +
                '<input type="email" class="cv-pub-field cv-author-email" placeholder="อีเมล" autocomplete="off" spellcheck="false" value="' + (a.email || '').replace(/"/g, '&quot;') + '">' +
                '</div>' +
                '<div class="flex flex-wrap items-center gap-2">' +
                '<input type="text" class="cv-pub-field flex-1 min-w-[10rem] cv-author-aff" placeholder="สังกัด" value="' + (a.affiliation || 'มหาวิทยาลัยราชภัฏอุตรดิตถ์').replace(/"/g, '&quot;') + '">' +
                '<label class="inline-flex items-center gap-1.5 text-xs text-slate-600 shrink-0">' +
                '<input type="checkbox" class="cv-author-corr rounded border-slate-300"' + (a.corresponding ? ' checked' : '') + '> ผู้ติดต่อ*' +
                '</label>' +
                '<button type="button" class="text-xs px-2 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50 cv-author-remove">ลบ</button>' +
                '</div>';
            list.appendChild(row);
        });
        syncAuthorsHidden();
    }

    window.syncPublicationAuthorsHidden = syncAuthorsHidden;
    window.renderPublicationAuthors = renderAuthors;
    window.dedupePublicationAuthors = dedupeAuthors;

    function setPubType(code) {
        var pubSel = cvEl('pubtype');
        if (!pubSel || !code) return;
        var pv = String(code);
        if (!Array.from(pubSel.options).some(function (o) { return o.value === pv; })) {
            var opt = document.createElement('option');
            opt.value = pv;
            opt.textContent = 'จาก AI: ' + pv;
            opt.setAttribute('data-temp-option', '1');
            pubSel.appendChild(opt);
        }
        pubSel.value = pv;
    }

    function fillFromPublication(pub) {
        if (!pub) return;
        if (cvEl('title')) cvEl('title').value = pub.title || '';
        if (cvEl('org')) cvEl('org').value = pub.organization || '';
        if (cvEl('loc')) cvEl('loc').value = pub.location || '';
        if (cvEl('desc')) cvEl('desc').value = pub.description || '';
        if (cvEl('doi')) cvEl('doi').value = pub.doi || '';
        if (cvEl('rrid')) cvEl('rrid').value = pub.rr_publication_id ? String(pub.rr_publication_id) : '';
        if (cvEl('meta-src')) cvEl('meta-src').value = 'ai_assistant';
        var urlIn = el('cv-pub-ai-url');
        var userUrl = urlIn && urlIn.value.trim() ? urlIn.value.trim() : '';
        if (cvEl('url')) {
            if (userUrl) cvEl('url').value = userUrl;
            else if (window.__cvPubAiUploaded && window.__cvPubAiUploaded.download_url) {
                cvEl('url').value = window.__cvPubAiUploaded.download_url;
            } else {
                cvEl('url').value = pub.url || '';
            }
        }
        if (pub.publication_type) setPubType(pub.publication_type);
        if (pub.year && cvEl('year-be')) {
            cvEl('year-be').value = String(parseInt(pub.year, 10) + 543);
        }
        if (pub.month && cvEl('month')) cvEl('month').value = String(pub.month);
        if (cvEl('abstract')) cvEl('abstract').value = pub.abstract || pub.description || '';
        if (pub.volume && cvEl('volume')) cvEl('volume').value = pub.volume;
        if (pub.pages && cvEl('pages')) cvEl('pages').value = pub.pages;
        if (pub.keywords && cvEl('keywords')) cvEl('keywords').value = pub.keywords;
        renderAuthors(Array.isArray(pub.authors) && pub.authors.length ? pub.authors : defaultAuthors());
        var title = cvEl('title');
        if (title) title.focus();
    }

    window.__cvPubAiUploaded = null;

    var FIELD_LABELS = {
        entry_title: 'ชื่อผลงาน',
        organization: 'แหล่งเผยแพร่',
        publication_type: 'ประเภทผลงาน',
        publication_year_be: 'ปีที่เผยแพร่ (พ.ศ.)'
    };

    function publicationValidationConfig() {
        var v = (cfg.validation || {});
        return {
            yearBeMin: v.yearBeMin || 2400,
            yearBeMax: v.yearBeMax || 2700,
            fieldIds: v.fieldIds || {},
            validPubTypeCodes: Array.isArray(v.validPubTypeCodes) ? v.validPubTypeCodes : []
        };
    }

    function isValidPubTypeCode(code, validCodes) {
        var v = String(code || '').trim();
        if (!v) return false;
        if (v === 'อื่นๆ') return true;
        if (validCodes.indexOf(v) >= 0) return true;
        return /^[a-z][a-z0-9_\-.]{0,79}$/i.test(v);
    }

    function collectPublicationPost() {
        return {
            entry_title: (cvEl('title') || {}).value || '',
            organization: (cvEl('org') || {}).value || '',
            publication_type: (cvEl('pubtype') || {}).value || '',
            publication_year_be: (cvEl('year-be') || {}).value || '',
            start_date: ''
        };
    }

    /**
     * @returns {Array<{field:string,message:string}>}
     */
    function publicationPageFieldErrors(post) {
        var rules = publicationValidationConfig();
        var errors = [];
        var title = String(post.entry_title || '').trim();
        if (!title) {
            errors.push({ field: 'entry_title', message: 'กรุณากรอกชื่อรายการ' });
        } else if (title.length > 500) {
            errors.push({ field: 'entry_title', message: 'ชื่อรายการยาวเกิน 500 ตัวอักษร' });
        }

        var ptype = String(post.publication_type || '').trim();
        if (!ptype) {
            errors.push({ field: 'publication_type', message: 'กรุณาเลือกประเภทผลงานเผยแพร่' });
        } else if (!isValidPubTypeCode(ptype, rules.validPubTypeCodes)) {
            errors.push({ field: 'publication_type', message: 'ประเภทผลงานเผยแพร่ไม่ถูกต้อง' });
        }

        if (!String(post.organization || '').trim()) {
            errors.push({ field: 'organization', message: 'กรุณากรอกแหล่งเผยแพร่ (source)' });
        }

        var yearBe = String(post.publication_year_be || '').trim();
        var start = String(post.start_date || '').trim();
        if (!yearBe && !start) {
            errors.push({ field: 'publication_year_be', message: 'กรุณาระบุปีที่เผยแพร่ (พ.ศ.) หรือวันเริ่ม' });
        } else if (yearBe && !start) {
            if (!/^\d+$/.test(yearBe)) {
                errors.push({ field: 'publication_year_be', message: 'ปีที่เผยแพร่ (พ.ศ.) ต้องเป็นตัวเลข' });
            } else {
                var y = parseInt(yearBe, 10);
                if (y < rules.yearBeMin || y > rules.yearBeMax) {
                    errors.push({
                        field: 'publication_year_be',
                        message: 'ปีที่เผยแพร่ (พ.ศ.) ต้องอยู่ระหว่าง ' + rules.yearBeMin + '–' + rules.yearBeMax
                    });
                }
            }
        }

        return errors;
    }

    function fieldElement(field) {
        var ids = publicationValidationConfig().fieldIds;
        var domId = ids[field] || ('cv-p-' + field);
        return el(domId);
    }

    function clearPublicationFieldErrors() {
        var banner = el('cv-pub-form-errors');
        if (banner) {
            banner.textContent = '';
            banner.classList.add('hidden');
        }
        document.querySelectorAll('.cv-pub-field--invalid').forEach(function (node) {
            node.classList.remove('cv-pub-field--invalid');
            node.removeAttribute('aria-invalid');
        });
        document.querySelectorAll('.cv-pub-field-error').forEach(function (node) {
            node.remove();
        });
    }

    function showPublicationFieldErrors(errors) {
        clearPublicationFieldErrors();
        if (!errors.length) return true;

        var banner = el('cv-pub-form-errors');
        var lines = errors.map(function (e) {
            var label = FIELD_LABELS[e.field] || e.field;
            return label + ': ' + e.message;
        });
        if (banner) {
            banner.textContent = lines.join('\n');
            banner.classList.remove('hidden');
        }

        var seen = {};
        errors.forEach(function (err) {
            if (seen[err.field]) return;
            seen[err.field] = true;
            var input = fieldElement(err.field);
            if (!input) return;
            input.classList.add('cv-pub-field--invalid');
            input.setAttribute('aria-invalid', 'true');
            var hint = document.createElement('span');
            hint.className = 'cv-pub-field-error';
            hint.id = input.id + '-error';
            hint.textContent = err.message;
            input.setAttribute('aria-describedby', hint.id);
            if (input.parentNode) {
                input.parentNode.appendChild(hint);
            }
        });

        var first = fieldElement(errors[0].field);
        if (first) {
            first.focus({ preventScroll: false });
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        var summary = 'กรุณาแก้ไขข้อมูลก่อนบันทึก:\n' + lines.join('\n');
        if (typeof swalAlert === 'function') {
            swalAlert(summary.replace(/\n/g, '<br>'), 'warning');
        }

        return false;
    }

    function bindPublicationFieldClear() {
        var form = el('cv-pub-form');
        if (!form) return;
        form.addEventListener('input', function (ev) {
            var t = ev.target;
            if (!t || !t.classList || !t.classList.contains('cv-pub-field')) return;
            if (!t.classList.contains('cv-pub-field--invalid')) return;
            t.classList.remove('cv-pub-field--invalid');
            t.removeAttribute('aria-invalid');
            var errId = t.id + '-error';
            var errNode = document.getElementById(errId);
            if (errNode) errNode.remove();
        });
    }

    async function uploadAiFile(file) {
        var fs = el('cv-pub-ai-file-status');
        if (!file) {
            window.__cvPubAiUploaded = null;
            if (fs) fs.textContent = '';
            return null;
        }
        if (fs) fs.textContent = 'กำลังอัปโหลด…';
        var fd = new FormData();
        fd.append('file', file);
        fd.append(csrf.name, csrf.hash);
        var res = await fetch(cfg.endpoints.upload, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        var data = await res.json().catch(function () { return {}; });
        if (!data.success || !data.file) {
            window.__cvPubAiUploaded = null;
            if (fs) fs.textContent = data.message || 'อัปโหลดไม่สำเร็จ';
            return null;
        }
        window.__cvPubAiUploaded = data.file;
        if (fs) fs.textContent = '✓ ' + (data.file.original_name || 'อัปโหลดแล้ว');
        return data.file;
    }

    async function runAi() {
        if (!cfg.aiReady) return;
        var st = el('cv-pub-ai-status');
        var btn = el('cv-pub-ai-run');
        var fileIn = el('cv-pub-ai-file');
        var urlIn = el('cv-pub-ai-url');
        var textIn = el('cv-pub-ai-text');
        if (!st) return;
        st.textContent = 'กำลังเรียก AI…';
        if (btn) btn.disabled = true;
        try {
            if (fileIn && fileIn.files && fileIn.files[0] && !window.__cvPubAiUploaded) {
                await uploadAiFile(fileIn.files[0]);
            }
            var p = new URLSearchParams();
            p.append(csrf.name, csrf.hash);
            if (window.__cvPubAiUploaded && window.__cvPubAiUploaded.stored_name) {
                p.append('stored_name', window.__cvPubAiUploaded.stored_name);
            } else if (urlIn && urlIn.value.trim()) {
                var ext = urlIn.value.trim();
                if (/localhost|127\.0\.0\.1|::1/i.test(ext)) {
                    st.textContent = 'ไม่ใช้ URL localhost ได้ — อัปโหลดไฟล์แทน';
                    return;
                }
                p.append('url', ext);
            } else if (textIn && textIn.value.trim()) {
                p.append('text', textIn.value.trim());
            } else {
                st.textContent = 'อัปโหลดไฟล์ ใส่ URL หรือวางข้อความ';
                return;
            }
            var res = await fetch(cfg.endpoints.preview, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: p.toString()
            });
            var data = await res.json().catch(function () { return {}; });
            if (!data.success) {
                st.textContent = data.message || 'ผิดพลาด';
                return;
            }
            fillFromPublication(data.publication || null);
            st.textContent = 'กรอกฟอร์มแล้ว — ตรวจสอบแล้วกดบันทึก';
        } finally {
            if (btn) btn.disabled = !cfg.aiReady;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var initial = cfg.authorsInitial;
        if (!Array.isArray(initial) || !initial.length) {
            initial = defaultAuthors();
        }
        renderAuthors(initial);

        var addBtn = el('cv-p-add-author');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var list = el('cv-p-authors-list');
                if (!list) return;
                var rows = list.querySelectorAll('[data-author-index]');
                var idx = rows.length;
                var a = { name: '', email: '', affiliation: 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', corresponding: 0, order: idx + 1 };
                var row = document.createElement('div');
                row.className = 'rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-2 min-w-0';
                row.dataset.authorIndex = String(idx);
                row.innerHTML =
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">' +
                    '<input type="text" class="cv-pub-field cv-author-name" placeholder="ชื่อ-นามสกุล">' +
                    '<input type="email" class="cv-pub-field cv-author-email" placeholder="อีเมล" autocomplete="off" spellcheck="false">' +
                    '</div>' +
                    '<div class="flex flex-wrap items-center gap-2">' +
                    '<input type="text" class="cv-pub-field flex-1 min-w-[10rem] cv-author-aff" placeholder="สังกัด" value="มหาวิทยาลัยราชภัฏอุตรดิตถ์">' +
                    '<label class="inline-flex items-center gap-1.5 text-xs text-slate-600 shrink-0">' +
                    '<input type="checkbox" class="cv-author-corr rounded border-slate-300"> ผู้ติดต่อ*' +
                    '</label>' +
                    '<button type="button" class="text-xs px-2 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50 cv-author-remove">ลบ</button>' +
                    '</div>';
                list.appendChild(row);
                syncAuthorsHidden();
            });
        }

        var list = el('cv-p-authors-list');
        if (list) {
            list.addEventListener('input', syncAuthorsHidden);
            list.addEventListener('click', function (ev) {
                if (ev.target && ev.target.classList.contains('cv-author-remove')) {
                    var row = ev.target.closest('[data-author-index]');
                    if (row) row.remove();
                    syncAuthorsHidden();
                }
            });
        }

        bindPublicationFieldClear();

        var form = el('cv-pub-form');
        if (form) {
            form.addEventListener('submit', function (ev) {
                syncAuthorsHidden();
                var errors = publicationPageFieldErrors(collectPublicationPost());
                if (errors.length) {
                    ev.preventDefault();
                    showPublicationFieldErrors(errors);
                } else {
                    clearPublicationFieldErrors();
                }
            });
        }

        var aiRun = el('cv-pub-ai-run');
        if (aiRun) aiRun.addEventListener('click', runAi);

        var aiFile = el('cv-pub-ai-file');
        if (aiFile) {
            aiFile.addEventListener('change', function () {
                window.__cvPubAiUploaded = null;
                var f = aiFile.files && aiFile.files[0];
                if (f) uploadAiFile(f);
            });
        }

        if (form && form.getAttribute('data-open-ai') === '1') {
            var panel = el('cv-pub-ai-panel');
            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
})();
