<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$type = $_GET['type'] ?? 'Monthly'; 

$stmt = $conn->prepare("SELECT * FROM budgets WHERE user_id = ? AND type = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("is", $userId, $type);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    sendResponse(true, "Budget fetched", $data);
} else {
    sendResponse(true, "No budget found", ['amount' => 0, 'type' => $type]);
}
?>