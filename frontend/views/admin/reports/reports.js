/**
 * Admin Sales Reports Module Script
 * Handles date filtering, quick presets, card metrics, fast-moving items, and transactions.
 */
document.addEventListener('DOMContentLoaded', () => {
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const generateBtn = document.getElementById('generateReportBtn');
    const printBtn = document.getElementById('printReportBtn');
    const presetBtns = document.querySelectorAll('.preset-btn');

    // KPI Card Elements
    const statRevenue = document.getElementById('statRevenue');
    const statTransactions = document.getElementById('statTransactions');
    const statAvgValue = document.getElementById('statAvgValue');
    const statUnitsSold = document.getElementById('statUnitsSold');

    // Containers
    const reportsTableBody = document.getElementById('reportsTableBody');
    const txCountBadge = document.getElementById('txCountBadge');
    const topProductsList = document.getElementById('topProductsList');
    const categoryBreakdownList = document.getElementById('categoryBreakdownList');

    // Helper: format currency
    const formatPeso = (amount) => `₱${parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    // Initial Load
    loadReports();

    // Event Listeners
    if (generateBtn) {
        generateBtn.addEventListener('click', () => {
            // Remove active state from preset buttons when user manually clicks apply
            presetBtns.forEach(b => b.classList.remove('active'));
            loadReports();
        });
    }

    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }

    // Quick Date Preset Buttons
    presetBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            presetBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const range = btn.dataset.range;
            const today = new Date();
            const formatDate = (d) => d.toISOString().split('T')[0];

            if (range === 'today') {
                startDateInput.value = formatDate(today);
                endDateInput.value = formatDate(today);
            } else if (range === 'week') {
                const pastWeek = new Date();
                pastWeek.setDate(today.getDate() - 7);
                startDateInput.value = formatDate(pastWeek);
                endDateInput.value = formatDate(today);
            } else if (range === 'month') {
                const firstDayMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                startDateInput.value = formatDate(firstDayMonth);
                endDateInput.value = formatDate(today);
            }

            loadReports();
        });
    });

    // Core Fetch & Render Function
    function loadReports() {
        const startDate = startDateInput ? startDateInput.value : '';
        const endDate = endDateInput ? endDateInput.value : '';

        fetch(`/motor_shop/backend/modules/reports/get_sales_reports.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // 1. Update KPI Cards
                    const summary = res.summary || {};
                    if (statRevenue) statRevenue.innerText = formatPeso(summary.total_revenue);
                    if (statTransactions) statTransactions.innerText = summary.total_transactions || 0;
                    if (statAvgValue) statAvgValue.innerText = formatPeso(summary.average_order_value);
                    if (statUnitsSold) statUnitsSold.innerText = summary.total_units_sold || 0;

                    // 2. Render Transaction Audit Ledger
                    const transactions = res.transactions || [];
                    if (txCountBadge) txCountBadge.innerText = `${transactions.length} records`;

                    if (reportsTableBody) {
                        reportsTableBody.innerHTML = '';
                        if (transactions.length === 0) {
                            reportsTableBody.innerHTML = `
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 2rem;">
                                        No transactions found for the selected date range.
                                    </td>
                                </tr>
                            `;
                        } else {
                            transactions.forEach(t => {
                                reportsTableBody.innerHTML += `
                                    <tr>
                                        <td><strong class="font-mono">${t.invoice_no}</strong></td>
                                        <td>${t.created_at}</td>
                                        <td>${t.cashier_name}</td>
                                        <td class="text-right font-mono"><strong>${formatPeso(t.total_amount)}</strong></td>
                                        <td class="text-right font-mono">${formatPeso(t.amount_paid)}</td>
                                        <td class="text-right font-mono">${formatPeso(t.change_amount)}</td>
                                    </tr>
                                `;
                            });
                        }
                    }

                    // 3. Render Fast-Moving Products (Top 5)
                    if (topProductsList) {
                        topProductsList.innerHTML = '';
                        const topItems = res.top_products || [];
                        if (topItems.length === 0) {
                            topProductsList.innerHTML = `<p style="font-size: 0.85rem; color: var(--muted);">No items sold in this period.</p>`;
                        } else {
                            topItems.forEach((item, index) => {
                                topProductsList.innerHTML += `
                                    <div class="ranking-item">
                                        <div class="ranking-rank">${index + 1}</div>
                                        <div class="ranking-info">
                                            <div class="ranking-name">${item.product_name}</div>
                                            <div class="ranking-meta">${item.part_number} · ${item.category_name}</div>
                                        </div>
                                        <div class="ranking-stats">
                                            <div class="ranking-units">${item.units_sold} sold</div>
                                            <div class="ranking-rev font-mono">${formatPeso(item.total_revenue)}</div>
                                        </div>
                                    </div>
                                `;
                            });
                        }
                    }

                    // 4. Render Category Sales Breakdown
                    if (categoryBreakdownList) {
                        categoryBreakdownList.innerHTML = '';
                        const categories = res.category_breakdown || [];
                        if (categories.length === 0) {
                            categoryBreakdownList.innerHTML = `<p style="font-size: 0.85rem; color: var(--muted);">No category data available.</p>`;
                        } else {
                            categories.forEach(cat => {
                                categoryBreakdownList.innerHTML += `
                                    <div class="category-row">
                                        <span class="cat-title">${cat.category_name} (${cat.code_prefix})</span>
                                        <span class="cat-rev font-mono">${formatPeso(cat.category_revenue)}</span>
                                    </div>
                                `;
                            });
                        }
                    }
                }
            })
            .catch(err => console.error('Error fetching sales reports:', err));
    }
});
