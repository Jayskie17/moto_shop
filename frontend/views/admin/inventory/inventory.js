/**
 * Inventory Module Script
 * Handles Add/Edit modals, category prefix generation, and modal events.
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('product-modal');

    // Open Modal for adding a new product
    window.openModal = function () {
        if (!modal) return;
        document.getElementById('modal-title').textContent = 'Add Spare Part';
        document.getElementById('product_id').value = '';
        document.getElementById('category_id').value = '';
        document.getElementById('part_number').value = '';
        document.getElementById('product_name').value = '';
        document.getElementById('brand').value = '';
        document.getElementById('unit_price').value = '';
        document.getElementById('quantity').value = '';
        document.getElementById('min_stock_level').value = '5';
        modal.style.display = 'flex';
    };

    // Open Modal for editing an existing product
    window.editProduct = function (id, categoryId, partNumber, name, brand, price, qty, minLevel) {
        if (!modal) return;
        document.getElementById('modal-title').textContent = 'Edit Spare Part';
        document.getElementById('product_id').value = id;
        document.getElementById('category_id').value = categoryId;
        document.getElementById('part_number').value = partNumber;
        document.getElementById('product_name').value = name;
        document.getElementById('brand').value = brand;
        document.getElementById('unit_price').value = price;
        document.getElementById('quantity').value = qty;
        document.getElementById('min_stock_level').value = minLevel;
        modal.style.display = 'flex';
    };

    // Auto-generate sequential part number code when selecting or changing category (Add & Edit)
    window.handleCategoryChange = function () {
        const select = document.getElementById('category_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        const nextCode = selectedOption ? selectedOption.getAttribute('data-next-code') : '';
        const partNumberInput = document.getElementById('part_number');

        if (partNumberInput && nextCode) {
            partNumberInput.value = nextCode;
        }
    };



    // Close Product Modal
    window.closeModal = function () {
        if (modal) modal.style.display = 'none';
    };

    // Brand Modal handlers
    const brandModal = document.getElementById('brand-modal');

    window.openBrandModal = function () {
        if (!brandModal) return;
        const brandInput = document.getElementById('brand_name');
        if (brandInput) brandInput.value = '';
        brandModal.style.display = 'flex';
    };

    window.closeBrandModal = function () {
        if (brandModal) brandModal.style.display = 'none';
    };

    // Stock In Modal handlers
    const stockInModal = document.getElementById('stockin-modal');

    window.openStockInModal = function (id, name, currentQty) {
        if (!stockInModal) return;
        const idInput = document.getElementById('stockin_product_id');
        const nameEl = document.getElementById('stockin_product_name');
        const qtyEl = document.getElementById('stockin_current_qty');
        const addQtyInput = document.getElementById('add_quantity');

        if (idInput) idInput.value = id;
        if (nameEl) nameEl.textContent = name;
        if (qtyEl) qtyEl.textContent = currentQty;
        if (addQtyInput) addQtyInput.value = '';

        stockInModal.style.display = 'flex';
    };
    window.stepDownQty = function () {
        const input = document.getElementById('add_quantity');
        if (input) {
            let val = parseInt(input.value) || 1;
            if (val > 1) input.value = val - 1;
        }
    };

    window.stepUpQty = function () {
        const input = document.getElementById('add_quantity');
        if (input) {
            let val = parseInt(input.value) || 0;
            input.value = val + 1;
        }
    };


    window.closeStockInModal = function () {
        if (stockInModal) stockInModal.style.display = 'none';
    };

    // Close when clicking outside of modals
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            window.closeModal();
        }
        if (e.target === brandModal) {
            window.closeBrandModal();
        }
        if (e.target === stockInModal) {
            window.closeStockInModal();
        }
    });

    // Real-Time Search & Multi-Filters
    const searchInput = document.getElementById('inventorySearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('.data-table tbody tr');

    function applyInventoryFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedCat = categoryFilter ? categoryFilter.value.toLowerCase().trim() : '';
        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase().trim() : '';

        tableRows.forEach(row => {
            // Read column texts
            const partText = row.children[0] ? row.children[0].innerText.toLowerCase() : '';
            const nameText = row.children[1] ? row.children[1].innerText.toLowerCase() : '';
            const brandText = row.children[2] ? row.children[2].innerText.toLowerCase() : '';
            const categoryText = row.children[3] ? row.children[3].innerText.toLowerCase() : '';
            const statusText = row.children[6] ? row.children[6].innerText.toLowerCase() : '';

            // Match conditions
            const matchesSearch = !searchTerm ||
                partText.includes(searchTerm) ||
                nameText.includes(searchTerm) ||
                brandText.includes(searchTerm);

            const matchesCategory = !selectedCat || categoryText.includes(selectedCat);
            const matchesStatus = !selectedStatus || statusText.includes(selectedStatus);

            // Show or hide row
            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyInventoryFilters);
    if (categoryFilter) categoryFilter.addEventListener('change', applyInventoryFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyInventoryFilters);

});

