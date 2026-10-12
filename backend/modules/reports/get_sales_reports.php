<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

checkRole(['Admin']);

header('Content-Type: application/json');

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

$conn = getDBConnection();

// 1. Fetch Aggregated Sales & Volume Metrics
$summary_stmt = $conn->prepare("
    SELECT 
        COUNT(s.sale_id) AS total_transactions,
        IFNULL(SUM(s.total_amount), 0) AS total_revenue,
        IFNULL(AVG(s.total_amount), 0) AS average_order_value,
        IFNULL((
            SELECT SUM(si.quantity) 
            FROM sale_items si 
            JOIN sales s2 ON si.sale_id = s2.sale_id 
            WHERE DATE(s2.created_at) BETWEEN ? AND ?
        ), 0) AS total_units_sold
    FROM sales s
    WHERE DATE(s.created_at) BETWEEN ? AND ?
");
$summary_stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
$summary_stmt->close();

// 2. Fetch Top 5 Fast-Moving / Best-Selling Products
$top_stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.part_number,
        p.product_name,
        p.brand,
        c.category_name,
        SUM(si.quantity) AS units_sold,
        SUM(si.subtotal) AS total_revenue
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.sale_id
    JOIN products p ON si.product_id = p.product_id
    JOIN categories c ON p.category_id = c.category_id
    WHERE DATE(s.created_at) BETWEEN ? AND ?
    GROUP BY p.product_id, p.part_number, p.product_name, p.brand, c.category_name
    ORDER BY units_sold DESC
    LIMIT 5
");
$top_stmt->bind_param("ss", $start_date, $end_date);
$top_stmt->execute();
$top_res = $top_stmt->get_result();
$top_products = [];
while ($row = $top_res->fetch_assoc()) {
    $top_products[] = $row;
}
$top_stmt->close();

// 3. Fetch Category Sales Breakdown
$cat_stmt = $conn->prepare("
    SELECT 
        c.category_name,
        c.code_prefix,
        SUM(si.quantity) AS units_sold,
        SUM(si.subtotal) AS category_revenue
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.sale_id
    JOIN products p ON si.product_id = p.product_id
    JOIN categories c ON p.category_id = c.category_id
    WHERE DATE(s.created_at) BETWEEN ? AND ?
    GROUP BY c.category_id, c.category_name, c.code_prefix
    ORDER BY category_revenue DESC
");
$cat_stmt->bind_param("ss", $start_date, $end_date);
$cat_stmt->execute();
$cat_res = $cat_stmt->get_result();
$category_breakdown = [];
while ($row = $cat_res->fetch_assoc()) {
    $category_breakdown[] = $row;
}
$cat_stmt->close();

// 4. Fetch Itemized Transactions List
$sales_stmt = $conn->prepare("
    SELECT 
        s.sale_id,
        s.invoice_no,
        s.total_amount,
        s.amount_paid,
        s.change_amount,
        s.created_at,
        CONCAT(u.first_name, ' ', u.last_name) AS cashier_name
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    WHERE DATE(s.created_at) BETWEEN ? AND ?
    ORDER BY s.created_at DESC
");
$sales_stmt->bind_param("ss", $start_date, $end_date);
$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();

$transactions = [];
while ($row = $sales_result->fetch_assoc()) {
    $transactions[] = $row;
}
$sales_stmt->close();
$conn->close();

// Return Unified JSON Payload
echo json_encode([
    'status'             => 'success',
    'date_range'         => ['start' => $start_date, 'end' => $end_date],
    'summary'            => $summary,
    'top_products'       => $top_products,
    'category_breakdown' => $category_breakdown,
    'transactions'       => $transactions
]);
