<?php
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
?>
<aside class="sidebar">
    <h3>MOTORSHOP INVENTORY</h3>
    <nav>
        <a href="../dashboard/dashboard.php" class="<?= (strpos($current_uri, '/dashboard') !== false) ? 'active' : ''; ?>">Dashboard</a>
        <a href="../inventory/inventory.php" class="<?= (strpos($current_uri, '/inventory') !== false) ? 'active' : ''; ?>">Products & Stock</a>
        <a href="../reports/reports.php" class="<?= (strpos($current_uri, '/reports') !== false) ? 'active' : ''; ?>">Inventory Reports</a>
    </nav>
</aside>