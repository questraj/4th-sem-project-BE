<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
// Get the requested type (defaults to Monthly if not sent)
$type = $_GET['type'] ?? 'Monthly'; 

// Fetch the specific budget type for this user
$stmt = $conn->prepare("SELECT * FROM budgets WHERE user_id = ? AND type = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("is", $userId, $type);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    sendResponse(true, "Budget fetched", $data);
} else {
    // Return 0 if this specific budget type isn't set yet
    sendResponse(true, "No budget found", ['amount' => 0, 'type' => $type]);
}
?>