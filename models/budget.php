<?php
require_once __DIR__ . '/../config/db.php';

class Budget {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Set or update budget with TYPE
    public function setBudget($user_id, $amount, $type = "Monthly") {
        // Check if budget exists
        $stmt = $this->conn->prepare("SELECT id FROM budgets WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing
            $stmt = $this->conn->prepare("UPDATE budgets SET amount = ?, type = ?, created_at = NOW() WHERE user_id = ?");
            $stmt->bind_param("dsi", $amount, $type, $user_id);
        } else {
            // Insert new
            $stmt = $this->conn->prepare("INSERT INTO budgets (user_id, amount, type) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $user_id, $amount, $type);
        }

        return $stmt->execute();
    }

    public function getBudget($user_id) {
        // We removed the 'name' filter to simplify fetching the main active budget
        $stmt = $this->conn->prepare("SELECT * FROM budgets WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>