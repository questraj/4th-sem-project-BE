<?php
require_once '../../config/db.php';
require_once '../../models/FutureExpense.php';
require_once '../../models/TransactionLog.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;

if (!$id) sendResponse(false, "Invalid ID");

$future = new FutureExpense($conn);
// Pass entire data array in case user modified amount/desc
$result = $future->confirm($id, $userId, $data); 

if ($result) {
    $logger = new TransactionLog($conn);
    $logger->log($userId, "CONFIRMED_EXPENSE", "Confirmed scheduled expense ID: $id");
    sendResponse(true, "Expense confirmed and added to records");
} else {
    sendResponse(false, "Failed to confirm");
}
?>