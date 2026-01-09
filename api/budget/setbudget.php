<?php
session_start();
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';

// Authorization
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Unauthorized access");
}

// Get POST data & sanitize
$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
$name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : "Monthly Budget";

// Validate
if ($amount === false || $amount < 0) {
    sendResponse(false, "Invalid budget amount");
}

$budget = new Budget($conn);
$result = $budget->setBudget($_SESSION['user_id'], $amount, $name);

if ($result) {
    sendResponse(true, "Budget set successfully");
} else {
    sendResponse(false, "Failed to set budget");
}
?>
