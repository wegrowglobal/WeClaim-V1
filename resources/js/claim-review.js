window.approveClaim = function(claimId) {
    Swal.fire({
        title: 'Approve Claim',
        text: 'Are you sure you want to approve this claim?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send an AJAX request to approve the claim
            axios.put(`/claims/${claimId}/approve`)
                .then(response => {
                    Swal.fire('Approved!', 'The claim has been approved.', 'success')
                        .then(() => {
                            // Reload the page after the success message
                            location.reload();
                        });
                })
                .catch(error => {
                    console.error('Error approving claim:', error);
                    Swal.fire('Error', 'Failed to approve the claim. Please try again.', 'error');
                });
        }
    });
}

window.rejectClaim = function(claimId) {
    Swal.fire({
        title: 'Reject Claim',
        text: 'Are you sure you want to reject this claim?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Reject!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send an AJAX request to reject the claim
            axios.put(`/claims/${claimId}/reject`)
                .then(response => {
                    Swal.fire('Rejected!', 'The claim has been rejected.', 'success')
                        .then(() => {
                            // Reload the page after the success message
                            location.reload();
                        });
                })
                .catch(error => {
                    console.error('Error rejecting claim:', error);
                    Swal.fire('Error', 'Failed to reject the claim. Please try again.', 'error');
                });
        }
    });
}

console.log('claim-review.js loaded');