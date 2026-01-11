<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$category_id = filter_var($data['category_id'] ?? 0, FILTER_VALIDATE_INT);
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$date = filter_var($data['date'] ?? '', FILTER_SANITIZE_STRING);
$description = filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING);

if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input. Category, Amount, and Date are required.");
}

$expense = new Expense($conn);
// Assuming your Expense Model's add method is: add($user_id, $category_id, $amount, $date, $description)
$result = $expense->add($userId, $category_id, $amount, $date, $description);

if ($result) {
    sendResponse(true, "Expense added successfully");
} else {
    sendResponse(false, "Failed to add expense");
}
?>