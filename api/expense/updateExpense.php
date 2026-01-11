<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

// 1. Authenticate (Get User ID from Token)
$userId = authenticate();

// 2. Get Data (JSON or POST)
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
$category_id = filter_var($data['category_id'] ?? 0, FILTER_VALIDATE_INT);
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$date = filter_var($data['date'] ?? '', FILTER_SANITIZE_STRING);
$description = filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING);

// 3. Validate
if (!$id || !$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input. ID, Category, Amount, and Date required.");
}

$expense = new Expense($conn);
$result = $expense->update($id, $userId, $category_id, $amount, $date, $description);

if ($result) {
    sendResponse(true, "Expense updated successfully");
} else {
    sendResponse(false, "Failed to update expense");
}
?>