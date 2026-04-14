/**
 * ระบุตำแหน่งชื่อผู้รับบนแม่แบบใบรับรอง: กดปุ่มแสดงภาพ แล้วลากกรอบสี่เหลี่ยม (หน่วย mm บนหน้า A4 210×297)
 * ค่าเขียนลง input[name=layout_json] อัตโนมัติ — ไม่ต้องแก้ JSON มือ
 */
(function (window) {
    'use strict';

    var PAGE_W_MM = 210;
    var PAGE_H_MM = 297;

    /** @type {{ root: Element, img: HTMLElement, startX: number, startY: number, drawActive: boolean }|null} */
    var activeDrag = null;

    function bindGlobalPointerOnce() {
        if (window._certLpPointerBound) {
            return;
        }
        window._certLpPointerBound = true;
        document.addEventListener('pointermove', function (ev) {
            if (!activeDrag || !activeDrag.drawActive) {
                return;
            }
            var img = activeDrag.img;
            if (img.style.display === 'none') {
                return;
            }
            var ir = img.getBoundingClientRect();
            var x = ev.clientX - ir.left;
            var y = ev.clientY - ir.top;
            var l = Math.min(activeDrag.startX, x);
            var t = Math.min(activeDrag.startY, y);
            var w = Math.abs(x - activeDrag.startX);
            var h = Math.abs(y - activeDrag.startY);
            var rubber = activeDrag.root.querySelector('.cert-lp-rubber');
            if (rubber) {
                rubber.style.display = w > 0 && h > 0 ? 'block' : 'none';
                rubber.style.left = l + 'px';
                rubber.style.top = t + 'px';
                rubber.style.width = w + 'px';
                rubber.style.height = h + 'px';
            }
        });
        document.addEventListener('pointerup', function (ev) {
            if (!activeDrag || !activeDrag.drawActive) {
                return;
            }
            var root = activeDrag.root;
            var img = activeDrag.img;
            var layoutInput = activeDrag.layoutInput;
            var defaultsJson = activeDrag.defaultsJson;
            activeDrag.drawActive = false;
            var ir = img.getBoundingClientRect();
            var x = ev.clientX - ir.left;
            var y = ev.clientY - ir.top;
            var boxMm = pxRectToMm(activeDrag.startX, activeDrag.startY, x, y, { width: ir.width, height: ir.height });
            var rubber = root.querySelector('.cert-lp-rubber');
            if (rubber) {
                rubber.style.display = 'none';
                rubber.style.width = '0';
                rubber.style.height = '0';
            }
            if (boxMm && layoutInput) {
                setStudentNameBox(layoutInput, defaultsJson, boxMm);
                if (typeof activeDrag.syncFromLayout === 'function') {
                    activeDrag.syncFromLayout();
                }
            }
            activeDrag = null;
        });
        document.addEventListener('pointercancel', function () {
            if (activeDrag) {
                activeDrag.drawActive = false;
                var rubber = activeDrag.root.querySelector('.cert-lp-rubber');
                if (rubber) {
                    rubber.style.display = 'none';
                }
                activeDrag = null;
            }
        });
    }

    function safeJsonParse(str, fallback) {
        try {
            var o = JSON.parse(str);
            return o && typeof o === 'object' ? o : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function getStudentNameLayout(layoutObj) {
        var fm = layoutObj.field_mapping;
        if (!fm || !fm.student_name) {
            return null;
        }
        return fm.student_name;
    }

    function fontFromBoxHeight(hMm) {
        if (!hMm || hMm <= 0) {
            return 22;
        }
        return Math.min(36, Math.max(10, Math.round(hMm * 0.32)));
    }

    function setStudentNameBox(layoutInput, defaultsJson, boxMm) {
        var defaults = safeJsonParse(defaultsJson, {});
        var current = safeJsonParse(String(layoutInput.value || '').trim(), {});
        var out = Object.assign({}, defaults, current);
        out.field_mapping = Object.assign(
            {},
            defaults.field_mapping || {},
            current.field_mapping || {}
        );
        var fs = fontFromBoxHeight(boxMm.h);
        out.field_mapping.student_name = {
            x: Math.round(boxMm.x * 10) / 10,
            y: Math.round(boxMm.y * 10) / 10,
            font_size: fs,
            box_w: Math.round(boxMm.w * 10) / 10,
            box_h: Math.round(boxMm.h * 10) / 10
        };
        layoutInput.value = JSON.stringify(out);
        layoutInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function pxRectToMm(x0, y0, x1, y1, rect) {
        var l = Math.min(x0, x1);
        var t = Math.min(y0, y1);
        var r = Math.max(x0, x1);
        var b = Math.max(y0, y1);
        l = Math.max(0, Math.min(rect.width, l));
        t = Math.max(0, Math.min(rect.height, t));
        r = Math.max(0, Math.min(rect.width, r));
        b = Math.max(0, Math.min(rect.height, b));
        var w = r - l;
        var h = b - t;
        if (w < 2 || h < 2) {
            return null;
        }
        return {
            x: (l / rect.width) * PAGE_W_MM,
            y: (t / rect.height) * PAGE_H_MM,
            w: (w / rect.width) * PAGE_W_MM,
            h: (h / rect.height) * PAGE_H_MM
        };
    }

    function mmRectToPx(box, imgRect) {
        return {
            left: (box.x / PAGE_W_MM) * imgRect.width,
            top: (box.y / PAGE_H_MM) * imgRect.height,
            width: (box.w / PAGE_W_MM) * imgRect.width,
            height: (box.h / PAGE_H_MM) * imgRect.height
        };
    }

    function initRoot(root) {
        var hiddenId = root.getAttribute('data-layout-input-id');
        var fileId = root.getAttribute('data-file-input-id');
        var defaultsJson = root.getAttribute('data-defaults-json') || '{}';
        var previewUrl = (root.getAttribute('data-preview-url') || '').trim();
        var sampleText = root.getAttribute('data-sample-text') || 'ชื่อ นามสกุล ผู้เข้ารับการอบรม';

        var layoutInput = hiddenId ? document.getElementById(hiddenId) : null;
        var fileInput = fileId ? document.getElementById(fileId) : null;
        if (!layoutInput) {
            return;
        }

        var btnOpen = root.querySelector('.cert-lp-open');
        var stageWrap = root.querySelector('.cert-lp-stage-wrap');
        var stage = root.querySelector('.cert-lp-stage');
        var img = root.querySelector('.cert-lp-img');
        var rubber = root.querySelector('.cert-lp-rubber');
        var rectFinal = root.querySelector('.cert-lp-rect-final');
        var ghost = root.querySelector('.cert-lp-ghost');
        var notePdf = root.querySelector('.cert-lp-note-pdf');
        var objectUrl = null;

        if (ghost) {
            ghost.textContent = sampleText;
        }

        function showRubber(left, top, w, h) {
            if (!rubber) {
                return;
            }
            rubber.style.display = w > 0 && h > 0 ? 'block' : 'none';
            rubber.style.left = left + 'px';
            rubber.style.top = top + 'px';
            rubber.style.width = w + 'px';
            rubber.style.height = h + 'px';
        }

        function showFinalRect(boxMm) {
            if (!rectFinal || !img || img.style.display === 'none' || !boxMm) {
                return;
            }
            var ir = img.getBoundingClientRect();
            var px = mmRectToPx(boxMm, { width: ir.width, height: ir.height });
            rectFinal.style.display = 'block';
            rectFinal.style.left = px.left + 'px';
            rectFinal.style.top = px.top + 'px';
            rectFinal.style.width = px.width + 'px';
            rectFinal.style.height = px.height + 'px';
            if (ghost) {
                ghost.style.display = 'block';
                ghost.style.left = px.left + 4 + 'px';
                ghost.style.top = px.top + 4 + 'px';
                ghost.style.maxWidth = Math.max(40, px.width - 8) + 'px';
                ghost.style.whiteSpace = 'normal';
            }
        }

        function hideFinalRect() {
            if (rectFinal) {
                rectFinal.style.display = 'none';
            }
            if (ghost) {
                ghost.style.display = 'none';
            }
        }

        function syncFromLayoutInput() {
            var layout = safeJsonParse(String(layoutInput.value || '').trim(), safeJsonParse(defaultsJson, {}));
            var sn = getStudentNameLayout(layout);
            if (sn && typeof sn.x === 'number' && typeof sn.y === 'number' && img.style.display !== 'none') {
                var bw = Number(sn.box_w);
                var bh = Number(sn.box_h);
                if (!(bw > 0) || !(bh > 0)) {
                    bw = 95;
                    bh = Math.max(10, Math.round(Number(sn.font_size || 16) * 0.55));
                }
                showFinalRect({
                    x: Number(sn.x),
                    y: Number(sn.y),
                    w: bw,
                    h: bh
                });
            } else {
                hideFinalRect();
            }
        }

        function setImageSrc(src) {
            if (!img) {
                return;
            }
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
            img.onload = function () {
                img.style.display = 'block';
                if (notePdf) {
                    notePdf.style.display = 'none';
                }
                syncFromLayoutInput();
            };
            img.onerror = function () {
                img.style.display = 'none';
                hideFinalRect();
            };
            img.src = src;
        }

        function handleFileChange() {
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                return;
            }
            var f = fileInput.files[0];
            var type = f.type || '';
            if (type === 'application/pdf' || /\.pdf$/i.test(f.name)) {
                if (notePdf) {
                    notePdf.style.display = 'block';
                    notePdf.textContent =
                        'ไฟล์ PDF: ไม่สามารถลากกรอบบนภาพได้ — ระบบจะใช้ตำแหน่งชื่อตามค่าเริ่มต้น หรืออัปโหลดเป็น JPG/PNG';
                }
                if (img) {
                    img.style.display = 'none';
                }
                if (stageWrap) {
                    stageWrap.style.display = 'none';
                }
                hideFinalRect();
                showRubber(0, 0, 0, 0);
                return;
            }
            if (type.indexOf('image/') === 0) {
                objectUrl = URL.createObjectURL(f);
                setImageSrc(objectUrl);
            }
        }

        if (fileInput) {
            fileInput.addEventListener('change', handleFileChange);
        }

        if (previewUrl) {
            setImageSrc(previewUrl);
        }

        if (btnOpen && stageWrap) {
            btnOpen.addEventListener('click', function () {
                if (!img || img.style.display === 'none' || !img.src) {
                    window.alert('กรุณาอัปโหลดรูป JPG หรือ PNG ของแม่แบบใบรับรองก่อน');
                    return;
                }
                var vis = stageWrap.style.display !== 'none';
                stageWrap.style.display = vis ? 'none' : 'block';
                btnOpen.setAttribute('aria-expanded', vis ? 'false' : 'true');
                if (!vis) {
                    syncFromLayoutInput();
                }
            });
        }

        if (img) {
            img.addEventListener('pointerdown', function (ev) {
                if (img.style.display === 'none' || ev.button !== 0) {
                    return;
                }
                var ir = img.getBoundingClientRect();
                activeDrag = {
                    root: root,
                    img: img,
                    layoutInput: layoutInput,
                    defaultsJson: defaultsJson,
                    startX: ev.clientX - ir.left,
                    startY: ev.clientY - ir.top,
                    drawActive: true,
                    syncFromLayout: syncFromLayoutInput
                };
                showRubber(activeDrag.startX, activeDrag.startY, 0, 0);
                ev.preventDefault();
            });
        }

        layoutInput.addEventListener('change', function () {
            syncFromLayoutInput();
        });

        window.addEventListener('resize', function () {
            syncFromLayoutInput();
        });

        syncFromLayoutInput();
    }

    function initAll() {
        bindGlobalPointerOnce();
        document.querySelectorAll('[data-cert-layout-picker]').forEach(function (root) {
            if (root.getAttribute('data-cert-lp-inited') === '1') {
                return;
            }
            root.setAttribute('data-cert-lp-inited', '1');
            initRoot(root);
        });
    }

    window.CertLayoutPicker = { initAll: initAll };
})(window);
