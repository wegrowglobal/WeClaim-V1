class DocumentUpload {
    constructor() {
        // Initialization is now handled by the component triggering updatePreview
        this.bindEvents();
    }

    // Function called by the file input's onchange event
    updatePreview(type, inputElement) {
        // Construct IDs based on the input element's ID, which matches the component prop
        const baseId = inputElement.id; // e.g., "email_report" or "toll_report"
        const preview = document.getElementById(`${baseId}-preview`);
        const filename = document.getElementById(`${baseId}-filename`);
        
        if (!inputElement || !preview || !filename) {
            console.error(`Preview elements not found for ID: ${baseId}`);
            return;
        }

        const file = inputElement.files[0];
        if (file) {
            filename.textContent = file.name;
            preview.classList.remove('hidden');
        } else {
            // If no file is selected (e.g., user cancelled), hide preview
            filename.textContent = 'No file selected';
            preview.classList.add('hidden');
        }
    }

    // Function called by the remove button
    removeFile(type) {
        // Construct IDs based on the type passed (which should match the component's base ID like 'email_report')
        // Ensure the correct base ID is passed to this function if it differs from just 'email' or 'toll'.
        // Assuming the `type` passed here matches the component ID (e.g., 'email_report')
        const input = document.getElementById(type);
        const preview = document.getElementById(`${type}-preview`);
        const filename = document.getElementById(`${type}-filename`);
        
        if (input) input.value = ''; // Clear the file input
        if (preview) preview.classList.add('hidden');
        if (filename) filename.textContent = 'No file selected';
    }

    // We still need drag and drop, but it should trigger the input's change event
    initializeDragAndDrop(area, input) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, () => {
                area.classList.add('border-indigo-500');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, () => {
                area.classList.remove('border-indigo-500');
            });
        });

        area.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            
            if (file) {
                input.files = dt.files;
                // Manually trigger the change event to ensure updatePreview runs
                input.dispatchEvent(new Event('change'));
            }
        });
    }

    bindEvents() {
        // Expose methods to the window object for the component to call
        window.claimDocument = {
            updatePreview: this.updatePreview.bind(this),
            removeFile: this.removeFile.bind(this)
        };

        // Still initialize drag and drop for the relevant areas if they exist
        // Note: This assumes the component structure provides these IDs
        const emailArea = document.getElementById('email_report-upload-area');
        const emailInput = document.getElementById('email_report');
        if (emailArea && emailInput) {
            this.initializeDragAndDrop(emailArea, emailInput);
        }

        const tollArea = document.getElementById('toll_report-upload-area');
        const tollInput = document.getElementById('toll_report');
        if (tollArea && tollInput) {
            this.initializeDragAndDrop(tollArea, tollInput);
        }
    }
}

// Initialize document upload only when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on step 3 before initializing
    const step3Container = document.querySelector('[data-step="3"]');
    if (step3Container) {
        new DocumentUpload(); // Instantiate to bind events
    }
});

// Export for use in other modules
export { DocumentUpload }; 