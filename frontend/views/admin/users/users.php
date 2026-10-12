<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

$conn = getDBConnection();

// Fetch roles for the modal dropdown
$roles_query = $conn->query("SELECT * FROM roles ORDER BY role_id ASC");
$roles = [];
while ($r = $roles_query->fetch_assoc()) {
    $roles[] = $r;
}

// Handle Add / Edit Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role_id    = (int)($_POST['role_id'] ?? 2);
    $status     = $_POST['status'] ?? 'Active';

    if ($id) {
        // Update user
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, username=?, password=?, role_id=?, status=? WHERE user_id=?");
            $stmt->bind_param("ssssisi", $first_name, $last_name, $username, $hashed, $role_id, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, username=?, role_id=?, status=? WHERE user_id=?");
            $stmt->bind_param("sssisi", $first_name, $last_name, $username, $role_id, $status, $id);
        }
    } else {
        // Insert new user
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, password, role_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $first_name, $last_name, $username, $hashed, $role_id, $status);
    }

    $stmt->execute();
    header("Location: users.php");
    exit;
}

// Handle Delete (prevent deleting primary admin id 1)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 1) {
        $conn->query("DELETE FROM users WHERE user_id = $id");
    }
    header("Location: users.php");
    exit;
}
// Handle Status Toggle (protect primary admin id 1)
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    if ($id > 1) {
        $stmt = $conn->prepare("UPDATE users SET status = IF(status = 'Active', 'Inactive', 'Active') WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: users.php");
    exit;
}

// Fetch all users with role name
$users = $conn->query("
    SELECT u.user_id, u.first_name, u.last_name, u.username, u.role_id, u.status, u.created_at, r.role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.user_id ASC
");

// Metrics
$total_users = (int)$conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$admin_count = (int)$conn->query("SELECT COUNT(*) AS total FROM users WHERE role_id = 1")->fetch_assoc()['total'] ?? 0;
$staff_count = (int)$conn->query("SELECT COUNT(*) AS total FROM users WHERE role_id = 2")->fetch_assoc()['total'] ?? 0;
$inv_count   = (int)$conn->query("SELECT COUNT(*) AS total FROM users WHERE role_id = 3")->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management – R'A Motor Parts and Accesories</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="users.css">
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
                <h1 class="page-title">User Management</h1>
                <p class="stat-card-sub">Manage system operator roles, permissions, and security credentials</p>
            </div>
            <button type="button" class="btn-add" onclick="openModal()">+ Add New User</button>
        </div>

        <!-- KPI Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Accounts</h4>
                <div class="value"><?= $total_users; ?></div>
                <div class="stat-card-sub">Active login credentials</div>
            </div>
            <div class="stat-card">
                <h4>Administrators</h4>
                <div class="value"><?= $admin_count; ?></div>
                <div class="stat-card-sub">Full system control</div>
            </div>
            <div class="stat-card">
                <h4>Cashier / Staff</h4>
                <div class="value"><?= $staff_count; ?></div>
                <div class="stat-card-sub">Point of sale operators</div>
            </div>
            <div class="stat-card">
                <h4>Inventory Officers</h4>
                <div class="value"><?= $inv_count; ?></div>
                <div class="stat-card-sub">Stock & receiving managers</div>
            </div>
        </div>

        <!-- Users Data Table -->
        <div class="table-container table-container-padded">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Assigned Role</th>
                        <th>Account Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                                   <?php while ($row = $users->fetch_assoc()): ?>
                            <tr>
                                <!-- 1. ID -->
                                <td><code>#<?= $row['user_id']; ?></code></td>

                                <!-- 2. Full Name -->
                                <td><strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong></td>

                                <!-- 3. Username -->
                                <td><?= htmlspecialchars($row['username']); ?></td>

                                <!-- 4. Assigned Role -->
                                <td>
                                    <?php
                                        $role_class = 'role-cashier';
                                        if ($row['role_id'] == 1) $role_class = 'role-admin';
                                        if ($row['role_id'] == 3) $role_class = 'role-officer';
                                    ?>
                                    <span class="badge-role <?= $role_class; ?>">
                                        <?= htmlspecialchars($row['role_name'] ?? 'Staff'); ?>
                                    </span>
                                </td>

                                <!-- 5. Account Status Toggle -->
                                <td>
                                    <?php if ($row['user_id'] == 1): ?>
                                        <span class="status-toggle-pill is-active is-locked" title="Primary Administrator cannot be deactivated">
                                            <span class="toggle-dot"></span>
                                            <span class="toggle-text">Active</span>
                                        </span>
                                    <?php else: ?>
                                        <a href="users.php?toggle_status=<?= (int)$row['user_id']; ?>" 
                                           class="status-toggle-pill <?= $row['status'] === 'Active' ? 'is-active' : 'is-inactive'; ?>"
                                           title="Click to toggle status (Active / Inactive)">
                                            <span class="toggle-dot"></span>
                                            <span class="toggle-text"><?= htmlspecialchars($row['status']); ?></span>
                                        </a>
                                    <?php endif; ?>
                                </td>

                               
                                <td><span class="font-mono"><?= date('M d, Y', strtotime($row['created_at'])); ?></span></td>

                             
                                <td>
                                    <button type="button" class="btn-action btn-edit" onclick="editUser(
                                        <?= (int)$row['user_id']; ?>,
                                        '<?= addslashes($row['first_name']); ?>',
                                        '<?= addslashes($row['last_name']); ?>',
                                        '<?= addslashes($row['username']); ?>',
                                        <?= (int)$row['role_id']; ?>,
                                        '<?= addslashes($row['status']); ?>'
                                    )">Edit</button>

                                    <?php if ($row['user_id'] > 1): ?>
                                        <a href="users.php?delete=<?= (int)$row['user_id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('Delete user account <?= addslashes($row['username']); ?>?')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-table-msg">No system users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add / Edit User Modal -->
<div class="modal" id="user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add System User</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="users.php">
            <input type="hidden" name="user_id" id="user_id">

            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="e.g. John" required>
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="e.g. Doe" required>
            </div>

            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="e.g. jdoe" required>
            </div>

            <div class="form-group">
                <label>Password <small class="form-helper" id="pwd-helper">(Required for new user)</small></label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label>System Role *</label>
                <select name="role_id" id="role_id" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['role_id']; ?>">
                            <?= htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status *</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn-add btn-modal-submit">Save Account</button>
        </form>
    </div>
</div>

<!-- Module-Specific JS -->
<script src="users.js"></script>
</body>
</html>
