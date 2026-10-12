<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

checkRole(['Admin', 'Inventory Officer']);

header('Content-Type: application/json');

$conn = getDBConnection();

// Fetch Categories
$cat_result = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch Products with Category Name
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$prod_result = $conn->query($sql);
$products = [];
while ($row = $prod_result->fetch_assoc()) {
    // Auto-calculate dynamic status
    if ($row['quantity'] <= 0) {
        $row['status'] = 'Out of Stock';
    } elseif ($row['quantity'] <= $row['min_stock_level']) {
        $row['status'] = 'Low Stock';
    } else {
        $row['status'] = 'In Stock';
    }
    $products[] = $row;
}

$conn->close();
echo json_encode(['status' => 'success', 'categories' => $categories, 'products' => $products]);
?>