/**
 * Motor Shop Inventory Management Module
 * Handles inventory fetching, modal control, product creation/updating, and deletion.
 */
document.addEventListener('DOMContentLoaded', () => {
    let isEdit = false;
    let productsList = [];

    const productModal = document.getElementById('productModal');
    const productForm = document.getElementById('productForm');
    const modalTitle = document.getElementById('modalTitle');
    const categorySelect = document.getElementById('categoryId');
    const partNumberInput = document.getElementById('partNumber');
    const productIdInput = document.getElementById('productId');
    const inventoryTableBody = document.getElementById('inventoryTableBody');
    const openAddBtn = document.getElementById('openAddProductBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Initialize inventory table
    loadInventory();

    // Event Listeners
    if (openAddBtn) {
        openAddBtn.addEventListener('click', openAddProductModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', generatePartNumber);
    }

    if (productForm) {
        productForm.addEventListener('submit', handleFormSubmit);
    }

    // Modal background click to close
    window.addEventListener('click', (e) => {
        if (e.target === productModal) {
            closeModal();
        }
    });

    // Event delegation on table body for Edit & Delete buttons
    if (inventoryTableBody) {
        inventoryTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const action = btn.dataset.action;
            const productId = parseInt(btn.dataset.id, 10);

            if (action === 'edit') {
                const product = productsList.find(p => parseInt(p.product_id, 10) === productId);
                if (product) {
                    openEditModal(product);
                }
            } else if (action === 'delete') {
                deleteProduct(productId);
            }
        });
    }

    /**
     * Fetch categories and products from backend
     */
    function loadInventory() {
        fetch('/motor_shop/backend/modules/inventory/get_inventory.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Populate Category Dropdown
                    if (categorySelect) {
                        const currentCat = categorySelect.value;
                        categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
                        res.categories.forEach(cat => {
                            categorySelect.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
                        });
                        if (currentCat) {
                            categorySelect.value = currentCat;
                        }
                    }

                    // Store products for edit lookup
                    productsList = res.products || [];

                    // Populate Table Body
                    if (!inventoryTableBody) return;
                    inventoryTableBody.innerHTML = '';

                    if (productsList.length === 0) {
                        inventoryTableBody.innerHTML = `
                            <tr>
                                <td colspan="8" class="empty-table-msg">No spare parts found in inventory.</td>
                            </tr>
                        `;
                        return;
                    }

                    productsList.forEach(p => {
                        let badgeClass = 'badge-active';
                        if (p.status === 'Low Stock' || p.status === 'Out of Stock') {
                            badgeClass = 'badge-inactive';
                        }

                        const brandDisplay = p.brand ? p.brand : 'N/A';
                        const priceDisplay = parseFloat(p.unit_price).toFixed(2);

                        inventoryTableBody.innerHTML += `
                            <tr>
                                <td><strong>${p.part_number}</strong></td>
                                <td>${p.product_name}</td>
                                <td>${brandDisplay}</td>
                                <td>${p.category_name}</td>
                                <td>₱${priceDisplay}</td>
                                <td>${p.quantity}</td>
                                <td><span class="badge ${badgeClass}">${p.status}</span></td>
                                <td>
                                    <button type="button" class="btn-action btn-edit" data-action="edit" data-id="${p.product_id}">Edit</button>
                                    <button type="button" class="btn-action btn-delete" data-action="delete" data-id="${p.product_id}">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(err => {
                console.error('Error loading inventory:', err);
            });
    }

    /**
     * Auto-generate part number based on selected category
     */
    function generatePartNumber() {
        if (isEdit) return;

        const catId = categorySelect ? categorySelect.value : '';
        if (!partNumberInput) return;

        if (!catId) {
            partNumberInput.value = '';
            return;
        }

        fetch(`/motor_shop/backend/modules/inventory/generate_part_number.php?category_id=${catId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    partNumberInput.value = res.part_number;
                }
            })
            .catch(err => {
                console.error('Error generating part number:', err);
            });
    }

    /**
     * Open Modal in Add Product Mode
     */
    function openAddProductModal() {
        isEdit = false;
        if (modalTitle) modalTitle.innerText = 'Add Spare Part';
        if (productForm) productForm.reset();
        if (productIdInput) productIdInput.value = '';
        if (partNumberInput) {
            partNumberInput.value = '';
            partNumberInput.readOnly = true;
        }
        const brandInput = document.getElementById('brand');
        if (brandInput) brandInput.value = '';
        if (productModal) productModal.style.display = 'flex';
    }

    /**
     * Open Modal in Edit Product Mode
     */
    function openEditModal(p) {
        isEdit = true;
        if (modalTitle) modalTitle.innerText = 'Edit Spare Part';
        if (productIdInput) productIdInput.value = p.product_id;
        if (categorySelect) categorySelect.value = p.category_id;
        if (partNumberInput) {
            partNumberInput.value = p.part_number;
            partNumberInput.readOnly = true;
        }

        const nameInput = document.getElementById('productName');
        if (nameInput) nameInput.value = p.product_name;

        const brandInput = document.getElementById('brand');
        if (brandInput) brandInput.value = p.brand || '';

        const priceInput = document.getElementById('unitPrice');
        if (priceInput) priceInput.value = p.unit_price;

        const qtyInput = document.getElementById('quantity');
        if (qtyInput) qtyInput.value = p.quantity;

        const minStockInput = document.getElementById('minStockLevel');
        if (minStockInput) minStockInput.value = p.min_stock_level;

        if (productModal) productModal.style.display = 'flex';
    }

    /**
     * Close the Modal
     */
    function closeModal() {
        if (productModal) productModal.style.display = 'none';
    }

    /**
     * Handle Form Submission for Adding / Updating Product
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData(productForm);
        const endpoint = isEdit
            ? '/motor_shop/backend/modules/inventory/update_product.php'
            : '/motor_shop/backend/modules/inventory/add_product.php';

        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    closeModal();
                    loadInventory();
                }
            })
            .catch(err => {
                console.error('Error saving product:', err);
                alert('An unexpected error occurred while saving.');
            });
    }

    /**
     * Delete Product confirmation & execution
     */
    function deleteProduct(id) {
        if (confirm('Delete this item from inventory?')) {
            const formData = new FormData();
            formData.append('product_id', id);

            fetch('/motor_shop/backend/modules/inventory/delete_product.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(res => {
                    alert(res.message);
                    if (res.status === 'success') {
                        loadInventory();
                    }
                })
                .catch(err => {
                    console.error('Error deleting product:', err);
                    alert('An error occurred while deleting the product.');
                });
        }
    }
});
