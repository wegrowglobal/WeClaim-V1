import { ClaimMap } from './maps/claim-map.js';
import ErrorHandler from './utils/error-handler.js';
import ValidationUtils from './utils/validation.js';
import Logger from './utils/logger.js';

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
        this.handleSubmit = this.handleSubmit.bind(this);

        this.debouncedSave = this.debounce(this.saveCurrentStep, 500);

        // Initialize the instance
        this.init();
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async init() {
        // Set up the global instance
        window.claimForm = {
            nextStep: this.nextStep,
            previousStep: this.previousStep,
            resetForm: this.resetForm,
            saveCurrentStep: this.saveCurrentStep,
            handleSubmit: this.handleSubmit
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
            Logger.log('Loaded draft data', draftData);
            return draftData;
        } catch (error) {
            Logger.error('Error loading draft data', error);
            return {};
        }
    }

    populateFormFields(data) {
        
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
            if (window.claimMap.initialized) {
                
                // Check if there are any location inputs
                const locationInputs = document.querySelectorAll('.location-input');
                if (locationInputs.length === 0) {
                    window.claimMap.addInitialLocationInputs();
                } else {
                    window.claimMap.loadSavedData();
                }
    
                // Hide loading overlay after a short delay
                setTimeout(() => {
                    if (loadingOverlay) {
                        loadingOverlay.classList.add('hidden');
                    }
                }, 500);
            } else {
                setTimeout(checkMapAndPopulate, 100);
            }
        };
    
        checkMapAndPopulate();
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

        // Add beforeunload event listener for browser navigation
        window.addEventListener('beforeunload', (e) => {
            const draftData = document.getElementById('draftData')?.value;
            const currentUrl = window.location.href;
            
            // Don't show prompt if navigating between claim form steps
            if (currentUrl.includes('/claims/new')) {
                return;
            }
            
            if (draftData && draftData !== '{}') {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Handle navigation clicks
        document.addEventListener('click', async (e) => {
            const target = e.target.closest('a, button[type="button"]');
            if (!target) return;

            const href = target.getAttribute('href');
            const onClick = target.getAttribute('onclick')?.toString() || '';
            
            // Skip if it's internal navigation or form actions
            if (onClick.includes('nextStep') || 
                onClick.includes('previousStep') || 
                onClick.includes('resetForm') ||
                onClick.includes('handleSubmit') ||
                !href || 
                href.includes('/claims/new') ||
                href.startsWith('#')) {
                return;
            }

            // Check if we have draft data
            const draftData = document.getElementById('draftData')?.value;
            if (draftData && draftData !== '{}') {
                e.preventDefault();
                e.stopPropagation();
                
                const shouldLeave = await this.confirmLeave();
                if (shouldLeave) {
                    // Reset form without confirmation
                    const event = new Event('click');
                    event.skipConfirmation = true;
                    await this.resetForm(event);
                    
                    // Navigate away
                    window.location.href = href;
                }
            }
        });
    }

    async showContinuePrompt() {
        const draftData = localStorage.getItem('draftData');
        if (draftData) {
            const result = await Swal.fire({
                title: 'Continue where you left off?',
                text: 'It looks like you have an unfinished claim. Do you want to continue from where you left off?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'No, start fresh',
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    htmlContainer: 'text-base text-gray-600'
                }
            });

            if (result.isConfirmed) {
                return true;
            } else {
                localStorage.removeItem('draftData');
                return false;
            }
        }
        return false;
    }

    async saveCurrentStep() {
        const form = document.getElementById('claimForm');
        if (!form) return true;

        console.log('SaveCurrentStep - Initial draft data:', {
            draftData: JSON.parse(document.getElementById('draftData')?.value || '{}'),
            accommodationsData: JSON.parse(document.getElementById('accommodations-data')?.value || '[]')
        });

        // Get existing draft data
        const draftDataInput = document.getElementById('draftData');
        let existingData = {};
        try {
            existingData = JSON.parse(draftDataInput?.value || '{}');
        } catch (error) {
            console.error('Error parsing existing draft data:', error);
        }

        const formData = new FormData(form);
        
        // Preserve existing accommodations data
        let accommodationsData = existingData.accommodations || [];
        // If we're on step 3, get fresh accommodations data
        if (this.currentStep === 3 && window.accommodationManagerInstance) {
            window.accommodationManagerInstance.updateAccommodationsData(); // Force update
            const accommodationsInput = document.getElementById('accommodations-data');
            try {
                const newAccommodations = JSON.parse(accommodationsInput?.value || '[]');
                if (newAccommodations.length > 0) {
                    // Remove receipt_path and file-related fields from accommodations data
                    accommodationsData = newAccommodations.map(acc => {
                        const {
                            receipt_path,
                            receipt_file,
                            file,
                            ...rest
                        } = acc;
                        return rest;
                    });
                }
            } catch (error) {
                console.error('Error parsing accommodations data:', error);
            }
        }

        // Create merged data object - excluding document-related fields
        const currentStepData = {
            ...existingData,
            current_step: this.currentStep,
            
            // Step 1 data
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
            
            // Step 3 data (excluding document fields)
            accommodations: accommodationsData.length > 0 ? accommodationsData : existingData.accommodations || [],
            toll_amount: formData.get('toll_amount') || existingData.toll_amount || '0'
        };

        // Remove any document-related fields that might exist
        delete currentStepData.toll_file;
        delete currentStepData.email_file;
        delete currentStepData.accommodation_receipts;
        delete currentStepData.accommodation_files;
        delete currentStepData.files;
        
        // Clean up any receipt paths from accommodations
        if (currentStepData.accommodations) {
            currentStepData.accommodations = currentStepData.accommodations.map(acc => {
                const { receipt_path, receipt_file, file, ...rest } = acc;
                return rest;
            });
        }

        console.log('SaveCurrentStep - Data to be saved:', {
            currentStepData,
            accommodationsData,
            existingAccommodations: existingData.accommodations
        });

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
                
                // Log final state
                console.log('SaveCurrentStep - Final draft data after save:', {
                    draftData: JSON.parse(draftDataInput.value),
                    accommodationsData: currentStepData.accommodations
                });
            }

            return true;
        } catch (error) {
            console.error('Error saving step data:', error);
            return false;
        }
    }

    async nextStep(currentStep) {
        // Add logging before saving
        console.log('Next Step - Current draft data before save:', {
            draftData: JSON.parse(document.getElementById('draftData')?.value || '{}'),
            accommodationsData: JSON.parse(document.getElementById('accommodations-data')?.value || '[]')
        });

        // Save current step data before proceeding
        const savedSuccessfully = await this.saveCurrentStep();
        if (!savedSuccessfully) {
            console.error('Failed to save current step data');
            return;
        }

        // Log after saving
        console.log('Next Step - Draft data after save:', {
            draftData: JSON.parse(document.getElementById('draftData')?.value || '{}')
        });

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

        // Handle accommodations data and files
        let accommodationsData = [];
        const accommodationEntries = document.querySelectorAll('.accommodation-entry');
        accommodationEntries.forEach((acc, index) => {
            // Only add if at least one field is filled
            const hasContent = Array.from(acc.querySelectorAll('input'))
                .some(input => input.value.trim() !== '');
            
            if (hasContent) {
                const location = acc.querySelector(`input[name="accommodation_location_${index}"]`)?.value?.trim() || '';
                const price = acc.querySelector(`input[name="accommodation_price_${index}"]`)?.value?.trim() || '';
                const checkIn = acc.querySelector(`input[name="accommodation_check_in_${index}"]`)?.value?.trim() || '';
                const checkOut = acc.querySelector(`input[name="accommodation_check_out_${index}"]`)?.value?.trim() || '';

                accommodationsData.push({
                    location: location,
                    price: price,
                    check_in: checkIn,
                    check_out: checkOut
                });

                // Add accommodation receipt file if it exists
                const receiptFile = document.getElementById(`accommodation_receipt_${index}`)?.files[0];
                if (receiptFile) {
                    formData.append(`accommodation_receipts[${index}]`, receiptFile);
                }
            }
        });

        // Only include accommodations in claimData if there are valid entries
        const claimData = {
            claim_company: formData.get('claim_company') || existingData.claim_company,
            date_from: formData.get('date_from') || existingData.date_from,
            date_to: formData.get('date_to') || existingData.date_to,
            remarks: formData.get('remarks') || existingData.remarks,
            total_distance: existingData.total_distance || '0',
            petrol_amount: existingData.total_cost || '0',
            toll_amount: formData.get('toll_amount') || '0',
            locations: existingData.locations || [],
            status: 'Submitted',
            title: `Petrol Claim ${existingData.date_from} to ${existingData.date_to}`,
            claim_type: 'Petrol',
        };

        // Only add accommodations if there are valid entries
        if (accommodationsData.length > 0) {
            claimData.accommodations = accommodationsData;
        } else {
            // Explicitly set to empty array if no accommodations
            claimData.accommodations = [];
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
            accommodations: accommodationsData.length > 0 ? accommodationsData : existingData.accommodations,
            toll_amount: formData.get('toll_amount') || existingData.toll_amount
        };

        console.log('Merged data before navigation:', mergedData);

        // Update draft data input
        if (draftDataInput) {
            draftDataInput.value = JSON.stringify(mergedData);
        }

        // Navigate to next step
        await this.loadStep(currentStep + 1);
    }

    async previousStep(currentStep) {
        // Add logging before saving
        console.log('Previous Step - Current draft data before save:', {
            draftData: JSON.parse(document.getElementById('draftData')?.value || '{}'),
            accommodationsData: JSON.parse(document.getElementById('accommodations-data')?.value || '[]')
        });

        // Save current step data before proceeding
        const savedSuccessfully = await this.saveCurrentStep();
        if (!savedSuccessfully) {
            console.error('Failed to save current step data');
            return;
        }

        // Log after saving
        console.log('Previous Step - Draft data after save:', {
            draftData: JSON.parse(document.getElementById('draftData')?.value || '{}')
        });

        // Navigate to previous step
        await this.loadStep(currentStep - 1);
    }

    async resetForm(e) {
        if (e) e.preventDefault();
        
        // Skip confirmation if specified
        if (!e?.skipConfirmation) {
            const result = await Swal.fire({
                title: 'Reset Form?',
                text: 'This will clear all entered data.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#4F46E5',
                confirmButtonText: 'Reset Form',
                cancelButtonText: 'Keep Editing',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-lg shadow-xl border border-gray-200',
                    title: 'text-xl font-medium text-gray-900',
                    htmlContainer: 'text-base text-gray-600',
                    confirmButton: 'inline-flex items-center ml-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all',
                    cancelButton: 'inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all'
                },
                buttonsStyling: false
            });

            if (!result.isConfirmed) return;
        }

        try {
            // Reset all form inputs
            const form = document.getElementById('claimForm');
            if (form) {
                form.reset();
            }

            // Clear file inputs
            ['toll_report', 'email_report'].forEach(id => {
                const fileInput = document.getElementById(id);
                if (fileInput) {
                    fileInput.value = '';
                    const previewId = id.replace('_report', '-preview');
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.classList.add('hidden');
                    }
                }
            });

            // Clear accommodation entries
            const accommodationContainer = document.getElementById('accommodations-container');
            if (accommodationContainer) {
                accommodationContainer.innerHTML = '';
            }

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
            await fetch('/claims/reset-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            // Redirect to step 1
            window.location.href = '/claims/new?step=1';
        } catch (error) {
            console.error('Error resetting form:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to reset form. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        Logger.group('Claim Form Submission');

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
            const form = document.getElementById('claimForm');
            const formData = new FormData(form);
            const draftDataInput = document.getElementById('draftData');
            let draftData = {};
            
            try {
                draftData = JSON.parse(draftDataInput?.value || '{}');
            } catch (error) {
                Logger.error('Error parsing draft data', error);
            }

            // Handle accommodations data and files
            let accommodationsData = [];
            const accommodationEntries = document.querySelectorAll('.accommodation-entry');
            accommodationEntries.forEach((acc, index) => {
                // Only add if at least one field is filled
                const hasContent = Array.from(acc.querySelectorAll('input'))
                    .some(input => input.value.trim() !== '');
                
                if (hasContent) {
                    const location = acc.querySelector(`input[name="accommodation_location_${index}"]`)?.value?.trim() || '';
                    const price = acc.querySelector(`input[name="accommodation_price_${index}"]`)?.value?.trim() || '';
                    const checkIn = acc.querySelector(`input[name="accommodation_check_in_${index}"]`)?.value?.trim() || '';
                    const checkOut = acc.querySelector(`input[name="accommodation_check_out_${index}"]`)?.value?.trim() || '';

                    accommodationsData.push({
                        location: location,
                        price: price,
                        check_in: checkIn,
                        check_out: checkOut
                    });

                    // Add accommodation receipt file if it exists
                    const receiptFile = document.getElementById(`accommodation_receipt_${index}`)?.files[0];
                    if (receiptFile) {
                        formData.append(`accommodation_receipts[${index}]`, receiptFile);
                    }
                }
            });

            // Only include accommodations in claimData if there are valid entries
            const claimData = {
                claim_company: formData.get('claim_company') || draftData.claim_company,
                date_from: formData.get('date_from') || draftData.date_from,
                date_to: formData.get('date_to') || draftData.date_to,
                remarks: formData.get('remarks') || draftData.remarks,
                total_distance: draftData.total_distance || '0',
                petrol_amount: draftData.total_cost || '0',
                toll_amount: formData.get('toll_amount') || '0',
                locations: draftData.locations || [],
                status: 'Submitted',
                title: `Petrol Claim ${draftData.date_from} to ${draftData.date_to}`,
                claim_type: 'Petrol',
            };

            // Only add accommodations if there are valid entries
            if (accommodationsData.length > 0) {
                claimData.accommodations = accommodationsData;
            } else {
                // Explicitly set to empty array if no accommodations
                claimData.accommodations = [];
            }

            Object.entries(claimData).forEach(([key, value]) => {
                if (key === 'locations' || key === 'accommodations') {
                    formData.append(key, JSON.stringify(value));
                } else {
                    const cleanValue = typeof value === 'string' ? 
                        value.trim() : 
                        String(value).trim();
                    formData.append(key, cleanValue);
                }
            });

            // Add files if they exist
            const tollFile = document.getElementById('toll_report')?.files[0];
            const emailFile = document.getElementById('email_report')?.files[0];
            
            if (tollFile) {
                formData.append('toll_file', tollFile);
            }
            if (emailFile) {
                formData.append('email_file', emailFile);
            }

            const response = await fetch('/claims/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    throw new Error(Object.values(result.errors).flat().join('\n'));
                }
                throw new Error(result.message || 'Submission failed');
            }

            if (result.success) {
                await Swal.fire({
                    title: 'Success!',
                    text: 'Your claim has been submitted successfully.',
                    icon: 'success',
                    confirmButtonText: 'Go to Dashboard'
                });

                // Clear all stored data
                localStorage.removeItem('claimFormData');
                localStorage.removeItem('draftData');
                sessionStorage.clear();
                
                // Clear map data if exists
                if (window.claimMap) {
                    window.claimMap.clearMapData();
                }

                window.location.href = '/claims/dashboard';
            }
        } catch (error) {
            Logger.error('Submission error', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to submit claim. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } finally {
            Logger.groupEnd();
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
        const invalidFields = [];

        requiredFields.forEach(field => {
            const value = field.value.trim();
            const isValid = ValidationUtils.isRequired(value) && 
                          (field.type !== 'email' || ValidationUtils.isValidEmail(value)) &&
                          (field.type !== 'number' || ValidationUtils.isValidNumber(value));

            field.classList.toggle('border-red-500', !isValid);
            if (!isValid) {
                invalidFields.push(field);
            }
        });

        if (invalidFields.length > 0) {
            const firstInvalid = invalidFields[0];
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            Swal.fire({
                title: 'Validation Error',
                text: 'Please fill in all required fields correctly',
                icon: 'warning',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
            
            return false;
        }

        return true;
    }

    updateTotalCost(distance) {
        const RATE_PER_KM = 0.60; // Rate is RM 0.60 per kilometer
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
        const formattedDistance = parseFloat(distance).toFixed(2);
        
        // Update display element
        const totalDistanceElement = document.getElementById('total-distance');
        if (totalDistanceElement) {
            totalDistanceElement.textContent = formattedDistance;
        }
        
        // Update hidden input
        const totalDistanceInput = document.getElementById('total-distance-input');
        if (totalDistanceInput) {
            totalDistanceInput.value = formattedDistance;
        }
        
        // Update cost whenever distance changes
        this.updateTotalCost(formattedDistance);
    }

    async verifyDraftData() {
        return await ErrorHandler.handle(async () => {
            const draftDataInput = document.getElementById('draftData');
            if (!draftDataInput) return;

            const draftData = JSON.parse(draftDataInput.value);

            return true;
        }, 'verifyDraftData');
    }

    async confirmLeave() {
        const result = await Swal.fire({
            title: 'Leave Form?',
            text: 'You have unsaved changes. Leaving this page will discard your form data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Leave',
            cancelButtonText: 'Stay',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#4F46E5',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-lg shadow-xl border border-gray-200',
                title: 'text-xl font-medium text-gray-900',
                htmlContainer: 'text-base text-gray-600',
                confirmButton: 'inline-flex items-center ml-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all',
                cancelButton: 'inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all'
            },
            buttonsStyling: false
        });

        return result.isConfirmed;
    }

    async loadStep(step) {
        try {
            const response = await fetch(`/claims/get-step/${step}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to load step');

            // Update URL
            const url = new URL(window.location.href);
            url.searchParams.set('step', step);
            window.history.pushState({}, '', url.toString());

            // Reload the page to ensure proper initialization
            window.location.href = url.toString();
        } catch (error) {
            console.error('Error loading step:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to load the next step. Please try again.',
                icon: 'error'
            });
        }
    }

    initializeWithExistingData(data) {
        console.log('Initializing form with existing data:', data);

        const claim = data.claim;
        
        // Initialize basic form fields
        this.setFormValue('claim_company', claim.claim_company);
        this.setFormValue('date_from', claim.date_from);
        this.setFormValue('date_to', claim.date_to);
        this.setFormValue('description', claim.description);
        this.setFormValue('toll_amount', claim.toll_amount);

        // Initialize locations
        if (data.locations && data.locations.length > 0) {
            // Clear existing locations
            const locationInputs = document.getElementById('location-inputs');
            if (locationInputs) {
                locationInputs.innerHTML = '';
            }

            // Add each location
            data.locations.forEach((location, index) => {
                if (window.claimMap) {
                    window.claimMap.addLocationInput(location.from_location, index);
                }
            });

            // Update map and calculations after a short delay
            setTimeout(() => {
                if (window.claimMap) {
                    window.claimMap.updateRoute();
                }
            }, 500);
        }

        // Initialize accommodations
        if (data.accommodations && data.accommodations.length > 0) {
            data.accommodations.forEach(accommodation => {
                this.addAccommodation({
                    location: accommodation.location,
                    price: accommodation.price,
                    check_in: accommodation.check_in,
                    check_out: accommodation.check_out,
                    receipt_path: accommodation.receipt_path,
                    receipt_url: accommodation.receipt_url,
                    receipt_name: accommodation.receipt_name
                });
            });
        }

        // Initialize documents
        if (data.documents) {
            data.documents.forEach(doc => {
                const previewContainer = document.getElementById(`${doc.type}_preview`);
                if (previewContainer) {
                    this.createDocumentPreview(previewContainer, doc);
                }
            });
        }

        // Initialize toll receipts
        if (data.toll_receipts) {
            const container = document.getElementById('toll-receipts-container');
            if (container) {
                data.toll_receipts.forEach(receipt => {
                    this.createDocumentPreview(container, receipt);
                });
            }
        }

        // Initialize email approval
        if (data.email_approval) {
            const container = document.getElementById('email-approval-preview');
            if (container) {
                this.createDocumentPreview(container, data.email_approval);
            }
        }

        // Update form state
        this.updateFormState();
    }

    updateFormState() {
        // Update hidden inputs
        const draftDataInput = document.getElementById('draftData');
        if (draftDataInput) {
            const currentData = JSON.parse(draftDataInput.value || '{}');
            draftDataInput.value = JSON.stringify({
                ...currentData,
                total_distance: document.getElementById('total-distance')?.textContent,
                total_cost: document.getElementById('total-cost')?.textContent,
                total_duration: document.getElementById('total-duration')?.textContent
            });
        }
    }

    setFormValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            if (element.type === 'date' && value) {
                // Format date strings to YYYY-MM-DD
                const date = new Date(value);
                element.value = date.toISOString().split('T')[0];
            } else {
                element.value = value;
            }
        }
    }

    initializeLocations(locations) {
        // Clear existing locations
        const locationInputs = document.getElementById('location-inputs');
        if (locationInputs) {
            locationInputs.innerHTML = '';
        }

        // Add each location
        locations.forEach((location, index) => {
            this.addLocationInput(location.from_location, index);
        });

        // Update map and calculations
        this.updateMapAndCalculations();
    }

    initializeAccommodations(accommodations) {
        accommodations.forEach((accommodation, index) => {
            this.addAccommodation({
                location: accommodation.location,
                price: accommodation.price,
                check_in: accommodation.check_in,
                check_out: accommodation.check_out,
                receipt_path: accommodation.receipt_path,
                receipt_url: accommodation.receipt_url,
                receipt_name: accommodation.receipt_name
            });
        });
    }

    initializeDocuments(documents) {
        // Initialize document previews and file inputs
        documents.forEach(doc => {
            const previewContainer = document.getElementById(`${doc.type}_preview`);
            if (previewContainer) {
                this.createDocumentPreview(previewContainer, doc);
            }
        });
    }

    initializeTollReceipts(receipts) {
        const container = document.getElementById('toll-receipts-container');
        if (container && receipts.length > 0) {
            receipts.forEach(receipt => {
                this.createDocumentPreview(container, {
                    name: receipt.name,
                    url: receipt.url,
                    path: receipt.path
                });
            });
        }
    }

    initializeEmailApproval(approval) {
        const container = document.getElementById('email-approval-preview');
        if (container) {
            this.createDocumentPreview(container, {
                name: approval.name,
                url: approval.url,
                path: approval.path
            });
        }
    }

    createDocumentPreview(container, doc) {
        const preview = document.createElement('div');
        preview.className = 'document-preview flex items-center justify-between p-3 border rounded-lg bg-gray-50 mb-3';
        preview.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-600">${doc.name}</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="${doc.url}" target="_blank"
                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                    <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View
                </a>
                <button type="button" onclick="window.claimForm.removeDocument(this, '${doc.path}')"
                    class="inline-flex items-center text-sm text-red-600 hover:text-red-500">
                    <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>
        `;
        container.appendChild(preview);
    }

    removeDocument(button, path) {
        const preview = button.closest('.document-preview');
        if (preview) {
            preview.remove();
            // Add the path to a hidden input to track removed documents
            const removedDocsInput = document.getElementById('removed_documents') || this.createRemovedDocsInput();
            const removedDocs = JSON.parse(removedDocsInput.value || '[]');
            removedDocs.push(path);
            removedDocsInput.value = JSON.stringify(removedDocs);
        }
    }

    createRemovedDocsInput() {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.id = 'removed_documents';
        input.name = 'removed_documents';
        input.value = '[]';
        document.getElementById('claimForm').appendChild(input);
        return input;
    }
}

// Initialize single instance
document.addEventListener('DOMContentLoaded', () => {
    new ClaimForm();
});

// Export the class if needed
export default ClaimForm;
