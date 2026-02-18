<?php
require_once '../../config/db.php';
require_once '../../models/TransactionLog.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

if (!$id) sendResponse(false, "Invalid ID");

// Only delete if it belongs to the user and is still PENDING
$stmt = $conn->prepare("DELETE FROM future_expenses WHERE id = ? AND user_id = ? AND status = 'PENDING'");
$stmt->bind_param("ii", $id, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $logger = new TransactionLog($conn);
    $logger->log($userId, "DELETED_SCHEDULE", "Deleted scheduled expense ID: $id");
    sendResponse(true, "Scheduled expense removed.");
} else {
    sendResponse(false, "Failed to delete or already processed.");
}