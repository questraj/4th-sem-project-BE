<?php
header("Content-Type: application/json");
require_once "../../config/db.php";

// 1. Get and Clean Data
$email      = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password   = $_POST['password'] ?? '';
$first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
$last_name  = htmlspecialchars(trim($_POST['last_name'] ?? ''));
$mid_name   = htmlspecialchars(trim($_POST['middle_name'] ?? '')); // Optional

// 2. Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    die(json_encode(["message" => "Invalid email or password too short"]));
}

// 3. Hash Password
$hashed_pass = password_hash($password, PASSWORD_DEFAULT);

// 4. Secure Insert (Prepared Statement)
$sql  = "INSERT INTO users (email, password, first_name, middle_name, last_name) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

// Bind all 5 strings ("sssss")
mysqli_stmt_bind_param($stmt, "sssss", $email, $hashed_pass, $first_name, $mid_name, $last_name);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["message" => "Registration successful"]);
} else {
    echo json_encode(["message" => "Registration failed"]);
}