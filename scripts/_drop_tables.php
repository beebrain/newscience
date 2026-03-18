<?php
// Temp script to drop evaluate_self and evaluate_user_rights tables
$token = $_GET['t'] ?? '';
if ($token !== 'drop2026') { http_response_code(403); exit('403'); }

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=newscience', 'root', 'admin@SCI@2026');
    $pdo->exec("DROP TABLE IF EXISTS evaluate_self, evaluate_user_rights;");
    echo "OK: Tables dropped successfully\n";
} catch (PDOException $e) {
    exit("ERROR: " . $e->getMessage());
}

@unlink(__FILE__);
echo "[This file has been deleted]\n";
