<?php
require_once '../../config/db.php';
require_once '../../models/Category.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

// 1. Authenticate User
$userId = authenticate();

// 2. Get Input Data (JSON)
$data = json_decode(file_get_contents("php://input"), true);
$name = trim($data['name'] ?? '');

// 3. Validation
if (empty($name)) {
    sendResponse(false, "Category name is required");
}

// 4. Initialize Model and Add Category
$category = new Category($conn);
$result = $category->add($userId, $name);

// 5. Send Response
if ($result['success']) {
    // We return the new ID and Name so the Frontend can update the UI immediately
    sendResponse(true, "Category added successfully", [
        "id" => $result['id'],
        "name" => $result['name'], 
        "category_name" => $result['name'],
        "user_id" => $userId
    ]);
} else {
    sendResponse(false, "Failed to add category");
}
?>