<?php
require_once('../class/Auth.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['valid' => false, 'msg' => 'Invalid request method.']);
    exit();
}

// 1. Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid security token.']);
    exit();
}

// 2. Authentication Check (admin only)
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['valid' => false, 'msg' => 'Unauthorized access.']);
    exit();
}

// 3. Validate document_id (positive integer, bound as parameter)
$documentId = filter_var($_POST['document_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$documentId) {
    echo json_encode(['valid' => false, 'msg' => 'Invalid document selected.']);
    exit();
}

// 4. Validate fee: non-negative monetary value, max 2 decimals,
// within DECIMAL(10,2) range. No currency symbols, no exponents.
$feeRaw = trim($_POST['fee'] ?? '');
if ($feeRaw === '' || !preg_match('/^\d+(\.\d{1,2})?$/', $feeRaw)) {
    echo json_encode(['valid' => false, 'msg' => 'Please enter a valid non-negative fee (e.g. 25 or 25.00).']);
    exit();
}
$fee = (float)$feeRaw;
if ($fee < 0 || $fee > 99999999.99) {
    echo json_encode(['valid' => false, 'msg' => 'Fee is out of range (0 to 99999999.99).']);
    exit();
}
$fee = number_format($fee, 2, '.', '');

// 5. Confirm the document exists
$doc = $auth->getRow("SELECT document_id FROM document_types WHERE document_id = ? LIMIT 1", [$documentId]);
if (!$doc) {
    echo json_encode(['valid' => false, 'msg' => 'Document not found.']);
    exit();
}

// 6. Update fee (prepared statement, no raw input in SQL)
try {
    $auth->insertRow("UPDATE document_types SET fee = ? WHERE document_id = ?", [$fee, $documentId]);
    echo json_encode(['valid' => true, 'msg' => 'Document fee updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>
