<?php
session_start();
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';

// Authorization
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Unauthorized access");
}

// Get POST data & sanitize
$category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
$description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

// Validate
if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input");
}

$expense = new Expense($conn);
$result = $expense->add($_SESSION['user_id'], $category_id, $amount, $date, $description);

if ($result) {
    sendResponse(true, "Expense added successfully");
} else {
    sendResponse(false, "Failed to add expense");
}
?>
