<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$data = json_decode(file_get_contents("php://input"), true);

$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
// Get the type (Weekly, Monthly, Yearly) - Default to Monthly
$type = filter_var($data['type'] ?? 'Monthly', FILTER_SANITIZE_STRING);

if ($amount === false || $amount < 0) {
    sendResponse(false, "Invalid budget amount");
}

$budget = new Budget($conn);
// Pass the type to the model
$result = $budget->setBudget($userId, $amount, $type);

if ($result) {
    sendResponse(true, "Budget set successfully");
} else {
    sendResponse(false, "Failed to set budget");
}
?>