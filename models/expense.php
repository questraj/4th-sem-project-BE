<?php
require_once __DIR__ . '/../config/db.php';

class Expense {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add expense
    public function add($user_id, $category_id, $amount, $date, $description) {
        $stmt = $this->conn->prepare("
            INSERT INTO expenses (user_id, category_id, amount, date, description) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iidss", $user_id, $category_id, $amount, $date, $description);
        return $stmt->execute();
    }

    // Get all expenses for a user
    public function getAll($user_id) {
        $stmt = $this->conn->prepare("
            SELECT e.id, e.amount, e.date, e.description, c.category_name, c.id as category_id
            FROM expenses e
            JOIN categories c ON e.category_id = c.id
            WHERE e.user_id = ?
            ORDER BY e.date DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update expense
    public function update($id, $user_id, $category_id, $amount, $date, $description) {
        $stmt = $this->conn->prepare("
            UPDATE expenses 
            SET category_id=?, amount=?, date=?, description=? 
            WHERE id=? AND user_id=?
        ");
        $stmt->bind_param("idssii", $category_id, $amount, $date, $description, $id, $user_id);
        return $stmt->execute();
    }

    // Delete expense
    public function delete($id, $user_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM expenses 
            WHERE id=? AND user_id=?
        ");
        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }
}
?>
