/**
 * Motor Shop Admin User Management Module
 * Handles user listing, creation, updates, and deletion.
 */
document.addEventListener('DOMContentLoaded', () => {
    let isEdit = false;
    let usersList = [];

    const userModal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const userIdInput = document.getElementById('userId');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const passHelp = document.getElementById('passHelp');
    const roleIdSelect = document.getElementById('roleId');
    const statusSelect = document.getElementById('status');
    const userTableBody = document.getElementById('userTableBody');
    const openAddBtn = document.getElementById('openAddUserBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Initial load
    loadUsers();

    // Event Listeners
    if (openAddBtn) {
        openAddBtn.addEventListener('click', openAddModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (userForm) {
        userForm.addEventListener('submit', handleFormSubmit);
    }

    // Modal background click
    window.addEventListener('click', (e) => {
        if (e.target === userModal) {
            closeModal();
        }
    });

    // Event delegation on table body for Edit & Delete buttons
    if (userTableBody) {
        userTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const action = btn.dataset.action;
            const userId = parseInt(btn.dataset.id, 10);

            if (action === 'edit') {
                const user = usersList.find(u => parseInt(u.user_id, 10) === userId);
                if (user) {
                    openEditModal(user);
                }
            } else if (action === 'delete') {
                deleteUser(userId);
            }
        });
    }

    /**
     * Fetch user records from backend
     */
    function loadUsers() {
        fetch('/motor_shop/backend/modules/users/get_users.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    usersList = res.data || [];
                    if (!userTableBody) return;
                    userTableBody.innerHTML = '';

                    if (usersList.length === 0) {
                        userTableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="empty-table-msg">No users registered yet.</td>
                            </tr>
                        `;
                        return;
                    }

                    usersList.forEach(user => {
                        const statusClass = user.status === 'Active' ? 'badge-active' : 'badge-inactive';
                        const deleteBtn = parseInt(user.user_id, 10) !== 1
                            ? `<button type="button" class="btn-action btn-delete" data-action="delete" data-id="${user.user_id}">Delete</button>`
                            : '';

                        userTableBody.innerHTML += `
                            <tr>
                                <td>${user.user_id}</td>
                                <td>${user.first_name} ${user.last_name}</td>
                                <td>${user.username}</td>
                                <td>${user.role_name}</td>
                                <td><span class="badge ${statusClass}">${user.status}</span></td>
                                <td>
                                    <button type="button" class="btn-action btn-edit" data-action="edit" data-id="${user.user_id}">Edit</button>
                                    ${deleteBtn}
                                </td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(err => {
                console.error('Error fetching users:', err);
            });
    }

    /**
     * Open Modal in Add User Mode
     */
    function openAddModal() {
        isEdit = false;
        if (modalTitle) modalTitle.innerText = 'Add New User';
        if (userForm) userForm.reset();
        if (userIdInput) userIdInput.value = '';
        if (passHelp) {
            passHelp.classList.add('d-none');
            passHelp.classList.remove('d-inline');
        }
        if (passwordInput) passwordInput.required = true;
        if (userModal) userModal.style.display = 'flex';
    }

    /**
     * Open Modal in Edit User Mode
     */
    function openEditModal(user) {
        isEdit = true;
        if (modalTitle) modalTitle.innerText = 'Edit User';
        if (userIdInput) userIdInput.value = user.user_id;
        if (firstNameInput) firstNameInput.value = user.first_name;
        if (lastNameInput) lastNameInput.value = user.last_name;
        if (usernameInput) usernameInput.value = user.username;
        if (roleIdSelect) roleIdSelect.value = user.role_id;
        if (statusSelect) statusSelect.value = user.status;
        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.required = false;
        }
        if (passHelp) {
            passHelp.classList.remove('d-none');
            passHelp.classList.add('d-inline');
        }
        if (userModal) userModal.style.display = 'flex';
    }

    /**
     * Close User Modal
     */
    function closeModal() {
        if (userModal) userModal.style.display = 'none';
    }

    /**
     * Handle Add / Update Submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData(userForm);
        const endpoint = isEdit
            ? '/motor_shop/backend/modules/users/update_user.php'
            : '/motor_shop/backend/modules/users/add_user.php';

        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    closeModal();
                    loadUsers();
                }
            })
            .catch(err => {
                console.error('Error saving user:', err);
                alert('An error occurred while saving the user account.');
            });
    }

    /**
     * Delete User
     */
    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user?')) {
            const formData = new FormData();
            formData.append('user_id', id);

            fetch('/motor_shop/backend/modules/users/delete_user.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(res => {
                    alert(res.message);
                    if (res.status === 'success') {
                        loadUsers();
                    }
                })
                .catch(err => {
                    console.error('Error deleting user:', err);
                    alert('An error occurred while deleting the user.');
                });
        }
    }
});
