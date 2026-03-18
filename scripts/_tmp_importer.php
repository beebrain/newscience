<?php
// Temporary evaluation data importer — DELETE after use
$token = $_GET['t'] ?? '';
if ($token !== 'ns2026import') { http_response_code(403); exit('403 Forbidden'); }

$sqlFile = __DIR__ . '/eval_migrate.sql';
if (!file_exists($sqlFile)) { exit('SQL file not found: ' . $sqlFile); }

$pdo = new PDO('mysql:host=127.0.0.1;dbname=newscience;charset=utf8mb4', 'root', 'admin@SCI@2026', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$sql = file_get_contents($sqlFile);

// Split on semicolons that are NOT inside quotes
$statements = [];
$current = '';
$inString = false;
$strChar  = '';
$len = strlen($sql);
for ($i = 0; $i < $len; $i++) {
    $c = $sql[$i];
    if (!$inString && ($c === '"' || $c === "'")) {
        $inString = true; $strChar = $c;
    } elseif ($inString && $c === $strChar && ($i === 0 || $sql[$i-1] !== '\\')) {
        $inString = false;
    }
    $current .= $c;
    if (!$inString && $c === ';') {
        $stmt = trim($current);
        if ($stmt !== '') $statements[] = $stmt;
        $current = '';
    }
}

$ok = 0; $skip = 0; $errors = [];
foreach ($statements as $stmt) {
    if (preg_match('/^(--|\/\*|\s*$)/s', $stmt)) { $skip++; continue; }
    try {
        $pdo->exec($stmt);
        $ok++;
    } catch (PDOException $e) {
        $errors[] = substr($stmt, 0, 80) . ' => ' . $e->getMessage();
    }
}

// Self-delete both files
@unlink($sqlFile);
@unlink(__FILE__);

header('Content-Type: text/plain');
echo "Import complete.\n";
echo "Executed: $ok statements\n";
echo "Skipped : $skip\n";
echo "Errors  : " . count($errors) . "\n";
foreach ($errors as $e) echo "  - $e\n";
echo "\n[Files deleted from server]\n";
