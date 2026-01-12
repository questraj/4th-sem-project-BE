<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

// Note: When sending files, we use $_POST, not json_decode
$category_id = $_POST['category_id'] ?? 0;
$sub_category_id = !empty($_POST['sub_category_id']) ? $_POST['sub_category_id'] : NULL;
$amount = $_POST['amount'] ?? 0;
$date = $_POST['date'] ?? '';
$description = $_POST['description'] ?? '';

if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input. Category, Amount, and Date required.");
}

// 1. Insert Expense
$expense = new Expense($conn);
$result = $expense->add($userId, $category_id, $amount, $date, $description, $sub_category_id);

if ($result) {
    $expenseId = $conn->insert_id; // Get the ID of the expense we just created

    // 2. Handle Image Uploads
    if (!empty($_FILES['bills']['name'][0])) {
        $uploadDir = '../../uploads/bills/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true); // Create folder if missing

        foreach ($_FILES['bills']['name'] as $key => $name) {
            $tmpName = $_FILES['bills']['tmp_name'][$key];
            $fileName = time() . '_' . basename($name);
            $targetPath = $uploadDir . $fileName;
            
            // Move file and save to DB
            if (move_uploaded_file($tmpName, $targetPath)) {
                $dbPath = 'uploads/bills/' . $fileName; // Path to store in DB
                $stmt = $conn->prepare("INSERT INTO expense_bills (expense_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $expenseId, $dbPath);
                $stmt->execute();
            }
        }
    }
    sendResponse(true, "Expense added successfully");
} else {
    sendResponse(false, "Failed to add expense");
}
?>