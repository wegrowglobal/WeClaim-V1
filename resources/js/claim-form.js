import { ClaimMap } from './maps/claim-map.js';

class ClaimForm {
    constructor() {
        this.currentStep = parseInt(new URLSearchParams(window.location.search).get('step')) || 1;
        this.formData = new FormData();
        this.locationManager = null;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        this.draftData = this.loadDraftData();

        // Bind methods to this instance
        this.nextStep = this.nextStep.bind(this);
        this.previousStep = this.previousStep.bind(this);
        this.resetForm = this.resetForm.bind(this);
        this.saveCurrentStep = this.saveCurrentStep.bind(this);

        // Initialize the instance
        this.init();
    }

    async init() {
        // Set up the global instance
        window.claimForm = {
            nextStep: this.nextStep,
            previousStep: this.previousStep,
            resetForm: this.resetForm,
            saveCurrentStep: this.saveCurrentStep
        };

        const mapElement = document.getElementById('map');
        if (mapElement) {
            window.claimMap = new ClaimMap();
            window.claimMap.init();
        }

        this.bindEvents();
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
        console.log('populateStep1 called with data:', data);

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
        console.log('populateStep2 called with data:', data);
        
        if (!window.claimMap) {
            console.warn('Map not initialized');
            return;
        }
    
        // Show loading overlay
        const loadingOverlay = document.getElementById('map-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.remove('hidden');
        }
    
        // Wait for map initialization
        const checkMapAndPopulate = () => {
            console.log('Checking map initialization...');
            if (window.claimMap.initialized) {
                console.log('Map is initialized');
                
                // Check if there are any location inputs
                const locationInputs = document.querySelectorAll('.location-input');
                if (locationInputs.length === 0) {
                    console.log('No location inputs found, adding initial inputs');
                    window.claimMap.addInitialLocationInputs();
                } else {
                    console.log('Location inputs already exist, loading saved data');
                    window.claimMap.loadSavedData();
                }
    
                // Hide loading overlay after a short delay
                setTimeout(() => {
                    if (loadingOverlay) {
                        loadingOverlay.classList.add('hidden');
                    }
                }, 500);
            } else {
                console.log('Map not initialized yet, retrying...');
                setTimeout(checkMapAndPopulate, 100);
            }
        };
    
        checkMapAndPopulate();
    }

    populateStep3(data) {
        console.log('populateStep3 called with data:', data);
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
        
        // Show confirmation dialog first
        const result = await Swal.fire({
            title: 'Reset Form?',
            text: 'This will clear all entered data. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#4F46E5',
            confirmButtonText: 'Reset form',
            cancelButtonText: 'Keep editing',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-lg shadow-xl border border-gray-200',
                title: 'text-xl font-medium text-gray-900',
                htmlContainer: 'text-base text-gray-600',
                confirmButton: 'inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all',
                cancelButton: 'inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all'
            },
            buttonsStyling: false
        });

        if (result.isConfirmed) {
            const funnyMessages = [
                "Unleashing the form-eating gremlins...",
                "Sending your data on a one-way trip to the void...",
                "Performing a magic trick: Watch your inputs disappear!",
                "Initiating the great form purge of 2024...",
                "Calling in the data demolition crew..."
            ];

            let currentMessageIndex = 0;

            // Show loading state with timer
            const loadingAlert = Swal.fire({
                title: '<div class="flex items-center space-x-3"><div class="p-2 bg-indigo-50 rounded-lg"><svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div><span>Resetting Form</span></div>',
                html: `<p class="text-gray-600">${funnyMessages[0]}</p>`,
                allowOutsideClick: false,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900 !flex !justify-center',
                    htmlContainer: 'text-base text-center',
                    timerProgressBar: 'bg-indigo-600'
                },
                didOpen: () => {
                    Swal.showLoading();
                    const messageInterval = setInterval(() => {
                        currentMessageIndex = (currentMessageIndex + 1) % funnyMessages.length;
                        const content = document.querySelector('.swal2-html-container');
                        if (content) {
                            content.innerHTML = `<p class="text-gray-600">${funnyMessages[currentMessageIndex]}</p>`;
                        }
                    }, 3000);

                    // Store the interval ID
                    Swal.messageInterval = messageInterval;
                },
                willClose: () => {
                    clearInterval(Swal.messageInterval);
                }
            });

            // Perform reset operations after delay
            setTimeout(async () => {
                try {
                    // Clear all stored data
                    localStorage.removeItem('claimFormData');
                    localStorage.removeItem('draftData');
                    sessionStorage.clear();
                    
                    // Clear the draft data input
                    const draftDataInput = document.getElementById('draftData');
                    if (draftDataInput) {
                        draftDataInput.value = '{}';
                    }

                    // Clear map data if exists
                    if (window.claimMap && typeof window.claimMap.clearMapData === 'function') {
                        window.claimMap.clearMapData();
                    }

                    // Make API call to clear server-side session
                    const response = await fetch('/claims/reset-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to reset session');
                    }

                    // Wait for loading animation to complete
                    await loadingAlert;

                    // Show success message
                    await Swal.fire({
                        title: '<div class="flex items-center space-x-3"><div class="p-2 bg-green-50 rounded-lg"><svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span>Form Reset Successfully</span></div>',
                        html: '<p class="text-gray-600">Preparing a new claim form...</p>',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rounded-lg shadow-xl border border-gray-200',
                            title: 'text-xl font-medium text-gray-900 !flex !justify-center',
                            htmlContainer: 'text-base text-center'
                        }
                    });

                    // Redirect to step 1
                    window.location.href = '/claims/new?step=1&draft_data={}';
                } catch (error) {
                    console.error('Error resetting form:', error);
                    Swal.fire({
                        title: '<div class="flex items-center space-x-3"><div class="p-2 bg-red-50 rounded-lg"><svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><span>Error</span></div>',
                        html: '<p class="text-gray-600">Failed to reset form. Redirecting anyway...</p>',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rounded-lg shadow-xl border border-gray-200',
                            title: 'text-xl font-medium text-gray-900 !flex !justify-center',
                            htmlContainer: 'text-base text-center'
                        }
                    });
                    window.location.href = '/claims/new?step=1&draft_data={}';
                }
            }, 15000); // Wait for the full animation duration
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        console.group('Claim Form Submission Debug');
        
        // Get form and draft data
        const form = document.getElementById('claimForm');
        const formData = new FormData(form);
        const draftDataInput = document.getElementById('draftData');
        let draftData = {};
        
        try {
            draftData = JSON.parse(draftDataInput?.value || '{}');
            console.log('Parsed Draft Data:', draftData);
        } catch (error) {
            console.error('Error parsing draft data:', error);
        }

        // Get segments data and locations
        const segmentsDataElement = document.getElementById('segments-data');
        let locations = [];
        try {
            const parsedSegments = JSON.parse(segmentsDataElement?.value || '[]');
            console.log('Parsed Segments Data:', parsedSegments);
            
            locations = parsedSegments.map(segment => ({
                from_location: segment.from_location,
                to_location: segment.to_location,
                distance: parseFloat(segment.distance),
                order: segment.order
            }));
        } catch (error) {
            console.error('Error parsing segments:', error);
        }

        // Get total distance and cost from draft data
        const totalDistance = draftData.total_distance || '0';
        const totalCost = draftData.total_cost || '0';

        console.log('Distance and Cost:', {
            totalDistance,
            totalCost
        });

        // Prepare the main claim data
        const claimData = {
            claim_company: formData.get('claim_company') || draftData.claim_company,
            date_from: formData.get('date_from') || draftData.date_from,
            date_to: formData.get('date_to') || draftData.date_to,
            remarks: formData.get('remarks') || draftData.remarks,
            total_distance: totalDistance,
            petrol_amount: totalCost,
            toll_amount: formData.get('toll_amount') || '0',
            locations: locations,
            status: 'Submitted',
            title: `Petrol Claim ${draftData.date_from} to ${draftData.date_to}`,
            claim_type: 'Petrol',
        };

        console.log('Final Claim Data:', claimData);

        // Create the final form data for submission
        const submitFormData = new FormData();
        
        // Add claim data
        Object.entries(claimData).forEach(([key, value]) => {
            if (key === 'locations') {
                submitFormData.append(key, JSON.stringify(value));
            } else {
                // Ensure we're sending strings and removing any whitespace
                const cleanValue = typeof value === 'string' ? 
                    value.trim() : 
                    String(value).trim();
                submitFormData.append(key, cleanValue);
            }
        });

        // Add files if they exist
        const tollFile = document.getElementById('toll_report')?.files[0];
        const emailFile = document.getElementById('email_report')?.files[0];
        
        if (tollFile) {
            submitFormData.append('toll_file', tollFile);
        }
        if (emailFile) {
            submitFormData.append('email_file', emailFile);
        }

        // Log the final FormData entries
        console.log('Final FormData entries:');
        for (let pair of submitFormData.entries()) {
            console.log(pair[0], pair[1]);
        }

        console.groupEnd();

    // Show loading state
    const submitButton = document.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Submitting...
    `;

        try {
            console.log('Submitting FormData:', Object.fromEntries(submitFormData.entries()));

            const response = await fetch('/claims/store', {
                method: 'POST',
                body: submitFormData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json' // Add this to ensure JSON response
                }
            });

            const result = await response.json();
            console.log('Server Response:', result);

            if (!response.ok) {
                // Log validation errors if they exist
                if (result.errors) {
                    console.error('Validation Errors:', result.errors);
                    throw new Error(Object.values(result.errors).flat().join('\n'));
                }
                throw new Error(result.message || 'Submission failed');
            }

            if (result.success) {
                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: 'Your claim has been submitted successfully.',
                    icon: 'success',
                    confirmButtonText: 'Go to Dashboard'
                }).then((result) => {
                    // Clear all stored data
                    localStorage.removeItem('claimFormData');
                    localStorage.removeItem('draftData');
                    sessionStorage.clear();
                    
                    // Clear map data if exists
                    if (window.claimMap) {
                        window.claimMap.clearMapData();
                    }

                    if (result.isConfirmed) {
                        window.location.href = '/claims/dashboard';
                    }
                });
            }
        } catch (error) {
            console.error('Error submitting claim:', error);
            
            // Show error message with more details
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to submit claim. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } finally {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
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

// Initialize single instance
document.addEventListener('DOMContentLoaded', () => {
    new ClaimForm();
});

// Export the class if needed
export default ClaimForm;