<?php
require_once __DIR__ . '/../config/db.php';

class IncomePlan {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Updated: Takes 'amount' directly. Weeks are optional/unused.
    public function setMonthlyPlan($user_id, $year, $month, $amount, $w1=0, $w2=0, $w3=0, $w4=0) {
        
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
        $stmt->bind_param("iiiddddd", $user_id, $year, $month, $amount, $w1, $w2, $w3, $w4);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Income plan updated"];
        }
        return ["success" => false, "message" => "Database error"];
    }

    public function getYearlyBreakdown($user_id, $year) {
        $stmt = $this->conn->prepare("SELECT month, amount FROM monthly_income_plans WHERE user_id = ? AND year = ?");
        $stmt->bind_param("ii", $user_id, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[$row['month']] = (float)$row['amount'];
        }
        
        $finalData = [];
        for ($i = 1; $i <= 12; $i++) {
            $finalData[] = [
                "month" => $i,
                "amount" => $data[$i] ?? 0
            ];
        }
        
        return $finalData;
    }
}
?>