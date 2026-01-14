<?php
require_once '../../config/db.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$currentPass = $data['current_password'] ?? '';
$newPass = $data['new_password'] ?? '';

if (empty($currentPass) || strlen($newPass) < 6) {
    sendResponse(false, "Invalid input. New password must be 6+ chars.");
}

// 1. Verify Old Password
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!password_verify($currentPass, $user['password'])) {
    sendResponse(false, "Current password is incorrect");
}

// 2. Update to New Password
$newHash = password_hash($newPass, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->bind_param("si", $newHash, $userId);

if ($update->execute()) {
    sendResponse(true, "Password changed successfully");
} else {
    sendResponse(false, "Failed to change password");
}
?>