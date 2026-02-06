<?php
require_once '../../config/db.php';
require_once '../../models/FutureExpense.php';
require_once '../../models/TransactionLog.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;
$category_id = $data['category_id'] ?? 0;
$amount = $data['amount'] ?? 0;
$date = $data['date'] ?? '';
$description = $data['description'] ?? '';

if (!$id || !$amount || !$date) {
    sendResponse(false, "Invalid input");
}

$future = new FutureExpense($conn);
if ($future->update($id, $userId, $category_id, $amount, $date, $description)) {
    // Log it
    $logger = new TransactionLog($conn);
    $logger->log($userId, "UPDATED_SCHEDULE", "Modified future expense ID: $id");
    
    sendResponse(true, "Scheduled expense updated");
} else {
    sendResponse(false, "Update failed");
}
?>