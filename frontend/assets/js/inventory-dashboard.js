/**
 * Motor Shop Inventory Analytics Dashboard Module
 * Handles Chart.js initialization for fastest moving products.
 */
document.addEventListener('DOMContentLoaded', () => {
    const chartCanvas = document.getElementById('fastestMovingChart');
    if (!chartCanvas || typeof Chart === 'undefined') return;

    let chartLabels = [];
    let chartData = [];

    try {
        chartLabels = JSON.parse(chartCanvas.dataset.labels || '[]');
        chartData = JSON.parse(chartCanvas.dataset.values || '[]');
    } catch (e) {
        console.error('Error parsing chart data from canvas dataset:', e);
    }

    const ctx = chartCanvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Units Sold',
                data: chartData,
                backgroundColor: '#EF4444',
                borderColor: '#DC2626',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
