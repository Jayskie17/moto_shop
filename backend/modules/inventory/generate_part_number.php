<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

checkRole(['Admin', 'Inventory Officer']);

header('Content-Type: application/json');

$category_id = intval($_GET['category_id'] ?? 0);

if ($category_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid category selected.']);
    exit();
}

$conn = getDBConnection();

// 1. Fetch Category Prefix
$cat_stmt = $conn->prepare("SELECT code_prefix FROM categories WHERE category_id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_res = $cat_stmt->get_result();

if ($cat_row = $cat_res->fetch_assoc()) {
    $prefix = $cat_row['code_prefix'];

    // 2. Count existing items IN THIS SPECIFIC CATEGORY to calculate next sequence
    $seq_stmt = $conn->prepare("SELECT COUNT(*) AS total_in_cat FROM products WHERE category_id = ?");
    $seq_stmt->bind_param("i", $category_id);
    $seq_stmt->execute();
    $seq_res = $seq_stmt->get_result();
    
    $next_seq = ($seq_res->fetch_assoc()['total_in_cat'] ?? 0) + 1;
    $seq_stmt->close();

    // 3. Format per-category sequence number (e.g., BRK-0001)
    $auto_part_number = sprintf("%s-%04d", $prefix, $next_seq);

    echo json_encode(['status' => 'success', 'part_number' => $auto_part_number]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Category not found.']);
}

$cat_stmt->close();
$conn->close();
?>