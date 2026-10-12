/**
 * Inventory Products & Stock Module Script
 * Handles product listing, auto-generating part numbers, modal form, and deletion.
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

    loadInventory();

    if (openAddBtn) openAddBtn.addEventListener('click', openAddProductModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (categorySelect) categorySelect.addEventListener('change', generatePartNumber);
    if (productForm) productForm.addEventListener('submit', handleFormSubmit);

    window.addEventListener('click', (e) => {
        if (e.target === productModal) closeModal();
    });

    if (inventoryTableBody) {
        inventoryTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const action = btn.dataset.action;
            const productId = parseInt(btn.dataset.id, 10);

            if (action === 'edit') {
                const product = productsList.find(p => parseInt(p.product_id, 10) === productId);
                if (product) openEditModal(product);
            } else if (action === 'delete') {
                deleteProduct(productId);
            }
        });
    }

    function loadInventory() {
        fetch('/motor_shop/backend/modules/inventory/get_inventory.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (categorySelect) {
                        const currentCat = categorySelect.value;
                        categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
                        res.categories.forEach(cat => {
                            categorySelect.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
                        });
                        if (currentCat) categorySelect.value = currentCat;
                    }

                    productsList = res.products || [];
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
            .catch(err => console.error('Error loading inventory:', err));
    }

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
            .catch(err => console.error('Error generating part number:', err));
    }

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

    function closeModal() {
        if (productModal) productModal.style.display = 'none';
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData(productForm);
        const endpoint = isEdit
            ? '/motor_shop/backend/modules/inventory/update_product.php'
            : '/motor_shop/backend/modules/inventory/add_product.php';

        fetch(endpoint, { method: 'POST', body: formData })
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

    function deleteProduct(id) {
        if (confirm('Delete this item from inventory?')) {
            const formData = new FormData();
            formData.append('product_id', id);

            fetch('/motor_shop/backend/modules/inventory/delete_product.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    alert(res.message);
                    if (res.status === 'success') loadInventory();
                })
                .catch(err => {
                    console.error('Error deleting product:', err);
                    alert('An error occurred while deleting the product.');
                });
        }
    }
});
