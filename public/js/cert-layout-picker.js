/**
 * ระบุตำแหน่งชื่อผู้รับบนแม่แบบ A4 แนวตั้ง (210×297 mm ใน PDF)
 * พื้นที่ลากแสดงเป็น A4 แนวนอน (อัตราส่วน 297:210) โดยหมุนภาพ -90° — ค่า layout_json ยังเป็นหน่วย mm บนหน้าแนวตั้ง
 */
(function (window) {
    'use strict';

    var PAGE_W_MM = 210;
    var PAGE_H_MM = 297;

    /** @type {Record<string, unknown>|null} */
    var activeDrag = null;

    /**
     * แปลงพิกัดหน้าจอ → พิกัดในระบบ "แผ่น A4 แนวตั้ง" (px) บน .cert-lp-sheet ก่อนหมุน
     * (sheet หมุน -90° รอบจุดกลาง; ใช้สมการผกผันเดียวกับที่ตรวจมุมท้ายแผ่น)
     */
    function clientToPortraitLocal(clientX, clientY, sheet, w0, h0) {
        var r = sheet.getBoundingClientRect();
        var mcx = r.left + r.width / 2;
        var mcy = r.top + r.height / 2;
        var mx = clientX - mcx;
        var my = clientY - mcy;
        var lx = w0 / 2 - my;
        var ly = mx + h0 / 2;
        if (lx < 0) {
            lx = 0;
        }
        if (lx > w0) {
            lx = w0;
        }
        if (ly < 0) {
            ly = 0;
        }
        if (ly > h0) {
            ly = h0;
        }
        return { lx: lx, ly: ly };
    }

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
            if (!img || img.style.display === 'none') {
                return;
            }
            var sheet = activeDrag.sheet;
            var pw = activeDrag.pw;
            var ph = activeDrag.ph;
            if (!sheet || !(pw > 0) || !(ph > 0)) {
                return;
            }
            var loc = clientToPortraitLocal(ev.clientX, ev.clientY, sheet, pw, ph);
            var l = Math.min(activeDrag.startLx, loc.lx);
            var t = Math.min(activeDrag.startLy, loc.ly);
            var w = Math.abs(loc.lx - activeDrag.startLx);
            var h = Math.abs(loc.ly - activeDrag.startLy);
            var rubber = activeDrag.rubberEl;
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
            var sheet = activeDrag.sheet;
            var layoutInput = activeDrag.layoutInput;
            var defaultsJson = activeDrag.defaultsJson;
            var pw = activeDrag.pw;
            var ph = activeDrag.ph;
            activeDrag.drawActive = false;
            var loc = clientToPortraitLocal(ev.clientX, ev.clientY, sheet, pw, ph);
            var boxMm = pxRectToMm(activeDrag.startLx, activeDrag.startLy, loc.lx, loc.ly, { width: pw, height: ph });
            var rubber = activeDrag.rubberEl;
            if (rubber) {
                rubber.style.display = 'none';
                rubber.style.width = '0';
                rubber.style.height = '0';
            }
            if (sheet.releasePointerCapture && ev.pointerId != null) {
                try {
                    sheet.releasePointerCapture(ev.pointerId);
                } catch (e) {
                    /* ignore */
                }
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
                var rubber = activeDrag.rubberEl;
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

    function mmRectToPx(box, portraitPxRect) {
        return {
            left: (box.x / PAGE_W_MM) * portraitPxRect.width,
            top: (box.y / PAGE_H_MM) * portraitPxRect.height,
            width: (box.w / PAGE_W_MM) * portraitPxRect.width,
            height: (box.h / PAGE_H_MM) * portraitPxRect.height
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
        var sheet = root.querySelector('.cert-lp-sheet');
        var img = root.querySelector('.cert-lp-img');
        var rubber = root.querySelector('.cert-lp-rubber');
        var rectFinal = root.querySelector('.cert-lp-rect-final');
        var ghost = root.querySelector('.cert-lp-ghost');
        var notePdf = root.querySelector('.cert-lp-note-pdf');
        var objectUrl = null;

        if (ghost) {
            ghost.textContent = sampleText;
        }

        var reviewTools = root.querySelector('.cert-lp-review-tools');
        var btnCropOpen = root.querySelector('.cert-lp-crop-open');
        var cropPanel = root.querySelector('.cert-lp-crop-panel');
        var cropTarget = root.querySelector('.cert-lp-crop-target');
        var btnCropApply = root.querySelector('.cert-lp-crop-apply');
        var btnCropCancel = root.querySelector('.cert-lp-crop-cancel');
        var btnCropRotL = root.querySelector('.cert-lp-crop-rotate-left');
        var btnCropRotR = root.querySelector('.cert-lp-crop-rotate-right');
        var cropperInstance = null;

        function ensureCropperAssets(cb) {
            if (typeof window.Cropper === 'function') {
                cb();
                return;
            }
            if (!window._certLpCropperLoading) {
                window._certLpCropperLoading = true;
                window._certLpCropperQueue = [];
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css';
                link.crossOrigin = 'anonymous';
                document.head.appendChild(link);
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js';
                script.crossOrigin = 'anonymous';
                script.onload = function () {
                    window._certLpCropperLoading = false;
                    cb();
                    (window._certLpCropperQueue || []).forEach(function (fn) {
                        fn();
                    });
                    window._certLpCropperQueue = [];
                };
                script.onerror = function () {
                    window._certLpCropperLoading = false;
                    window.alert('โหลดเครื่องมือครอบภาพไม่สำเร็จ กรุณาลองใหม่หรือตรวจสอบการเชื่อมต่ออินเทอร์เน็ต');
                };
                document.head.appendChild(script);
                return;
            }
            window._certLpCropperQueue = window._certLpCropperQueue || [];
            window._certLpCropperQueue.push(cb);
        }

        function destroyCropperInstance() {
            if (cropperInstance) {
                try {
                    cropperInstance.destroy();
                } catch (e) {
                    /* ignore */
                }
                cropperInstance = null;
            }
        }

        function showReviewTools(visible) {
            if (reviewTools) {
                reviewTools.style.display = visible ? 'block' : 'none';
            }
            if (!visible) {
                destroyCropperInstance();
                if (cropPanel) {
                    cropPanel.style.display = 'none';
                }
                if (cropTarget) {
                    cropTarget.removeAttribute('src');
                }
            }
        }

        function initCropperOnImage() {
            if (!cropTarget || !img || !img.src || img.style.display === 'none') {
                return;
            }
            destroyCropperInstance();
            if (cropPanel) {
                cropPanel.style.display = 'block';
            }
            ensureCropperAssets(function () {
                destroyCropperInstance();
                var loader = new Image();
                loader.crossOrigin = 'anonymous';
                loader.onload = function () {
                    cropTarget.src = loader.src;
                    try {
                        cropperInstance = new window.Cropper(cropTarget, {
                            aspectRatio: 210 / 297,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.92,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: true,
                            background: true,
                            toggleDragModeOnDblclick: false
                        });
                    } catch (e) {
                        window.alert('ไม่สามารถเปิดตัวครอบภาพได้');
                    }
                };
                loader.onerror = function () {
                    window.alert('โหลดภาพสำหรับครอบไม่สำเร็จ');
                };
                loader.src = img.src;
            });
        }

        if (btnCropOpen && cropTarget && cropPanel) {
            btnCropOpen.addEventListener('click', function () {
                if (!img || !img.src || img.style.display === 'none') {
                    window.alert('กรุณาอัปโหลดรูป JPG หรือ PNG ก่อน');
                    return;
                }
                if (cropPanel.style.display === 'none' || cropPanel.style.display === '') {
                    initCropperOnImage();
                } else {
                    destroyCropperInstance();
                    cropPanel.style.display = 'none';
                    if (cropTarget) {
                        cropTarget.removeAttribute('src');
                    }
                }
            });
        }
        if (btnCropRotL) {
            btnCropRotL.addEventListener('click', function () {
                if (cropperInstance) {
                    cropperInstance.rotate(-90);
                }
            });
        }
        if (btnCropRotR) {
            btnCropRotR.addEventListener('click', function () {
                if (cropperInstance) {
                    cropperInstance.rotate(90);
                }
            });
        }
        if (btnCropCancel && cropPanel && cropTarget) {
            btnCropCancel.addEventListener('click', function () {
                destroyCropperInstance();
                cropPanel.style.display = 'none';
                cropTarget.removeAttribute('src');
            });
        }
        if (btnCropApply && cropTarget && fileInput) {
            btnCropApply.addEventListener('click', function () {
                if (!cropperInstance) {
                    return;
                }
                var canvas = cropperInstance.getCroppedCanvas({
                    width: 2310,
                    height: 3267,
                    imageSmoothingQuality: 'high',
                    fillColor: '#ffffff'
                });
                if (!canvas) {
                    window.alert('ไม่สามารถสร้างภาพจากกรอบครอบได้');
                    return;
                }
                canvas.toBlob(function (blob) {
                    if (!blob) {
                        window.alert('ส่งออกภาพไม่สำเร็จ');
                        return;
                    }
                    destroyCropperInstance();
                    cropPanel.style.display = 'none';
                    cropTarget.removeAttribute('src');
                    var base = (fileInput.files && fileInput.files[0] && fileInput.files[0].name)
                        ? fileInput.files[0].name.replace(/\.[^.]+$/, '')
                        : 'certificate-background';
                    var file = new File([blob], base + '-a4.png', { type: 'image/png' });
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }, 'image/png', 0.95);
            });
        }

        function layoutSheet() {
            if (!stage || !sheet) {
                return;
            }
            var sw = stage.clientWidth;
            if (sw < 8) {
                return;
            }
            var s = sw / 297;
            var w0 = 210 * s;
            var h0 = 297 * s;
            sheet.style.width = w0 + 'px';
            sheet.style.height = h0 + 'px';
            sheet.dataset.lpW0 = String(w0);
            sheet.dataset.lpH0 = String(h0);
        }

        function getPortraitPx() {
            if (!sheet) {
                return { w0: 0, h0: 0 };
            }
            layoutSheet();
            var w0 = parseFloat(sheet.dataset.lpW0 || '0');
            var h0 = parseFloat(sheet.dataset.lpH0 || '0');
            return { w0: w0, h0: h0 };
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
            var dims = getPortraitPx();
            if (!(dims.w0 > 0) || !(dims.h0 > 0)) {
                return;
            }
            var px = mmRectToPx(boxMm, { width: dims.w0, height: dims.h0 });
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
                ghost.style.writingMode = 'horizontal-tb';
                ghost.style.textOrientation = 'mixed';
                ghost.style.wordBreak = 'keep-all';
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

        function setImageSrc(src, onAfterLoad) {
            if (!img) {
                return;
            }
            var prevBlob = objectUrl;
            objectUrl = null;
            if (prevBlob && prevBlob !== src) {
                URL.revokeObjectURL(prevBlob);
            }
            if (typeof src === 'string' && src.indexOf('blob:') === 0) {
                objectUrl = src;
            }
            img.onload = function () {
                img.style.display = 'block';
                if (notePdf) {
                    notePdf.style.display = 'none';
                }
                showReviewTools(true);
                layoutSheet();
                syncFromLayoutInput();
                if (typeof onAfterLoad === 'function') {
                    onAfterLoad();
                }
            };
            img.onerror = function () {
                img.style.display = 'none';
                showReviewTools(false);
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
                showReviewTools(false);
                if (stageWrap) {
                    stageWrap.style.display = 'none';
                }
                hideFinalRect();
                showRubber(0, 0, 0, 0);
                return;
            }
            if (type.indexOf('image/') === 0) {
                objectUrl = URL.createObjectURL(f);
                setImageSrc(objectUrl, function () {
                    if (stageWrap && btnOpen) {
                        stageWrap.style.display = 'block';
                        btnOpen.setAttribute('aria-expanded', 'true');
                        try {
                            stageWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } catch (e) {
                            stageWrap.scrollIntoView(false);
                        }
                    }
                });
            }
        }

        if (fileInput) {
            fileInput.addEventListener('change', handleFileChange);
        }

        if (previewUrl) {
            setImageSrc(previewUrl);
        }

        if (typeof ResizeObserver !== 'undefined' && stage) {
            var ro = new ResizeObserver(function () {
                layoutSheet();
                syncFromLayoutInput();
            });
            ro.observe(stage);
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
                    window.requestAnimationFrame(function () {
                        layoutSheet();
                        syncFromLayoutInput();
                    });
                }
            });
        }

        if (sheet && img) {
            sheet.addEventListener('pointerdown', function (ev) {
                if (img.style.display === 'none' || ev.button !== 0) {
                    return;
                }
                layoutSheet();
                var w0 = parseFloat(sheet.dataset.lpW0 || '0');
                var h0 = parseFloat(sheet.dataset.lpH0 || '0');
                if (!(w0 > 0) || !(h0 > 0)) {
                    return;
                }
                var loc = clientToPortraitLocal(ev.clientX, ev.clientY, sheet, w0, h0);
                activeDrag = {
                    root: root,
                    sheet: sheet,
                    img: img,
                    rubberEl: rubber,
                    layoutInput: layoutInput,
                    defaultsJson: defaultsJson,
                    pw: w0,
                    ph: h0,
                    startLx: loc.lx,
                    startLy: loc.ly,
                    drawActive: true,
                    syncFromLayout: syncFromLayoutInput
                };
                showRubber(loc.lx, loc.ly, 0, 0);
                if (sheet.setPointerCapture) {
                    sheet.setPointerCapture(ev.pointerId);
                }
                ev.preventDefault();
            });
        }

        layoutInput.addEventListener('change', function () {
            syncFromLayoutInput();
        });

        window.addEventListener('resize', function () {
            layoutSheet();
            syncFromLayoutInput();
        });

        layoutSheet();
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
