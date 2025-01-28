import { SwalUtils } from './utils/swal-utils';
import Logger from './utils/logger';
import ErrorHandler from './utils/error-handler';

class BulkEmailHandler {
    constructor() {
        this.selectAllCheckbox = document.getElementById('select-all-claims');
        this.claimCheckboxes = document.querySelectorAll('.claim-checkbox');
        this.bulkEmailBtn = document.getElementById('bulk-email-btn');
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
document.addEventListener('DOMContentLoaded', () => {
    new BulkEmailHandler();
}); 