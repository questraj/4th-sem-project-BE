<?php
require_once '../../config/db.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$first = trim($data['first_name'] ?? '');
$last = trim($data['last_name'] ?? '');
$mid = trim($data['middle_name'] ?? '');
$bank = trim($data['bank_name'] ?? '');
$acc = trim($data['bank_account_no'] ?? '');

if (empty($first) || empty($last)) sendResponse(false, "Name is required");

$stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, bank_name=?, bank_account_no=? WHERE id=?");
$stmt->bind_param("sssssi", $first, $mid, $last, $bank, $acc, $userId);

if ($stmt->execute()) {
    sendResponse(true, "Profile updated successfully");
} else {
    sendResponse(false, "Failed to update");
}
?>