/**
 * คลิก/ลากบนภาพแม่แบบใบรับรอง (A4 210×297 mm) เพื่อตั้งตำแหน่งชื่อผู้รับ (field student_name → layout_json)
 */
(function (window) {
    'use strict';

    var PAGE_W_MM = 210;
    var PAGE_H_MM = 297;

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

    function setStudentNameInLayoutJson(textarea, defaultsJson, xMm, yMm, fontSize) {
        var defaults = safeJsonParse(defaultsJson, {});
        var current = safeJsonParse(textarea.value.trim(), {});
        var out = Object.assign({}, defaults, current);
        out.field_mapping = Object.assign(
            {},
            defaults.field_mapping || {},
            current.field_mapping || {}
        );
        out.field_mapping.student_name = {
            x: Math.round(xMm * 10) / 10,
            y: Math.round(yMm * 10) / 10,
            font_size: fontSize
        };
        textarea.value = JSON.stringify(out, null, 2);
    }

    function pxToMm(xPx, yPx, rect) {
        return {
            x: (xPx / rect.width) * PAGE_W_MM,
            y: (yPx / rect.height) * PAGE_H_MM
        };
    }

    function mmToPx(xMm, yMm, rect) {
        return {
            x: (xMm / PAGE_W_MM) * rect.width,
            y: (yMm / PAGE_H_MM) * rect.height
        };
    }

    function initRoot(root) {
        var selLayout = root.getAttribute('data-layout-textarea');
        var selFile = root.getAttribute('data-file-input');
        var defaultsJson = root.getAttribute('data-defaults-json') || '{}';
        var previewUrl = (root.getAttribute('data-preview-url') || '').trim();
        var sampleText = root.getAttribute('data-sample-text') || 'ชื่อ นามสกุล ผู้เข้ารับ';

        var textarea = document.querySelector(selLayout);
        var fileInput = selFile ? document.querySelector(selFile) : null;
        if (!textarea) {
            return;
        }

        var stage = root.querySelector('.cert-lp-stage');
        var img = root.querySelector('.cert-lp-img');
        var marker = root.querySelector('.cert-lp-marker');
        var ghost = root.querySelector('.cert-lp-ghost');
        var notePdf = root.querySelector('.cert-lp-note-pdf');
        var fontInput = root.querySelector('.cert-lp-font-size');
        var objectUrl = null;

        if (ghost) {
            ghost.textContent = sampleText;
        }

        function fontSize() {
            var v = fontInput ? parseInt(String(fontInput.value), 10) : 22;
            if (isNaN(v) || v < 8) {
                return 22;
            }
            if (v > 48) {
                return 48;
            }
            return v;
        }

        function showStage(show) {
            if (stage) {
                stage.style.display = show ? 'block' : 'none';
            }
        }

        function hideMarker() {
            if (marker) {
                marker.style.display = 'none';
            }
            if (ghost) {
                ghost.style.display = 'none';
            }
        }

        function positionMarkerFromMm(xMm, yMm) {
            if (!img || !marker || img.style.display === 'none') {
                return;
            }
            var rect = img.getBoundingClientRect();
            var px = mmToPx(xMm, yMm, rect);
            marker.style.left = px.x + 'px';
            marker.style.top = px.y + 'px';
            marker.style.display = 'block';
            if (ghost) {
                ghost.style.left = px.x + 'px';
                ghost.style.top = px.y + 'px';
                ghost.style.display = 'block';
            }
        }

        function syncMarkerFromTextarea() {
            var layout = safeJsonParse(textarea.value.trim(), safeJsonParse(defaultsJson, {}));
            var sn = getStudentNameLayout(layout);
            if (sn && typeof sn.x === 'number' && typeof sn.y === 'number' && img.style.display !== 'none') {
                positionMarkerFromMm(Number(sn.x), Number(sn.y));
                if (fontInput && sn.font_size) {
                    fontInput.value = String(sn.font_size);
                }
            } else {
                var def = safeJsonParse(defaultsJson, {});
                var dsn = getStudentNameLayout(def);
                if (dsn && img.style.display !== 'none') {
                    positionMarkerFromMm(Number(dsn.x), Number(dsn.y));
                } else {
                    hideMarker();
                }
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
                showStage(true);
                syncMarkerFromTextarea();
            };
            img.onerror = function () {
                img.style.display = 'none';
                hideMarker();
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
                        'ไฟล์ PDF: ใช้ช่อง layout_json ด้านล่างตั้งพิกัดเองได้ หรืออัปโหลดเป็น JPG/PNG เพื่อคลิกวางตำแหน่งบนภาพ';
                }
                if (img) {
                    img.style.display = 'none';
                }
                hideMarker();
                showStage(false);
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

        if (img) {
            img.addEventListener('click', function (ev) {
                if (img.style.display === 'none') {
                    return;
                }
                var rect = img.getBoundingClientRect();
                var x = ev.clientX - rect.left;
                var y = ev.clientY - rect.top;
                var mm = pxToMm(x, y, rect);
                setStudentNameInLayoutJson(textarea, defaultsJson, mm.x, mm.y, fontSize());
                positionMarkerFromMm(mm.x, mm.y);
            });
        }

        /* ลากจุดยึดตำแหน่ง */
        var drag = false;
        function onDocMove(ev) {
            if (!drag || !img || img.style.display === 'none') {
                return;
            }
            var rect = img.getBoundingClientRect();
            var x = ev.clientX - rect.left;
            var y = ev.clientY - rect.top;
            x = Math.max(0, Math.min(rect.width, x));
            y = Math.max(0, Math.min(rect.height, y));
            var mm = pxToMm(x, y, rect);
            setStudentNameInLayoutJson(textarea, defaultsJson, mm.x, mm.y, fontSize());
            positionMarkerFromMm(mm.x, mm.y);
        }

        function endDrag() {
            if (!drag) {
                return;
            }
            drag = false;
            document.removeEventListener('pointermove', onDocMove);
            document.removeEventListener('pointerup', endDrag);
            document.removeEventListener('pointercancel', endDrag);
        }

        function onPointerDown(ev) {
            if (!marker || ev.target !== marker) {
                return;
            }
            drag = true;
            ev.preventDefault();
            ev.stopPropagation();
            document.addEventListener('pointermove', onDocMove);
            document.addEventListener('pointerup', endDrag);
            document.addEventListener('pointercancel', endDrag);
        }

        if (marker) {
            marker.addEventListener('pointerdown', onPointerDown);
        }

        if (fontInput) {
            fontInput.addEventListener('change', function () {
                var layout = safeJsonParse(textarea.value.trim(), safeJsonParse(defaultsJson, {}));
                var sn = getStudentNameLayout(layout) || safeJsonParse(defaultsJson, {}).field_mapping.student_name;
                if (sn) {
                    setStudentNameInLayoutJson(textarea, defaultsJson, Number(sn.x), Number(sn.y), fontSize());
                }
                syncMarkerFromTextarea();
            });
        }

        textarea.addEventListener('input', function () {
            syncMarkerFromTextarea();
        });

        window.addEventListener('resize', function () {
            syncMarkerFromTextarea();
        });

        syncMarkerFromTextarea();
    }

    function initAll() {
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
