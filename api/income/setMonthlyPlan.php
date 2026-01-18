<?php
require_once '../../config/db.php';
require_once '../../models/IncomePlan.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$year = $data['year'] ?? date('Y');
$month = $data['month'] ?? 0;
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);

if ($month < 1 || $month > 12 || $amount < 0) {
    sendResponse(false, "Invalid input");
}

$plan = new IncomePlan($conn);
$result = $plan->setMonthlyPlan($userId, $year, $month, $amount);

sendResponse($result['success'], $result['message']);
?>