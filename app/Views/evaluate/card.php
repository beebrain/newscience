<?php
helper('url');
$formUrl = base_url('evaluate/self-form');
$success = session()->getFlashdata('success');
$error = session()->getFlashdata('error');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบประเมินของตนเอง | ระบบประเมินผลการสอน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); min-height: 100vh; padding: 2rem 0; }
        .card-eval { border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; }
        .card-eval:hover { transform: translateY(-4px); box-shadow: 0 14px 50px rgba(0,0,0,0.12); }
        .card-eval .card-body { padding: 2rem; }
        .card-eval .btn-link { text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <h1 class="text-center text-success mb-4">
                    <i class="bi bi-clipboard-check"></i> ระบบประเมินผลการสอน
                </h1>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= esc($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= esc($error) ?></div>
                <?php endif; ?>

                <div class="card card-eval">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-lines-fill text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="card-title h4 mb-3">แบบประเมินของตนเอง</h2>
                        <p class="text-muted mb-4">
                            ทุกคนสามารถเข้าไปกรอกแบบประเมินของตนเองได้ โดยไม่ต้องเข้าสู่ระบบ<br>
                            คลิกปุ่มด้านล่างเพื่อไปยังแบบฟอร์ม
                        </p>
                        <a href="<?= esc($formUrl) ?>" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-pencil-square me-2"></i>กรอกแบบประเมินของตนเอง
                        </a>
                    </div>
                </div>

                <p class="text-center text-muted mt-4 small">
                    <a href="<?= base_url() ?>" class="text-muted">กลับหน้าแรก</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
