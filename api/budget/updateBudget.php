<?php
session_start();
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';

// Authorization check
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Unauthorized access");
}

// Get POST data & sanitize
$budget_id = filter_var($_POST['budget_id'], FILTER_VALIDATE_INT);
$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
$name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : null;

// Validate input
if (!$budget_id || $amount === false || $amount < 0) {
    sendResponse(false, "Invalid input");
}

// Fetch budget first to ensure it belongs to the user
$stmt = $conn->prepare("SELECT * FROM budgets WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $budget_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();

if (!$existing) {
    sendResponse(false, "Budget not found");
}

// Update budget
$updateStmt = $conn->prepare("UPDATE budgets SET amount=?, name=? WHERE id=? AND user_id=?");
$updateName = $name ?? $existing['name'];
$updateStmt->bind_param("dsii", $amount, $updateName, $budget_id, $_SESSION['user_id']);

if ($updateStmt->execute()) {
    sendResponse(true, "Budget updated successfully");
} else {
    sendResponse(false, "Failed to update budget");
}
?>
