<?php
require_once '../../config/db.php';
require_once '../../models/Expense.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$category_id = $_POST['category_id'] ?? 0;
$sub_category_id = !empty($_POST['sub_category_id']) ? $_POST['sub_category_id'] : NULL;
$amount = $_POST['amount'] ?? 0;
$date = $_POST['date'] ?? '';
$description = $_POST['description'] ?? '';
$source = $_POST['source'] ?? 'Cash';

if (!$category_id || !$amount || !$date) {
    sendResponse(false, "Invalid input. Category, Amount, and Date required.");
}

$expense = new Expense($conn);
$result = $expense->add($userId, $category_id, $amount, $date, $description, $sub_category_id, $source);

if ($result) {
    $expenseId = $conn->insert_id;

    if (!empty($_FILES['bills']['name'][0])) {
        $uploadDir = '../../uploads/bills/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['bills']['name'] as $key => $name) {
            $tmpName = $_FILES['bills']['tmp_name'][$key];
            $fileName = time() . '_' . basename($name);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                $dbPath = 'uploads/bills/' . $fileName;
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