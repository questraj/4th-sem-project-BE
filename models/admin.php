<?php
require_once __DIR__ . '/../config/db.php';

class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function isAdmin($userId) {
        $stmt = $this->conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        return $user && $user['role'] === 'admin';
    }

    // Helper to generate dynamic date condition
    private function getDateCondition($period, $month, $year, $dateCol = 'date') {
        if (strcasecmp($period, 'Weekly') == 0) {
            return "YEARWEEK($dateCol, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif (strcasecmp($period, 'Yearly') == 0) {
            return "YEAR($dateCol) = " . intval($year);
        } else {
            return "MONTH($dateCol) = " . intval($month) . " AND YEAR($dateCol) = " . intval($year);
        }
    }

    public function getAllUsers() {
        $stmt = $this->conn->prepare("
            SELECT 
                u.id, 
                u.first_name, 
                u.last_name, 
                u.email, 
                u.role, 
                u.created_at,
                (
                    SELECT CONCAT(p.first_name, ' ', p.last_name) 
                    FROM family_links fl 
                    JOIN users p ON fl.parent_id = p.id 
                    WHERE fl.student_id = u.id AND fl.status = 'ACTIVE' 
                    LIMIT 1
                ) as parent_name
            FROM users u 
            WHERE u.role != 'admin' 
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateUser($id, $first_name, $last_name, $email, $role) {
        $stmt = $this->conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $id);
        return $stmt->execute();
    }

    public function resetPassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $id);
        return $stmt->execute();
    }

    public function deleteUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Apply filters to totals
    public function getSystemStats($period = 'Monthly', $month = null, $year = null) {
        $stats = ["total_income" => 0, "total_expense" => 0, "total_budget" => 0];

        $condDate = $this->getDateCondition($period, $month, $year, 'date');
        $condCreated = $this->getDateCondition($period, $month, $year, 'created_at');

        $inc = $this->conn->query("SELECT SUM(amount) as total FROM incomes WHERE $condDate")->fetch_assoc();
        $stats['total_income'] = $inc['total'] ?? 0;

        $exp = $this->conn->query("SELECT SUM(amount) as total FROM expenses WHERE $condDate")->fetch_assoc();
        $stats['total_expense'] = $exp['total'] ?? 0;

        $bud = $this->conn->query("SELECT SUM(amount) as total FROM budgets WHERE $condCreated")->fetch_assoc();
        $stats['total_budget'] = $bud['total'] ?? 0;

        return $stats;
    }

    // Apply filters to pie charts
    public function getPieChartData($period = 'Monthly', $month = null, $year = null) {
        $data = ["income" => [], "expense" => [], "budget" => []];

        $condDateInc = $this->getDateCondition($period, $month, $year, 'date');
        $condDateExp = $this->getDateCondition($period, $month, $year, 'e.date');
        $condCreatedBud = $this->getDateCondition($period, $month, $year, 'created_at');

        $inc = $this->conn->query("SELECT source as name, SUM(amount) as value FROM incomes WHERE $condDateInc GROUP BY source");
        while ($row = $inc->fetch_assoc()) { $data['income'][] = ["name" => $row['name'], "value" => (float)$row['value']]; }

        $exp = $this->conn->query("SELECT c.category_name as name, SUM(e.amount) as value FROM expenses e JOIN categories c ON e.category_id = c.id WHERE $condDateExp GROUP BY c.category_name");
        while ($row = $exp->fetch_assoc()) { $data['expense'][] = ["name" => $row['name'], "value" => (float)$row['value']]; }

        $bud = $this->conn->query("SELECT type as name, SUM(amount) as value FROM budgets WHERE $condCreatedBud GROUP BY type");
        while ($row = $bud->fetch_assoc()) { $data['budget'][] = ["name" => $row['name'], "value" => (float)$row['value']]; }

        return $data;
    }

    public function getUserExpenses($targetUserId) {
        $stmt = $this->conn->prepare("
            SELECT e.id, e.amount, e.date, e.description, c.category_name 
            FROM expenses e
            JOIN categories c ON e.category_id = c.id
            WHERE e.user_id = ? 
            ORDER BY e.date DESC
        ");
        $stmt->bind_param("i", $targetUserId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>