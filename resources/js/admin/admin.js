let claimIdToDelete = null;
let claimIdToEdit = null;

function showDeleteModal(claimId) {
    claimIdToDelete = claimId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    claimIdToDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
}

function openApprovalModal(claimId) {
    claimIdToEdit = claimId;
    // Show loading state
    document.getElementById('approvalModalContent').innerHTML = `
        <div class="flex items-center justify-center p-6">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
    `;
    document.getElementById('approvalModal').classList.remove('hidden');
    
    // Fetch claim details
    fetch(`/claims/${claimId}/edit`)
        .then(response => response.json())
        .then(claim => {
            document.getElementById('approvalModalContent').innerHTML = `
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Edit Claim</h2>
                        <p class="mt-1 text-sm text-gray-500">Update claim details</p>
                    </div>
                    <form id="editClaimForm" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Company Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="edit_claim_company">Company</label>
                                <select class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    id="edit_claim_company" name="claim_company" required>
                                    <option value="WGG" ${claim.claim_company === 'WGG' ? 'selected' : ''}>Wegrow Global Sdn. Bhd.</option>
                                    <option value="WGE" ${claim.claim_company === 'WGE' ? 'selected' : ''}>Wegrow Edutainment (M) Sdn. Bhd.</option>
                                    <option value="WGS" ${claim.claim_company === 'WGS' ? 'selected' : ''}>Wegrow Studios Sdn. Bhd.</option>
                                </select>
                            </div>

                            <!-- Status Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="edit_status">Status</label>
                                <select class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    id="edit_status" name="status" required>
                                    <option value="submitted" ${claim.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                                    <option value="approved_admin" ${claim.status === 'approved_admin' ? 'selected' : ''}>Approved by Admin</option>
                                    <option value="approved_datuk" ${claim.status === 'approved_datuk' ? 'selected' : ''}>Approved by Datuk</option>
                                    <option value="approved_hr" ${claim.status === 'approved_hr' ? 'selected' : ''}>Approved by HR</option>
                                    <option value="approved_finance" ${claim.status === 'approved_finance' ? 'selected' : ''}>Approved by Finance</option>
                                    <option value="rejected" ${claim.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                    <option value="done" ${claim.status === 'done' ? 'selected' : ''}>Done</option>
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="edit_date_from">Date From</label>
                                <input type="date" id="edit_date_from" name="date_from"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    value="${claim.date_from}" required>
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="edit_date_to">Date To</label>
                                <input type="date" id="edit_date_to" name="date_to"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    value="${claim.date_to}" required>
                            </div>

                            <!-- Total Distance -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="edit_total_distance">Total Distance (KM)</label>
                                <input type="number" step="0.01" id="edit_total_distance" name="total_distance"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    value="${claim.total_distance}" required>
                            </div>

                            <!-- Total Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="edit_total_amount">Total Amount (RM)</label>
                                <input type="number" step="0.01" id="edit_total_amount" name="total_amount"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    value="${claim.total_amount}" required>
                            </div>
                        </div>

                        <!-- Description/Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="edit_remarks">Remarks</label>
                            <textarea id="edit_remarks" name="remarks" rows="3"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">${claim.remarks || ''}</textarea>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="closeApprovalModal()"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Cancel
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            `;

            // Add form submit handler
            document.getElementById('editClaimForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateClaim(claimId, this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('approvalModalContent').innerHTML = `
                <div class="p-6 text-center text-red-600">
                    Error loading claim details. Please try again.
                </div>
            `;
        });
}

async function updateClaim(claimId, form) {
    try {
        const formData = new FormData(form);
        const response = await fetch(`/claims/${claimId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        const data = await response.json();

        if (response.ok && data.success) {
            window.location.reload();
        } else {
            throw new Error(data.message || 'Error updating claim');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Error updating claim');
    }
}

function closeApprovalModal() {
    claimIdToEdit = null;
    document.getElementById('approvalModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDelete').addEventListener('click', async function() {
        if (claimIdToDelete) {
            try {
                const response = await fetch(`/claims/${claimIdToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Error deleting claim');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Error deleting claim');
            }
        }
        closeDeleteModal();
    });

    // Close delete modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Close approval modal when clicking outside
    document.getElementById('approvalModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeApprovalModal();
        }
    });

    // Close approval modal when pressing escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeApprovalModal();
            closeDeleteModal();
        }
    });
});

// Make functions globally available
window.showDeleteModal = showDeleteModal;
window.closeDeleteModal = closeDeleteModal;
window.openApprovalModal = openApprovalModal;
window.closeApprovalModal = closeApprovalModal;
