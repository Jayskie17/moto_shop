<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

$conn = getDBConnection();

// Fetch all sales with cashier name
$sales = $conn->query("
    SELECT s.sale_id, s.invoice_no, u.first_name, u.last_name,
           s.total_amount, s.amount_paid, s.change_amount, s.created_at
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    ORDER BY s.created_at DESC
");

// Today's summary
$today_revenue = $conn->query("SELECT IFNULL(SUM(total_amount), 0) AS total FROM sales WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$today_count   = $conn->query("SELECT COUNT(*) AS total FROM sales WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$total_revenue = $conn->query("SELECT IFNULL(SUM(total_amount), 0) AS total FROM sales")->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales – R'A Motor Parts</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
<div class="dashboard-container">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">

        <header class="topbar">
            <div>Welcome back, <strong>Administrator</strong></div>

        </header>

        <div class="page-header">
            <h1 class="page-title">Sales</h1>
        </div>

        <!-- KPI Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Today's Revenue</h4>
                <div class="value">₱ <?= number_format($today_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <h4>Transactions Today</h4>
                <div class="value"><?= $today_count; ?></div>
            </div>
            <div class="stat-card">
                <h4>Total Revenue (All Time)</h4>
                <div class="value">₱ <?= number_format($total_revenue, 2); ?></div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="table-container table-container-padded">
            <table class="data-table mt-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No.</th>
                        <th>Cashier</th>
                        <th>Total Amount</th>
                        <th>Amount Paid</th>
                        <th>Change</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sales && $sales->num_rows > 0): ?>
                        <?php while ($row = $sales->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['sale_id']; ?></td>
                                <td><?= htmlspecialchars($row['invoice_no']); ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td>₱ <?= number_format($row['total_amount'], 2); ?></td>
                                <td>₱ <?= number_format($row['amount_paid'], 2); ?></td>
                                <td>₱ <?= number_format($row['change_amount'], 2); ?></td>
                                <td><?= $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-table-msg">No sales transactions yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>
</body>
</html>
