<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

// 1. Authenticate
$userId = authenticate();

// 2. Get Data
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$budget_id = filter_var($data['budget_id'] ?? 0, FILTER_VALIDATE_INT);
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$name = isset($data['name']) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

// 3. Validate
if (!$budget_id || $amount === false || $amount < 0) {
    sendResponse(false, "Invalid input");
}

// 4. Security Check: Does this budget belong to this user?
$stmt = $conn->prepare("SELECT * FROM budgets WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $budget_id, $userId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if (!$existing) {
    sendResponse(false, "Budget not found or unauthorized");
}

// 5. Update
$updateStmt = $conn->prepare("UPDATE budgets SET amount=?, name=? WHERE id=? AND user_id=?");
$updateName = $name ?? $existing['name'];
$updateStmt->bind_param("dsii", $amount, $updateName, $budget_id, $userId);

if ($updateStmt->execute()) {
    sendResponse(true, "Budget updated successfully");
} else {
    sendResponse(false, "Failed to update budget");
}
?>