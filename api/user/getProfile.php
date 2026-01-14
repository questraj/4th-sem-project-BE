<?php
require_once '../../config/db.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, bank_name, bank_account_no FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    sendResponse(true, "Profile fetched", $user);
} else {
    sendResponse(false, "User not found");
}
?>