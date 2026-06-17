<?= $this->extend('scienceweek/layout') ?>
<?= $this->section('content') ?>

<?php
// validation errors ถูก flash ไว้ใน session โดย redirect()->withInput()
// (Services::validation() เป็น instance ใหม่ใน request ถัดไป จึงคืน [] — ต้องอ่านจาก session)
$swErrors = session('_ci_validation_errors') ?? [];
function swErr(string $field): string {
    $errors = session('_ci_validation_errors') ?? [];
    $err = $errors[$field] ?? '';
    return $err ? '<div class="invalid-feedback d-block mt-1">'.esc($err).'</div>' : '';
}
function swOld(string $field, array $old): string {
    $keys = explode('.', $field);
    $val = $old;
    foreach ($keys as $k) {
        $val = $val[$k] ?? '';
        if (!is_array($val)) break;
    }
    return is_string($val) ? esc($val) : '';
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="font-size:.82rem;">
        <li class="breadcrumb-item"><a href="<?= base_url('scienceweek') ?>" style="color:var(--sw-blue);">หน้าหลัก</a></li>
        <li class="breadcrumb-item active text-muted"><?= esc($comp['name_th']) ?></li>
    </ol>
</nav>

<!-- Header card -->
<div class="sw-card mb-4">
    <div class="sw-card-header">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
                <h4><?= esc($comp['name_th']) ?></h4>
                <small><?= esc($comp['name_en']) ?></small>
            </div>
            <?php if (!empty($comp['docs'])): ?>
                <div class="d-flex gap-2 flex-wrap mt-1">
                    <?php foreach ($comp['docs'] as $doc): ?>
                        <?php
                            $docFile  = is_array($doc) ? ($doc['file'] ?? '') : $doc;
                            $docLabel = is_array($doc) ? ($doc['label'] ?? 'เอกสาร') : 'เอกสาร';
                        ?>
                        <a href="<?= base_url(config('SciWeek')->docsPublicPath.'/'.rawurlencode($docFile)) ?>"
                           target="_blank"
                           class="btn-doc" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.3);color:#fff;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1L14 5.5z"/>
                                <path d="M4 11h8v1H4zm0-2h8v1H4zm0-2h3v1H4z"/>
                            </svg>
                            ดาวน์โหลด<?= esc($docLabel) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="sw-card-body">

        <?php if ($deadlineMsg): ?>
            <div class="alert alert-<?= $deadlineClosed ? 'danger' : 'info' ?> d-flex align-items-center gap-2 py-2 mb-4">
                <span style="font-size:1.1rem;"><?= $deadlineClosed ? '🔒' : '📅' ?></span>
                <span><?= esc($deadlineMsg) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($swErrors)): ?>
            <div class="alert alert-danger mb-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <strong>⚠️ กรุณาตรวจสอบข้อมูล:</strong>
                </div>
                <ul class="mb-0 ps-3" style="font-size:.88rem;">
                    <?php foreach ($swErrors as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('scienceweek/register/'.$competitionKey) ?>" novalidate>
            <?= csrf_field() ?>

            <!-- ── Section 1: ระดับการแข่งขัน ── -->
            <div class="sw-section">
                <div class="sw-section-num">1</div>
                <h5>ระดับการแข่งขัน <span class="required-mark">*</span></h5>
                <div class="sw-section-line"></div>
            </div>

            <div class="level-radio-group mb-1">
                <?php foreach ($comp['levels'] as $lk => $lv): ?>
                    <label>
                        <input type="radio" name="level_key"
                               value="<?= esc($lk) ?>"
                               <?= ($old['level_key'] ?? '') === $lk ? 'checked' : '' ?> required>
                        <span class="level-radio-label">
                            <span style="width:8px;height:8px;border-radius:50%;background:var(--sw-teal-l);display:inline-block;"></span>
                            <?= esc($lv) ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?= swErr('level_key') ?>

            <!-- ── Section 2: สถานศึกษา ── -->
            <div class="sw-section mt-4">
                <div class="sw-section-num">2</div>
                <h5>ข้อมูลสถานศึกษา</h5>
                <div class="sw-section-line"></div>
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">ชื่อโรงเรียน / สถานศึกษา <span class="required-mark">*</span></label>
                    <input type="text" name="school_name" class="form-control"
                           value="<?= swOld('school_name', $old) ?>" required maxlength="255"
                           placeholder="เช่น โรงเรียนอุตรดิตถ์ดรุณี">
                    <?= swErr('school_name') ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">โทรศัพท์สถานศึกษา <span class="required-mark">*</span></label>
                    <input type="text" name="contact_phone" class="form-control"
                           value="<?= swOld('contact_phone', $old) ?>" required maxlength="40"
                           placeholder="0x-xxxx-xxxx">
                    <?= swErr('contact_phone') ?>
                </div>
                <?php if (!empty($comp['address_fields'])): ?>
                    <?php $addr = $old['addr'] ?? []; ?>
                    <div class="col-md-8">
                        <label class="form-label">เลขที่ / หมู่ / ถนน</label>
                        <input type="text" name="addr[road]" class="form-control"
                               value="<?= esc($addr['road'] ?? '') ?>" maxlength="190"
                               placeholder="เช่น 27 ถนนอินใจมี">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ตำบล / แขวง</label>
                        <input type="text" name="addr[subdistrict]" class="form-control"
                               value="<?= esc($addr['subdistrict'] ?? '') ?>" maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">อำเภอ / เขต</label>
                        <input type="text" name="addr[district]" class="form-control"
                               value="<?= esc($addr['district'] ?? '') ?>" maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">จังหวัด</label>
                        <input type="text" name="addr[province]" class="form-control"
                               value="<?= esc($addr['province'] ?? '') ?>" maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">รหัสไปรษณีย์</label>
                        <input type="text" name="addr[postcode]" class="form-control"
                               value="<?= esc($addr['postcode'] ?? '') ?>" maxlength="5"
                               inputmode="numeric" pattern="[0-9]{5}" placeholder="53000">
                        <?= swErr('addr.postcode') ?>
                    </div>
                <?php else: ?>
                <div class="col-12">
                    <label class="form-label">ที่อยู่สถานศึกษา</label>
                    <input type="text" name="school_address" class="form-control"
                           value="<?= swOld('school_address', $old) ?>" maxlength="500"
                           placeholder="เลขที่ ถนน ตำบล อำเภอ จังหวัด">
                </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label">อีเมลสถานศึกษา</label>
                    <input type="email" name="contact_email" class="form-control"
                           value="<?= swOld('contact_email', $old) ?>" maxlength="190"
                           placeholder="school@example.com">
                </div>
                <?php if (!empty($comp['show_fax'])): ?>
                <div class="col-md-6">
                    <label class="form-label">โทรสาร <span class="text-muted fw-normal">(ถ้ามี)</span></label>
                    <input type="text" name="fax" class="form-control"
                           value="<?= swOld('fax', $old) ?>" maxlength="40"
                           placeholder="0x-xxx-xxxx">
                </div>
                <?php endif; ?>
                <?php if ($comp['team_min'] < $comp['team_max']): ?>
                <div class="col-md-6">
                    <label class="form-label">ชื่อทีม</label>
                    <input type="text" name="team_name" class="form-control"
                           value="<?= swOld('team_name', $old) ?>" maxlength="190"
                           placeholder="ชื่อทีมของคุณ">
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Section 3: อาจารย์ผู้ควบคุม ── -->
            <div class="sw-section mt-4">
                <div class="sw-section-num">3</div>
                <h5>อาจารย์ผู้ควบคุม / ที่ปรึกษา</h5>
                <div class="sw-section-line"></div>
            </div>

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">ชื่อ-สกุล <span class="required-mark">*</span></label>
                    <input type="text" name="coach_name" class="form-control"
                           value="<?= swOld('coach_name', $old) ?>" required maxlength="190"
                           placeholder="ชื่อ นามสกุล">
                    <?= swErr('coach_name') ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ตำแหน่ง</label>
                    <input type="text" name="coach_position" class="form-control"
                           value="<?= swOld('coach_position', $old) ?>" maxlength="120"
                           placeholder="ครู / ผู้ช่วยศาสตราจารย์">
                </div>
                <div class="col-md-4">
                    <label class="form-label">โทรศัพท์มือถือ</label>
                    <input type="text" name="coach_phone" class="form-control"
                           value="<?= swOld('coach_phone', $old) ?>" maxlength="40"
                           placeholder="08x-xxx-xxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="coach_email" class="form-control"
                           value="<?= swOld('coach_email', $old) ?>" maxlength="190"
                           placeholder="coach@example.com">
                </div>
            </div>

            <?php if ($comp['extra_coaches'] >= 2): ?>
            <div class="participant-block reserve-block mt-3" style="border-left-color:#94a3b8;">
                <div class="fw-semibold mb-2" style="color:var(--sw-muted);font-size:.88rem;">
                    อาจารย์ผู้ควบคุมคนที่ 2 <span class="text-muted fw-normal">(ถ้ามี)</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">ชื่อ-สกุล</label>
                        <input type="text" name="coach2_name" class="form-control form-control-sm"
                               value="<?= swOld('coach2_name', $old) ?>" maxlength="190">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">โทรศัพท์</label>
                        <input type="text" name="coach2_phone" class="form-control form-control-sm"
                               value="<?= swOld('coach2_phone', $old) ?>" maxlength="40">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="coach2_email" class="form-control form-control-sm"
                               value="<?= swOld('coach2_email', $old) ?>" maxlength="190">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ID Line</label>
                        <input type="text" name="coach2_line" class="form-control form-control-sm"
                               value="<?= swOld('coach2_line', $old) ?>" maxlength="100">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── Section 4: ผู้เข้าแข่งขัน ── -->
            <div class="sw-section mt-4">
                <div class="sw-section-num">4</div>
                <h5>
                    รายชื่อผู้เข้าแข่งขัน
                    <?php if ($comp['team_min'] === $comp['team_max']): ?>
                        <span class="text-muted fw-normal" style="font-size:.85rem;"><?= $comp['team_min'] ?> คน</span>
                    <?php else: ?>
                        <span class="text-muted fw-normal" style="font-size:.85rem;"><?= $comp['team_min'] ?>–<?= $comp['team_max'] ?> คน</span>
                    <?php endif; ?>
                </h5>
                <div class="sw-section-line"></div>
            </div>

            <?php for ($i = 0; $i < $comp['team_max']; $i++): ?>
                <?php $required = $i < $comp['team_min']; ?>
                <div class="participant-block">
                    <div class="mb-2" style="font-size:.88rem;font-weight:600;color:var(--sw-blue);">
                        <span class="participant-num"><?= $i + 1 ?></span>
                        คนที่ <?= $i + 1 ?>
                        <?php if (!$required): ?>
                            <span style="color:var(--sw-muted);font-weight:400;">(เพิ่มเติม)</span>
                        <?php else: ?>
                            <span class="required-mark">*</span>
                        <?php endif; ?>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label">ชื่อ-นามสกุล<?= $required ? ' <span class="required-mark">*</span>' : '' ?></label>
                            <input type="text" name="participants[<?= $i ?>][full_name]"
                                   class="form-control form-control-sm"
                                   value="<?= esc($old['participants'][$i]['full_name'] ?? '') ?>"
                                   <?= $required ? 'required' : '' ?> maxlength="190"
                                   placeholder="ชื่อ นามสกุล">
                            <?= swErr("participants.{$i}.full_name") ?>
                        </div>
                        <?php foreach ($comp['per_person'] as $field => $meta): ?>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <?= esc($meta['label']) ?>
                                    <?= ($meta['required'] && $required) ? '<span class="required-mark">*</span>' : '' ?>
                                </label>
                                <input type="<?= esc($meta['type']) ?>"
                                       name="participants[<?= $i ?>][<?= esc($field) ?>]"
                                       class="form-control form-control-sm"
                                       value="<?= esc($old['participants'][$i][$field] ?? '') ?>"
                                       <?= ($meta['required'] && $required) ? 'required' : '' ?>
                                       maxlength="190">
                                <?= swErr("participants.{$i}.{$field}") ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endfor; ?>

            <!-- ตัวสำรอง -->
            <?php if ($comp['has_reserve']): ?>
                <div class="sw-section mt-4">
                    <div class="sw-section-num" style="background:var(--sw-muted);">5</div>
                    <h5 style="color:var(--sw-muted);">ผู้เล่นตัวสำรอง <span class="fw-normal text-muted" style="font-size:.85rem;">(สูงสุด <?= $comp['reserve_max'] ?> คน)</span></h5>
                    <div class="sw-section-line"></div>
                </div>
                <?php for ($i = 0; $i < $comp['reserve_max']; $i++): ?>
                    <div class="participant-block reserve-block">
                        <div class="mb-2" style="font-size:.88rem;font-weight:600;color:var(--sw-muted);">
                            <span class="participant-num">ส<?= $i + 1 ?></span>
                            สำรองคนที่ <?= $i + 1 ?> <span style="font-weight:400;">(ถ้ามี)</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" name="reserves[<?= $i ?>][full_name]"
                                       class="form-control form-control-sm"
                                       value="<?= esc($old['reserves'][$i]['full_name'] ?? '') ?>"
                                       maxlength="190">
                            </div>
                            <?php foreach ($comp['per_person'] as $field => $meta): ?>
                                <div class="col-md-3">
                                    <label class="form-label"><?= esc($meta['label']) ?></label>
                                    <input type="<?= esc($meta['type']) ?>"
                                           name="reserves[<?= $i ?>][<?= esc($field) ?>]"
                                           class="form-control form-control-sm"
                                           value="<?= esc($old['reserves'][$i][$field] ?? '') ?>"
                                           maxlength="190">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>

            <!-- Note & Submit -->
            <div class="alert alert-warning mt-4 d-flex gap-2" style="font-size:.85rem;">
                <span>📌</span>
                <div>
                    <strong>หมายเหตุ:</strong> กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนส่ง
                    หากต้องการแก้ไขภายหลัง กรุณาติดต่อ <?= esc($comp['contact']) ?>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
                <button type="submit" class="btn-sw-submit">
                    ✔ ส่งใบสมัคร
                </button>
                <a href="<?= base_url('scienceweek') ?>"
                   class="btn btn-outline-secondary" style="border-radius:10px;">ยกเลิก</a>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>
