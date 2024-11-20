import { SwalUtils } from './utils/swal-utils';
import ErrorHandler from './utils/error-handler';

window.resubmitClaim = function(claimId) {
    const description = document.getElementById('description')?.value;
    if (!description) {
        SwalUtils.showError('Please enter description before resubmitting');
        return;
    }

    return ErrorHandler.handle(async () => {
        const result = await Swal.fire({
            title: 'Resubmit Claim',
            text: 'Are you sure you want to resubmit this claim?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Resubmit',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4F46E5',
            cancelButtonColor: '#6B7280'
        });

        if (result.isConfirmed) {
            const formData = new FormData();
            
            // Add fields with correct IDs
            formData.append('description', description);
            formData.append('claim_company', document.getElementById('claim_company')?.value);
            formData.append('date_from', document.getElementById('date_from')?.value);
            formData.append('date_to', document.getElementById('date_to')?.value);
            formData.append('petrol_amount', document.getElementById('petrol-amount-input')?.value);
            formData.append('toll_amount', document.getElementById('toll_amount')?.value);
            formData.append('total_distance', document.getElementById('total-distance-input')?.value);
            formData.append('locations', document.getElementById('locations')?.value);
            formData.append('segments_data', document.getElementById('segments-data')?.value);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content);

            try {
                const response = await axios.post(`/claims/${claimId}/resubmit`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    }
                });

                const data = response.data;
                if (data.success) {
                    await Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Your claim has been resubmitted successfully.',
                        icon: 'success',
                        confirmButtonText: 'Go to Dashboard'
                    });
                    window.location.href = '/claims/dashboard';
                } else {
                    throw new Error(data.message || 'Failed to resubmit claim');
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || 'Failed to resubmit the claim. Please try again.';
                SwalUtils.showError(errorMessage);
            }
        }
    }, 'resubmitting claim');
}