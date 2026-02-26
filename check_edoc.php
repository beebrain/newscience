<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();

// Check edoctitle count
$result = $db->query('SELECT COUNT(*) as count FROM edoctitle')->getRow();
echo "edoctitle count: " . $result->count . "\n";

// Check edoctag count  
$result = $db->query('SELECT COUNT(*) as count FROM edoctag')->getRow();
echo "edoctag count: " . $result->count . "\n";

// Check edoctaggroups count
$result = $db->query('SELECT COUNT(*) as count FROM edoctaggroups')->getRow();
echo "edoctaggroups count: " . $result->count . "\n";

// Check documentviews count
$result = $db->query('SELECT COUNT(*) as count FROM documentviews')->getRow();
echo "documentviews count: " . $result->count . "\n";

echo "Done!\n";
