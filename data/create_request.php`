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
    echo json_encode(['valid' => false, 'msg' => 'Invalid security token. Please refresh the page.']);
    exit();
}

// 2. Authentication Check
if (!$auth->isLoggedIn()) {
    echo json_encode(['valid' => false, 'msg' => 'Authentication required.']);
    exit();
}

// 3. Collect and Validate Input
$docId    = $_POST['document_id'] ?? '';
$purpose  = trim($_POST['purpose'] ?? '');
$copies   = $_POST['copies'] ?? '';
$userId   = $_SESSION['user_id'];

if (empty($docId) || empty($purpose) || empty($copies)) {
    echo json_encode(['valid' => false, 'msg' => 'All required fields must be filled.']);
    exit();
}

// Validate Copies (Integer, 1-10)
if (!filter_var($copies, FILTER_VALIDATE_INT) || $copies < 1 || $copies > 10) {
    echo json_encode(['valid' => false, 'msg' => 'Please enter a valid number of copies (1 to 10).']);
    exit();
}

// 4. Validate Document (Exist and Active)
$docSql = "SELECT document_id, document_name FROM document_types WHERE document_id = ? AND active = 1 LIMIT 1";
$doc = $auth->getRow($docSql, [$docId]);

if (!$doc) {
    echo json_encode(['valid' => false, 'msg' => 'The selected document is unavailable or invalid.']);
    exit();
}

// 5. Create Request
$insertSql = "INSERT INTO requests (user_id, document_id, purpose, copies, status)
              VALUES (?, ?, ?, ?, 'Pending')";

try {
    $auth->insertRow($insertSql, [$userId, $docId, $purpose, $copies]);
    $requestId = $auth->lastID();

    // Generate Reference Number (Example: SPVAI-2026-0001042)
    $year = date('Y');
    $refNumber = sprintf("SPVAI-%s-%07d", $year, $requestId);

    echo json_encode([
        'valid' => true,
        'msg' => 'Request submitted successfully!',
        'request_id' => $requestId,
        'ref_number' => $refNumber,
        'document_name' => $doc['document_name'],
        'copies' => $copies,
        'purpose' => $purpose,
        'status' => 'Pending',
        'url' => 'request_confirmation.php?id=' . $requestId
    ]);
} catch (Exception $e) {
    echo json_encode(['valid' => false, 'msg' => 'A database error occurred. Please try again.']);
}
?>
