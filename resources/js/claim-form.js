import { LocationManager } from './claim-map.js';

class ClaimForm {
    constructor() {
        this.currentStep = parseInt(new URLSearchParams(window.location.search).get('step')) || 1;
        this.formData = new FormData();
        this.locationManager = null;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        this.draftData = this.loadDraftData();

        this.bindEvents();
        window.nextStep = this.nextStep.bind(this);
        window.previousStep = this.previousStep.bind(this);
        window.resetClaimForm = this.resetForm.bind(this);
        this.verifyDraftData();
    }

    loadDraftData() {
        const draftDataInput = document.getElementById('draftData');
        if (!draftDataInput) return {};

        try {
            const draftData = JSON.parse(draftDataInput.value);
            console.log('Loaded draft data:', draftData);
            return draftData;
        } catch (error) {
            console.error('Error loading draft data:', error);
            return {};
        }
    }

    populateFormFields(data) {
        console.log('Populating fields with:', data); // Debug log
        
        // Get the current step element
        const currentStep = document.querySelector('[data-step]');
        if (!currentStep) return;

        const stepNumber = parseInt(currentStep.dataset.step);
        
        // Store data in class property for access across steps
        this.formData = data;
        
        switch (stepNumber) {
            case 1:
                this.populateStep1(data);
                break;
            case 2:
                this.populateStep2(data);
                break;
            case 3:
                this.populateStep3(data);
                break;
        }
    }

    populateStep1(data) {
        if (data.claim_company) {
            document.getElementById('claim_company').value = data.claim_company;
        }
        if (data.date_from) {
            document.getElementById('date_from').value = data.date_from;
        }
        if (data.date_to) {
            document.getElementById('date_to').value = data.date_to;
        }
        if (data.remarks) {
            document.getElementById('remarks').value = data.remarks;
        }
    }

    populateStep2(data) {
        if (this.locationManager && data.locations) {
            this.locationManager.loadSavedData();
        }
    }

    populateStep3(data) {
        // Get the elements
        const totalDistanceEl = document.querySelector('[data-summary="distance"]');
        const petrolClaimEl = document.querySelector('[data-summary="petrol"]');
        const totalLocationsEl = document.querySelector('[data-summary="locations"]');

        if (data.total_distance) {
            const distance = parseFloat(data.total_distance).toFixed(2);
            if (totalDistanceEl) totalDistanceEl.textContent = `${distance} km`;
        }

        if (data.total_cost) {
            const cost = parseFloat(data.total_cost).toFixed(2);
            if (petrolClaimEl) petrolClaimEl.textContent = `RM ${cost}`;
        }

        if (data.locations) {
            let locations = [];
            try {
                locations = typeof data.locations === 'string' ? 
                    JSON.parse(data.locations) : data.locations;
            } catch (error) {
                console.error('Error parsing locations:', error);
            }
            
            const locationCount = Array.isArray(locations) ? 
                locations.filter(loc => loc && loc.trim() !== '').length : 0;
                
            if (totalLocationsEl) totalLocationsEl.textContent = `${locationCount} stops`;
        }
    }

    bindEvents() {
        // Bind form submission
        const form = document.getElementById('claimForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Bind reset button
        const resetBtn = document.querySelector('button[onclick="window.resetClaimForm()"]');
        if (resetBtn) {
            resetBtn.onclick = (e) => this.resetForm(e);
        }
    }

    async saveCurrentStep() {
        const form = document.getElementById('claimForm');
        if (!form) return true;

        // Get existing draft data
        const draftDataInput = document.getElementById('draftData');
        let existingData = {};
        try {
            existingData = JSON.parse(draftDataInput?.value || '{}');
            console.log('Existing draft data:', existingData);
        } catch (error) {
            console.error('Error parsing existing draft data:', error);
        }

        const formData = new FormData(form);
        
        // Create merged data object
        const currentStepData = {
            ...existingData, // Start with existing data
            current_step: this.currentStep,
            
            // Step 1 data (preserve from existing if not in current form)
            claim_company: formData.get('claim_company') || existingData.claim_company || '',
            date_from: formData.get('date_from') || existingData.date_from || '',
            date_to: formData.get('date_to') || existingData.date_to || '',
            remarks: formData.get('remarks') || existingData.remarks || '',
            
            // Step 2 data
            segments_data: document.getElementById('segments-data')?.value || existingData.segments_data || '[]',
            locations: formData.get('locations') || existingData.locations || '[]',
            total_distance: formData.get('total_distance') || existingData.total_distance || '0',
            total_cost: formData.get('total_cost') || existingData.total_cost || '0',
            total_duration: formData.get('total_duration') || existingData.total_duration || '0',
            
            // Step 3 data
            toll_amount: formData.get('toll_amount') || existingData.toll_amount || '0'
        };

        console.log('Saving merged step data:', currentStepData);

        try {
            const response = await fetch('/claims/save-step', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(currentStepData)
            });

            if (!response.ok) {
                throw new Error('Failed to save step data');
            }

            // Update the draft data input with merged data
            if (draftDataInput) {
                draftDataInput.value = JSON.stringify(currentStepData);
                console.log('Updated draft data input:', currentStepData);
            }

            return true;
        } catch (error) {
            console.error('Error saving step:', error);
            return false;
        }
    }

    async nextStep(currentStep) {
        // Save current step data before proceeding
        const savedSuccessfully = await this.saveCurrentStep();
        if (!savedSuccessfully) {
            console.error('Failed to save current step data');
            return;
        }

        // Get all current form data
        const form = document.getElementById('claimForm');
        const formData = new FormData(form);
        
        // Get existing draft data
        const draftDataInput = document.getElementById('draftData');
        let existingData = {};
        try {
            existingData = JSON.parse(draftDataInput?.value || '{}');
        } catch (error) {
            console.error('Error parsing existing draft data:', error);
        }

        // Merge all data
        const mergedData = {
            ...existingData,
            // Step 1 data
            claim_company: formData.get('claim_company') || existingData.claim_company,
            date_from: formData.get('date_from') || existingData.date_from,
            date_to: formData.get('date_to') || existingData.date_to,
            remarks: formData.get('remarks') || existingData.remarks,
            // Step 2 data
            locations: formData.get('locations') || existingData.locations,
            segments_data: document.getElementById('segments-data')?.value || existingData.segments_data,
            total_distance: formData.get('total_distance') || existingData.total_distance,
            total_cost: formData.get('total_cost') || existingData.total_cost,
            // Step 3 data
            toll_amount: formData.get('toll_amount') || existingData.toll_amount
        };

        // Update the URL with next step
        const nextStepNumber = currentStep + 1;
        const url = new URL(window.location.href);
        url.searchParams.set('step', nextStepNumber);
        
        // Add merged data to URL
        url.searchParams.set('draft_data', JSON.stringify(mergedData));
        
        window.location.href = url.toString();
    }

    async previousStep(currentStep) {
        // Remove the save step requirement for going back
        const prevStep = currentStep - 1;
        if (prevStep >= 1) {
            window.location.href = `/claims/new?step=${prevStep}`;
        }
    }

    async resetForm(e) {
        if (e) e.preventDefault();
        
        try {
            const response = await fetch('/claims/reset-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) throw new Error('Failed to reset form');

            window.location.href = '/claims/new?step=1';
        } catch (error) {
            console.error('Error resetting form:', error);
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        // Load current draft data from hidden input
        const draftDataInput = document.getElementById('draftData');
        let draftData = {};
        try {
            draftData = JSON.parse(draftDataInput?.value || '{}');
            console.log('Loaded draft data from input:', draftData);
        } catch (error) {
            console.error('Error parsing draft data:', error);
        }

        // Get current form data
        const form = document.getElementById('claimForm');
        const formData = new FormData(form);
        
        // Get segments data
        const segmentsData = document.getElementById('segments-data')?.value || '[]';
        let parsedSegments = [];
        try {
            parsedSegments = JSON.parse(segmentsData);
        } catch (error) {
            console.error('Error parsing segments:', error);
        }

        // Merge draft data with current form data
        const mergedData = {
            // Step 1 data (from draft)
            claim_company: draftData.claim_company || formData.get('claim_company') || '',
            date_from: draftData.date_from || formData.get('date_from') || '',
            date_to: draftData.date_to || formData.get('date_to') || '',
            remarks: draftData.remarks || formData.get('remarks') || '',
            
            // Step 2 data (from current form or draft)
            total_distance: formData.get('total_distance') || draftData.total_distance || '0',
            total_cost: formData.get('total_cost') || draftData.total_cost || '0',
            
            // Status and user data
            status: 'draft',
            user_id: document.querySelector('meta[name="user-id"]')?.content
        };

        console.log('Merged data:', mergedData);

        // Build debug data structure
        const debugData = {
            claim: mergedData,
            locations: parsedSegments,
            segments_data: parsedSegments,
            documents: {
                toll_receipt: {
                    file: document.getElementById('toll_report')?.files[0],
                    filename: document.getElementById('toll_report')?.files[0]?.name || '',
                    amount: document.getElementById('toll_amount')?.value || '0'
                },
                approval_email: {
                    file: document.getElementById('email_report')?.files[0],
                    filename: document.getElementById('email_report')?.files[0]?.name || ''
                }
            },
            raw: {
                draftData: draftData,
                currentFormData: Object.fromEntries(formData),
                segmentsData: parsedSegments
            }
        };

        console.log('Final debug data:', debugData);
        this.showDebugModal(debugData);
        return false;
    }

    // Helper method to get location segments
    getLocationSegments() {
        // Try to get segments data from hidden input
        const segmentsDataInput = document.getElementById('segments-data');
        if (segmentsDataInput && segmentsDataInput.value) {
            try {
                const segments = JSON.parse(segmentsDataInput.value);
                if (Array.isArray(segments) && segments.length > 0) {
                    console.log('Found valid segments data:', segments);
                    return segments;
                }
            } catch (error) {
                console.error('Error parsing segments data:', error);
            }
        }

        // Fallback: collect from segment elements
        const segments = [];
        const segmentElements = document.querySelectorAll('.segment-detail');
        
        segmentElements.forEach(element => {
            try {
                const segmentInfo = element.getAttribute('data-segment-info');
                if (segmentInfo) {
                    const parsedInfo = JSON.parse(segmentInfo);
                    segments.push(parsedInfo);
                }
            } catch (error) {
                console.error('Error parsing segment info:', error);
            }
        });

        console.log('Collected segments from elements:', segments);
        return segments;
    }

    // Helper method to get hidden input values
    getHiddenInputValues() {
        const hiddenInputs = document.querySelectorAll('input[type="hidden"]');
        const values = {};
        
        hiddenInputs.forEach(input => {
            values[input.name || input.id] = input.value;
            
            // Try to parse JSON values
            if (input.value.startsWith('{') || input.value.startsWith('[')) {
                try {
                    values[`${input.name || input.id}_parsed`] = JSON.parse(input.value);
                } catch (e) {
                    values[`${input.name || input.id}_parsed`] = 'Invalid JSON';
                }
            }
        });
        
        return values;
    }

    // Helper method to get calculated values
    getCalculatedValues() {
        return {
            total_distance: document.getElementById('total-distance')?.textContent,
            total_duration: document.getElementById('total-duration')?.textContent,
            total_cost: document.getElementById('total-cost')?.textContent,
        };
    }

    // Helper method to show debug modal
    showDebugModal(debugData) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Form Debug Data</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2 space-y-4 overflow-auto max-h-[70vh]">
                    <div class="space-y-2">
                        <h4 class="font-medium text-indigo-600">Main Claim Data</h4>
                        <pre class="bg-gray-50 p-3 rounded-lg overflow-x-auto text-sm">${JSON.stringify(debugData.claim, null, 2)}</pre>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-indigo-600">Location Segments</h4>
                        <pre class="bg-gray-50 p-3 rounded-lg overflow-x-auto text-sm">${JSON.stringify(debugData.locations, null, 2)}</pre>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-green-600">Segments Data (Hidden Input)</h4>
                        <pre class="bg-gray-50 p-3 rounded-lg overflow-x-auto text-sm">${JSON.stringify(debugData.segments_data, null, 2)}</pre>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-indigo-600">Documents</h4>
                        <pre class="bg-gray-50 p-3 rounded-lg overflow-x-auto text-sm">${JSON.stringify(debugData.documents, null, 2)}</pre>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-indigo-600">Raw Form Data</h4>
                        <pre class="bg-gray-50 p-3 rounded-lg overflow-x-auto text-sm">${JSON.stringify(debugData.raw, null, 2)}</pre>
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="this.closest('.fixed').remove()" 
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    }

    validateStep(step) {
        if (step === 2) {
            return this.validateStep2();
        }
        return this.validateGenericStep();
    }

    validateStep2() {
        const locationInputs = document.querySelectorAll('.location-input');
        const locations = Array.from(locationInputs)
            .map(input => input.value.trim())
            .filter(value => value !== '');

        if (locations.length < 2) {
            alert('Please add at least two locations');
            return false;
        }

        const totalDistance = parseFloat(document.getElementById('total-distance')?.textContent || '0');
        if (totalDistance <= 0) {
            alert('Please ensure a valid route is calculated');
            return false;
        }

        return true;
    }

    validateGenericStep() {
        const form = document.getElementById('claimForm');
        if (!form) return true;

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalidField = null;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('border-red-500');
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            } else {
                field.classList.remove('border-red-500');
            }
        });

        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    updateTotalCost(distance) {
        const RATE_PER_KM = 0.50; // Update rate to RM 0.50 per kilometer
        const totalCost = (parseFloat(distance) * RATE_PER_KM).toFixed(2);
        
        // Update all cost displays on the page
        const costDisplays = document.querySelectorAll('[data-cost-display]');
        costDisplays.forEach(element => {
            element.textContent = totalCost;
        });
        
        // Update the hidden input
        const totalCostInput = document.getElementById('total-cost-input');
        if (totalCostInput) {
            totalCostInput.value = totalCost;
        }
        
        return totalCost;
    }

    updateTotalDistance(distance) {
        const totalDistanceElement = document.getElementById('total-distance');
        if (totalDistanceElement) {
            totalDistanceElement.textContent = parseFloat(distance).toFixed(2);
        }
        
        const totalDistanceInput = document.getElementById('total-distance-input');
        if (totalDistanceInput) {
            totalDistanceInput.value = distance;
        }
        
        // Update cost whenever distance changes
        this.updateTotalCost(distance);
    }

    verifyDraftData() {
        const draftDataInput = document.getElementById('draftData');
        if (!draftDataInput) return;

        try {
            const draftData = JSON.parse(draftDataInput.value);
            console.log('Current draft data:', {
                step1: {
                    claim_company: draftData.claim_company,
                    date_from: draftData.date_from,
                    date_to: draftData.date_to,
                    remarks: draftData.remarks,
                },
                step2: {
                    total_distance: draftData.total_distance,
                    total_cost: draftData.total_cost,
                    locations: draftData.locations,
                    segments_data: draftData.segments_data,
                }
            });
        } catch (error) {
            console.error('Error verifying draft data:', error);
        }
    }
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    window.claimForm = new ClaimForm();
});