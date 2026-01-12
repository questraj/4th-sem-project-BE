<?php
require_once __DIR__ . '/../config/db.php';

class Budget {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function setBudget($user_id, $amount, $type = "Monthly") {
        // CHECK TYPE AS WELL
        $stmt = $this->conn->prepare("SELECT id FROM budgets WHERE user_id = ? AND type = ?");
        $stmt->bind_param("is", $user_id, $type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update the specific type
            $stmt = $this->conn->prepare("UPDATE budgets SET amount = ?, created_at = NOW() WHERE user_id = ? AND type = ?");
            $stmt->bind_param("dis", $amount, $user_id, $type);
        } else {
            // Insert new type
            $stmt = $this->conn->prepare("INSERT INTO budgets (user_id, amount, type) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $user_id, $amount, $type);
        }

        return $stmt->execute();
    }

    // ... existing getBudget function ...
}
?>