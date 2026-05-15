/**
 * Cert Event Wizard — 5-step flow for creating/editing certificate events.
 *
 * Steps:
 *   1) Info (title, description, date)
 *   2) Upload + orientation (JPG/PNG only, choose A4 portrait/landscape)
 *   3) Crop + rotate (Cropper.js, locked to A4 ratio from step 2)
 *   4) Drag-to-position name box on cropped image
 *   5) Confirm + submit
 *
 * Public API: window.CertEventWizard.init('#cewForm')
 */
(function (window) {
    'use strict';

    var CROPPER_CSS = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css';
    var CROPPER_JS  = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js';

    function init(selector) {
        var form = document.querySelector(selector);
        if (!form || form.dataset.cewInited === '1') { return; }
        form.dataset.cewInited = '1';
        new Wizard(form);
    }

    function ensureCropper(cb) {
        if (typeof window.Cropper === 'function') { cb(); return; }
        if (window._cewCropperLoading) {
            (window._cewCropperQueue = window._cewCropperQueue || []).push(cb);
            return;
        }
        window._cewCropperLoading = true;
        window._cewCropperQueue = [cb];
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = CROPPER_CSS;
        document.head.appendChild(link);
        var script = document.createElement('script');
        script.src = CROPPER_JS;
        script.onload = function () {
            window._cewCropperLoading = false;
            (window._cewCropperQueue || []).forEach(function (fn) { try { fn(); } catch (e) { /* ignore */ } });
            window._cewCropperQueue = [];
        };
        script.onerror = function () {
            window._cewCropperLoading = false;
            window.alert('โหลดเครื่องมือครอบภาพไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต');
        };
        document.head.appendChild(script);
    }

    function Wizard(form) {
        this.form = form;
        this.editMode = form.dataset.editMode === '1';
        this.existingBgUrl = form.dataset.existingBgUrl || '';
        this.existingBgKind = form.dataset.existingBgKind || '';
        this.initOrientation = form.dataset.initOrientation || 'portrait';
        this.orientation = this.initOrientation;

        /* Step nav */
        this.indicators = form.querySelectorAll('[data-step-indicator]');
        this.sections   = form.querySelectorAll('[data-step]');
        this.btnBack    = form.querySelector('#cewBack');
        this.btnNext    = form.querySelector('#cewNext');
        this.btnSubmit  = form.querySelector('#cewSubmit');

        /* Step 1 */
        this.inputTitle = form.querySelector('#cewTitle');
        this.inputDesc  = form.querySelector('#cewDescription');
        this.inputDate  = form.querySelector('#cewEventDate');

        /* Step 2 */
        this.fileInput  = form.querySelector('#cewFile');
        this.orientRadios = form.querySelectorAll('input[name="cew_orientation"]');
        this.orientTiles  = form.querySelectorAll('.cew-orient-tile');

        /* Step 3 */
        this.cropTarget = form.querySelector('.cew-crop-target');

        /* Step 4 */
        this.positionStage = form.querySelector('[data-role="position-stage"]');
        this.positionSheet = form.querySelector('[data-role="position-sheet"]');
        this.positionImg   = this.positionSheet.querySelector('.cew-stage-img');
        this.rubber        = this.positionSheet.querySelector('.cew-rubber');
        this.positionRect  = this.positionSheet.querySelector('.cew-rect');
        this.positionGhost = this.positionSheet.querySelector('.cew-ghost');

        /* Step 5 */
        this.previewStage = form.querySelector('[data-role="preview-stage"]');
        this.previewImg   = this.previewStage.querySelector('.cew-stage-img');
        this.previewRect  = this.previewStage.querySelector('.cew-rect');
        this.previewGhost = this.previewStage.querySelector('.cew-ghost');
        this.summaryEls   = form.querySelectorAll('[data-summary]');

        /* Hidden */
        this.layoutJsonInput = form.querySelector('#cewLayoutJson');

        /* State */
        this.currentStep   = 1;
        this.cropper       = null;
        this.workingImgUrl = '';   /* URL displayed in step 4 (blob: หรือ remote) */
        this.workingIsBlob = false;
        this.nameBoxMm     = null; /* {x,y,w,h,font_size} */
        this.cropToolsBound = false;
        this.dragState     = null;

        /* Parse initial layout */
        try {
            var lj = JSON.parse(form.dataset.initLayoutJson || '{}');
            if (lj && lj.field_mapping && lj.field_mapping.student_name) {
                var sn = lj.field_mapping.student_name;
                if (typeof sn.x === 'number' && typeof sn.y === 'number') {
                    this.nameBoxMm = {
                        x: Number(sn.x),
                        y: Number(sn.y),
                        w: Number(sn.box_w || 95),
                        h: Number(sn.box_h || 12),
                        font_size: Number(sn.font_size || 22)
                    };
                }
            }
        } catch (e) { /* ignore */ }

        /* Edit mode with existing image → use as initial working image */
        if (this.editMode && this.existingBgUrl && this.existingBgKind === 'image') {
            this.workingImgUrl = this.existingBgUrl;
            this.workingIsBlob = false;
        }

        this.bindEvents();
        this.applyOrientationToStages();
        this.updateOrientTileUI();
        this.gotoStep(1);
    }

    Wizard.prototype.bindEvents = function () {
        var self = this;

        this.btnBack.addEventListener('click', function () { self.goBack(); });
        this.btnNext.addEventListener('click', function () { self.tryAdvance(); });

        this.fileInput.addEventListener('change', function () { self.handleFileChange(); });

        this.orientRadios.forEach(function (r) {
            r.addEventListener('change', function () {
                if (!r.checked) { return; }
                self.orientation = r.value;
                self.applyOrientationToStages();
                self.updateOrientTileUI();
            });
        });

        this.bindStageDrag();

        this.form.addEventListener('submit', function () { self.serializeLayoutJson(); });
    };

    /* ========== Step navigation ========== */

    Wizard.prototype.gotoStep = function (n) {
        if (n < 1) { n = 1; }
        if (n > 5) { n = 5; }
        this.currentStep = n;

        this.sections.forEach(function (el) {
            el.hidden = (parseInt(el.dataset.step, 10) !== n);
        });
        this.indicators.forEach(function (el) {
            var i = parseInt(el.dataset.stepIndicator, 10);
            el.classList.toggle('is-current', i === n);
            el.classList.toggle('is-done', i < n);
        });

        this.btnBack.hidden   = (n === 1);
        this.btnNext.hidden   = (n === 5);
        this.btnSubmit.hidden = (n !== 5);

        if (n === 3) { this.enterCropStep(); }
        if (n === 4) { this.enterPositionStep(); }
        if (n === 5) { this.enterConfirmStep(); }
    };

    Wizard.prototype.tryAdvance = function () {
        var err = null;
        switch (this.currentStep) {
            case 1: err = this.validateStep1(); break;
            case 2: err = this.validateStep2(); break;
            case 3: this.applyCropAndAdvance(); return; /* async */
            case 4: err = this.validateStep4(); break;
            default: break;
        }
        this.showError(this.currentStep, err);
        if (err) { return; }

        /* Skip step 3 if no re-crop is needed (edit mode, no new file, orientation unchanged) */
        if (this.currentStep === 2 && this.canSkipCropStep()) {
            this.gotoStep(4);
            return;
        }
        this.gotoStep(this.currentStep + 1);
    };

    Wizard.prototype.goBack = function () {
        /* Symmetric skip: step 4 → step 2 if crop step was skipped going forward */
        if (this.currentStep === 4 && this.canSkipCropStep()) {
            this.gotoStep(2);
            return;
        }
        this.gotoStep(this.currentStep - 1);
    };

    Wizard.prototype.canSkipCropStep = function () {
        if (!this.editMode) { return false; }
        if (this.fileInput.files && this.fileInput.files[0]) { return false; }
        if (!this.existingBgUrl) { return false; }
        if (this.existingBgKind !== 'image') { return false; }
        if (this.orientation !== this.initOrientation) { return false; }
        return true;
    };

    Wizard.prototype.showError = function (step, msg) {
        var el = this.form.querySelector('[data-error-for="' + step + '"]');
        if (el) { el.textContent = msg || ''; }
    };

    /* ========== Step 1 validation ========== */

    Wizard.prototype.validateStep1 = function () {
        var t = (this.inputTitle.value || '').trim();
        if (t.length < 3) { return 'กรุณากรอกชื่อกิจกรรมอย่างน้อย 3 ตัวอักษร'; }
        return null;
    };

    /* ========== Step 2: file + orientation ========== */

    Wizard.prototype.validateStep2 = function () {
        var newFile = this.fileInput.files && this.fileInput.files[0];
        var hasUsableExisting = this.editMode && this.existingBgUrl && this.existingBgKind === 'image';
        if (!newFile && !hasUsableExisting) {
            return 'กรุณาอัปโหลดไฟล์ JPG หรือ PNG';
        }
        if (newFile) {
            if (/pdf$/i.test(newFile.name) || newFile.type === 'application/pdf') {
                return 'ระบบไม่รองรับไฟล์ PDF กรุณาใช้ JPG หรือ PNG';
            }
            if (!/^image\/(jpeg|png)$/.test(newFile.type) && !/\.(jpg|jpeg|png)$/i.test(newFile.name)) {
                return 'รองรับเฉพาะ JPG และ PNG';
            }
        }
        var orient = this.form.querySelector('input[name="cew_orientation"]:checked');
        if (!orient) { return 'กรุณาเลือกแนวกระดาษ A4'; }
        this.orientation = orient.value;
        return null;
    };

    Wizard.prototype.handleFileChange = function () {
        var f = this.fileInput.files && this.fileInput.files[0];
        if (!f) { return; }
        if (/pdf$/i.test(f.name) || f.type === 'application/pdf') {
            this.showError(2, 'ระบบไม่รองรับไฟล์ PDF กรุณาใช้ JPG หรือ PNG');
            this.fileInput.value = '';
            return;
        }
        if (!/^image\/(jpeg|png)$/.test(f.type) && !/\.(jpg|jpeg|png)$/i.test(f.name)) {
            this.showError(2, 'รองรับเฉพาะ JPG และ PNG');
            this.fileInput.value = '';
            return;
        }
        this.showError(2, '');

        /* Auto-detect orientation from natural image dimensions */
        var self = this;
        var probeUrl = URL.createObjectURL(f);
        var probe = new Image();
        probe.onload = function () {
            var auto = probe.naturalWidth > probe.naturalHeight ? 'landscape' : 'portrait';
            self.setOrientation(auto);
            URL.revokeObjectURL(probeUrl);
        };
        probe.onerror = function () { URL.revokeObjectURL(probeUrl); };
        probe.src = probeUrl;
    };

    Wizard.prototype.setOrientation = function (o) {
        this.orientation = o;
        this.orientRadios.forEach(function (r) { r.checked = (r.value === o); });
        this.applyOrientationToStages();
        this.updateOrientTileUI();
    };

    Wizard.prototype.updateOrientTileUI = function () {
        var o = this.orientation;
        this.orientTiles.forEach(function (t) {
            t.classList.toggle('is-active', t.dataset.value === o);
        });
    };

    Wizard.prototype.applyOrientationToStages = function () {
        var o = this.orientation;
        var w = o === 'landscape' ? 297 : 210;
        var h = o === 'landscape' ? 210 : 297;
        var maxW = o === 'landscape' ? '900px' : '520px';
        [this.positionStage, this.previewStage].forEach(function (s) {
            if (!s) { return; }
            s.style.aspectRatio = w + ' / ' + h;
            s.style.maxWidth = maxW;
            s.style.width = '100%';
        });
    };

    Wizard.prototype.pageMm = function () {
        return this.orientation === 'landscape' ? { w: 297, h: 210 } : { w: 210, h: 297 };
    };

    /* ========== Step 3: Crop & Rotate ========== */

    Wizard.prototype.enterCropStep = function () {
        var self = this;
        /* Choose source: new file (blob) > existing remote URL */
        var srcUrl = null;
        var f = this.fileInput.files && this.fileInput.files[0];
        if (f) {
            srcUrl = URL.createObjectURL(f);
            this.cropTarget.dataset.cewSrcIsBlob = '1';
        } else if (this.existingBgUrl && this.existingBgKind === 'image') {
            srcUrl = this.existingBgUrl;
            this.cropTarget.dataset.cewSrcIsBlob = '0';
        }
        if (!srcUrl) {
            this.showError(3, 'ไม่พบไฟล์รูปสำหรับครอบ — กรุณาย้อนกลับไปอัปโหลด');
            return;
        }

        /* Bind tool buttons once */
        if (!this.cropToolsBound) {
            this.cropToolsBound = true;
            this.form.querySelector('[data-action="rotate-left"]').addEventListener('click', function () {
                if (self.cropper) { self.cropper.rotate(-90); self.reshapeCropBox(); }
            });
            this.form.querySelector('[data-action="rotate-right"]').addEventListener('click', function () {
                if (self.cropper) { self.cropper.rotate(90); self.reshapeCropBox(); }
            });
            this.form.querySelector('[data-action="reset-crop"]').addEventListener('click', function () {
                if (self.cropper) { self.cropper.reset(); self.reshapeCropBox(); }
            });
        }

        /* Load image then init Cropper */
        this.cropTarget.onload = function () {
            self.cropTarget.onload = null;
            self.initCropper();
        };
        this.cropTarget.onerror = function () {
            self.cropTarget.onerror = null;
            self.showError(3, 'โหลดภาพไม่สำเร็จ');
        };
        if (this.cropTarget.src === srcUrl && this.cropTarget.complete && this.cropTarget.naturalWidth > 0) {
            this.initCropper();
        } else {
            this.cropTarget.src = srcUrl;
        }
    };

    Wizard.prototype.cropAspectRatio = function () {
        return this.orientation === 'landscape' ? 297 / 210 : 210 / 297;
    };

    Wizard.prototype.initCropper = function () {
        var self = this;
        if (this.cropper) {
            try { this.cropper.destroy(); } catch (e) { /* ignore */ }
            this.cropper = null;
        }
        ensureCropper(function () {
            var ratio = self.cropAspectRatio();
            try {
                self.cropper = new window.Cropper(self.cropTarget, {
                    aspectRatio: ratio,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: true,
                    background: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    ready: function () { self.reshapeCropBox(); }
                });
            } catch (e) {
                self.showError(3, 'เปิดเครื่องมือครอบภาพไม่สำเร็จ');
            }
        });
    };

    Wizard.prototype.reshapeCropBox = function () {
        if (!this.cropper) { return; }
        var ratio = this.cropAspectRatio();
        try {
            this.cropper.setAspectRatio(ratio);
            var canvas = this.cropper.getCanvasData();
            var cw = canvas.width, ch = canvas.height;
            var boxW, boxH;
            if (cw / ch > ratio) {
                boxH = ch * 0.9;
                boxW = boxH * ratio;
            } else {
                boxW = cw * 0.9;
                boxH = boxW / ratio;
            }
            this.cropper.setCropBoxData({
                left: canvas.left + (cw - boxW) / 2,
                top: canvas.top + (ch - boxH) / 2,
                width: boxW,
                height: boxH
            });
        } catch (e) { /* ignore */ }
    };

    Wizard.prototype.applyCropAndAdvance = function () {
        var self = this;
        if (!this.cropper) {
            this.showError(3, 'ไม่พบเครื่องมือครอบ');
            return;
        }
        var canvas;
        try {
            canvas = this.cropper.getCroppedCanvas({
                maxWidth: 4096,
                maxHeight: 4096,
                imageSmoothingQuality: 'high',
                fillColor: '#ffffff'
            });
        } catch (e) {
            this.showError(3, 'ไม่สามารถสร้างภาพจากกรอบครอบได้');
            return;
        }
        if (!canvas) {
            this.showError(3, 'ไม่สามารถสร้างภาพจากกรอบครอบได้');
            return;
        }
        canvas.toBlob(function (blob) {
            if (!blob) {
                self.showError(3, 'ส่งออกภาพไม่สำเร็จ');
                return;
            }
            var origName = 'certificate-background';
            if (self.fileInput.files && self.fileInput.files[0]) {
                origName = self.fileInput.files[0].name.replace(/\.[^.]+$/, '');
            }
            var newFile = new File([blob], origName + '-cropped.png', { type: 'image/png' });
            try {
                var dt = new DataTransfer();
                dt.items.add(newFile);
                self.fileInput.files = dt.files;
            } catch (e) {
                /* DataTransfer not supported — fall back: blob still used for preview, file submit may fail */
            }
            if (self.workingIsBlob && self.workingImgUrl) {
                URL.revokeObjectURL(self.workingImgUrl);
            }
            self.workingImgUrl = URL.createObjectURL(blob);
            self.workingIsBlob = true;
            self.showError(3, '');
            self.gotoStep(4);
        }, 'image/png', 0.95);
    };

    /* ========== Step 4: Position name box ========== */

    Wizard.prototype.enterPositionStep = function () {
        if (!this.workingImgUrl) {
            this.showError(4, 'ไม่พบภาพแม่แบบสำหรับวางตำแหน่ง');
            return;
        }
        this.positionImg.src = this.workingImgUrl;
        this.applyOrientationToStages();
        var self = this;
        window.requestAnimationFrame(function () { self.renderNameBox(); });
    };

    Wizard.prototype.bindStageDrag = function () {
        var self = this;

        function localPos(ev) {
            var r = self.positionSheet.getBoundingClientRect();
            var x = Math.max(0, Math.min(r.width, ev.clientX - r.left));
            var y = Math.max(0, Math.min(r.height, ev.clientY - r.top));
            return { x: x, y: y, w: r.width, h: r.height };
        }

        this.positionSheet.addEventListener('pointerdown', function (ev) {
            if (ev.button !== 0) { return; }
            if (!self.positionImg.src) { return; }
            if (self.currentStep !== 4) { return; }
            var p = localPos(ev);
            self.dragState = { sx: p.x, sy: p.y };
            self.rubber.hidden = false;
            self.rubber.style.left = p.x + 'px';
            self.rubber.style.top = p.y + 'px';
            self.rubber.style.width = '0';
            self.rubber.style.height = '0';
            try { self.positionSheet.setPointerCapture(ev.pointerId); } catch (e) { /* ignore */ }
            ev.preventDefault();
        });

        this.positionSheet.addEventListener('pointermove', function (ev) {
            if (!self.dragState) { return; }
            var p = localPos(ev);
            var l = Math.min(self.dragState.sx, p.x);
            var t = Math.min(self.dragState.sy, p.y);
            var w = Math.abs(p.x - self.dragState.sx);
            var h = Math.abs(p.y - self.dragState.sy);
            self.rubber.style.left = l + 'px';
            self.rubber.style.top = t + 'px';
            self.rubber.style.width = w + 'px';
            self.rubber.style.height = h + 'px';
        });

        this.positionSheet.addEventListener('pointerup', function (ev) {
            if (!self.dragState) { return; }
            var p = localPos(ev);
            var l = Math.min(self.dragState.sx, p.x);
            var t = Math.min(self.dragState.sy, p.y);
            var w = Math.abs(p.x - self.dragState.sx);
            var h = Math.abs(p.y - self.dragState.sy);
            self.rubber.hidden = true;
            try { self.positionSheet.releasePointerCapture(ev.pointerId); } catch (e) { /* ignore */ }
            self.dragState = null;
            if (w < 5 || h < 5) { return; }

            var rect = self.positionSheet.getBoundingClientRect();
            var pm = self.pageMm();
            var rawX = (l / rect.width) * pm.w;
            var rawY = (t / rect.height) * pm.h;
            var rawW = (w / rect.width) * pm.w;
            var rawH = (h / rect.height) * pm.h;
            var fs = Math.min(36, Math.max(10, Math.round(rawH * 0.32)));
            self.nameBoxMm = {
                x: Math.round(rawX * 10) / 10,
                y: Math.round(rawY * 10) / 10,
                w: Math.round(rawW * 10) / 10,
                h: Math.round(rawH * 10) / 10,
                font_size: fs
            };
            self.renderNameBox();
            self.showError(4, '');
        });

        this.positionSheet.addEventListener('pointercancel', function () {
            self.dragState = null;
            self.rubber.hidden = true;
        });
    };

    Wizard.prototype.renderNameBox = function () {
        if (!this.nameBoxMm) {
            this.positionRect.hidden = true;
            this.positionGhost.hidden = true;
            return;
        }
        var rect = this.positionSheet.getBoundingClientRect();
        if (!(rect.width > 0)) {
            var self = this;
            window.requestAnimationFrame(function () { self.renderNameBox(); });
            return;
        }
        var pm = this.pageMm();
        var left = (this.nameBoxMm.x / pm.w) * rect.width;
        var top  = (this.nameBoxMm.y / pm.h) * rect.height;
        var w    = (this.nameBoxMm.w / pm.w) * rect.width;
        var h    = (this.nameBoxMm.h / pm.h) * rect.height;

        this.positionRect.hidden = false;
        this.positionRect.style.left = left + 'px';
        this.positionRect.style.top  = top + 'px';
        this.positionRect.style.width  = w + 'px';
        this.positionRect.style.height = h + 'px';

        this.positionGhost.hidden = false;
        this.positionGhost.style.left = (left + 4) + 'px';
        this.positionGhost.style.top  = (top + 4) + 'px';
        this.positionGhost.style.maxWidth = Math.max(40, w - 8) + 'px';
        this.positionGhost.textContent = 'ชื่อ นามสกุล ผู้เข้ารับการอบรม';
    };

    Wizard.prototype.validateStep4 = function () {
        if (!this.nameBoxMm) {
            return 'กรุณาลากกรอบระบุตำแหน่งชื่อบนภาพ';
        }
        return null;
    };

    /* ========== Step 5: Confirm ========== */

    Wizard.prototype.enterConfirmStep = function () {
        var self = this;
        /* Update summary */
        this.summaryEls.forEach(function (el) {
            var key = el.dataset.summary;
            if (key === 'title') {
                el.textContent = (self.inputTitle.value || '').trim() || '—';
            } else if (key === 'description') {
                var d = (self.inputDesc.value || '').trim();
                el.textContent = d || '—';
            } else if (key === 'event_date') {
                el.textContent = (self.inputDate.value || '').trim() || '—';
            } else if (key === 'orientation') {
                el.textContent = self.orientation === 'landscape'
                    ? 'A4 แนวนอน (297 × 210 mm)'
                    : 'A4 แนวตั้ง (210 × 297 mm)';
            } else if (key === 'namebox') {
                if (self.nameBoxMm) {
                    el.textContent = 'x=' + self.nameBoxMm.x + ' mm, y=' + self.nameBoxMm.y +
                        ' mm, กว้าง ' + self.nameBoxMm.w + ' mm, สูง ' + self.nameBoxMm.h +
                        ' mm (font ~ ' + self.nameBoxMm.font_size + ' pt)';
                } else {
                    el.textContent = '—';
                }
            }
        });

        if (this.workingImgUrl) { this.previewImg.src = this.workingImgUrl; }

        window.requestAnimationFrame(function () { self.renderPreviewBox(); });
        this.serializeLayoutJson();
    };

    Wizard.prototype.renderPreviewBox = function () {
        if (!this.nameBoxMm) {
            this.previewRect.hidden = true;
            this.previewGhost.hidden = true;
            return;
        }
        var rect = this.previewStage.getBoundingClientRect();
        if (!(rect.width > 0)) {
            var self = this;
            window.requestAnimationFrame(function () { self.renderPreviewBox(); });
            return;
        }
        var pm = this.pageMm();
        var left = (this.nameBoxMm.x / pm.w) * rect.width;
        var top  = (this.nameBoxMm.y / pm.h) * rect.height;
        var w    = (this.nameBoxMm.w / pm.w) * rect.width;
        var h    = (this.nameBoxMm.h / pm.h) * rect.height;
        this.previewRect.hidden = false;
        this.previewRect.style.left = left + 'px';
        this.previewRect.style.top  = top + 'px';
        this.previewRect.style.width  = w + 'px';
        this.previewRect.style.height = h + 'px';
        this.previewGhost.hidden = false;
        this.previewGhost.style.left = (left + 4) + 'px';
        this.previewGhost.style.top  = (top + 4) + 'px';
        this.previewGhost.style.maxWidth = Math.max(40, w - 8) + 'px';
        this.previewGhost.textContent = 'ชื่อ นามสกุล ผู้เข้ารับการอบรม';
    };

    Wizard.prototype.serializeLayoutJson = function () {
        var out = { orientation: this.orientation };
        if (this.nameBoxMm) {
            out.field_mapping = {
                student_name: {
                    x: this.nameBoxMm.x,
                    y: this.nameBoxMm.y,
                    box_w: this.nameBoxMm.w,
                    box_h: this.nameBoxMm.h,
                    font_size: this.nameBoxMm.font_size
                }
            };
        }
        this.layoutJsonInput.value = JSON.stringify(out);
    };

    window.CertEventWizard = { init: init };
})(window);
