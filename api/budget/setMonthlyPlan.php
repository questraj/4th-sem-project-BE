<?php
require_once '../../config/db.php';
require_once '../../models/Budget.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$year = $data['year'] ?? date('Y');
$month = $data['month'] ?? 0;

// Accept individual weeks (default to 0 if empty)
$w1 = filter_var($data['week1'] ?? 0, FILTER_VALIDATE_FLOAT);
$w2 = filter_var($data['week2'] ?? 0, FILTER_VALIDATE_FLOAT);
$w3 = filter_var($data['week3'] ?? 0, FILTER_VALIDATE_FLOAT);
$w4 = filter_var($data['week4'] ?? 0, FILTER_VALIDATE_FLOAT);

if ($month < 1 || $month > 12) {
    sendResponse(false, "Invalid month");
}

$budget = new Budget($conn);
$result = $budget->setMonthlyAmount($userId, $year, $month, $w1, $w2, $w3, $w4);

sendResponse($result['success'], $result['message']);
?>