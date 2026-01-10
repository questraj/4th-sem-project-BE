<?php
header("Content-Type: application/json");
include('../../config/db.php');

$userId = $_GET['userId'];
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Total expenses
$totalQuery = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE user_id='$userId' AND MONTH(date)='$month' AND YEAR(date)='$year'");
$total = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

// By category
$categoryQuery = mysqli_query($conn, "SELECT category, SUM(amount) as total FROM expenses WHERE user_id='$userId' AND MONTH(date)='$month' AND YEAR(date)='$year' GROUP BY category");
$categories = [];
while ($row = mysqli_fetch_assoc($categoryQuery)) {
    $categories[] = $row;
}

echo json_encode([
    "status" => true,
    "totalExpense" => $total,
    "byCategory" => $categories
]);
//dashboard overviw//