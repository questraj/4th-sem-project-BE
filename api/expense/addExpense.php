<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../models/FutureExpense.php'; // Import Future Model
require_once '../../models/TransactionLog.php'; // Import Logger
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$category_id = $_POST['category_id'] ?? 0;
$sub_category_id = !empty($_POST['sub_category_id']) ? $_POST['sub_category_id'] : NULL;
$amount = $_POST['amount'] ?? 0;
$date = $_POST['date'] ?? '';
$description = $_POST['description'] ?? '';
$source = $_POST['source'] ?? 'Cash';

if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input.");
}

$today = date('Y-m-d');
$logger = new TransactionLog($conn);

if ($date > $today) {
    // --- FUTURE EXPENSE LOGIC ---
    $future = new FutureExpense($conn);
    $result = $future->add($userId, $category_id, $amount, $date, $description, $sub_category_id, $source);
    
    if ($result) {
        $logger->log($userId, "SCHEDULED_EXPENSE", "Scheduled NPR $amount for $date");
        sendResponse(true, "Expense scheduled for future date.");
    } else {
        sendResponse(false, "Failed to schedule expense.");
    }
} else {
    // --- NORMAL EXPENSE LOGIC ---
    $expense = new Expense($conn);
    $result = $expense->add($userId, $category_id, $amount, $date, $description, $sub_category_id, $source);

    if ($result) {
        $expenseId = $conn->insert_id;
        // ... (Keep existing bill upload logic here) ...
        
        $logger->log($userId, "ADDED_EXPENSE", "Added NPR $amount via $source");
        sendResponse(true, "Expense added successfully");
    } else {
        sendResponse(false, "Failed to add expense");
    }
}
?>