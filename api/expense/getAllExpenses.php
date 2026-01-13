<?php
require_once '../../config/db.php';
require_once '../../utils/auth.php';

$userId = authenticate();

// 1. Check if filter dates are provided
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$sql = "
    SELECT 
        e.id, 
        e.amount, 
        e.date, 
        e.description, 
        e.category_id,
        c.category_name, 
        e.sub_category_id,
        s.name as sub_category_name
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    LEFT JOIN sub_categories s ON e.sub_category_id = s.id
    WHERE e.user_id = ? 
";

// 2. Add Date Filter Logic
if ($startDate && $endDate) {
    $sql .= " AND e.date BETWEEN ? AND ? ";
}

$sql .= " ORDER BY e.date DESC, e.id DESC";

// 3. Prepare Statement based on logic
$stmt = $conn->prepare($sql);

if ($startDate && $endDate) {
    // Bind UserID (int), StartDate (string), EndDate (string)
    $stmt->bind_param("iss", $userId, $startDate, $endDate);
} else {
    // Bind only UserID
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status" => true, "data" => $data]);
?>