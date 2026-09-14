<?php
require_once('Database.php');

$db = new Database();
$sqlFile = 'phase1_schema.sql';

if (!file_exists($sqlFile)) {
    die("Error: SQL file not found: $sqlFile");
}

$sql = file_get_contents($sqlFile);
$queries = array_filter(explode(';', $sql));

$successCount = 0;
$errorCount = 0;

echo "<h2>SPVAI Phase 1 Database Setup</h2>";
echo "<pre>";

foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;

    try {
        $db->insertRow($query);
        echo "SUCCESS: " . substr($query, 0, 50) . "...<br>";
        $successCount++;
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . " | Query: " . substr($query, 0, 50) . "...<br>";
        $errorCount++;
    }
}

echo "</pre>";
echo "<h3>Results:</h3>";
echo "Successfully executed: $successCount queries<br>";
echo "Errors: $errorCount queries<br>";

if ($errorCount === 0) {
    echo "<h2 style='color:green;'>Database Phase 1 setup completed successfully!</h2>";
} else {
    echo "<h2 style='color:red;'>Database Phase 1 setup completed with errors.</h2>";
}
?>
