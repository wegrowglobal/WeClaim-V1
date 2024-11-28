// Global variables for modals and forms
let deleteUserId = null;

window.openEditUserModal = function(user) {
    Swal.fire({
        title: 'Edit User',
        html: `
            <form id="editUserForm" class="space-y-4 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="first_name" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="First Name" value="${user.first_name}">
                    </div>
                    <div>
                        <label for="second_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="second_name" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="Last Name" value="${user.second_name}">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="Email" value="${user.email}">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                    <input type="tel" id="phone" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="Phone" value="${user.phone || ''}">
                </div>
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role_id" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all">
                        <option value="">Select Role</option>
                        ${window.roles.map(role => `
                            <option value="${role.id}" ${role.id === user.role_id ? 'selected' : ''}>
                                ${role.name}
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department_id" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all">
                        <option value="">Select Department</option>
                        ${window.departments.map(dept => `
                            <option value="${dept.id}" ${dept.id === user.department_id ? 'selected' : ''}>
                                ${dept.name}
                            </option>
                        `).join('')}
                    </select>
                </div>
            </form>
        `,
        customClass: {
            container: 'swal-wide',
            popup: 'swal-tall',
            content: 'swal-content-left'
        },
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const formData = {
                first_name: document.getElementById('first_name').value,
                second_name: document.getElementById('second_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                role_id: document.getElementById('role_id').value,
                department_id: document.getElementById('department_id').value,
            };

            return fetch(`/users/${user.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed to update user');
                return data;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'User updated successfully',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        }
    }).catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
        });
    });
};

window.showDeleteModal = function(userId) {
    deleteUserId = userId;
    Swal.fire({
        title: 'Delete User',
        text: 'Are you sure you want to delete this user? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            confirmDeleteUser();
        }
    });
};

// Create User Modal Functions
window.openCreateUserModal = function() {
    Swal.fire({
        title: 'Create New User',
        html: `
            <form id="createUserForm" class="space-y-4 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="first_name" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="First Name">
                    </div>
                    <div>
                        <label for="second_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="second_name" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="Last Name">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="Email">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                    <input type="tel" id="phone" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all" placeholder="Phone">
                </div>
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role_id" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all">
                        <option value="">Select Role</option>
                        ${window.roles.map(role => `<option value="${role.id}">${role.name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department_id" class="mt-1 block w-full px-3 py-2 text-sm sm:text-base rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all">
                        <option value="">Select Department</option>
                        ${window.departments.map(dept => `<option value="${dept.id}">${dept.name}</option>`).join('')}
                    </select>
                </div>
            </form>
        `,
        customClass: {
            container: 'swal-wide',
            popup: 'swal-tall',
            content: 'swal-content-left'
        },
        showCancelButton: true,
        confirmButtonText: 'Create',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const formData = {
                first_name: document.getElementById('first_name').value,
                second_name: document.getElementById('second_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                role_id: document.getElementById('role_id').value,
                department_id: document.getElementById('department_id').value,
            };

            return fetch('/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed to create user');
                return data;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Success', 'User created successfully', 'success')
                .then(() => location.reload());
        }
    }).catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
        });
    });
};

// Delete User Functions
function confirmDeleteUser() {
    if (!deleteUserId) return;

    fetch(`/users/${deleteUserId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Deleted!', 'User has been deleted.', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Failed to delete user', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'Failed to delete user', 'error');
    });
}

// Initialize table sorting when document is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initializeTableSorting === 'function') {
        initializeTableSorting();
    }
});

// View switching functionality
window.switchView = function(view) {
    const registeredView = document.getElementById('registeredUsersView');
    const pendingView = document.getElementById('pendingRequestsView');
    const registeredBtn = document.getElementById('registeredUsersBtn');
    const pendingBtn = document.getElementById('pendingRequestsBtn');

    if (view === 'registered') {
        registeredView.classList.remove('hidden');
        pendingView.classList.add('hidden');
        registeredBtn.classList.add('bg-white', 'shadow');
        pendingBtn.classList.remove('bg-white', 'shadow');
    } else {
        registeredView.classList.add('hidden');
        pendingView.classList.remove('hidden');
        registeredBtn.classList.remove('bg-white', 'shadow');
        pendingBtn.classList.add('bg-white', 'shadow');
    }
};

// Request handling functions
window.approveRequest = function(requestId) {
    Swal.fire({
        title: 'Approve Request',
        text: 'Are you sure you want to approve this registration request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        confirmButtonText: 'Approve'
    }).then((result) => {
        if (result.isConfirmed) {
            handleRequestAction(requestId, 'approve');
        }
    });
};

window.rejectRequest = function(requestId) {
    Swal.fire({
        title: 'Reject Request',
        text: 'Are you sure you want to reject this registration request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Reject'
    }).then((result) => {
        if (result.isConfirmed) {
            handleRequestAction(requestId, 'reject');
        }
    });
};

function handleRequestAction(requestId, action) {
    fetch(`/registration-requests/${requestId}/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
    if (data.success) {
            Swal.fire('Success', `Request ${action}ed successfully`, 'success')
                .then(() => location.reload());
        } else {
            throw new Error(data.message || `Failed to ${action} request`);
        }
    })
    .catch(error => {
        Swal.fire('Error', error.message, 'error');
    });
}

window.handleRegistrationRequest = function(requestId, action) {
    Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Request`,
        text: `Are you sure you want to ${action} this registration request?`,
        icon: action === 'approve' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'approve' ? '#059669' : '#dc2626',
        confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1)
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/registration-requests/${requestId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: `Request ${action}ed successfully`,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.message || `Failed to ${action} request`);
                }
            })
            .catch(error => {
                Swal.fire('Error', error.message, 'error');
            });
        }
    });
};
