<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous">
<style>
.staff-profile-crop-modal { position: fixed; inset: 0; z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.staff-profile-crop-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.6); }
.staff-profile-crop-modal__box { position: relative; background: #fff; border-radius: 12px; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.staff-profile-crop-modal__header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-gray-200); flex-shrink: 0; }
.staff-profile-crop-modal__title { margin: 0; font-size: 1.125rem; font-weight: 600; }
.staff-profile-crop-modal__close { background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: var(--color-gray-600); padding: 0 0.25rem; }
.staff-profile-crop-modal__body { padding: 0; overflow: hidden; flex: 1; min-height: 0; }
.staff-profile-crop-container { width: 100%; height: 60vh; max-height: 500px; background: #000; overflow: hidden; }
.staff-profile-crop-container img { max-width: 100%; max-height: 100%; display: block; }
.staff-profile-crop-modal__footer { padding: 1rem 1.25rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; flex-shrink: 0; }
</style>
<div id="staff-profile-crop-modal" class="staff-profile-crop-modal" role="dialog" aria-modal="true" aria-labelledby="staff-profile-crop-title" style="display: none;">
    <div class="staff-profile-crop-modal__backdrop"></div>
    <div class="staff-profile-crop-modal__box">
        <div class="staff-profile-crop-modal__header">
            <h3 id="staff-profile-crop-title" class="staff-profile-crop-modal__title">ตัดรูปโปรไฟล์ (สี่เหลี่ยมจัตุรัส)</h3>
            <button type="button" class="staff-profile-crop-modal__close" id="staffProfileCropClose" aria-label="ปิด">×</button>
        </div>
        <div class="staff-profile-crop-modal__body">
            <div class="staff-profile-crop-container">
                <img id="staff-profile-crop-image" src="" alt="">
            </div>
        </div>
        <div class="staff-profile-crop-modal__footer">
            <button type="button" class="btn btn-secondary" id="staffProfileCropCancel">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="staffProfileCropConfirm">ตัดและใช้ภาพ</button>
        </div>
    </div>
</div>
