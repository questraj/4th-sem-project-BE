<?php
require_once __DIR__ . '/../config/db.php';

class Category {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get Categories AND their Sub-categories nested
    public function getAll($userId) {
        $sql = "
            SELECT 
                c.id as cat_id, 
                c.category_name, 
                s.id as sub_id, 
                s.name as sub_name
            FROM categories c
            LEFT JOIN sub_categories s ON c.id = s.category_id 
                 AND (s.user_id IS NULL OR s.user_id = ?)
            WHERE c.user_id IS NULL OR c.user_id = ?
            ORDER BY c.category_name ASC, s.name ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];

        while ($row = $result->fetch_assoc()) {
            $catId = $row['cat_id'];

            // Initialize category if not exists
            if (!isset($categories[$catId])) {
                $categories[$catId] = [
                    'id' => $catId,
                    'category_name' => $row['category_name'],
                    'sub_categories' => []
                ];
            }

            // If there is a sub-category, add it
            if ($row['sub_id']) {
                $categories[$catId]['sub_categories'][] = [
                    'id' => $row['sub_id'],
                    'name' => $row['sub_name']
                ];
            }
        }

        // Return indexed array (0, 1, 2...) not associative
        return array_values($categories);
    }

    // Add Main Category
    public function add($userId, $name) {
        $stmt = $this->conn->prepare("INSERT INTO categories (user_id, category_name) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $name);
        if ($stmt->execute()) {
            return ["success" => true, "id" => $stmt->insert_id];
        }
        return ["success" => false, "message" => "Failed to add category"];
    }

    // Add Sub Category
    public function addSubCategory($userId, $categoryId, $name) {
        $stmt = $this->conn->prepare("INSERT INTO sub_categories (user_id, category_id, name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $categoryId, $name);
        if ($stmt->execute()) {
            return ["success" => true, "id" => $stmt->insert_id];
        }
        return ["success" => false, "message" => "Failed to add sub-category"];
    }
}
?>