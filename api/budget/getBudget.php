<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$name = isset($_GET['name']) ? filter_var($_GET['name'], FILTER_SANITIZE_STRING) : "Monthly Budget";

$budget = new Budget($conn);
$data = $budget->getBudget($userId, $name);

if ($data) {
    sendResponse(true, "Budget fetched successfully", $data);
} else {
    sendResponse(true, "No budget set yet", ['amount' => 0]);
}
?>