<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'PDF Viewer') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background: #1a1a2e; }
        .viewer-header {
            background: linear-gradient(135deg, #1e3a5f, #2d5986);
            color: white; padding: 0.75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
        }
        .viewer-header h1 { font-size: 1rem; font-weight: 500; }
        .viewer-header a { color: #93c5fd; text-decoration: none; font-size: 0.875rem; }
        .viewer-header a:hover { color: #bfdbfe; }
        .pdf-frame { width: 100%; height: calc(100vh - 48px); border: none; }
    </style>
</head>
<body>
    <div class="viewer-header">
        <h1><?= esc($title ?? 'เอกสาร') ?></h1>
        <div>
            <a href="<?= esc($pdf_url) ?>" download>ดาวน์โหลด</a>
            &nbsp;|&nbsp;
            <a href="<?= base_url('edoc') ?>">กลับหน้าหลัก</a>
        </div>
    </div>
    <iframe src="<?= esc($pdf_url) ?>" class="pdf-frame" allowfullscreen></iframe>
</body>
</html>
