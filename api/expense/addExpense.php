<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../models/FutureExpense.php';
require_once '../../models/TransactionLog.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

// Handle both JSON and Form-Data (for bill uploads)
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$category_id = $data['category_id'] ?? 0;
$sub_category_id = !empty($data['sub_category_id']) ? $data['sub_category_id'] : NULL;
$amount = $data['amount'] ?? 0;
$date = $data['date'] ?? '';
$description = $data['description'] ?? '';
$source = $data['source'] ?? 'Cash';

if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Required fields missing: Category, Amount, and Date.");
}

$today = date('Y-m-d');
$logger = new TransactionLog($conn);

if ($date > $today) {
    // --- SCHEDULE FOR FUTURE ---
    $future = new FutureExpense($conn);
    $result = $future->add($userId, $category_id, $amount, $date, $description, $sub_category_id, $source);
    
    if ($result) {
        $logger->log($userId, "SCHEDULED_EXPENSE", "Scheduled NPR $amount for $date");
        sendResponse(true, "Expense scheduled for $date");
    } else {
        sendResponse(false, "Failed to schedule expense.");
    }
} else {
    // --- ADD AS ACTUAL EXPENSE ---
    $expense = new Expense($conn);
    $result = $expense->add($userId, $category_id, $amount, $date, $description, $sub_category_id, $source);

    if ($result) {
        $logger->log($userId, "ADDED_EXPENSE", "Added NPR $amount via $source");
        sendResponse(true, "Expense added successfully");
    } else {
        sendResponse(false, "Failed to add expense");
    }
}
?>