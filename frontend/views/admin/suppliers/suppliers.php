<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

$conn = getDBConnection();

// Handle Add / Edit submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'] ?? '';
    $contact = $_POST['contact_person'] ?? '';
    $phone   = $_POST['phone'] ?? '';
    $email   = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $id      = $_POST['supplier_id'] ?? null;

    if ($id) {
       $stmt = $conn->prepare("UPDATE suppliers SET supplier_name=?, contact_person=?, phone=?, email=?, address=? WHERE supplier_id=?");
        $stmt->bind_param("sssssi", $name, $contact, $phone, $email, $address, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");

        $stmt->bind_param("sssss", $name, $contact, $phone, $email, $address);
    }
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM suppliers WHERE supplier_id = $id");
    header("Location: suppliers.php");
    exit;
}

// Fetch all suppliers
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name ASC");
$total = $conn->query("SELECT COUNT(*) AS total FROM suppliers")->fetch_assoc()['total'] ?? 0;
$active = $conn->query("SELECT COUNT(*) AS total FROM suppliers WHERE status='Active'")->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers – R'A Motor Parts</title>
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
            <h1 class="page-title">Suppliers</h1>
            <button class="btn-add" onclick="openModal()">+ Add Supplier</button>
        </div>

        <!-- KPI Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Suppliers</h4>
                <div class="value"><?= $total; ?></div>
            </div>
            <div class="stat-card">
                <h4>Active Suppliers</h4>
                <div class="value"><?= $active; ?></div>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="table-container table-container-padded">
            <table class="data-table mt-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                        <?php while ($row = $suppliers->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['supplier_id']; ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                                <td><?= htmlspecialchars($row['contact_person'] ?? '—'); ?></td>
                                <td><?= htmlspecialchars($row['phone'] ?? '—'); ?></td>
                                <td><?= htmlspecialchars($row['email'] ?? '—'); ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] === 'Active' ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-edit" onclick="editSupplier(
                                        <?= $row['supplier_id']; ?>,
                                        '<?= addslashes($row['supplier_name']); ?>',
                                        '<?= addslashes($row['contact_person']); ?>',
                                        '<?= addslashes($row['phone']); ?>',
                                        '<?= addslashes($row['email']); ?>',
                                        '<?= addslashes($row['address']); ?>'
                                    )">Edit</button>
                                    <a href="suppliers.php?delete=<?= $row['supplier_id']; ?>"
                                       class="btn-action btn-delete"
                                       onclick="return confirm('Delete this supplier?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-table-msg">No suppliers found. Add one to get started.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<!-- Add / Edit Modal -->
<div class="modal" id="supplier-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add Supplier</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="suppliers.php">
            <input type="hidden" name="supplier_id" id="supplier_id">

            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" name="name" id="s-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact_person" id="s-contact" class="form-control">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="s-phone" class="form-control">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="s-email" class="form-control">
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" id="s-address" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn-add" style="width:100%;margin-top:0.5rem;">Save Supplier</button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('supplier-modal');

function openModal() {
    document.getElementById('modal-title').textContent = 'Add Supplier';
    document.getElementById('supplier_id').value = '';
    document.getElementById('s-name').value = '';
    document.getElementById('s-contact').value = '';
    document.getElementById('s-phone').value = '';
    document.getElementById('s-email').value = '';
    document.getElementById('s-address').value = '';
    modal.style.display = 'flex';
}

function editSupplier(id, name, contact, phone, email, address) {
    document.getElementById('modal-title').textContent = 'Edit Supplier';
    document.getElementById('supplier_id').value = id;
    document.getElementById('s-name').value = name;
    document.getElementById('s-contact').value = contact;
    document.getElementById('s-phone').value = phone;
    document.getElementById('s-email').value = email;
    document.getElementById('s-address').value = address;
    modal.style.display = 'flex';
}

function closeModal() {
    modal.style.display = 'none';
}

window.onclick = function(e) {
    if (e.target === modal) closeModal();
}
</script>
</body>
</html>
