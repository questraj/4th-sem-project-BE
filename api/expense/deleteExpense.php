<?php
session_start();
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Unauthorized access");
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

if (!$id) {
    sendResponse(false, "Invalid expense ID");
}

$expense = new Expense($conn);
$result = $expense->delete($id, $_SESSION['user_id']);

if ($result) {
    sendResponse(true, "Expense deleted successfully");
} else {
    sendResponse(false, "Failed to delete expense");
}
?>
