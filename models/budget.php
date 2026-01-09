<?php
require_once __DIR__ . '/../config/db.php';

class Budget {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Set or update budget for a user
    public function setBudget($user_id, $amount, $name = "Monthly Budget") {
        // Check if budget exists
        $stmt = $this->conn->prepare("SELECT id FROM budgets WHERE user_id = ? AND name = ?");
        $stmt->bind_param("is", $user_id, $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing budget
            $stmt = $this->conn->prepare("UPDATE budgets SET amount = ?, created_at = NOW() WHERE user_id = ? AND name = ?");
            $stmt->bind_param("dis", $amount, $user_id, $name);
        } else {
            // Insert new budget
            $stmt = $this->conn->prepare("INSERT INTO budgets (user_id, name, amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $user_id, $name, $amount);
        }

        return $stmt->execute();
    }

    // Get budget for a user
    public function getBudget($user_id, $name = "Monthly Budget") {
        $stmt = $this->conn->prepare("SELECT * FROM budgets WHERE user_id = ? AND name = ?");
        $stmt->bind_param("is", $user_id, $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
