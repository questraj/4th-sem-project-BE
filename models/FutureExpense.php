<?php
require_once __DIR__ . '/../config/db.php';

class FutureExpense {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add to future queue
    public function add($user_id, $category_id, $amount, $date, $description, $sub_category_id, $source) {
        $stmt = $this->conn->prepare("
            INSERT INTO future_expenses (user_id, category_id, sub_category_id, amount, date, description, source, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING')
        ");
        $stmt->bind_param("iiidsss", $user_id, $category_id, $sub_category_id, $amount, $date, $description, $source);
        return $stmt->execute();
    }

    // Get expenses that are due (Date is today or passed) and still PENDING
    public function getDueExpenses($user_id) {
        $today = date('Y-m-d');
        $stmt = $this->conn->prepare("
            SELECT f.*, c.category_name 
            FROM future_expenses f
            JOIN categories c ON f.category_id = c.id
            WHERE f.user_id = ? AND f.status = 'PENDING' AND f.date <= ?
        ");
        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Confirm: Move from Future -> Main Expenses
    public function confirm($id, $user_id, $updatedData = null) {
        // 1. Get the future expense
        $stmt = $this->conn->prepare("SELECT * FROM future_expenses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $future = $stmt->get_result()->fetch_assoc();

        if (!$future) return false;

        // 2. Use updated data if provided (user modified it), else use original
        $cat = $updatedData['category_id'] ?? $future['category_id'];
        $sub = $updatedData['sub_category_id'] ?? $future['sub_category_id'];
        $amt = $updatedData['amount'] ?? $future['amount'];
        $date = $updatedData['date'] ?? date('Y-m-d'); // Usually confirmed as 'Today'
        $desc = $updatedData['description'] ?? $future['description'];
        $src = $updatedData['source'] ?? $future['source'];

        // 3. Insert into main expenses
        $insert = $this->conn->prepare("
            INSERT INTO expenses (user_id, category_id, sub_category_id, amount, date, description, source) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("iiidsss", $user_id, $cat, $sub, $amt, $date, $desc, $src);
        
        if ($insert->execute()) {
            // 4. Mark future expense as processed
            $update = $this->conn->prepare("UPDATE future_expenses SET status = 'PROCESSED' WHERE id = ?");
            $update->bind_param("i", $id);
            $update->execute();
            return true;
        }
        return false;
    }
}
?>