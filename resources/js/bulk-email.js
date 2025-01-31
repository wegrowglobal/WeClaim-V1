import { SwalUtils } from './utils/swal-utils';
import Logger from './utils/logger';
import ErrorHandler from './utils/error-handler';

class BulkEmailHandler {
    constructor() {
        this.selectAllCheckbox = document.getElementById('select-all');
        this.claimCheckboxes = document.querySelectorAll('.claim-checkbox');
        this.bulkEmailBtn = document.getElementById('sendSelectedBtn');
        this.selectedCount = document.getElementById('selectedCount');
        this.selectedAmount = document.getElementById('selectedAmount');
        this.floatingActionBar = document.getElementById('floatingActionBar');
        this.isHR = document.querySelector('.claims-table')?.dataset.userRole === '3';

        if (this.isHR) {
            this.initializeEventListeners();
        }
    }

    initializeEventListeners() {
        // Handle select all
        this.selectAllCheckbox?.addEventListener('change', () => {
            this.claimCheckboxes.forEach(checkbox => {
                checkbox.checked = this.selectAllCheckbox.checked;
            });
            this.updateBulkEmailButton();
        });

        // Handle individual checkboxes
        this.claimCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateBulkEmailButton());
        });

        // Handle bulk email button click
        this.bulkEmailBtn?.addEventListener('click', () => this.handleBulkEmail());
    }

    updateBulkEmailButton() {
        const checkedBoxes = document.querySelectorAll('.claim-checkbox:checked');
        this.bulkEmailBtn.classList.toggle('hidden', checkedBoxes.length === 0);
        this.updateSelectionStats();
    }

    updateSelectionStats() {
        const checkedBoxes = document.querySelectorAll('.claim-checkbox:checked');
        const totalAmount = Array.from(checkedBoxes).reduce((sum, checkbox) => {
            const amount = parseFloat(checkbox.dataset.amount || 0);
            return sum + amount;
        }, 0);

        this.selectedCount.textContent = checkedBoxes.length;
        this.selectedAmount.textContent = `RM ${totalAmount.toFixed(2)}`;
        this.floatingActionBar.classList.toggle('translate-y-full', checkedBoxes.length === 0);
    }

    async handleBulkEmail() {
        const selectedClaims = Array.from(document.querySelectorAll('.claim-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedClaims.length === 0) {
            return;
        }

        try {
            const result = await Swal.fire({
                title: 'Send Claims to Datuk',
                text: `Are you sure you want to send ${selectedClaims.length} claim(s) to Datuk for review?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, send them',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) return;

            // Show loading state
            await Swal.fire({
                title: 'Sending...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send request
            await this.sendBulkEmail(selectedClaims);

        } catch (error) {
            Logger.error('Error in bulk email operation', error);
            await SwalUtils.showError(error.response?.data?.message || 'Failed to send claims to Datuk');
        }
    }

    async sendBulkEmail(selectedClaims) {
        return ErrorHandler.handle(async () => {
            const response = await axios.post('/claims/bulk-email/send', {
                claims: selectedClaims
            });

            if (response.data.success) {
                await SwalUtils.showSuccess(response.data.message);
                window.location.reload();
                return true;
            }

            throw new Error(response.data.message);
        }, 'sending bulk email');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const claimCheckboxes = document.querySelectorAll('.claim-checkbox');
    const floatingActionBar = document.getElementById('floatingActionBar');
    const selectedCount = document.getElementById('selectedCount');
    const selectedAmount = document.getElementById('selectedAmount');

    function updateSelectionStats() {
        const checkedBoxes = document.querySelectorAll('.claim-checkbox:checked');
        const totalAmount = Array.from(checkedBoxes).reduce((sum, checkbox) => {
            const amount = parseFloat(checkbox.dataset.amount || 0);
            return sum + amount;
        }, 0);

        selectedCount.textContent = checkedBoxes.length;
        selectedAmount.textContent = `RM ${totalAmount.toFixed(2)}`;
        
        // Show/hide floating bar
        if (checkedBoxes.length > 0) {
            floatingActionBar.classList.remove('translate-y-full');
        } else {
            floatingActionBar.classList.add('translate-y-full');
        }
    }

    // Handle select all checkbox
    selectAllCheckbox?.addEventListener('change', function() {
        claimCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectionStats();
    });

    // Handle individual checkboxes
    claimCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionStats);
    });

    // Handle search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#claimsTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Initialize send claims functionality
    window.sendSelectedClaims = function() {
        const selectedClaims = Array.from(document.querySelectorAll('.claim-checkbox:checked')).map(cb => cb.value);
        
        if (selectedClaims.length === 0) {
            Swal.fire('Error', 'Please select at least one claim to send.', 'error');
            return;
        }

        Swal.fire({
            title: 'Send Claims to Datuk',
            text: `Are you sure you want to send ${selectedClaims.length} claim(s) to Datuk for review?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, send them',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Sending...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send request
                axios.post('/claims/bulk-email/send', {
                    claims: selectedClaims
                })
                .then(response => {
                    if (response.data.success) {
                        Swal.fire('Success', response.data.message, 'success')
                            .then(() => {
                                window.location.reload();
                            });
                    } else {
                        throw new Error(response.data.message || 'Failed to send claims');
                    }
                })
                .catch(error => {
                    console.error('Error sending claims:', error);
                    const errorMessage = error.response?.data?.message || 'Failed to send the claims. Please try again.';
                    Swal.fire('Error', errorMessage, 'error');
                });
            }
        });
    };

    // Initialize stats on page load
    updateSelectionStats();
}); 