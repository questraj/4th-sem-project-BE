<?php
require_once "../../config/db.php";
require_once "../../models/User.php";

$data = json_decode(file_get_contents("php://input"), true);

// Fallback if form-data was sent instead of JSON
if (!$data) $data = $_POST;

$email      = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password   = $data['password'] ?? '';
$first_name = htmlspecialchars(trim($data['first_name'] ?? ''));
$last_name  = htmlspecialchars(trim($data['last_name'] ?? ''));
$mid_name   = htmlspecialchars(trim($data['middle_name'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    die(json_encode(["success" => false, "message" => "Invalid email or password too short"]));
}

$userModel = new User($conn);
$result = $userModel->register($email, $password, $first_name, $mid_name, $last_name);

echo json_encode($result);
?>