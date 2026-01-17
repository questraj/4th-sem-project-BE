<?php
require_once '../../config/db.php';
require_once '../../models/Category.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

$userId = authenticate();

$data = json_decode(file_get_contents("php://input"), true);
$name = trim($data['name'] ?? '');

if (empty($name)) {
    sendResponse(false, "Category name is required");
}

$category = new Category($conn);
$result = $category->add($userId, $name);

if ($result['success']) {
    sendResponse(true, "Category added successfully", [
        "id" => $result['id'],
        "name" => $result['name'], 
        "category_name" => $result['name'],
        "user_id" => $userId
    ]);
} else {
    // Pass the specific error message (e.g., "Category already exists")
    sendResponse(false, $result['message']);
}
?>