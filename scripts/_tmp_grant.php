<?php
// Temporary script: grant remote MySQL access then self-delete
// DELETE THIS FILE after use
$token = $_GET['t'] ?? '';
if ($token !== 'ns2026grant') { http_response_code(403); exit('403'); }

$pdo = new PDO('mysql:host=127.0.0.1;dbname=newscience', 'root', 'admin@SCI@2026');
$myIp = $_SERVER['REMOTE_ADDR'] ?? '%';

$pdo->exec("GRANT ALL PRIVILEGES ON newscience.* TO 'root'@'%' IDENTIFIED BY 'admin@SCI@2026'; FLUSH PRIVILEGES;");
echo "OK: granted root@% on newscience. Your IP: $myIp";
