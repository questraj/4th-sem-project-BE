<?php
require_once '../../config/db.php';
require_once '../../models/Income.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

$income = new Income($conn);
if ($income->delete($id, $userId)) {
    sendResponse(true, "Income deleted");
} else {
    sendResponse(false, "Delete failed");
}
?>