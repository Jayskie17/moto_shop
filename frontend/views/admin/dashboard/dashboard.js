/**
 * Dashboard Module Script
 * Handles subtle entrance animations and live ERP pulse.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Subtle staggered entrance for stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(8px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 60);
    });

    // Subtle staggered entrance for ERP panels
    const erpCards = document.querySelectorAll('.erp-card');
    erpCards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 150 + (i * 80));
    });
});
