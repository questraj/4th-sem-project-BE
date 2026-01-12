<?php
require_once '../../config/db.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

if (!$id) sendResponse(false, "Invalid ID");

$stmt = $conn->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $userId);

if ($stmt->execute()) {
    sendResponse(true, "Budget deleted successfully");
} else {
    sendResponse(false, "Failed to delete");
}
?>