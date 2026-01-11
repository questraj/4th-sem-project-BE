<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$name = isset($data['name']) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : "Monthly Budget";

if ($amount === false || $amount < 0) {
    sendResponse(false, "Invalid budget amount");
}

$budget = new Budget($conn);
$result = $budget->setBudget($userId, $amount, $name);

if ($result) {
    sendResponse(true, "Budget set successfully");
} else {
    sendResponse(false, "Failed to set budget");
}
?>