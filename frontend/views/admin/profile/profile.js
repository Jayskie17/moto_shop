document.addEventListener('DOMContentLoaded', () => {
    const logoutBtn = document.getElementById('btnLogout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to log out of your session?')) {
                e.preventDefault();
            }
        });
    }
});
