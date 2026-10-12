/**
 * Motor Shop Inventory Reports Module
 * Handles report type switching and printing.
 */
document.addEventListener('DOMContentLoaded', () => {
    const printBtn = document.getElementById('printReportBtn');
    const reportTypeSelect = document.getElementById('reportTypeSelect');

    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }

    if (reportTypeSelect) {
        reportTypeSelect.addEventListener('change', function () {
            if (this.form) {
                this.form.submit();
            }
        });
    }
});
