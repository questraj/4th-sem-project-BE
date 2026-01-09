<?php


$host = "localhost";
$username = "root";
$password = "";     
$db_name = "expense_tracker";


$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed",
        "error" => mysqli_connect_error()
    ]);
    exit;
}
?>
