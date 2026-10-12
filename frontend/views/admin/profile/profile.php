<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 1);
$conn = getDBConnection();

// Fetch current user details
$stmt = $conn->prepare("
    SELECT u.*, r.role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.role_id 
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – R'A Motor Parts and Accessories</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css?v=<?= time(); ?>">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="profile.css?v=<?= time(); ?>">
</head>
<body>
<div class="dashboard-container">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div>Welcome back, <strong>Administrator</strong></div>
        </header>

        <div class="page-header">
            <div>
                <h1 class="page-title">User Profile</h1>
                <p class="stat-card-sub">Manage your personal credentials and system access details</p>
            </div>
        </div>

        <div class="profile-card">
            <!-- Avatar Banner -->
            <div class="profile-hero">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['first_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="profile-hero-text">
                    <h2><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h2>
                    <span class="profile-badge-role"><?= htmlspecialchars($user['role_name'] ?? 'Admin'); ?></span>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <div class="profile-grid">
                <div class="profile-item">
                    <label>Username</label>
                    <div class="profile-val">@<?= htmlspecialchars($user['username'] ?? 'admin'); ?></div>
                </div>

                <div class="profile-item">
                    <label>Account Status</label>
                    <div class="profile-val">
                        <span class="status-pill-active"><?= htmlspecialchars($user['status'] ?? 'Active'); ?></span>
                    </div>
                </div>

                <div class="profile-item">
                    <label>Member Since</label>
                    <div class="profile-val">
                        <?= !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                    </div>
                </div>

                <div class="profile-item">
                    <label>Access Level</label>
                    <div class="profile-val">Full Administrative Access</div>
                </div>
            </div>

            <!-- Sign Out Action -->
            <div class="profile-logout-section">
                <div>
                    <div class="logout-title">Sign Out of Session</div>
                    <div class="logout-desc">End your active session on this workstation.</div>
                </div>
                <a href="/motor_shop/backend/modules/auth/logout.php" id="btnLogout" class="btn-logout-confirm">
                    Log Out
                </a>
            </div>
        </div>
    </main>
</div>

<!-- Module-Specific JS -->
<script src="profile.js?v=<?= time(); ?>"></script>
</body>
</html>
