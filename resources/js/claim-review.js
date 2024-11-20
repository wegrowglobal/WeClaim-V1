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