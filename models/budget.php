<?php
require_once __DIR__ . '/../config/db.php';

class Budget {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Set Budget with Validation Logic (Weekly <= Monthly <= Yearly)
    public function setBudget($user_id, $amount, $type = "Monthly") {
        // 1. Fetch all existing budgets for comparison
        $stmt = $this->conn->prepare("SELECT type, amount FROM budgets WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $existing = [];
        while($row = $result->fetch_assoc()) {
            $existing[$row['type']] = (float)$row['amount'];
        }

        // 2. Perform Validation Logic
        if ($type === 'Weekly') {
            // Check: Weekly must be <= Monthly (if Monthly exists)
            if (isset($existing['Monthly']) && $amount > $existing['Monthly']) {
                return [
                    "success" => false, 
                    "message" => "Weekly budget ($amount) cannot exceed Monthly budget (" . $existing['Monthly'] . ")"
                ];
            }
            // Check: Weekly must be <= Yearly (if Yearly exists)
            if (isset($existing['Yearly']) && $amount > $existing['Yearly']) {
                return [
                    "success" => false, 
                    "message" => "Weekly budget cannot exceed Yearly budget (" . $existing['Yearly'] . ")"
                ];
            }
        } 
        elseif ($type === 'Monthly') {
            // Check: Monthly must be >= Weekly (if Weekly exists)
            if (isset($existing['Weekly']) && $amount < $existing['Weekly']) {
                return [
                    "success" => false, 
                    "message" => "Monthly budget ($amount) cannot be less than Weekly budget (" . $existing['Weekly'] . ")"
                ];
            }
            // Check: Monthly must be <= Yearly (if Yearly exists)
            if (isset($existing['Yearly']) && $amount > $existing['Yearly']) {
                return [
                    "success" => false, 
                    "message" => "Monthly budget cannot exceed Yearly budget (" . $existing['Yearly'] . ")"
                ];
            }
        } 
        elseif ($type === 'Yearly') {
            // Check: Yearly must be >= Monthly (if Monthly exists)
            if (isset($existing['Monthly']) && $amount < $existing['Monthly']) {
                return [
                    "success" => false, 
                    "message" => "Yearly budget cannot be less than Monthly budget (" . $existing['Monthly'] . ")"
                ];
            }
            // Check: Yearly must be >= Weekly (if Weekly exists)
            if (isset($existing['Weekly']) && $amount < $existing['Weekly']) {
                 return [
                    "success" => false, 
                    "message" => "Yearly budget cannot be less than Weekly budget (" . $existing['Weekly'] . ")"
                ];
            }
        }

        // 3. If validation passes, proceed to UPSERT (Insert or Update)
        
        // Check if this specific type already exists
        $check = $this->conn->prepare("SELECT id FROM budgets WHERE user_id = ? AND type = ?");
        $check->bind_param("is", $user_id, $type);
        $check->execute();
        $checkRes = $check->get_result();

        if ($checkRes->num_rows > 0) {
            // Update existing row
            $stmt = $this->conn->prepare("UPDATE budgets SET amount = ?, created_at = NOW() WHERE user_id = ? AND type = ?");
            $stmt->bind_param("dis", $amount, $user_id, $type);
        } else {
            // Insert new row
            $stmt = $this->conn->prepare("INSERT INTO budgets (user_id, amount, type) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $user_id, $amount, $type);
        }

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Budget set successfully"];
        } else {
            return ["success" => false, "message" => "Database error occurred"];
        }
    }

    // Helper to get budget (Used by getBudget.php)
    public function getBudget($user_id, $type) {
        $stmt = $this->conn->prepare("SELECT * FROM budgets WHERE user_id = ? AND type = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("is", $user_id, $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>