<?php
require_once '../../config/db.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$categoryId = filter_var($data['category_id'] ?? 0, FILTER_VALIDATE_INT);
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);

if (!$categoryId || $amount <= 0) {
    sendResponse(false, "Invalid input");
}

$check = $conn->prepare("SELECT id FROM category_budgets WHERE user_id = ? AND category_id = ?");
$check->bind_param("ii", $userId, $categoryId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE category_budgets SET amount = ? WHERE user_id = ? AND category_id = ?");
    $stmt->bind_param("dii", $amount, $userId, $categoryId);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO category_budgets (user_id, category_id, amount) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $userId, $categoryId, $amount);
}

if ($stmt->execute()) {
    sendResponse(true, "Category budget set successfully");
} else {
    sendResponse(false, "Failed to set budget");
}
?>