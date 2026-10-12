<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

checkRole(['Admin', 'Inventory Officer']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        exit();
    }

    $product_id     = intval($_POST['product_id'] ?? 0);
    $part_number    = sanitizeInput($_POST['part_number'] ?? '');
    $product_name   = sanitizeInput($_POST['product_name'] ?? '');
    $brand          = sanitizeInput($_POST['brand'] ?? '');
    $category_id    = intval($_POST['category_id'] ?? 0);
    $unit_price     = floatval($_POST['unit_price'] ?? 0);
    $quantity       = intval($_POST['quantity'] ?? 0);
    $min_stock_level= intval($_POST['min_stock_level'] ?? 5);

    if ($product_id <= 0 || empty($part_number) || empty($product_name) || empty($brand) || $category_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields including brand.']);
        exit();
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("UPDATE products SET category_id = ?, part_number = ?, product_name = ?, brand = ?, unit_price = ?, quantity = ?, min_stock_level = ? WHERE product_id = ?");
    $stmt->bind_param("isssdiii", $category_id, $part_number, $product_name, $brand, $unit_price, $quantity, $min_stock_level, $product_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}
?>