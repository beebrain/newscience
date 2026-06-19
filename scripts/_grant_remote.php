<?php
// MySQL remote access grant script - DELETE after use
$token = $_GET['t'] ?? '';
if ($token !== 'grant2026') { http_response_code(403); exit('403'); }

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mysql', 'root', 'CHANGE_ME_DB_PASS');
    
    // Grant remote access to root@% (or create new user)
    $pdo->exec("GRANT ALL PRIVILEGES ON newscience.* TO 'root'@'%' IDENTIFIED BY 'CHANGE_ME_DB_PASS' WITH GRANT OPTION;");
    $pdo->exec("FLUSH PRIVILEGES;");
    
    // Also create a dedicated remote user for safety
    $pdo->exec("GRANT ALL PRIVILEGES ON newscience.* TO 'remote_import'@'%' IDENTIFIED BY 'CHANGE_ME_IMPORT_PASS';");
    $pdo->exec("FLUSH PRIVILEGES;");
    
    echo "OK: Remote MySQL access granted\n";
    echo "  - root@% can now connect with password: CHANGE_ME_DB_PASS\n";
    echo "  - remote_import@% can connect with password: CHANGE_ME_IMPORT_PASS\n";
    echo "\nTest command from your machine:\n";
    echo "  mysql -h 49.231.30.18 -u remote_import -pCHANGE_ME_IMPORT_PASS newscience -e 'SELECT 1;'\n";
    
} catch (PDOException $e) {
    exit("ERROR: " . $e->getMessage());
}

// Self-delete
@unlink(__FILE__);
echo "\n[This file has been deleted from server]\n";
