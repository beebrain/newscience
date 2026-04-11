<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    var fileInput = document.getElementById('image');
    var previewImg = document.getElementById('preview-image');
    var cropModal = document.getElementById('staff-profile-crop-modal');
    var cropImageEl = document.getElementById('staff-profile-crop-image');
    var cropCloseBtn = document.getElementById('staffProfileCropClose');
    var cropCancelBtn = document.getElementById('staffProfileCropCancel');
    var cropConfirmBtn = document.getElementById('staffProfileCropConfirm');
    var cropperInstance = null;
    var cropObjectUrl = null;

    function closeCropModal(clearInput) {
        if (cropModal) cropModal.style.display = 'none';
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        if (cropObjectUrl) {
            URL.revokeObjectURL(cropObjectUrl);
            cropObjectUrl = null;
        }
        if (clearInput && fileInput) fileInput.value = '';
    }

    function openCropModal(file) {
        if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/i)) return;
        if (cropObjectUrl) URL.revokeObjectURL(cropObjectUrl);
        cropObjectUrl = URL.createObjectURL(file);
        cropImageEl.src = cropObjectUrl;
        if (cropModal) cropModal.style.display = 'flex';
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        setTimeout(function () {
            if (typeof Cropper !== 'undefined' && cropImageEl) {
                cropperInstance = new Cropper(cropImageEl, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true
                });
            }
        }, 100);
    }

    function applyCrop() {
        if (!cropperInstance || !fileInput) return;
        cropperInstance.getCroppedCanvas({
            width: 800,
            height: 800,
            maxWidth: 1024,
            maxHeight: 1024,
            imageSmoothingQuality: 'high'
        }).toBlob(function (blob) {
            if (!blob) return;
            var dt = new DataTransfer();
            dt.items.add(new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' }));
            fileInput.files = dt.files;
            if (previewImg) {
                var r = new FileReader();
                r.onload = function () {
                    previewImg.style.opacity = '0';
                    setTimeout(function () {
                        previewImg.src = r.result;
                        previewImg.style.opacity = '1';
                    }, 150);
                };
                r.readAsDataURL(blob);
            }
            closeCropModal(false);
        }, 'image/jpeg', 0.92);
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var f = this.files[0];
            if (f && f.type.match(/^image\//)) openCropModal(f);
            else if (f) this.value = '';
        });
    }
    if (cropCloseBtn) cropCloseBtn.addEventListener('click', function () { closeCropModal(true); });
    if (cropCancelBtn) cropCancelBtn.addEventListener('click', function () { closeCropModal(true); });
    if (cropConfirmBtn) cropConfirmBtn.addEventListener('click', applyCrop);
    if (cropModal && cropModal.querySelector('.staff-profile-crop-modal__backdrop')) {
        cropModal.querySelector('.staff-profile-crop-modal__backdrop').addEventListener('click', function () { closeCropModal(true); });
    }
})();
</script>
