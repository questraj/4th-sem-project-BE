<?php
require_once '../../config/db.php';
require_once '../../models/Income.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();
$data = json_decode(file_get_contents("php://input"), true);

$source = trim($data['source'] ?? '');
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$date = $data['date'] ?? '';
$description = trim($data['description'] ?? '');

if (empty($source) || !$amount || empty($date)) {
    sendResponse(false, "Source, Amount and Date are required");
}

$income = new Income($conn);
$result = $income->add($userId, $source, $amount, $date, $description);

if ($result['success']) {
    sendResponse(true, "Income added successfully");
} else {
    sendResponse(false, "Failed to add income");
}
?>