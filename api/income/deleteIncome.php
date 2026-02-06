<?php
require_once '../../config/db.php';
require_once '../../models/Income.php';
require_once '../../models/TransactionLog.php'; // Import Logger
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

$income = new Income($conn);
if ($income->delete($id, $userId)) {
    // LOGGING
    $logger = new TransactionLog($conn);
    $logger->log($userId, "DELETED_INCOME", "Deleted income ID: $id");

    sendResponse(true, "Income deleted");
} else {
    sendResponse(false, "Delete failed");
}
?>