/**
 * Smart Image Crop — wrapper ของ Cropper.js ที่รองรับหลาย aspect preset + ตัวเลือก "ไม่ crop"
 *
 * ต้องโหลด Cropper.js 1.6.x ก่อน (CSS + JS)
 *
 * การใช้งาน:
 *   SmartImageCrop.mount({
 *     triggerEl: document.getElementById('popupImageBox'),
 *     fileInput: document.getElementById('image'),
 *     base64Input: document.getElementById('image_base64'),
 *     previewEl: document.getElementById('popupImagePlaceholder'),
 *     entity: 'popup',
 *     aspectPresets: [
 *       { value: 'free',  label: 'อิสระ', ratio: NaN },
 *       { value: '4:3',   label: '4:3',   ratio: 4/3 },
 *       { value: '3:4',   label: '3:4',   ratio: 3/4 },
 *       { value: '1:1',   label: '1:1',   ratio: 1 },
 *     ],
 *     defaultAspect: 'free',
 *     allowNoCrop: true,
 *     maxWidth: 1600,
 *     maxHeight: 1600,
 *     quality: 0.92,
 *     initialPreviewUrl: '...',   // รูปเดิมสำหรับโหมด edit
 *     onChange: function(state) { ... }  // optional
 *   });
 */
(function (global) {
    'use strict';

    var MODAL_ID = '__smart_crop_modal__';

    function ensureModal() {
        var modal = document.getElementById(MODAL_ID);
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = MODAL_ID;
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.style.cssText = 'display:none;position:fixed;inset:0;z-index:10050;align-items:center;justify-content:center;padding:1rem;';
        modal.innerHTML = ''
            + '<div class="smart-crop-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.72);backdrop-filter:blur(4px);"></div>'
            + '<div class="smart-crop-box" style="position:relative;background:#fff;border-radius:12px;max-width:min(92vw,960px);width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);">'
            +   '<div class="smart-crop-head" style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #e5e7eb;flex-shrink:0;">'
            +     '<h3 style="margin:0;font-size:1.05rem;font-weight:600;">ตัดรูปภาพ</h3>'
            +     '<button type="button" class="smart-crop-close" aria-label="ปิด" style="background:none;border:none;font-size:1.5rem;line-height:1;cursor:pointer;color:#6b7280;padding:0 0.25rem;">×</button>'
            +   '</div>'
            +   '<div class="smart-crop-aspect-bar" style="display:flex;flex-wrap:wrap;gap:0.5rem;padding:0.75rem 1.25rem;border-bottom:1px solid #e5e7eb;align-items:center;font-size:0.875rem;flex-shrink:0;">'
            +     '<span style="color:#64748b;margin-right:0.25rem;">อัตราส่วน:</span>'
            +     '<div class="smart-crop-aspect-buttons" style="display:flex;flex-wrap:wrap;gap:0.375rem;"></div>'
            +   '</div>'
            +   '<div class="smart-crop-body" style="flex:1;min-height:0;padding:0;background:#0f172a;overflow:hidden;">'
            +     '<div class="smart-crop-img-wrap" style="width:100%;height:60vh;max-height:520px;">'
            +       '<img class="smart-crop-img" alt="" style="max-width:100%;display:block;">'
            +     '</div>'
            +   '</div>'
            +   '<div class="smart-crop-foot" style="display:flex;justify-content:space-between;align-items:center;gap:0.75rem;padding:0.85rem 1.25rem;border-top:1px solid #e5e7eb;flex-shrink:0;flex-wrap:wrap;">'
            +     '<label class="smart-crop-nocrop" style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:#475569;cursor:pointer;">'
            +       '<input type="checkbox" class="smart-crop-nocrop-cb"> ใช้ภาพต้นฉบับ (ไม่ crop)'
            +     '</label>'
            +     '<div style="display:flex;gap:0.5rem;">'
            +       '<button type="button" class="smart-crop-cancel btn btn-secondary">ยกเลิก</button>'
            +       '<button type="button" class="smart-crop-confirm btn btn-primary">ใช้ภาพนี้</button>'
            +     '</div>'
            +   '</div>'
            + '</div>';
        document.body.appendChild(modal);
        return modal;
    }

    function openModal(modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function renderAspectButtons(container, presets, currentVal, onPick) {
        container.innerHTML = '';
        presets.forEach(function (p) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = p.label;
            b.dataset.value = p.value;
            b.className = 'smart-crop-aspect-btn';
            var active = (p.value === currentVal);
            b.style.cssText = 'padding:0.35rem 0.75rem;border-radius:6px;border:1px solid ' + (active ? '#eab308' : '#d1d5db') + ';background:' + (active ? '#fef9c3' : '#fff') + ';color:' + (active ? '#78350f' : '#374151') + ';cursor:pointer;font-size:0.8125rem;';
            b.addEventListener('click', function () { onPick(p.value); });
            container.appendChild(b);
        });
    }

    function readFileAsDataUrl(file) {
        return new Promise(function (resolve, reject) {
            var r = new FileReader();
            r.onload = function () { resolve(r.result); };
            r.onerror = reject;
            r.readAsDataURL(file);
        });
    }

    function buildPreviewHtml(url, hint) {
        return '<div style="text-align:center;"><img src="' + url + '" alt="" style="max-width:100%;max-height:160px;object-fit:contain;border-radius:6px;"></div>'
             + '<p style="margin:0.5rem 0 0;font-size:0.8125rem;color:#6b7280;">' + (hint || 'คลิกเพื่อเปลี่ยนภาพ') + '</p>';
    }

    var SmartImageCrop = {
        mount: function (opts) {
            if (!opts || !opts.fileInput) return;
            if (typeof Cropper === 'undefined') {
                console.warn('SmartImageCrop: Cropper.js is not loaded');
                return;
            }

            var presets = opts.aspectPresets || [
                { value: 'free', label: 'อิสระ', ratio: NaN },
                { value: '16:9', label: '16:9', ratio: 16/9 },
                { value: '4:3',  label: '4:3',  ratio: 4/3 },
                { value: '1:1',  label: '1:1',  ratio: 1 },
                { value: '3:4',  label: '3:4',  ratio: 3/4 },
            ];
            var currentAspect = opts.defaultAspect || presets[0].value;
            var allowNoCrop = opts.allowNoCrop !== false;
            var maxW = opts.maxWidth || 1600;
            var maxH = opts.maxHeight || 1600;
            var quality = typeof opts.quality === 'number' ? opts.quality : 0.92;

            var modal = ensureModal();
            var imgEl = modal.querySelector('.smart-crop-img');
            var aspectBar = modal.querySelector('.smart-crop-aspect-buttons');
            var noCropCb = modal.querySelector('.smart-crop-nocrop-cb');
            var noCropLabel = modal.querySelector('.smart-crop-nocrop');
            noCropLabel.style.display = allowNoCrop ? 'flex' : 'none';

            var cropper = null;
            var objectUrl = null;
            var selectedFile = null;

            function setAspect(val) {
                currentAspect = val;
                renderAspectButtons(aspectBar, presets, val, setAspect);
                if (cropper) {
                    var preset = presets.filter(function (p) { return p.value === val; })[0];
                    var ratio = preset ? preset.ratio : NaN;
                    cropper.setAspectRatio(isNaN(ratio) ? NaN : ratio);
                }
            }

            function destroyCropper() {
                if (cropper) { try { cropper.destroy(); } catch (e) {} cropper = null; }
                if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
            }

            function openForFile(file) {
                selectedFile = file;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = URL.createObjectURL(file);
                imgEl.src = objectUrl;
                noCropCb.checked = false;
                openModal(modal);
                if (cropper) { try { cropper.destroy(); } catch (e) {} cropper = null; }
                setTimeout(function () {
                    var preset = presets.filter(function (p) { return p.value === currentAspect; })[0];
                    var ratio = preset ? preset.ratio : NaN;
                    cropper = new Cropper(imgEl, {
                        aspectRatio: isNaN(ratio) ? NaN : ratio,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.9,
                        guides: true,
                        center: true,
                        background: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                    });
                }, 60);
                renderAspectButtons(aspectBar, presets, currentAspect, setAspect);
            }

            function resetPreviewIfNeeded() {
                // ถ้าไม่มีรูปต้นฉบับ (create mode) และผู้ใช้ยกเลิก → เคลียร์ค่า input
                if (!opts.initialPreviewUrl) {
                    opts.fileInput.value = '';
                    if (opts.base64Input) opts.base64Input.value = '';
                }
            }

            function handleCancel() {
                destroyCropper();
                closeModal(modal);
                resetPreviewIfNeeded();
            }

            function handleConfirm() {
                if (noCropCb.checked) {
                    // โหมดไม่ crop → เคลียร์ base64 ให้ controller ใช้ file
                    if (opts.base64Input) opts.base64Input.value = '';
                    readFileAsDataUrl(selectedFile).then(function (dataUrl) {
                        if (opts.previewEl) opts.previewEl.innerHTML = buildPreviewHtml(dataUrl, 'ใช้ภาพต้นฉบับ (ไม่ crop) — คลิกเพื่อเปลี่ยน');
                        if (opts.triggerEl) opts.triggerEl.classList.add('has-image');
                        if (typeof opts.onChange === 'function') opts.onChange({ mode: 'file', dataUrl: dataUrl });
                        destroyCropper();
                        closeModal(modal);
                    });
                    return;
                }
                if (!cropper) return;
                cropper.getCroppedCanvas({
                    maxWidth: maxW,
                    maxHeight: maxH,
                    imageSmoothingQuality: 'high',
                    fillColor: '#fff',
                }).toBlob(function (blob) {
                    var reader = new FileReader();
                    reader.onload = function () {
                        var dataUrl = reader.result;
                        if (opts.base64Input) opts.base64Input.value = dataUrl;
                        // เคลียร์ fileInput กันส่งไฟล์ซ้ำซ้อน
                        opts.fileInput.value = '';
                        if (opts.previewEl) opts.previewEl.innerHTML = buildPreviewHtml(dataUrl);
                        if (opts.triggerEl) opts.triggerEl.classList.add('has-image');
                        if (typeof opts.onChange === 'function') opts.onChange({ mode: 'base64', dataUrl: dataUrl });
                        destroyCropper();
                        closeModal(modal);
                    };
                    reader.readAsDataURL(blob);
                }, 'image/jpeg', quality);
            }

            // Wire up events
            modal.querySelector('.smart-crop-close').addEventListener('click', handleCancel);
            modal.querySelector('.smart-crop-cancel').addEventListener('click', handleCancel);
            modal.querySelector('.smart-crop-confirm').addEventListener('click', handleConfirm);
            modal.querySelector('.smart-crop-backdrop').addEventListener('click', handleCancel);

            opts.fileInput.addEventListener('change', function () {
                var f = this.files && this.files[0];
                if (f && f.type.indexOf('image/') === 0) openForFile(f);
            });

            if (opts.triggerEl) {
                opts.triggerEl.addEventListener('click', function (e) {
                    // ไม่เปิด dialog ถ้าคลิกปุ่ม reset ภายใน
                    if (e.target.closest('[data-smart-crop-reset]')) return;
                    opts.fileInput.click();
                });
                opts.triggerEl.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        if (e.target.closest('[data-smart-crop-reset]')) return;
                        e.preventDefault();
                        opts.fileInput.click();
                    }
                });
                // Drag & drop
                opts.triggerEl.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '#eab308';
                });
                opts.triggerEl.addEventListener('dragleave', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '';
                });
                opts.triggerEl.addEventListener('drop', function (e) {
                    e.preventDefault();
                    this.style.borderColor = '';
                    var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                    if (f && f.type.indexOf('image/') === 0) openForFile(f);
                });
            }
        }
    };

    global.SmartImageCrop = SmartImageCrop;
})(window);
