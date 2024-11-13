class DocumentUpload {
    constructor() {
        this.initializeUploads();
        this.bindEvents();
    }

    initializeUploads() {
        // Initialize toll receipt upload
        this.initializeUploadArea('toll');
        // Initialize email approval upload
        this.initializeUploadArea('email');
        // Update summary if elements exist
        this.updateSummary();
    }

    initializeUploadArea(type) {
        const input = document.getElementById(`${type}_report`);
        const preview = document.getElementById(`${type}-preview`);
        const filename = document.getElementById(`${type}-filename`);
        
        if (!input || !preview || !filename) return;

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                filename.textContent = file.name;
                preview.classList.remove('hidden');
            }
        });

        // Initialize drag and drop
        const uploadArea = document.getElementById(`${type}-upload-area`);
        if (uploadArea) {
            this.initializeDragAndDrop(uploadArea, input);
        }
    }

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
                input.dispatchEvent(new Event('change'));
            }
        });
    }

    updateSummary() {
        const summaryDistance = document.getElementById('summary-distance');
        const summaryPetrol = document.getElementById('summary-petrol');
        const summaryLocations = document.getElementById('summary-locations');
        const draftDataEl = document.getElementById('draftData');

        if (!draftDataEl) return;

        try {
            const draftData = JSON.parse(draftDataEl.value);
            
            if (summaryDistance) {
                summaryDistance.textContent = draftData.total_distance || '0';
            }
            if (summaryPetrol) {
                summaryPetrol.textContent = draftData.total_cost || '0.00';
            }
            if (summaryLocations) {
                const locations = draftData.locations ? 
                    (typeof draftData.locations === 'string' ? 
                        JSON.parse(draftData.locations) : 
                        draftData.locations) : [];
                summaryLocations.textContent = locations.length || '0';
            }
        } catch (error) {
            console.error('Error parsing draft data:', error);
        }
    }

    bindEvents() {
        // Bind remove file events
        window.removeFile = (type) => {
            const input = document.getElementById(`${type}_report`);
            const preview = document.getElementById(`${type}-preview`);
            const filename = document.getElementById(`${type}-filename`);
            
            if (input) input.value = '';
            if (preview) preview.classList.add('hidden');
            if (filename) filename.textContent = 'No file selected';
        };
    }
}

// Initialize document upload only when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on step 3 before initializing
    const step3Container = document.querySelector('[data-step="3"]');
    if (step3Container) {
        window.documentUpload = new DocumentUpload();
    }
});

// Export for use in other modules
export { DocumentUpload }; 