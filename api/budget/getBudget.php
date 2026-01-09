<?php
session_start();
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';

// Authorization
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Unauthorized access");
}

$name = isset($_GET['name']) ? filter_var($_GET['name'], FILTER_SANITIZE_STRING) : "Monthly Budget";

$budget = new Budget($conn);
$data = $budget->getBudget($_SESSION['user_id'], $name);

if ($data) {
    sendResponse(true, "Budget fetched successfully", $data);
} else {
    sendResponse(true, "No budget set yet", ['amount' => 0]);
}
?>
