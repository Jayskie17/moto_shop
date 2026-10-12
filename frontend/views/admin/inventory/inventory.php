<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

$conn = getDBConnection();

// Fetch categories along with the next sequential part number (e.g. OIL-0004)
$cat_query = $conn->query("
    SELECT c.*,
           IFNULL(MAX(CAST(SUBSTRING_INDEX(p.part_number, '-', -1) AS UNSIGNED)), 0) + 1 AS next_seq
    FROM categories c
    LEFT JOIN products p ON (p.category_id = c.category_id OR p.part_number LIKE CONCAT(c.code_prefix, '-%'))
    GROUP BY c.category_id, c.category_name, c.code_prefix
    ORDER BY c.category_name ASC
");
$categories = [];
while ($cat = $cat_query->fetch_assoc()) {
    // Generate formatted sequence like OIL-0004 or ACC-0003
    $prefix = !empty($cat['code_prefix']) ? $cat['code_prefix'] : 'PRT';
    $cat['next_part_number'] = sprintf("%s-%04d", $prefix, (int)$cat['next_seq']);
    $categories[] = $cat;
}
// Fetch brands from database
$brand_query = $conn->query("SELECT * FROM brands ORDER BY brand_name ASC");
$brands = [];
if ($brand_query) {
    while ($b = $brand_query->fetch_assoc()) {
        $brands[] = $b;
    }
}
// Handle Add Brand Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_brand') {
    $new_brand = trim($_POST['brand_name'] ?? '');
    if (!empty($new_brand)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO brands (brand_name) VALUES (?)");
        $stmt->bind_param("s", $new_brand);
        $stmt->execute();
    }
    header("Location: inventory.php");
    exit;
}
// Handle Quick Stock Adjustment (Add or Decrease)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'stock_in') {
    $product_id  = (int)($_POST['product_id'] ?? 0);
    $qty_change  = (int)($_POST['add_quantity'] ?? 0);
    $adjust_type = $_POST['adjust_type'] ?? 'add';
    $user_id     = $_SESSION['user_id'] ?? 1;

    if ($product_id > 0 && $qty_change > 0) {
        $p_query = $conn->query("SELECT quantity, min_stock_level FROM products WHERE product_id = $product_id");
        if ($prod = $p_query->fetch_assoc()) {
            $prev_stock = (int)$prod['quantity'];

            if ($adjust_type === 'subtract') {
                $new_stock     = max(0, $prev_stock - $qty_change);
                $movement_type = 'Adjustment';
                $reason        = 'Manual Stock Reduction / Correction';
            } else {
                $new_stock     = $prev_stock + $qty_change;
                $movement_type = 'Stock In';
                $reason        = 'Restock Delivery';
            }

            // Recalculate status
            $new_status = 'In Stock';
            if ($new_stock <= 0) {
                $new_status = 'Out of Stock';
            } elseif ($new_stock <= (int)$prod['min_stock_level']) {
                $new_status = 'Low Stock';
            }

            // 1. Update product quantity & status
            $upd = $conn->prepare("UPDATE products SET quantity = ?, status = ? WHERE product_id = ?");
            $upd->bind_param("isi", $new_stock, $new_status, $product_id);
            $upd->execute();

            // 2. Log in stock_movements audit trail
            $mov = $conn->prepare("INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, previous_stock, new_stock, reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $mov->bind_param("iisiiis", $product_id, $user_id, $movement_type, $qty_change, $prev_stock, $new_stock, $reason);
            $mov->execute();
        }
    }
    header("Location: inventory.php");
    exit;
}



// Handle Add / Edit Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $category_id = (int)($_POST['category_id'] ?? 0);
    $part_number = trim($_POST['part_number'] ?? '');
    $product_name= trim($_POST['product_name'] ?? '');
    $brand       = trim($_POST['brand'] ?? '');
    $unit_price  = (float)($_POST['unit_price'] ?? 0);
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $min_stock   = (int)($_POST['min_stock_level'] ?? 5);

    // Calculate dynamic status
    $status = 'In Stock';
    if ($quantity <= 0) {
        $status = 'Out of Stock';
    } elseif ($quantity <= $min_stock) {
        $status = 'Low Stock';
    }

        if ($id) {
        // Update product
        $stmt = $conn->prepare("UPDATE products SET category_id=?, part_number=?, product_name=?, brand=?, unit_price=?, quantity=?, min_stock_level=?, status=? WHERE product_id=?");
        $stmt->bind_param("isssdiisi", $category_id, $part_number, $product_name, $brand, $unit_price, $quantity, $min_stock, $status, $id);
    } else {
        // Auto-generate part number if empty
        if (empty($part_number)) {
            $prefix = 'PRT';
            foreach ($categories as $c) {
                if ($c['category_id'] == $category_id && !empty($c['code_prefix'])) {
                    $prefix = $c['code_prefix'];
                    break;
                }
            }
            $seq_res = $conn->query("SELECT COUNT(*) AS total FROM products WHERE category_id = $category_id");
            $next_seq = ($seq_res->fetch_assoc()['total'] ?? 0) + 1;
            $part_number = sprintf("%s-%04d", $prefix, $next_seq);
        }

        // Insert new product
        $stmt = $conn->prepare("INSERT INTO products (category_id, part_number, product_name, brand, unit_price, quantity, min_stock_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdiis", $category_id, $part_number, $product_name, $brand, $unit_price, $quantity, $min_stock, $status);
    }

    $stmt->execute();
    header("Location: inventory.php");
    exit;

    $stmt->execute();
    header("Location: inventory.php");
    exit;
}
// Check active tab
$active_tab = $_GET['tab'] ?? 'inventory';

// Fetch Stock Movement History
$movements_query = $conn->query("
    SELECT sm.*, p.part_number, p.product_name, u.username, u.first_name, u.last_name
    FROM stock_movements sm
    LEFT JOIN products p ON sm.product_id = p.product_id
    LEFT JOIN users u ON sm.user_id = u.user_id
    ORDER BY sm.movement_id DESC
    LIMIT 100
");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE product_id = $id");
    header("Location: inventory.php");
    exit;
}

// Fetch all inventory items
$products = $conn->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.product_id DESC
");

// Metrics
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$total_units    = $conn->query("SELECT IFNULL(SUM(quantity), 0) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$low_stock      = $conn->query("SELECT COUNT(*) AS total FROM products WHERE quantity > 0 AND quantity <= min_stock_level")->fetch_assoc()['total'] ?? 0;
$out_stock      = $conn->query("SELECT COUNT(*) AS total FROM products WHERE quantity <= 0")->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory – R'A Motor Parts and Accesories</title>

    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
      <link rel="stylesheet" href="inventory.css?v=<?= time(); ?>">

</head>
<body>
<div class="dashboard-container">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
       <header class="topbar">
    <div>Welcome back, <strong>Administrator</strong></div>
</header>

        <div class="page-header">
            <h1 class="page-title">Inventory Control</h1>
            <div style="display: flex; gap: 0.6rem; align-items: center;">
                <button type="button" class="btn-add" onclick="openBrandModal()" style="background: #ffffff; color: var(--primary); border: 1.5px solid var(--border);">+ Add Brand</button>
                <button type="button" class="btn-add" onclick="openModal()">+ Add New Part</button>
            </div>
        </div>


        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Products</h4>
                <div class="value"><?= number_format($total_products); ?></div>
            </div>
            <div class="stat-card">
                <h4>Total Units on Hand</h4>
                <div class="value"><?= number_format($total_units); ?></div>
            </div>
            <div class="stat-card">
                <h4>Low Stock Alerts</h4>
                <div class="value" style="color: #f59e0b;"><?= number_format($low_stock); ?></div>
            </div>
            <div class="stat-card">
                <h4>Out of Stock</h4>
                <div class="value" style="color: #ef4444;"><?= number_format($out_stock); ?></div>
            </div>
        </div>
                <!-- 1. Tabs Switcher -->
        <div class="inventory-tabs">
            <a href="inventory.php" class="tab-link <?= $active_tab === 'inventory' ? 'active' : '' ?>">
                Parts Catalog
            </a>
            <a href="inventory.php?tab=logs" class="tab-link <?= $active_tab === 'logs' ? 'active' : '' ?>">
                Stock Movement Log
            </a>
        </div>

        <?php if ($active_tab === 'inventory'): ?>
            <!-- 2. Search & Filters Toolbar -->
            <div class="inventory-toolbar">
                <div class="search-wrapper">
                    <input type="text" id="inventorySearch" class="search-input" placeholder="Search part number, item, or brand...">
                </div>

                <div style="min-width: 180px;">
                    <select id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars(strtolower($cat['category_name'])); ?>">
                                <?= htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="min-width: 160px;">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="in stock">In Stock</option>
                        <option value="low stock">Low Stock</option>
                        <option value="out of stock">Out of Stock</option>
                    </select>
                </div>
            </div>

            <!-- 3. Active Products Catalog Table -->
            <div class="table-container table-container-padded">
                <table class="data-table mt-0">
                    <thead>
                        <tr>
                            <th>Part #</th>
                            <th>Item Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock Qty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products && $products->num_rows > 0): ?>
                            <?php while ($row = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['part_number']); ?></code></td>
                                    <td><strong><?= htmlspecialchars($row['product_name']); ?></strong></td>
                                    <td><?= htmlspecialchars($row['brand']); ?></td>
                                    <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>₱ <?= number_format($row['unit_price'], 2); ?></td>
                                    <td><?= (int)$row['quantity']; ?></td>
                                    <td>
                                        <?php 
                                            $qty = (int)$row['quantity'];
                                            $minStock = (int)($row['min_stock_level'] ?? 5);

                                            if ($qty <= 0) {
                                                $displayStatus = 'Out of Stock';
                                                $badgeClass = 'badge-inactive';
                                            } elseif ($qty <= $minStock) {
                                                $displayStatus = 'Low Stock';
                                                $badgeClass = 'badge-pending';
                                            } else {
                                                $displayStatus = 'In Stock';
                                                $badgeClass = 'badge-active';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass; ?>">
                                            <?= htmlspecialchars($displayStatus); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-action" 
                                                style="background: #e5f4ec; color: #1f6b47; font-weight: 600;" 
                                                onclick="openStockInModal(<?= (int)$row['product_id']; ?>, '<?= addslashes($row['product_name']); ?>', <?= (int)$row['quantity']; ?>)">
                                            ± Stock
                                        </button>

                                        <button class="btn-action btn-edit" onclick="editProduct(
                                            <?= (int)$row['product_id']; ?>,
                                            <?= (int)$row['category_id']; ?>,
                                            '<?= addslashes($row['part_number']); ?>',
                                            '<?= addslashes($row['product_name']); ?>',
                                            '<?= addslashes($row['brand']); ?>',
                                            <?= (float)$row['unit_price']; ?>,
                                            <?= (int)$row['quantity']; ?>,
                                            <?= (int)$row['min_stock_level']; ?>
                                        )">Edit</button>
                                        <a href="inventory.php?delete=<?= (int)$row['product_id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('Delete this product from inventory?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-table-msg">No inventory records found. Add your first spare part!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <!-- 4. Stock Movement / History Log Table -->
            <div class="table-container table-container-padded">
                <table class="data-table mt-0">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Date & Time</th>
                            <th>Part # & Item</th>
                            <th>Movement Type</th>
                            <th>Quantity</th>
                            <th>Stock Change</th>
                            <th>Reason / Remarks</th>
                            <th>Logged By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($movements_query && $movements_query->num_rows > 0): ?>
                            <?php while ($log = $movements_query->fetch_assoc()): ?>
                                <tr>
                                    <td><code>#<?= $log['movement_id']; ?></code></td>
                                    <td><span class="font-mono"><?= date('M d, Y h:i A', strtotime($log['created_at'])); ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['product_name'] ?? 'Unknown Item'); ?></strong>
                                        <br><small style="color: #6b7280; font-family: monospace;"><?= htmlspecialchars($log['part_number'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                            $m_type = $log['movement_type'];
                                            $badge_style = 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;';
                                            if ($m_type === 'Stock Out') {
                                                $badge_style = 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;';
                                            } elseif ($m_type === 'Adjustment') {
                                                $badge_style = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                                            }
                                        ?>
                                        <span class="badge" style="<?= $badge_style; ?>">
                                            <?= htmlspecialchars($m_type); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: <?= $m_type === 'Stock Out' ? '#dc2626' : '#16a34a'; ?>;">
                                            <?= ($m_type === 'Stock Out' ? '-' : '+') . (int)$log['quantity']; ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span style="color: #6b7280;"><?= (int)$log['previous_stock']; ?></span>
                                        &rarr;
                                        <strong><?= (int)$log['new_stock']; ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($log['reason'] ?: 'Standard update'); ?></td>
                                    <td><?= htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-table-msg">No stock movements recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </main>
</div>

<!-- Add / Edit Modal -->
<div class="modal" id="product-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add Spare Part</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="inventory.php">
            <input type="hidden" name="product_id" id="product_id">

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" id="category_id" class="form-control" required onchange="handleCategoryChange()">
    <option value="">-- Select Category --</option>
    <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['category_id']; ?>" 
                data-prefix="<?= htmlspecialchars($cat['code_prefix'] ?? 'PRT'); ?>"
                data-next-code="<?= htmlspecialchars($cat['next_part_number']); ?>">
            <?= htmlspecialchars($cat['category_name']); ?>
        </option>
    <?php endforeach; ?>
</select>

            </div>

            <div class="form-group">
                <label>Part Number <small class="form-helper">(Auto-generated or custom)</small></label>
                <input type="text" name="part_number" id="part_number" class="form-control" placeholder="Select category to generate">
            </div>

            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="product_name" id="product_name" class="form-control" placeholder="e.g. Racing Brake Caliper" required>
            </div>
                       <div class="form-group">
                <label>Brand *</label>
                <select name="brand" id="brand" class="form-control" required>
                    <option value="">-- Select Brand --</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= htmlspecialchars($b['brand_name']); ?>">
                            <?= htmlspecialchars($b['brand_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>



            <div class="form-group">
                <label>Unit Price (₱) *</label>
                <input type="number" step="0.01" name="unit_price" id="unit_price" class="form-control" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" name="quantity" id="quantity" class="form-control" placeholder="0" required>
            </div>

            <div class="form-group">
                <label>Low Stock Alert Level</label>
                <input type="number" name="min_stock_level" id="min_stock_level" class="form-control" value="5" required>
            </div>

            <button type="submit" class="btn-add" style="width: 100%; margin-top: 0.5rem;">Save Part</button>
        </form>
    </div>
</div>
<!-- Quick Add Brand Modal -->
<div class="modal" id="brand-modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Add New Brand</h3>
            <span class="close-modal" onclick="closeBrandModal()">&times;</span>
        </div>
        <form method="POST" action="inventory.php">
            <input type="hidden" name="action" value="add_brand">
            <div class="form-group">
                <label>Brand Name *</label>
                <input type="text" name="brand_name" id="brand_name" class="form-control" placeholder="e.g. Brembo, Koso, Akrapovic" required>
            </div>
            <button type="submit" class="btn-add" style="width: 100%; margin-top: 0.5rem;">Save Brand</button>
        </form>
    </div>
</div>
<!-- Quick Restock / Stock In Modal -->
<div class="modal" id="stockin-modal">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
            <h3>Restock Product</h3>
            <span class="close-modal" onclick="closeStockInModal()">&times;</span>
        </div>
        <form method="POST" action="inventory.php">
            <input type="hidden" name="action" value="stock_in">
            <input type="hidden" name="product_id" id="stockin_product_id">

            <div style="background: var(--surface-low); padding: 0.85rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--border);">
                <div style="font-weight: 700; color: var(--primary);" id="stockin_product_name">Product Name</div>
                <div style="font-size: 0.82rem; color: var(--muted); margin-top: 0.2rem;">
                    Current Units: <strong id="stockin_current_qty" style="color: var(--text);">0</strong>
                </div>
                      <!-- Quantity Stepper -->
            <div class="form-group">
                <label>Quantity *</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" onclick="stepDownQty()" style="width: 42px; height: 42px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--surface-low); font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">-</button>
                    <input type="number" name="add_quantity" id="add_quantity" class="form-control" min="1" value="1" style="text-align: center; font-size: 1rem; font-weight: 600;" required>
                    <button type="button" onclick="stepUpQty()" style="width: 42px; height: 42px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--surface-low); font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                </div>
            </div>

            <!-- Submit Button: Add Stock -->
            <div style="display: flex; gap: 0.5rem; margin-top: 0.8rem;">
                <button type="submit" name="adjust_type" value="add" class="btn-add" style="flex: 1; background: var(--secondary);">Add Stock</button>
            </div>

</div>

<script src="inventory.js?v=<?= time(); ?>"></script>
</body>
</html>

