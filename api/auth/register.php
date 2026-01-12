<?php
require_once "../../config/db.php";
require_once "../../models/User.php";

function sendResponse($success, $message) {
    echo json_encode(["success" => $success, "message" => $message]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) $data = $_POST;

$email      = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password   = trim($data['password'] ?? '');
$first_name = trim($data['first_name'] ?? '');
$last_name  = trim($data['last_name'] ?? '');
$mid_name   = trim($data['middle_name'] ?? '');

if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
    sendResponse(false, "First Name, Last Name, Email, and Password are required.");
}

if (!preg_match("/^[a-zA-Z]+$/", $first_name)) {
    sendResponse(false, "First name must contain only letters.");
}

if (!preg_match("/^[a-zA-Z]+$/", $last_name)) {
    sendResponse(false, "Last name must contain only letters.");
}

if (!empty($mid_name) && !preg_match("/^[a-zA-Z]+$/", $mid_name)) {
    sendResponse(false, "Middle name must contain only letters.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "Invalid email address format.");
}

if (strlen($password) < 6) {
    sendResponse(false, "Password must be at least 6 characters long.");
}

$userModel = new User($conn);
$result = $userModel->register($email, $password, $first_name, $mid_name, $last_name);

echo json_encode($result);
?>