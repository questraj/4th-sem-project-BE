<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

// 1. Authenticate User
$userId = authenticate();

// 2. Get Data
$data = json_decode(file_get_contents("php://input"), true);

$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$type = filter_var($data['type'] ?? 'Monthly', FILTER_SANITIZE_STRING);

// 3. Basic Validation
if ($amount === false || $amount < 0) {
    sendResponse(false, "Invalid budget amount");
}

// 4. Initialize Model and Call Function
$budget = new Budget($conn);
$result = $budget->setBudget($userId, $amount, $type);

// 5. Send Response based on logic outcome
if ($result['success']) {
    sendResponse(true, $result['message']);
} else {
    // This sends the specific validation error (e.g. "Weekly cannot exceed Monthly")
    sendResponse(false, $result['message']);
}
?>