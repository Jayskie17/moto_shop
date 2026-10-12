<?php
require_once __DIR__ . '/../../../../backend/includes/auth.php';
checkRole(['Admin']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports – R'A Motor Parts and Accesories</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="reports.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="topbar">
                <div>Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?></strong> (<?= htmlspecialchars($_SESSION['role_name'] ?? 'Admin'); ?>)</div>

            </header>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Sales Analytics & Ledger</h1>
                    <p class="page-subtitle">R'A Motor Parts and Accesories — Revenue audit, item velocities, and transaction records</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-secondary" id="printReportBtn">Print Report</button>
                </div>
            </div>

            <!-- Filter Panel with Quick Date Presets -->
            <div class="reports-filter-panel">
                <div class="filter-inputs">
                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" id="startDate" class="form-control" value="<?= date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input type="date" id="endDate" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                    <button type="button" class="btn-primary" id="generateReportBtn">Apply Filter</button>
                </div>
                <div class="quick-presets">
                    <span class="preset-label">Quick:</span>
                    <button type="button" class="preset-btn" data-range="today">Today</button>
                    <button type="button" class="preset-btn" data-range="week">Last 7 Days</button>
                    <button type="button" class="preset-btn active" data-range="month">This Month</button>
                </div>
            </div>

            <!-- 4-Stat KPI Summary Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Gross Revenue</h4>
                    <div class="stat-val font-mono" id="statRevenue">₱0.00</div>
                    <span class="stat-card-sub">Filtered turnover</span>
                </div>
                <div class="stat-card">
                    <h4>Completed Invoices</h4>
                    <div class="stat-val font-mono" id="statTransactions">0</div>
                    <span class="stat-card-sub">Customer checkouts</span>
                </div>
                <div class="stat-card">
                    <h4>Average Ticket Size</h4>
                    <div class="stat-val font-mono" id="statAvgValue">₱0.00</div>
                    <span class="stat-card-sub">Mean spend per sale</span>
                </div>
                <div class="stat-card">
                    <h4>Physical Items Sold</h4>
                    <div class="stat-val font-mono" id="statUnitsSold">0</div>
                    <span class="stat-card-sub">Stock units depleted</span>
                </div>
            </div>

            <!-- Split Analytics View: Transactions vs Fast-Moving & Category Breakdown -->
            <div class="reports-dual-layout">
                <!-- Left/Main: Transaction Ledger -->
                <section class="ledger-section">
                    <div class="section-card">
                        <div class="card-header-flex">
                            <h3>Transaction Audit Register</h3>
                            <span class="badge" id="txCountBadge">0 records</span>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date & Time</th>
                                        <th>Cashier</th>
                                        <th class="text-right">Total Amount</th>
                                        <th class="text-right">Paid</th>
                                        <th class="text-right">Change</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTableBody">
                                    <!-- Populated dynamically via reports.js -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Right/Sidebar: Fast Moving Parts & Category Breakdown -->
                <aside class="insights-section">
                    <!-- Top Moving Parts -->
                    <div class="section-card mb-4">
                        <div class="card-header-flex">
                            <h3>Fast-Moving Parts</h3>
                            <small class="text-muted">Top 5 by Volume</small>
                        </div>
                        <div id="topProductsList" class="ranking-list">
                            <!-- Populated dynamically via reports.js -->
                        </div>
                    </div>

                    <!-- Category Share -->
                    <div class="section-card">
                        <div class="card-header-flex">
                            <h3>Revenue by Category</h3>
                        </div>
                        <div id="categoryBreakdownList" class="category-breakdown-list">
                            <!-- Populated dynamically via reports.js -->
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>

    <!-- Module-Specific JS -->
    <script src="reports.js"></script>
</body>
</html>
