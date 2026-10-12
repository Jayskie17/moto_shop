/**
 * User Management Module Script
 * Handles Add/Edit modals and form population.
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('user-modal');

    // Open Modal for adding a new user
    window.openModal = function () {
        if (!modal) return;
        document.getElementById('modal-title').textContent = 'Add System User';
        document.getElementById('user_id').value = '';
        document.getElementById('first_name').value = '';
        document.getElementById('last_name').value = '';
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password').required = true;
        document.getElementById('pwd-helper').textContent = '(Required for new user)';
        document.getElementById('role_id').value = '2';
        document.getElementById('status').value = 'Active';
        modal.style.display = 'flex';
    };

    // Open Modal for editing an existing user
    window.editUser = function (id, firstName, lastName, username, roleId, status) {
        if (!modal) return;
        document.getElementById('modal-title').textContent = 'Edit System User';
        document.getElementById('user_id').value = id;
        document.getElementById('first_name').value = firstName;
        document.getElementById('last_name').value = lastName;
        document.getElementById('username').value = username;
        document.getElementById('password').value = '';
        document.getElementById('password').required = false;
        document.getElementById('pwd-helper').textContent = '(Leave blank to keep existing password)';
        document.getElementById('role_id').value = roleId;
        document.getElementById('status').value = status;
        modal.style.display = 'flex';
    };

    // Close Modal
    window.closeModal = function () {
        if (modal) modal.style.display = 'none';
    };

    // Close when clicking outside of modal
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            window.closeModal();
        }
    });
});
