let claimIdToDelete = null;

function showDeleteModal(claimId) {
    claimIdToDelete = claimId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    claimIdToDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
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

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
});

// Make functions globally available
window.showDeleteModal = showDeleteModal;
window.closeDeleteModal = closeDeleteModal;
