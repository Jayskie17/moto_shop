<?php $current_uri = $_SERVER['REQUEST_URI'] ?? ''; ?>
<aside class="sidebar">

    <div class="sidebar-brand">
        <img src="/motor_shop/frontend/assets/images/logo motorshop.png" alt="Logo" class="sidebar-logo">
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">R'A Motor Parts</span>
            <span class="sidebar-brand-sub">and Accessories</span>
        </div>
    </div>

    <nav>
        <a href="../dashboard/dashboard.php" class="<?= strpos($current_uri, '/dashboard') !== false ? 'active' : '' ?>">
            Dashboard
        </a>
        <a href="../sales/sales.php" class="<?= strpos($current_uri, '/sales') !== false ? 'active' : '' ?>">
            Sales
        </a>
        <a href="../inventory/inventory.php" class="<?= strpos($current_uri, '/inventory') !== false ? 'active' : '' ?>">
            Inventory
        </a>
        <a href="../suppliers/suppliers.php" class="<?= strpos($current_uri, '/suppliers') !== false ? 'active' : '' ?>">
            Suppliers
        </a>
        <a href="../users/users.php" class="<?= strpos($current_uri, '/users') !== false ? 'active' : '' ?>">
            User Management
        </a>
        <a href="../reports/reports.php" class="<?= strpos($current_uri, '/reports') !== false ? 'active' : '' ?>">
            Analytics & Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile-card">
            <!-- Clickable Profile Info -->
            <a href="../profile/profile.php" class="user-profile-link" title="View Profile">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?></span>
                    <span class="user-role"><?= htmlspecialchars($_SESSION['role_name'] ?? 'Admin'); ?></span>
                </div>
            </a>

            <!-- Quick Logout Action -->
            <a href="/motor_shop/backend/modules/auth/logout.php" class="user-logout-btn" title="Sign Out">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </a>
        </div>
    </div>

</aside>
