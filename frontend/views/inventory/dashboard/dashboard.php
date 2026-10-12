<?php
require_once __DIR__ . '/../../../../backend/includes/auth.php';
require_once __DIR__ . '/../../../../backend/config/database.php';

// Enforce RBAC: Strictly Admin and Inventory Officer access
checkRole(['Admin', 'Inventory Officer']);

$conn = getDBConnection();

// 1. Fetch Summary Metrics
$parts_count = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$low_stock   = $conn->query("SELECT COUNT(*) AS total FROM products WHERE quantity <= min_stock_level AND quantity > 0")->fetch_assoc()['total'] ?? 0;
$out_stock   = $conn->query("SELECT COUNT(*) AS total FROM products WHERE quantity = 0")->fetch_assoc()['total'] ?? 0;

// 2. Fetch Low Stock Items for the Table
$low_stock_query = "
    SELECT p.product_id, p.product_name, p.quantity, p.min_stock_level
    FROM products p
    WHERE p.quantity <= p.min_stock_level
    ORDER BY p.quantity ASC
";
$low_stock_items = $conn->query($low_stock_query);

// 3. Fetch Fastest Moving Products (Top 5 Sold Items)
$top_moving_query = "
    SELECT p.product_name, IFNULL(SUM(si.quantity), 0) AS total_sold
    FROM products p
    LEFT JOIN sale_items si ON p.product_id = si.product_id
    GROUP BY p.product_id, p.product_name
    ORDER BY total_sold DESC
    LIMIT 5
";
$top_moving_res = $conn->query($top_moving_query);

$chart_labels = [];
$chart_data   = [];

while ($row = $top_moving_res->fetch_assoc()) {
    $chart_labels[] = $row['product_name'];
    $chart_data[]   = intval($row['total_sold']);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard - Motor Shop System</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="dashboard.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div>
                    Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong> (<?= htmlspecialchars($_SESSION['role_name']); ?>)
                </div>
                <a href="/motor_shop/backend/modules/auth/logout.php" class="logout-btn">Logout</a>
            </header>

            <h2>Inventory Analytics Overview</h2>
            <p class="text-subtitle">Real-time inventory levels, critical stock alerts, and product velocity.</p>

            <div class="stats-grid mb-2">
                <div class="stat-card">
                    <h4>Total Products</h4>
                    <div class="value"><?= $parts_count; ?></div>
                </div>
                <div class="stat-card stat-card-warning">
                    <h4>Low Stock Alerts</h4>
                    <div class="value stat-value-warning"><?= $low_stock; ?></div>
                </div>
                <div class="stat-card stat-card-danger">
                    <h4>Out of Stock</h4>
                    <div class="value stat-value-danger"><?= $out_stock; ?></div>
                </div>
            </div>

            <div class="inv-dashboard-split-grid">
                
                <!-- Fastest Moving Products Graph -->
                <div class="inv-chart-card">
                    <h3 class="inv-card-title">Fastest Moving Products</h3>
                    <canvas id="fastestMovingChart" height="220"
                        data-labels="<?= htmlspecialchars(json_encode($chart_labels), ENT_QUOTES, 'UTF-8'); ?>"
                        data-values="<?= htmlspecialchars(json_encode($chart_data), ENT_QUOTES, 'UTF-8'); ?>">
                    </canvas>
                </div>

                <!-- Structured Low Stock Products Table -->
                <div class="inv-low-stock-card">
                    <h3 class="inv-card-title">Low Stock Alerts</h3>
                    <div class="table-responsive">
                        <table class="inv-minimal-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NAME</th>
                                    <th>STOCK</th>
                                    <th>LIMIT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($low_stock_items && $low_stock_items->num_rows > 0): ?>
                                    <?php while ($item = $low_stock_items->fetch_assoc()): ?>
                                        <?php $is_low = ($item['quantity'] <= $item['min_stock_level']); ?>
                                        <tr>
                                            <td><?= $item['product_id']; ?></td>
                                            <td><?= htmlspecialchars($item['product_name']); ?></td>
                                            <td class="<?= $is_low ? 'inv-stock-low' : 'inv-stock-normal'; ?>">
                                                <?= $item['quantity']; ?>
                                            </td>
                                            <td><?= $item['min_stock_level']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="empty-cell-msg">All inventory stock levels are healthy!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Module-Specific JS -->
    <script src="dashboard.js"></script>
</body>
</html>
