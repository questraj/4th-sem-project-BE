<?php
require_once '../../config/db.php';
require_once '../../models/Admin.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);
$admin = new Admin($conn);

if (!$admin->isAdmin($userId)) sendResponse(false, "Unauthorized Access");

if (strlen($data['new_password']) < 6) sendResponse(false, "Password must be at least 6 characters.");

if ($admin->resetPassword($data['id'], $data['new_password'])) {
    sendResponse(true, "Password reset successfully");
} else {
    sendResponse(false, "Failed to reset password");
}
?>