import Swal from 'sweetalert2';

// Initialize the window.reviewActions object if it doesn't exist
window.reviewActions = window.reviewActions || {};

// Add the approval-specific actions
Object.assign(window.reviewActions, {
    cancelApproval() {
        console.log('Cancel approval clicked'); // Debug log
        Swal.close();
    },

    async submitApproval(claimId) {
        // Get remarks from the textarea
        const remarks = document.getElementById('remarks').value.trim();
        
        // Validate remarks
        if (!remarks) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please provide approval remarks',
                icon: 'error',
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
                }
            });
            return;
        }

        try {
            const response = await axios.post(`/claims/${claimId}/update`, {
                action: 'approve',
                remarks: remarks,
                _token: document.querySelector('meta[name="csrf-token"]').content
            });

            if (response.data.success) {
                await Swal.fire({
                    title: 'Success',
                    text: 'Claim approved successfully',
                    icon: 'success',
                    customClass: {
                        popup: 'rounded-lg shadow-xl border border-gray-200',
                        title: 'text-xl font-medium text-gray-900',
                        confirmButton: 'inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
                    }
                });
                window.location.href = '/claims/approval';
            } else {
                throw new Error(response.data.message || 'Failed to approve claim');
            }
        } catch (error) {
            console.error('Error approving claim:', error);
            Swal.fire({
                title: 'Error',
                text: error.response?.data?.message || 'Failed to approve claim',
                icon: 'error',
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
                }
            });
        }
    }
}); 