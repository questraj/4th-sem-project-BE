<?php
require_once "../../config/db.php";
require_once "../../models/User.php";

function sendResponse($success, $message) {
    echo json_encode(["success" => $success, "message" => $message]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

// Sanitize Inputs
$email      = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password   = trim($data['password'] ?? '');
$first_name = trim($data['first_name'] ?? '');
$last_name  = trim($data['last_name'] ?? '');
$mid_name   = trim($data['middle_name'] ?? '');
// New Fields
$bank_name  = trim($data['bank_name'] ?? '');
$bank_acc   = trim($data['bank_account_no'] ?? '');

// Basic Validation
if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
    sendResponse(false, "Name, Email, and Password are required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "Invalid email address format.");
}

if (strlen($password) < 6) {
    sendResponse(false, "Password must be at least 6 characters long.");
}

$userModel = new User($conn);
// Pass new fields to model
$result = $userModel->register($email, $password, $first_name, $mid_name, $last_name, $bank_name, $bank_acc);

echo json_encode($result);
?>