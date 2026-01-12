<?php
require_once __DIR__ . '/../config/db.php';

class Category {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($userId) {
        // 1. Fetch Main Categories (Global + User Specific)
        $sqlCat = "SELECT id, category_name FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($sqlCat);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $cats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // 2. Fetch Sub Categories (Global + User Specific)
        $sqlSub = "SELECT id, category_id, name FROM sub_categories WHERE user_id IS NULL OR user_id = ? ORDER BY name ASC";
        $stmtSub = $this->conn->prepare($sqlSub);
        $stmtSub->bind_param("i", $userId);
        $stmtSub->execute();
        $subs = $stmtSub->get_result()->fetch_all(MYSQLI_ASSOC);

        // 3. Merge them manually (More robust than SQL JOIN)
        $finalCategories = [];
        foreach ($cats as $cat) {
            $catId = $cat['id'];
            $cat['sub_categories'] = [];
            
            // Find subs that belong to this cat
            foreach ($subs as $sub) {
                if ($sub['category_id'] == $catId) {
                    $cat['sub_categories'][] = $sub;
                }
            }
            $finalCategories[] = $cat;
        }

        return $finalCategories;
    }

    public function add($userId, $name) {
        $stmt = $this->conn->prepare("INSERT INTO categories (user_id, category_name) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $name);
        if ($stmt->execute()) return ["success" => true, "id" => $stmt->insert_id, "name" => $name];
        return ["success" => false];
    }

    public function addSubCategory($userId, $categoryId, $name) {
        $stmt = $this->conn->prepare("INSERT INTO sub_categories (user_id, category_id, name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $categoryId, $name);
        if ($stmt->execute()) return ["success" => true, "id" => $stmt->insert_id, "name" => $name];
        return ["success" => false];
    }
}
?>