/**
 * Motor Shop Admin Sales Reports Module
 * Handles date filtering, metric card population, and transaction reporting.
 */
document.addEventListener('DOMContentLoaded', () => {
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const generateBtn = document.getElementById('generateReportBtn');
    const statTransactions = document.getElementById('statTransactions');
    const statRevenue = document.getElementById('statRevenue');
    const statAvgValue = document.getElementById('statAvgValue');
    const reportsTableBody = document.getElementById('reportsTableBody');

    // Initial load
    loadReports();

    if (generateBtn) {
        generateBtn.addEventListener('click', loadReports);
    }

    /**
     * Load sales reports within the selected date range
     */
    function loadReports() {
        const startDate = startDateInput ? startDateInput.value : '';
        const endDate = endDateInput ? endDateInput.value : '';

        fetch(`/motor_shop/backend/modules/reports/get_sales_reports.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Update Summary Metric Cards
                    if (statTransactions) {
                        statTransactions.innerText = res.summary.total_transactions || 0;
                    }
                    if (statRevenue) {
                        const totalRev = parseFloat(res.summary.total_revenue || 0).toFixed(2);
                        statRevenue.innerText = `₱${totalRev}`;
                    }
                    if (statAvgValue) {
                        const avgVal = parseFloat(res.summary.average_order_value || 0).toFixed(2);
                        statAvgValue.innerText = `₱${avgVal}`;
                    }

                    // Populate Table
                    if (!reportsTableBody) return;
                    reportsTableBody.innerHTML = '';

                    const transactions = res.transactions || [];
                    if (transactions.length === 0) {
                        reportsTableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="empty-table-msg">No transactions found for the selected date range.</td>
                            </tr>
                        `;
                        return;
                    }

                    transactions.forEach(t => {
                        const totalAmt = parseFloat(t.total_amount).toFixed(2);
                        const paidAmt = parseFloat(t.amount_paid).toFixed(2);
                        const changeAmt = parseFloat(t.change_amount).toFixed(2);

                        reportsTableBody.innerHTML += `
                            <tr>
                                <td><strong>${t.invoice_no}</strong></td>
                                <td>${t.created_at}</td>
                                <td>${t.cashier_name}</td>
                                <td>₱${totalAmt}</td>
                                <td>₱${paidAmt}</td>
                                <td>₱${changeAmt}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(err => {
                console.error('Error fetching sales reports:', err);
            });
    }
});
