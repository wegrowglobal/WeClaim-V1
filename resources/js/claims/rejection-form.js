import Swal from 'sweetalert2';

// Initialize the window.reviewActions object if it doesn't exist
window.reviewActions = window.reviewActions || {};

// Add the rejection-specific actions
Object.assign(window.reviewActions, {
    cancelRejection() {
        Swal.close();
    },

    async submitRejection(claimId) {
        // Get all remarks fields
        const remarks = document.getElementById('remarks').value.trim();
        const basicInfoRemarks = document.getElementById('basic_info_remarks')?.value.trim();
        const tripDetailsRemarks = document.getElementById('trip_details_remarks')?.value.trim();
        const accommodationRemarks = document.getElementById('accommodation_remarks')?.value.trim();
        const documentsRemarks = document.getElementById('documents_remarks')?.value.trim();
        
        // Get selected sections
        const rejectionDetails = {
            requires_basic_info: document.getElementById('requires_basic_info').checked,
            requires_trip_details: document.getElementById('requires_trip_details').checked,
            requires_accommodation_details: document.getElementById('requires_accommodation_details').checked,
            requires_documents: document.getElementById('requires_documents').checked,
            // Add section-specific remarks
            basic_info_remarks: basicInfoRemarks || null,
            trip_details_remarks: tripDetailsRemarks || null,
            accommodation_remarks: accommodationRemarks || null,
            documents_remarks: documentsRemarks || null,
            // Add main remarks
            remarks: remarks
        };

        // Validate that at least one section is selected
        if (!Object.values(rejectionDetails).some(value => value === true)) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please select at least one section that needs revision',
                icon: 'error',
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
                }
            });
            return;
        }

        // Validate remarks
        if (!remarks) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please provide rejection remarks',
                icon: 'error',
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
                }
            });
            return;
        }

        try {
            const response = await axios.post(`/claims/${claimId}/update`, {
                action: 'reject',
                remarks: remarks,
                rejection_details: rejectionDetails,
                _token: document.querySelector('meta[name="csrf-token"]').content
            });

            if (response.data.success) {
                await Swal.fire({
                    title: 'Success',
                    text: 'Claim rejected successfully',
                    icon: 'success',
                    customClass: {
                        popup: 'rounded-lg shadow-xl border border-gray-200',
                        title: 'text-xl font-medium text-gray-900',
                        confirmButton: 'inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
                    }
                });
                window.location.href = '/claims/approval';
            } else {
                throw new Error(response.data.message || 'Failed to reject claim');
            }
        } catch (error) {
            console.error('Error rejecting claim:', error);
            Swal.fire({
                title: 'Error',
                text: error.response?.data?.message || 'Failed to reject claim',
                icon: 'error',
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
                }
            });
        }
    }
}); 