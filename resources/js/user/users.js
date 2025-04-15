// Global variables for modals and forms
let deleteUserId = null;

window.openEditUserModal = function(user) {
    Swal.fire({
        title: 'Edit User',
        html: `
            <form id="editUserForm" class="space-y-4 text-left">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Basic Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" value="${user.first_name}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="edit_second_name">Second Name</label>
                        <input type="text" id="edit_second_name" name="second_name" value="${user.second_name}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" value="${user.email}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="edit_phone">Phone</label>
                        <input type="tel" id="edit_phone" name="phone" value="${user.phone || ''}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Address Information -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Address Information</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700" for="edit_address">Address</label>
                            <input type="text" id="edit_address" name="address" value="${user.address || ''}"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_city">City</label>
                            <input type="text" id="edit_city" name="city" value="${user.city || ''}"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_state">State</label>
                            <input type="text" id="edit_state" name="state" value="${user.state || ''}"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_zip_code">ZIP Code</label>
                            <input type="text" id="edit_zip_code" name="zip_code" value="${user.zip_code || ''}"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_country">Country</label>
                            <input type="text" id="edit_country" name="country" value="${user.country || ''}"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Role and Department -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Role & Department</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_role">Role</label>
                            <select id="edit_role" name="role_id" 
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                                ${window.roles.map(role => `
                                    <option value="${role.id}" ${user.role_id === role.id ? 'selected' : ''}>
                                        ${role.name}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_department">Department</label>
                            <select id="edit_department" name="department_id"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                                ${window.departments.map(dept => `
                                    <option value="${dept.id}" ${user.department_id === dept.id ? 'selected' : ''}>
                                        ${dept.name}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        cancelButtonText: 'Cancel',
        customClass: {
            container: 'edit-user-modal',
            popup: 'rounded-lg shadow-xl',
            content: 'p-0',
            confirmButton: 'bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
            cancelButton: 'bg-white text-gray-700 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
        },
        preConfirm: () => {
            const form = document.getElementById('editUserForm');
            const formData = new FormData(form);
            return fetch(`/users/${user.id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(json => Promise.reject(json));
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(
                    `Request failed: ${error.message || 'Unknown error'}`
                );
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Success!',
                text: 'User updated successfully',
                icon: 'success',
                customClass: {
                    confirmButton: 'bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
                }
            }).then(() => {
                window.location.reload();
            });
        }
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

// Tab switching functionality
window.switchTab = function(tab) {
    const tabs = {
        users: document.getElementById('usersTab'),
        pending: document.getElementById('pendingTab')
    };
    
    // Hide all tabs
    Object.values(tabs).forEach(tabElement => {
        tabElement.classList.add('hidden');
    });
    
    // Show selected tab
    tabs[tab].classList.remove('hidden');
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Highlight active tab
    const activeBtn = document.querySelector(`[onclick="switchTab('${tab}')"]`);
    activeBtn.classList.add('active', 'border-indigo-500', 'text-indigo-600');
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
    
    // If switching to pending tab, fetch latest requests
    if (tab === 'pending') {
        fetchPendingRequests();
    }
}

// Fetch pending registration requests
function fetchPendingRequests() {
    fetch('/registration-requests', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updatePendingRequestsTable(data.requests);
        }
    })
    .catch(error => {
        console.error('Error fetching pending requests:', error);
    });
}

// Update pending requests table
function updatePendingRequestsTable(requests) {
    const tableBody = document.querySelector('#pendingTab table tbody');
    if (!tableBody) return;

    if (requests.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                    No pending requests found
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = requests.map(request => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-4 text-sm text-gray-900">${request.id}</td>
            <td class="px-4 py-4 text-sm text-gray-900">${request.first_name} ${request.second_name}</td>
            <td class="px-4 py-4 text-sm text-gray-900">${request.email}</td>
            <td class="px-4 py-4 text-sm text-gray-900">${request.created_at}</td>
            <td class="px-4 py-4 text-right">
                <div class="flex justify-end gap-2">
                    <button onclick="handleRegistrationRequest(${request.id}, 'approve')"
                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Approve
                    </button>
                    <button onclick="handleRegistrationRequest(${request.id}, 'reject')"
                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Reject
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Handle registration request actions
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
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || `Failed to ${action} request`);
                }
                return data;
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload(); // Refresh the entire page
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            });
        }
    });
};
