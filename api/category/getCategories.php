<?php
require_once '../../config/db.php';
require_once '../../models/Category.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

// 1. Authenticate User
$userId = authenticate();

// 2. Initialize Model
$category = new Category($conn);

// 3. Get Data (This calls the new function we wrote in Step 2 of previous answer)
$data = $category->getAll($userId);

// 4. Send Response
// IMPORTANT: We check if data exists. If array is empty, we send an empty array []
if ($data) {
    sendResponse(true, "Categories fetched", $data);
} else {
    sendResponse(true, "No categories found", []);
}
?>