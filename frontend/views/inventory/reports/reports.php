<?php
require_once __DIR__ . '/../../../../backend/includes/auth.php';
require_once __DIR__ . '/../../../../backend/config/database.php';

checkRole(['Admin', 'Inventory Officer']);

$conn = getDBConnection();

$report_type = sanitizeInput($_GET['report_type'] ?? 'valuation');

if ($report_type === 'low_stock') {
    $report_title = "Low Stock & Out of Stock Report";
    $query = "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.quantity <= p.min_stock_level ORDER BY p.quantity ASC";
} else {
    $report_title = "Inventory Valuation Report";
    $query = "SELECT p.*, c.category_name, (p.unit_price * p.quantity) AS total_value FROM products p JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_name ASC";
}

$results = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports - Motor Shop</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="reports.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div>Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong> (<?= htmlspecialchars($_SESSION['role_name']); ?>)</div>

            </header>

            <div class="inv-reports-header">
                <h2><?= $report_title; ?></h2>
                <button type="button" class="btn-primary btn-print w-auto" id="printReportBtn">Print Report</button>
            </div>

            <form method="GET" class="inv-reports-filter-card" id="reportFilterForm">
                <select name="report_type" id="reportTypeSelect" class="form-control filter-select">
                    <option value="valuation" <?= $report_type === 'valuation' ? 'selected' : ''; ?>>Inventory Valuation Report</option>
                    <option value="low_stock" <?= $report_type === 'low_stock' ? 'selected' : ''; ?>>Low / Out of Stock Report</option>
                </select>
            </form>

            <div class="inv-reports-table-card">
                <table class="inv-reports-table">
                    <thead>
                        <tr>
                            <th>Part #</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Stock Qty</th>
                            <?php if ($report_type === 'valuation'): ?>
                                <th>Total Stock Value</th>
                            <?php else: ?>
                                <th>Min Threshold</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_val = 0;
                        $grand_total_qty = 0;
                        while ($r = $results->fetch_assoc()): 
                            if ($report_type === 'valuation') $grand_total_val += $r['total_value'];
                            $grand_total_qty += $r['quantity'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['part_number']); ?></strong></td>
                                <td><?= htmlspecialchars($r['product_name']); ?></td>
                                <td><?= htmlspecialchars($r['category_name']); ?></td>
                                <td>₱<?= number_format($r['unit_price'], 2); ?></td>
                                <td><?= $r['quantity']; ?></td>
                                <?php if ($report_type === 'valuation'): ?>
                                    <td><strong>₱<?= number_format($r['total_value'], 2); ?></strong></td>
                                <?php else: ?>
                                    <td class="text-danger fw-bold"><?= $r['min_stock_level']; ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-footer">
                            <td colspan="4" class="text-right">Totals:</td>
                            <td><?= $grand_total_qty; ?> units</td>
                            <?php if ($report_type === 'valuation'): ?>
                                <td>₱<?= number_format($grand_total_val, 2); ?></td>
                            <?php else: ?>
                                <td>-</td>
                            <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </main>
    </div>

    <!-- Module-Specific JS -->
    <script src="reports.js"></script>
</body>
</html>
