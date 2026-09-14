<?php
require_once('database/Database.php');

$db = new Database();
$sqlFile = 'database/phase1_schema.sql';

if (!file_exists($sqlFile)) {
    die("Error: SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
$queries = array_filter(explode(';', $sql));

$successCount = 0;
$errorCount = 0;

foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;

    try {
        $db->insertRow($query);
        $successCount++;
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "Successfully executed: $successCount queries\n";
echo "Errors: $errorCount queries\n";
?>
