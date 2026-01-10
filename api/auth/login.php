<?php
header("Content-Type: application/json");
include('../../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = md5($data['password']);

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");

if (mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);
    
    // Create simple token
    $token = base64_encode($user['id'] . ':' . time());
    
    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "token" => $token,
        "userId" => $user['id'],
        "name" => $user['name']
    ]);
} else {
    echo json_encode(["status" => false, "message" => "Invalid credentials"]);
}
?>