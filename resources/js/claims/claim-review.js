window.approveClaim = function(claimId) {
    const remarks = document.getElementById('remarks').value;
    if (!remarks) {
        Swal.fire('Error', 'Please enter remarks before approving', 'error');
        return;
    }

    Swal.fire({
        title: 'Approve Claim',
        text: 'Are you sure you want to approve this claim?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/claims/${claimId}/update`, {
                action: 'approve',
                remarks: remarks
            })
            .then(response => {
                Swal.fire('Approved!', 'The claim has been approved.', 'success')
                    .then(() => {
                        window.location.href = '/claims/approval';
                    });
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to approve the claim. Please try again.', 'error');
            });
        }
    });
}

window.rejectClaim = function(claimId) {
    const remarks = document.getElementById('remarks').value;
    if (!remarks) {
        Swal.fire('Error', 'Please enter remarks before rejecting', 'error');
        return;
    }

    Swal.fire({
        title: 'Reject Claim',
        text: 'Are you sure you want to reject this claim?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/claims/${claimId}/update`, {
                action: 'reject',
                remarks: remarks
            })
            .then(response => {
                Swal.fire('Rejected!', 'The claim has been rejected.', 'success')
                    .then(() => {
                        location.reload();
                    });
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to reject the claim. Please try again.', 'error');
            });
        }
    });
}

window.sendToDatuk = function(claimId) {
    if (!claimId) {
        console.error('No claim ID provided');
        Swal.fire('Error', 'Invalid claim ID', 'error');
        return;
    }

    Swal.fire({
        title: 'Send to Datuk',
        text: 'Are you sure you want to send this claim to Datuk for review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Send',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/claims/send-to-datuk/${claimId}`)
                .then(response => {
                    if (response.data.success) {
                        Swal.fire('Sent!', 'The claim has been sent to Datuk for review.', 'success')
                            .then(() => {
                                window.location.href = '/claims/approval';
                            });
                    } else {
                        throw new Error(response.data.message || 'Failed to send claim');
                    }
                })
                .catch(error => {
                    console.error('Error sending claim to Datuk:', error);
                    const errorMessage = error.response?.data?.message || 'Failed to send the claim. Please try again.';
                    Swal.fire('Error', errorMessage, 'error');
                });
        }
    });
}