<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

$conn = getDBConnection();

// 1. ERP Core Metrics
$inv_metrics = $conn->query("
    SELECT IFNULL(SUM(quantity * unit_price), 0) AS total_val, 
           IFNULL(SUM(quantity), 0) AS total_units 
    FROM products
")->fetch_assoc();

$total_valuation = (float)($inv_metrics['total_val'] ?? 0);
$total_units     = (int)($inv_metrics['total_units'] ?? 0);

// Products count and low-stock count
$parts_count = (int)$conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$low_stock   = (int)$conn->query("SELECT COUNT(*) AS total FROM products WHERE quantity <= min_stock_level")->fetch_assoc()['total'] ?? 0;

// Today's counter sales
$today_sales_res = $conn->query("
    SELECT IFNULL(SUM(total_amount), 0) AS total, COUNT(*) AS count 
    FROM sales 
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc();

$today_revenue = (float)($today_sales_res['total'] ?? 0);
$today_count   = (int)($today_sales_res['count'] ?? 0);

// Active suppliers
$active_suppliers = (int)$conn->query("SELECT COUNT(*) AS total FROM suppliers WHERE status='Active'")->fetch_assoc()['total'] ?? 0;

// 2. Stock Allocation by Category (ERP Breakdown)
$cat_distribution = $conn->query("
    SELECT c.category_name, c.code_prefix,
           COUNT(p.product_id) AS total_skus,
           IFNULL(SUM(p.quantity), 0) AS physical_stock,
           IFNULL(SUM(p.quantity * p.unit_price), 0) AS category_valuation
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    GROUP BY c.category_id, c.category_name, c.code_prefix
    ORDER BY physical_stock DESC
");

// 3. Critical Reorder Radar (Items at or below safety stock)
$critical_reorders = $conn->query("
    SELECT p.product_id, p.part_number, p.product_name, p.brand, p.quantity, p.min_stock_level, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.quantity <= p.min_stock_level
    ORDER BY p.quantity ASC
    LIMIT 5
");

// 4. Recent Sales Stream
$recent_sales = $conn->query("
    SELECT s.invoice_no, s.total_amount, s.created_at, u.first_name, u.last_name
    FROM sales s
    LEFT JOIN users u ON s.user_id = u.user_id
    ORDER BY s.created_at DESC
    LIMIT 5
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – R'A Motor Parts and Accesories</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

                    <main class="main-content">
            <header class="topbar">
                <div>Welcome back, <strong>Administrator</strong></div>
            </header>


            <!-- Top ERP KPI Summary Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Inventory Capital Valuation</h4>
                    <div class="value">₱ <?= number_format($total_valuation, 2); ?></div>
                    <div class="stat-card-sub"><?= number_format($parts_count); ?> Distinct SKUs cataloged</div>
                </div>

                <div class="stat-card">
                    <h4>Physical Units on Hand</h4>
                    <div class="value"><?= number_format($total_units); ?></div>
                    <div class="stat-card-sub">Across all parts depot categories</div>
                </div>

                <div class="stat-card">
                    <h4>Critical Stock Reorders</h4>
                    <div class="value val-danger"><?= number_format($low_stock); ?></div>
                    <div class="stat-card-sub">Items below safety reserve level</div>
                </div>

                <div class="stat-card">
                    <h4>Today's Counter Revenue</h4>
                    <div class="value val-success">₱ <?= number_format($today_revenue, 2); ?></div>
                    <div class="stat-card-sub"><?= $today_count; ?> Completed transactions today</div>
                </div>
            </div>

            <!-- 2-Column Operational Grid -->
            <div class="dashboard-erp-grid">

                <!-- Left Column (Category Allocation + Reorder Radar) -->
                <div>
                    <!-- Category Stock Allocation -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div>
                                <h3 class="erp-card-title">Stock Allocation by Category</h3>
                                <div class="erp-card-sub">Real-time bin quantities and capital allocation</div>
                            </div>
                            <span class="badge badge-active">Live Audited</span>
                        </div>

                        <div class="table-container erp-table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Prefix</th>
                                        <th>Active SKUs</th>
                                        <th>Physical Stock</th>
                                        <th class="text-right">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($cat_distribution && $cat_distribution->num_rows > 0): ?>
                                        <?php while ($cat = $cat_distribution->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($cat['category_name']); ?></strong></td>
                                                <td><code><?= htmlspecialchars($cat['code_prefix'] ?? 'PRT'); ?></code></td>
                                                <td><?= (int)$cat['total_skus']; ?> items</td>
                                                <td><span class="mono-val"><?= number_format((int)$cat['physical_stock']); ?></span> units</td>
                                                <td class="text-right"><strong>₱ <?= number_format((float)$cat['category_valuation'], 2); ?></strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Critical Reorder Radar -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div>
                                <h3 class="erp-card-title">Urgent Restock Radar</h3>
                                <div class="erp-card-sub">Parts that breached safety stock threshold</div>
                            </div>
                            <a href="../inventory/inventory.php" class="erp-card-link">View All &rarr;</a>
                        </div>

                        <?php if ($critical_reorders && $critical_reorders->num_rows > 0): ?>
                            <div class="table-container erp-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Part #</th>
                                            <th>Product Name</th>
                                            <th>Remaining</th>
                                            <th>Min Level</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = $critical_reorders->fetch_assoc()): ?>
                                            <?php
                                                $dyn_status = $item['quantity'] <= 0 ? 'Out of Stock' : 'Low Stock';
                                                $dyn_badge  = $item['quantity'] <= 0 ? 'badge-inactive' : 'badge-warning';
                                            ?>
                                            <tr>
                                                <td><code><?= htmlspecialchars($item['part_number']); ?></code></td>
                                                <td><strong><?= htmlspecialchars($item['product_name']); ?></strong></td>
                                                <td><span class="qty-danger"><?= (int)$item['quantity']; ?></span></td>
                                                <td><?= (int)$item['min_stock_level']; ?></td>
                                                <td>
                                                    <span class="badge <?= $dyn_badge; ?>">
                                                        <?= $dyn_status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../inventory/inventory.php" class="btn-action btn-edit">+ Restock</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="empty-state-notice">
                                ✅ All inventory items are currently above safety thresholds.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                               <!-- Right Column (Recent Activity) -->
                <div>
                    <!-- Recent Counter Sales Activity Stream -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div>
                                <h3 class="erp-card-title">Recent Transactions</h3>
                                <div class="erp-card-sub">Latest counter dispatches and invoices</div>
                            </div>
                        </div>

                        <div>
                            <?php if ($recent_sales && $recent_sales->num_rows > 0): ?>
                                <?php while ($sale = $recent_sales->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <div>
                                            <div class="activity-invoice">
                                                <code><?= htmlspecialchars($sale['invoice_no']); ?></code>
                                            </div>
                                            <div class="activity-meta">
                                                <?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']); ?> • <?= date('h:i A', strtotime($sale['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="activity-amount">
                                                ₱ <?= number_format($sale['total_amount'], 2); ?>
                                            </div>
                                            <span class="badge badge-active badge-paid">Paid</span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-state-notice">
                                    No sales transactions recorded yet today.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Module-Specific JS -->
    <script src="dashboard.js"></script>
</body>
</html>
