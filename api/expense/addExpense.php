<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$category_id = filter_var($data['category_id'] ?? 0, FILTER_VALIDATE_INT);
$sub_category_id = !empty($data['sub_category_id']) ? filter_var($data['sub_category_id'], FILTER_VALIDATE_INT) : NULL;
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$date = filter_var($data['date'] ?? '', FILTER_SANITIZE_STRING);
$description = filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING);

if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input.");
}

$expense = new Expense($conn);
// Make sure your Expense.php model has been updated to accept sub_category_id too!
$result = $expense->add($userId, $category_id, $amount, $date, $description, $sub_category_id);

if ($result) {
    sendResponse(true, "Expense added successfully");
} else {
    sendResponse(false, "Failed to add expense");
}
?>