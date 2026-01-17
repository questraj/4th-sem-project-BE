<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$id) {
    sendResponse(false, "Invalid expense ID");
}

$expense = new Expense($conn);
$result = $expense->delete($id, $userId);

if ($result) {
    sendResponse(true, "Expense deleted successfully");
} else {
    sendResponse(false, "Failed to delete expense");
}
?>