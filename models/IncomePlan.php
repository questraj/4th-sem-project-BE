<?php
require_once __DIR__ . '/../config/db.php';

class IncomePlan {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Set Monthly Income Plan with Weekly Breakdown
    public function setMonthlyPlan($user_id, $year, $month, $w1, $w2, $w3, $w4) {
        $totalAmount = $w1 + $w2 + $w3 + $w4;

        $stmt = $this->conn->prepare("
            INSERT INTO monthly_income_plans (user_id, year, month, amount, week1, week2, week3, week4) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                amount = VALUES(amount),
                week1 = VALUES(week1),
                week2 = VALUES(week2),
                week3 = VALUES(week3),
                week4 = VALUES(week4)
        ");
        $stmt->bind_param("iiiddddd", $user_id, $year, $month, $totalAmount, $w1, $w2, $w3, $w4);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Income plan updated"];
        }
        return ["success" => false, "message" => "Database error"];
    }

    // Get Yearly Breakdown
    public function getYearlyBreakdown($user_id, $year) {
        $stmt = $this->conn->prepare("SELECT month, amount, week1, week2, week3, week4 FROM monthly_income_plans WHERE user_id = ? AND year = ?");
        $stmt->bind_param("ii", $user_id, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[$row['month']] = [
                "amount" => (float)$row['amount'],
                "week1" => (float)$row['week1'],
                "week2" => (float)$row['week2'],
                "week3" => (float)$row['week3'],
                "week4" => (float)$row['week4'],
            ];
        }
        
        // Fill missing months
        $finalData = [];
        for ($i = 1; $i <= 12; $i++) {
            $existing = $data[$i] ?? null;
            $finalData[] = [
                "month" => $i,
                "amount" => $existing ? $existing['amount'] : 0,
                "week1" => $existing ? $existing['week1'] : 0,
                "week2" => $existing ? $existing['week2'] : 0,
                "week3" => $existing ? $existing['week3'] : 0,
                "week4" => $existing ? $existing['week4'] : 0,
            ];
        }
        
        return $finalData;
    }
}
?>