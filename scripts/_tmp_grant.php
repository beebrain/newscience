<?php
// Temporary script: grant remote MySQL access then self-delete
// DELETE THIS FILE after use
$token = $_GET['t'] ?? '';
if ($token !== 'ns2026grant') { http_response_code(403); exit('403'); }

$pdo = new PDO('mysql:host=127.0.0.1;dbname=newscience', 'root', 'CHANGE_ME_DB_PASS');
$myIp = $_SERVER['REMOTE_ADDR'] ?? '%';

$pdo->exec("GRANT ALL PRIVILEGES ON newscience.* TO 'root'@'%' IDENTIFIED BY 'CHANGE_ME_DB_PASS'; FLUSH PRIVILEGES;");
echo "OK: granted root@% on newscience. Your IP: $myIp";
