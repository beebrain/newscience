<?php
helper('url');
$saveUrl = base_url('evaluate/self/save');
$cardUrl = base_url('evaluate/card');
$name  = $prefill_name ?? '';
$email = $prefill_email ?? '';
$uid   = $prefill_uid ?? null;
$ay    = $academic_year ?? (date('Y') + 543);
$sem   = $semester ?? ((int) date('n') <= 6 ? '1' : '2');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกแบบประเมินของตนเอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); min-height: 100vh; padding: 2rem 0; }
        .form-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card form-card">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-4"><i class="bi bi-pencil-square text-primary me-2"></i>แบบประเมินของตนเอง</h2>

                        <form id="selfForm" action="<?= esc($saveUrl) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= esc($name) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="email" value="<?= esc($email) ?>">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ปีการศึกษา</label>
                                    <input type="text" class="form-control" name="academic_year" value="<?= esc($ay) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ภาคเรียน</label>
                                    <select class="form-select" name="semester">
                                        <option value="1" <?= $sem === '1' ? 'selected' : '' ?>>1</option>
                                        <option value="2" <?= $sem === '2' ? 'selected' : '' ?>>2</option>
                                    </select>
                                </div>
                            </div>

                            <p class="text-muted small mb-2">กรุณาให้คะแนนระดับความพึงพอใจ 1–5 (1 น้อยที่สุด, 5 มากที่สุด)</p>
                            <div class="mb-2">
                                <label class="form-label">1. ระดับความพึงพอใจต่อการจัดการเรียนการสอน</label>
                                <select class="form-select" name="score_1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">2. ระดับความเหมาะสมของเนื้อหารายวิชา</label>
                                <select class="form-select" name="score_2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">3. ระดับความชัดเจนของอาจารย์ผู้สอน</label>
                                <select class="form-select" name="score_3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">4. ระดับความเพียงพอของสื่อการเรียนการสอน</label>
                                <select class="form-select" name="score_4">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">5. ระดับความพึงพอใจโดยรวม</label>
                                <select class="form-select" name="score_5">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">ข้อเสนอแนะ (ถ้ามี)</label>
                                <textarea class="form-control" name="comment" rows="3"></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>ส่งแบบประเมิน</button>
                                <a href="<?= esc($cardUrl) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
