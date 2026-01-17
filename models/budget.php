<?php
require_once __DIR__ . '/../config/db.php';

class Budget {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function setBudget($user_id, $amount, $type = "Monthly") {
        $stmt = $this->conn->prepare("SELECT type, amount FROM budgets WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $existing = [];
        while($row = $result->fetch_assoc()) {
            $existing[$row['type']] = (float)$row['amount'];
        }

        if ($type === 'Weekly') {
            if (isset($existing['Monthly']) && $amount > $existing['Monthly']) {
                return [
                    "success" => false, 
                    "message" => "Weekly budget ($amount) cannot exceed Monthly budget (" . $existing['Monthly'] . ")"
                ];
            }
            if (isset($existing['Yearly']) && $amount > $existing['Yearly']) {
                return [
                    "success" => false, 
                    "message" => "Weekly budget cannot exceed Yearly budget (" . $existing['Yearly'] . ")"
                ];
            }
        } 
        elseif ($type === 'Monthly') {
            if (isset($existing['Weekly']) && $amount < $existing['Weekly']) {
                return [
                    "success" => false, 
                    "message" => "Monthly budget ($amount) cannot be less than Weekly budget (" . $existing['Weekly'] . ")"
                ];
            }
            if (isset($existing['Yearly']) && $amount > $existing['Yearly']) {
                return [
                    "success" => false, 
                    "message" => "Monthly budget cannot exceed Yearly budget (" . $existing['Yearly'] . ")"
                ];
            }
        } 
        elseif ($type === 'Yearly') {
            if (isset($existing['Monthly']) && $amount < $existing['Monthly']) {
                return [
                    "success" => false, 
                    "message" => "Yearly budget cannot be less than Monthly budget (" . $existing['Monthly'] . ")"
                ];
            }
            if (isset($existing['Weekly']) && $amount < $existing['Weekly']) {
                 return [
                    "success" => false, 
                    "message" => "Yearly budget cannot be less than Weekly budget (" . $existing['Weekly'] . ")"
                ];
            }
        }

        $check = $this->conn->prepare("SELECT id FROM budgets WHERE user_id = ? AND type = ?");
        $check->bind_param("is", $user_id, $type);
        $check->execute();
        $checkRes = $check->get_result();

        if ($checkRes->num_rows > 0) {
            $stmt = $this->conn->prepare("UPDATE budgets SET amount = ?, created_at = NOW() WHERE user_id = ? AND type = ?");
            $stmt->bind_param("dis", $amount, $user_id, $type);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO budgets (user_id, amount, type) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $user_id, $amount, $type);
        }

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Budget set successfully"];
        } else {
            return ["success" => false, "message" => "Database error occurred"];
        }
    }

    public function getBudget($user_id, $type) {
        $stmt = $this->conn->prepare("SELECT * FROM budgets WHERE user_id = ? AND type = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("is", $user_id, $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>