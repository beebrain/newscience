<?php
/**
 * Convert seed_personnel.sql value lines from (title, first_name, last_name, first_name_en, last_name_en, ...)
 * to (name, name_en, ...) with merged names.
 * Run once: php scripts/convert_seed_personnel_to_name_fields.php
 */

$path = dirname(__DIR__) . '/database/seed_personnel.sql';
$content = file_get_contents($path);

// Match each line like ('a', 'b', 'c', 'd', 'e', 'position', ...) and convert to ('a b c', 'd e', 'position', ...)
$content = preg_replace_callback(
    "/\('([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*(\d+),\s*(\d+),\s*'(\w+)'\)/",
    function ($m) {
        $name = trim($m[1] . ' ' . $m[2] . ' ' . $m[3]);
        $nameEn = trim($m[4] . ' ' . $m[5]);
        $pos = $m[6];
        $posEn = $m[7];
        $dept = $m[8];
        $sort = $m[9];
        $status = $m[10];
        return "('" . addslashes($name) . "', '" . addslashes($nameEn) . "', '" . addslashes($pos) . "', '" . addslashes($posEn) . "', $dept, $sort, '$status')";
    },
    $content
);

file_put_contents($path, $content);
echo "OK: Converted seed_personnel.sql value lines to name/name_en.\n";
