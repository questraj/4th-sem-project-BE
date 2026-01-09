<?php
session_start();
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';

// Authorization
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Unauthorized access");
}

$expense = new Expense($conn);
$data = $expense->getAll($_SESSION['user_id']);

sendResponse(true, "Expenses fetched successfully", $data);
?>
