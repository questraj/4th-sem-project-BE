<?php
require_once '../../config/db.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM sub_categories WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    sendResponse(true, "Sub-category deleted");
} else {
    sendResponse(false, "Failed");
}
?>