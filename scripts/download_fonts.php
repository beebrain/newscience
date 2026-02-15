<?php

/**
 * ดาวน์โหลดไฟล์ฟอนต์จาก Google Fonts (gstatic) เข้า public/assets/fonts/
 * รันครั้งเดียว: php scripts/download_fonts.php
 *
 * ใช้กับ assets/css/fonts.css ที่อ้าง path ../fonts/*.woff2
 */
$baseDir = dirname(__DIR__);
$fontsDir = $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts';
$urls = [
    'https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2',
    'https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2',
    'https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfPI2h.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YL5rik8s6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YL5rilQs6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YL5rilUs6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YL5rilss6w.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVjJx26TKEr37c9aAFJn2QN.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVjJx26TKEr37c9aBpJn2QN.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVjJx26TKEr37c9aBtJn2QN.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVjJx26TKEr37c9aBVJnw.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YMptik8s6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YMptilQs6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YMptilUs6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YMptilss6w.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YLJvik8s6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YLJvilQs6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YLJvilUs6zDX.woff2',
    'https://fonts.gstatic.com/s/sarabun/v17/DtVmJx26TKEr37c9YLJvilss6w.woff2',
];

if (!is_dir($fontsDir)) {
    mkdir($fontsDir, 0755, true);
    echo "Created: $fontsDir\n";
}

$ok = 0;
$fail = 0;
foreach ($urls as $url) {
    $filename = basename(parse_url($url, PHP_URL_PATH));
    $path = $fontsDir . DIRECTORY_SEPARATOR . $filename;
    if (file_exists($path)) {
        echo "Skip (exists): $filename\n";
        $ok++;
        continue;
    }
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (compatible; NewScience/1.0)',
        ],
    ]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 0) {
        file_put_contents($path, $data);
        echo "OK: $filename\n";
        $ok++;
    } else {
        echo "FAIL: $filename ($url)\n";
        $fail++;
    }
}

echo "\nDone. OK=$ok, FAIL=$fail\n";
