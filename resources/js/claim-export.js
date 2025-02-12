const ClaimExport = {
    init() {
        this.attachEventListeners();
    },

    attachEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-export-claim]') || e.target.closest('[data-export-claim]')) {
                const button = e.target.matches('[data-export-claim]') ? e.target : e.target.closest('[data-export-claim]');
                const claimId = button.dataset.exportClaim;
                this.showExportOptions(claimId);
            }
        });
    },

    showExportOptions(claimId) {
        Swal.fire({
            title: 'Export Claim',
            text: 'Choose your preferred format:',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#059669',
            denyButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
            confirmButtonText: '<i class="fas fa-file-excel mr-2"></i>Excel',
            denyButtonText: '<i class="fas fa-file-pdf mr-2"></i>PDF',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                const format = result.isConfirmed ? 'excel' : 'pdf';
                this.exportClaim(claimId, format);
            }
        });
    },

    async exportClaim(claimId, format) {
        try {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/claims/${claimId}/export`;

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // Add format
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = format;
            form.appendChild(formatInput);

            // Add to body and submit
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

        } catch (error) {
            console.error('Export failed:', error);
            Swal.fire({
                title: 'Export Failed',
                text: 'There was an error exporting your claim. Please try again.',
                icon: 'error',
                confirmButtonColor: '#DC2626'
            });
        }
    }
};

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.ClaimExport = ClaimExport;
    ClaimExport.init();
});

export default ClaimExport; 