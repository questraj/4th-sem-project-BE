<?php
header("Content-Type: application/json");
include('../../config/db.php');

$userId = $_GET['userId'];

$query = mysqli_query($conn, "SELECT category, SUM(amount) as total FROM expenses WHERE user_id='$userId' GROUP BY category");

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

echo json_encode(["status" => true, "data" => $data]);
//for pie chart//