<?php

/**
 * แบ่ง public/assets/css/style.css เป็น 4 ไฟล์
 * รัน: php scripts/split_style_css.php
 */
$cssDir = dirname(__DIR__) . '/public/assets/css';
$src = $cssDir . '/style.css';
if (!is_file($src)) {
    echo "Not found: $src\n";
    exit(1);
}
$lines = file($src, FILE_IGNORE_NEW_LINES);
$total = count($lines);
echo "Total lines: $total\n";

$splits = [
    'base.css'       => [0, 986],    // 1-986 (0-indexed: 0-985)
    'components.css' => [986, 1989], // 987-1989
    'home.css'       => [1989, 2959],
    'pages.css'      => [2959, $total],
];

foreach ($splits as $file => $range) {
    [$start, $end] = $range;
    $chunk = array_slice($lines, $start, $end - $start);
    $header = "/* $file - part of style.css split */\n\n";
    $path = $cssDir . '/' . $file;
    file_put_contents($path, $header . implode("\n", $chunk));
    echo "Written " . count($chunk) . " lines to $file\n";
}
echo "Done.\n";
