<?php
require_once __DIR__ . '/../../../../backend/includes/auth.php';

// Enforce RBAC: Strictly Admin and Inventory Officer access
checkRole(['Admin', 'Inventory Officer']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Control - Motor Shop System</title>
    <!-- Base Layout CSS -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <!-- Module-Specific CSS -->
    <link rel="stylesheet" href="inventory.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div>Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong> (<?= htmlspecialchars($_SESSION['role_name']); ?>)</div>

            </header>

            <div class="inv-products-header">
                <h2>Inventory Control</h2>
                <button type="button" class="btn-add" id="openAddProductBtn">+ Add New Spare Part</button>
            </div>

            <div class="inv-products-table-card">
                <table class="inv-products-table">
                    <thead>
                        <tr>
                            <th>Part #</th>
                            <th>Item Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Price (₱)</th>
                            <th>Stock Qty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody">
                        <!-- Populated dynamically via inventory.js -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Product Modal -->
    <div class="modal" id="productModal">
        <div class="inv-products-modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Spare Part</h3>
                <span class="close-modal" id="closeModalBtn">&times;</span>
            </div>
            <form id="productForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                <input type="hidden" name="product_id" id="productId">

                <div class="form-group">
                    <label for="categoryId">Category</label>
                    <select name="category_id" id="categoryId" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label for="partNumber">Part Number <small class="text-muted">(Auto-generated)</small></label>
                    <input type="text" name="part_number" id="partNumber" class="form-control inv-readonly-input" placeholder="Select category first" readonly required>
                </div>
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" name="product_name" id="productName" class="form-control" placeholder="e.g., Brake Pad Set" required>
                </div>
                <div class="form-group">
                    <label for="brand">Brand</label>
                    <select name="brand" id="brand" class="form-control" required>
                        <option value="">-- Select Brand --</option>
                        <option value="RCB">RCB (Racing Boy)</option>
                        <option value="JVT">JVT Performance</option>
                        <option value="MTRT">MTRT</option>
                        <option value="Pertua">Pertua</option>
                        <option value="Spyder">Spyder</option>
                        <option value="Faito">Faito</option>
                        <option value="Okimura">Okimura</option>
                        <option value="Yamalube">Yamalube</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="unitPrice">Unit Price (₱)</label>
                    <input type="number" step="0.01" name="unit_price" id="unitPrice" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="minStockLevel">Min. Low Stock Threshold</label>
                    <input type="number" name="min_stock_level" id="minStockLevel" class="form-control" value="5" required>
                </div>
                <button type="submit" class="btn-primary inv-submit-btn">Save Product</button>
            </form>
        </div>
    </div>

    <!-- Module-Specific JS -->
    <script src="inventory.js"></script>
</body>
</html>
