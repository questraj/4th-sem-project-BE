<?php
require_once __DIR__ . '/../config/db.php';

class Income {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add Income
    public function add($user_id, $source, $amount, $date, $description) {
        $stmt = $this->conn->prepare("INSERT INTO incomes (user_id, source, amount, date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $user_id, $source, $amount, $date, $description);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Income added successfully"];
        }
        return ["success" => false, "message" => "Database error"];
    }

    // Get All Incomes (with Date Filter)
    public function getAll($user_id, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM incomes WHERE user_id = ?";
        
        if ($startDate && $endDate) {
            $sql .= " AND date BETWEEN ? AND ?";
        }
        
        $sql .= " ORDER BY date DESC, id DESC";
        $stmt = $this->conn->prepare($sql);

        if ($startDate && $endDate) {
            $stmt->bind_param("iss", $user_id, $startDate, $endDate);
        } else {
            $stmt->bind_param("i", $user_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update Income
    public function update($id, $user_id, $source, $amount, $date, $description) {
        $stmt = $this->conn->prepare("UPDATE incomes SET source=?, amount=?, date=?, description=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sdssii", $source, $amount, $date, $description, $id, $user_id);
        return $stmt->execute();
    }

    // Delete Income
    public function delete($id, $user_id) {
        $stmt = $this->conn->prepare("DELETE FROM incomes WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }
}
?>