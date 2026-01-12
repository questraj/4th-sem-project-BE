<?php
require_once '../../config/db.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$period = $_GET['period'] ?? 'Monthly'; // Default to Monthly

// Determine Date Condition based on Period
$dateCondition = "";
if (strcasecmp($period, 'Weekly') == 0) {
    // Current week (Mode 1: Monday-Sunday)
    $dateCondition = "YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif (strcasecmp($period, 'Yearly') == 0) {
    // Current year
    $dateCondition = "YEAR(date) = YEAR(CURDATE())";
} else {
    // Default: Current Month
    $dateCondition = "MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
}

// 1. Get Total Expense for the period
$sqlTotal = "SELECT SUM(amount) as total FROM expenses WHERE user_id = ? AND $dateCondition";
$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("i", $userId);
$stmtTotal->execute();
$total = (float)$stmtTotal->get_result()->fetch_assoc()['total'] ?? 0;

// 2. Get Breakdown by Category for the period
$sqlCat = "
    SELECT c.category_name as category, SUM(e.amount) as total 
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = ? AND $dateCondition
    GROUP BY c.category_name
";
$stmtCat = $conn->prepare($sqlCat);
$stmtCat->bind_param("i", $userId);
$stmtCat->execute();
$resultCat = $stmtCat->get_result();

$categories = [];
while ($row = $resultCat->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode([
    "status" => true,
    "totalExpense" => $total,
    "byCategory" => $categories,
    "period" => $period
]);
?>