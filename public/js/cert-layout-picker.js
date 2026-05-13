/**
 * ระบุตำแหน่งชื่อผู้รับบนแม่แบบ — พิกัดเป็น mm บนหน้า A4
 * - แนวตั้ง: 210×297 mm (sheet หมุน -90° บนพื้นที่ลาก)
 * - แนวนอน: 297×210 mm (ไม่หมุน) — ค่า page_orientation ใน layout_json
 */
(function (window) {
    'use strict';

    var MM_PORTRAIT_W = 210;
    var MM_PORTRAIT_H = 297;
    var MM_LANDSCAPE_W = 297;
    var MM_LANDSCAPE_H = 210;

    /** @type {Record<string, unknown>|null} */
    var activeDrag = null;

    function getPageMm(layoutObj) {
        var o = layoutObj && layoutObj.page_orientation;
        if (o === 'landscape') {
            return { wMm: MM_LANDSCAPE_W, hMm: MM_LANDSCAPE_H, mode: 'landscape' };
        }
        return { wMm: MM_PORTRAIT_W, hMm: MM_PORTRAIT_H, mode: 'portrait' };
    }

    /**
     * แปลงพิกัดหน้าจอ → พิกัดในระบบแผ่นแนวตั้งก่อนหมุน (px) — sheet หมุน -90°
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

    /** แนวนอน A4 — ไม่หมุน แมปตรงจาก rect ของ sheet */
    function clientToFlatLocal(clientX, clientY, sheet, w0, h0) {
        var r = sheet.getBoundingClientRect();
        var lx = (clientX - r.left) * (w0 / r.width);
        var ly = (clientY - r.top) * (h0 / r.height);
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
            if (!sheet || !(pw > 0) || !(ph > 0) || typeof activeDrag.pointerClientToLocal !== 'function') {
                return;
            }
            var loc = activeDrag.pointerClientToLocal(ev.clientX, ev.clientY);
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
            var sheet = activeDrag.sheet;
            var layoutInput = activeDrag.layoutInput;
            var defaultsJson = activeDrag.defaultsJson;
            var pw = activeDrag.pw;
            var ph = activeDrag.ph;
            activeDrag.drawActive = false;
            var loc =
                typeof activeDrag.pointerClientToLocal === 'function'
                    ? activeDrag.pointerClientToLocal(ev.clientX, ev.clientY)
                    : null;
            var boxMm =
                loc && layoutInput
                    ? pxRectToMm(
                          activeDrag.startLx,
                          activeDrag.startLy,
                          loc.lx,
                          loc.ly,
                          { width: pw, height: ph },
                          activeDrag.pageWMm,
                          activeDrag.pageHMm
                      )
                    : null;
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

    function pxRectToMm(x0, y0, x1, y1, rect, pageWMm, pageHMm) {
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
            x: (l / rect.width) * pageWMm,
            y: (t / rect.height) * pageHMm,
            w: (w / rect.width) * pageWMm,
            h: (h / rect.height) * pageHMm
        };
    }

    function mmRectToPx(box, pxRect, pageWMm, pageHMm) {
        return {
            left: (box.x / pageWMm) * pxRect.width,
            top: (box.y / pageHMm) * pxRect.height,
            width: (box.w / pageWMm) * pxRect.width,
            height: (box.h / pageHMm) * pxRect.height
        };
    }

    function mergePageOrientation(layoutInput, defaultsJson, orientation, prevOrientation) {
        var defaults = safeJsonParse(defaultsJson, {});
        var current = safeJsonParse(String(layoutInput.value || '').trim(), {});
        var prev = prevOrientation || current.page_orientation || 'portrait';
        var out = Object.assign({}, defaults, current);
        out.field_mapping = Object.assign(
            {},
            defaults.field_mapping || {},
            current.field_mapping || {}
        );
        out.page_orientation = orientation;
        if (prev !== orientation && out.field_mapping.student_name) {
            delete out.field_mapping.student_name;
        }
        layoutInput.value = JSON.stringify(out);
        layoutInput.dispatchEvent(new Event('change', { bubbles: true }));
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

        var cropOrientRadioName = hiddenId ? hiddenId + '__crop_orient' : '';

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

        function readLayoutObj() {
            return safeJsonParse(String(layoutInput.value || '').trim(), safeJsonParse(defaultsJson, {}));
        }

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
                cropTarget.removeAttribute('crossorigin');
                function buildCropper() {
                    try {
                        var c = new window.Cropper(cropTarget, {
                            aspectRatio: NaN,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.75,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: true,
                            background: true,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: true,
                            ready: function () {
                                try {
                                    c.setAspectRatio(NaN);
                                } catch (e2) {
                                    /* ignore */
                                }
                            }
                        });
                        cropperInstance = c;
                    } catch (e) {
                        window.alert('ไม่สามารถเปิดตัวครอบภาพได้');
                    }
                }
                function afterImagePainted() {
                    window.requestAnimationFrame(function () {
                        window.requestAnimationFrame(buildCropper);
                    });
                }
                if (cropTarget.src === img.src && cropTarget.complete && cropTarget.naturalWidth > 0) {
                    afterImagePainted();
                    return;
                }
                cropTarget.onload = function () {
                    cropTarget.onload = null;
                    cropTarget.onerror = null;
                    afterImagePainted();
                };
                cropTarget.onerror = function () {
                    cropTarget.onload = null;
                    cropTarget.onerror = null;
                    window.alert('โหลดภาพสำหรับครอบไม่สำเร็จ');
                };
                cropTarget.src = img.src;
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
        function freeCropAspect() {
            if (!cropperInstance) {
                return;
            }
            try {
                cropperInstance.setAspectRatio(NaN);
            } catch (e) {
                /* ignore */
            }
        }

        if (btnCropRotL) {
            btnCropRotL.addEventListener('click', function () {
                if (cropperInstance) {
                    cropperInstance.rotate(-90);
                    freeCropAspect();
                }
            });
        }
        if (btnCropRotR) {
            btnCropRotR.addEventListener('click', function () {
                if (cropperInstance) {
                    cropperInstance.rotate(90);
                    freeCropAspect();
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
                    maxWidth: 4096,
                    maxHeight: 4096,
                    imageSmoothingQuality: 'high',
                    fillColor: '#ffffff'
                });
                if (!canvas) {
                    window.alert('ไม่สามารถสร้างภาพจากกรอบครอบได้');
                    return;
                }
                var prevLayout = readLayoutObj();
                var prevO = prevLayout.page_orientation || 'portrait';
                var choice = 'auto';
                if (cropOrientRadioName) {
                    var sel = root.querySelector('input[name="' + cropOrientRadioName + '"]:checked');
                    if (sel && sel.value) {
                        choice = sel.value;
                    }
                }
                var orient;
                if (choice === 'portrait') {
                    orient = 'portrait';
                } else if (choice === 'landscape') {
                    orient = 'landscape';
                } else {
                    orient = canvas.width > canvas.height ? 'landscape' : 'portrait';
                }
                mergePageOrientation(layoutInput, defaultsJson, orient, prevO);
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
                    var file = new File([blob], base + '-layout.png', { type: 'image/png' });
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
            var pm = getPageMm(readLayoutObj());
            if (pm.mode === 'landscape') {
                sheet.style.transform = 'translate(-50%, -50%) rotate(0deg)';
                var sh = stage.clientHeight;
                sheet.style.width = sw + 'px';
                sheet.style.height = sh + 'px';
                sheet.dataset.lpW0 = String(sw);
                sheet.dataset.lpH0 = String(sh);
                sheet.dataset.lpMode = 'flat';
            } else {
                sheet.style.transform = 'translate(-50%, -50%) rotate(-90deg)';
                var s = sw / 297;
                var w0 = 210 * s;
                var h0 = 297 * s;
                sheet.style.width = w0 + 'px';
                sheet.style.height = h0 + 'px';
                sheet.dataset.lpW0 = String(w0);
                sheet.dataset.lpH0 = String(h0);
                sheet.dataset.lpMode = 'rotated';
            }
        }

        function getSheetPx() {
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
            var dims = getSheetPx();
            if (!(dims.w0 > 0) || !(dims.h0 > 0)) {
                return;
            }
            var pm = getPageMm(readLayoutObj());
            var px = mmRectToPx(boxMm, { width: dims.w0, height: dims.h0 }, pm.wMm, pm.hMm);
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
            var layout = readLayoutObj();
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
                var pm = getPageMm(readLayoutObj());
                var flat = sheet.dataset.lpMode === 'flat';
                var loc = flat ? clientToFlatLocal(ev.clientX, ev.clientY, sheet, w0, h0) : clientToPortraitLocal(ev.clientX, ev.clientY, sheet, w0, h0);
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
                    syncFromLayout: syncFromLayoutInput,
                    pageWMm: pm.wMm,
                    pageHMm: pm.hMm,
                    pointerClientToLocal: function (cx, cy) {
                        return flat ? clientToFlatLocal(cx, cy, sheet, w0, h0) : clientToPortraitLocal(cx, cy, sheet, w0, h0);
                    }
                };
                showRubber(loc.lx, loc.ly, 0, 0);
                if (sheet.setPointerCapture) {
                    sheet.setPointerCapture(ev.pointerId);
                }
                ev.preventDefault();
            });
        }

        layoutInput.addEventListener('change', function () {
            layoutSheet();
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
