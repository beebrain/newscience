<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">แจ้งข้อร้องเรียน</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <span>แจ้งข้อร้องเรียน</span>
        </div>
    </div>
</section>

<section class="section section-light">
    <div class="container">
        <div class="complaint-layout">
            <div class="complaint-intro card animate-on-scroll">
                <div class="card__content">
                    <span class="section-header__subtitle">Complaint Service</span>
                    <h2 class="section-header__title section-header__title--left">ส่งเรื่องถึงกรรมการบริหารคณะ</h2>
                    <p class="section-header__description section-header__description--left">
                        ใช้แบบฟอร์มนี้เพื่อแจ้งข้อร้องเรียนหรือข้อเสนอแนะที่ต้องการให้คณะรับทราบ ระบบจะส่งเรื่องต่อให้ผู้เกี่ยวข้องตรวจสอบโดยตรง
                    </p>

                    <div class="complaint-note-list">
                        <div class="complaint-note">
                            <strong>ข้อมูลที่ควรระบุ</strong>
                            <p>ชื่อผู้แจ้ง หัวข้อเรื่อง รายละเอียดเหตุการณ์ และช่องทางติดต่อกลับให้ครบถ้วน</p>
                        </div>
                        <div class="complaint-note">
                            <strong>ไฟล์แนบ</strong>
                            <p>แนบเอกสารหรือภาพประกอบได้ไม่เกิน 5 MB รองรับ `pdf`, `doc`, `docx`, `jpg`, `jpeg`, `png`</p>
                        </div>
                        <div class="complaint-note">
                            <strong>ช่องทางติดต่อทั่วไป</strong>
                            <p>
                                <?php if (!empty($contact_email)): ?>
                                    อีเมล: <a href="mailto:<?= esc($contact_email) ?>"><?= esc($contact_email) ?></a><br>
                                <?php endif; ?>
                                <?php if (!empty($contact_phone)): ?>
                                    โทรศัพท์: <a href="tel:<?= esc($contact_phone) ?>"><?= esc($contact_phone) ?></a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="complaint-form-card card animate-on-scroll">
                <div class="card__content">
                    <?php if (session('success')): ?>
                        <div class="complaint-alert complaint-alert--success"><?= esc(session('success')) ?></div>
                    <?php endif; ?>

                    <?php if (session('error')): ?>
                        <div class="complaint-alert complaint-alert--error"><?= esc(session('error')) ?></div>
                    <?php endif; ?>

                    <?php if (session('errors')): ?>
                        <div class="complaint-alert complaint-alert--error">
                            <strong>กรุณาตรวจสอบข้อมูลต่อไปนี้</strong>
                            <ul>
                                <?php foreach ((array) session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('complaints/submit') ?>" method="post" enctype="multipart/form-data" class="complaint-form">
                        <?= csrf_field() ?>

                        <div class="complaint-form__grid">
                            <div class="form-group">
                                <label for="complainant_name" class="form-label">ชื่อผู้แจ้ง <span class="required">*</span></label>
                                <input type="text" id="complainant_name" name="complainant_name" class="form-control" maxlength="255" value="<?= esc(old('complainant_name')) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="complainant_email" class="form-label">อีเมล <span class="required">*</span></label>
                                <input type="email" id="complainant_email" name="complainant_email" class="form-control" maxlength="255" value="<?= esc(old('complainant_email')) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="complainant_phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" id="complainant_phone" name="complainant_phone" class="form-control" maxlength="50" value="<?= esc(old('complainant_phone')) ?>">
                            </div>

                            <div class="form-group">
                                <label for="subject" class="form-label">หัวข้อร้องเรียน <span class="required">*</span></label>
                                <input type="text" id="subject" name="subject" class="form-control" maxlength="255" value="<?= esc(old('subject')) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="detail" class="form-label">รายละเอียด <span class="required">*</span></label>
                            <textarea id="detail" name="detail" class="form-control" rows="8" maxlength="5000" required><?= esc(old('detail')) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="attachment" class="form-label">ไฟล์แนบ (ถ้ามี)</label>
                            <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <p class="form-hint">รองรับไฟล์เอกสารและรูปภาพขนาดไม่เกิน 5 MB</p>
                        </div>

                        <div class="complaint-form__actions">
                            <button type="submit" class="btn btn-primary">ส่งเรื่องร้องเรียน</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.complaint-layout {
    display: grid;
    grid-template-columns: minmax(280px, 0.95fr) minmax(320px, 1.05fr);
    gap: 2rem;
    align-items: start;
}

.section-header__title--left,
.section-header__description--left {
    text-align: left;
}

.complaint-note-list {
    display: grid;
    gap: 1rem;
    margin-top: 1.5rem;
}

.complaint-note {
    padding: 1rem 1.125rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.complaint-note p {
    margin: 0.45rem 0 0 0;
    color: var(--color-gray-700);
}

.complaint-form {
    display: grid;
    gap: 1rem;
}

.complaint-form__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.complaint-form-card .card__content,
.complaint-intro .card__content {
    padding: 1.5rem;
}

.complaint-alert {
    margin-bottom: 1rem;
    padding: 0.9rem 1rem;
    border-radius: 12px;
    font-size: 0.95rem;
}

.complaint-alert ul {
    margin: 0.5rem 0 0 1.25rem;
}

.complaint-alert--success {
    background: rgba(34, 197, 94, 0.12);
    color: #166534;
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.complaint-alert--error {
    background: rgba(239, 68, 68, 0.12);
    color: #991b1b;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.required {
    color: #dc2626;
}

.complaint-form__actions {
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 900px) {
    .complaint-layout,
    .complaint-form__grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?= $this->endSection() ?>
