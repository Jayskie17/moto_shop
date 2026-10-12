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

    $part_number    = sanitizeInput($_POST['part_number'] ?? '');
    $product_name   = sanitizeInput($_POST['product_name'] ?? '');
    $brand          = sanitizeInput($_POST['brand'] ?? '');
    $category_id    = intval($_POST['category_id'] ?? 0);
    $unit_price     = floatval($_POST['unit_price'] ?? 0);
    $quantity       = intval($_POST['quantity'] ?? 0);
    $min_stock_level= intval($_POST['min_stock_level'] ?? 5);

    if (empty($part_number) || empty($product_name) || empty($brand) || $category_id <= 0 || $unit_price < 0 || $quantity < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide valid product details including brand.']);
        exit();
    }

    $conn = getDBConnection();

    // Check Duplicate Part Number
    $check_stmt = $conn->prepare("SELECT product_id FROM products WHERE part_number = ?");
    $check_stmt->bind_param("s", $part_number);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Part Number already exists.']);
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    $stmt = $conn->prepare("INSERT INTO products (category_id, part_number, product_name, brand, unit_price, quantity, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdii", $category_id, $part_number, $product_name, $brand, $unit_price, $quantity, $min_stock_level);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product added to inventory successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}
?>