<?php
// MySQL remote access grant script - DELETE after use
$token = $_GET['t'] ?? '';
if ($token !== 'grant2026') { http_response_code(403); exit('403'); }

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mysql', 'root', 'admin@SCI@2026');
    
    // Grant remote access to root@% (or create new user)
    $pdo->exec("GRANT ALL PRIVILEGES ON newscience.* TO 'root'@'%' IDENTIFIED BY 'admin@SCI@2026' WITH GRANT OPTION;");
    $pdo->exec("FLUSH PRIVILEGES;");
    
    // Also create a dedicated remote user for safety
    $pdo->exec("GRANT ALL PRIVILEGES ON newscience.* TO 'remote_import'@'%' IDENTIFIED BY 'import@SCI@2026';");
    $pdo->exec("FLUSH PRIVILEGES;");
    
    echo "OK: Remote MySQL access granted\n";
    echo "  - root@% can now connect with password: admin@SCI@2026\n";
    echo "  - remote_import@% can connect with password: import@SCI@2026\n";
    echo "\nTest command from your machine:\n";
    echo "  mysql -h 49.231.30.18 -u remote_import -pimport@SCI@2026 newscience -e 'SELECT 1;'\n";
    
} catch (PDOException $e) {
    exit("ERROR: " . $e->getMessage());
}

// Self-delete
@unlink(__FILE__);
echo "\n[This file has been deleted from server]\n";
