import { LocationManager } from '../utils/location-manager';
import ErrorHandler from '../utils/error-handler';

class AccommodationManager {
    constructor() {
        this.accommodationIndex = 0;
        this.locationManager = new LocationManager();
        this.claimDateFrom = null;
        this.claimDateTo = null;
        
        // Bind methods to this instance
        this.addAccommodation = this.addAccommodation.bind(this);
        this.removeAccommodation = this.removeAccommodation.bind(this);
        this.updateFileName = this.updateFileName.bind(this);
        this.validateDates = this.validateDates.bind(this);
        this.setupDateValidation = this.setupDateValidation.bind(this);
        
        // Initialize
        this.init();

        // Add event listener for form navigation
        this.setupFormNavigationListener();
    }

    init() {
        ErrorHandler.handle(async () => {
            // Wait for Google Maps to load
            if (!window.google || !window.google.maps) {
                console.warn('Waiting for Google Maps to load...');
                await new Promise(resolve => {
                    const checkGoogleMaps = setInterval(() => {
                        if (window.google && window.google.maps) {
                            clearInterval(checkGoogleMaps);
                            resolve();
                        }
                    }, 100);
                });
            }

            // Get claim period dates from draft data
            const draftDataInput = document.getElementById('draftData');
            if (draftDataInput) {
                try {
                    const draftData = JSON.parse(draftDataInput.value || '{}');
                    console.log('Initializing AccommodationManager with draft data:', draftData);
                    
                    // Store claim period dates
                    this.claimDateFrom = draftData.date_from || null;
                    this.claimDateTo = draftData.date_to || null;
                    
                    let accommodations = [];
                    if (draftData.accommodations) {
                        accommodations = typeof draftData.accommodations === 'string' 
                            ? JSON.parse(draftData.accommodations) 
                            : draftData.accommodations;
                            
                        // Remove any document-related fields
                        accommodations = accommodations.map(acc => {
                            const { receipt_path, receipt_file, file, receipt_name, ...rest } = acc;
                            return rest;
                        });
                    }
                    
                    console.log('Parsed accommodations:', accommodations);

                    if (Array.isArray(accommodations) && accommodations.length > 0) {
                        // Clear existing entries first
                        const container = document.getElementById('accommodations-container');
                        if (container) {
                            container.innerHTML = '';
                        }
                                
                        // Create accommodation entries for existing data
                        accommodations.forEach((accommodation, index) => {
                            this.accommodationIndex = index;
                            if (container) {
                                const template = this.getAccommodationTemplate();
                                container.insertAdjacentHTML('beforeend', template);
                                
                                // Populate the fields (excluding document-related fields)
                                const locationInput = document.getElementById(`accommodation_location_${index}`);
                                const priceInput = document.getElementById(`accommodation_price_${index}`);
                                const checkInInput = document.getElementById(`accommodation_check_in_${index}`);
                                const checkOutInput = document.getElementById(`accommodation_check_out_${index}`);
                                
                                if (locationInput) locationInput.value = accommodation.location || '';
                                if (priceInput) priceInput.value = accommodation.price || '';
                                if (checkInInput) {
                                    checkInInput.value = accommodation.check_in || '';
                                    this.setupDateValidation(checkInInput, 'check-in');
                                }
                                if (checkOutInput) {
                                    checkOutInput.value = accommodation.check_out || '';
                                    this.setupDateValidation(checkOutInput, 'check-out');
                                }
                                
                                // Initialize autocomplete for this entry
                                this.initializeAutocomplete(index);
                            }
                        });
                        
                        // Update index for new entries
                        this.accommodationIndex = accommodations.length;
                    }
                } catch (error) {
                    console.error('Error parsing draft data:', error);
                }
            }

            // Register global methods
            this.registerGlobalMethods();

            // Update accommodations data in draft
            this.updateAccommodationsData();

            console.info('AccommodationManager initialized', {
                existingAccommodations: this.accommodationIndex,
                claimDateFrom: this.claimDateFrom,
                claimDateTo: this.claimDateTo
            });
        }, 'AccommodationManager.init');
    }

    registerGlobalMethods() {
        // Make methods available globally
        window.accommodationManager = {
            addAccommodation: this.addAccommodation,
            removeAccommodation: this.removeAccommodation,
            updateFileName: this.updateFileName
        };
    }

    setupDateValidation(input, type) {
        if (!input) return;

        // Disable if claim period is not set
        if (!this.claimDateFrom || !this.claimDateTo) {
            input.disabled = true;
            input.classList.add('bg-gray-100', 'cursor-not-allowed');
            input.title = 'Please set the claim period in Step 1 first';
            
            // Find and update the tooltip message
            const tooltipContainer = input.parentElement.querySelector('.group div[class*="group-hover"]');
            if (tooltipContainer) {
                tooltipContainer.textContent = 'Please complete Step 1 and set the claim period dates first before adding accommodation dates';
                tooltipContainer.classList.add('bg-yellow-700'); // Change color to indicate warning
            }
            return;
        }

        // Remove disabled state if it was previously disabled
        input.disabled = false;
        input.classList.remove('bg-gray-100', 'cursor-not-allowed');
        
        // Reset tooltip to default message
        const tooltipContainer = input.parentElement.querySelector('.group div[class*="group-hover"]');
        if (tooltipContainer) {
            tooltipContainer.textContent = type === 'check-in' 
                ? 'Date must be within the claim period'
                : 'Date must be within the claim period and after check-in date';
            tooltipContainer.classList.remove('bg-yellow-700');
        }

        // Set min/max dates based on claim period
        input.min = this.claimDateFrom;
        input.max = this.claimDateTo;

        // Add event listeners for validation
        input.addEventListener('change', () => this.validateDates(input, type));
    }

    validateDates(input, type) {
        const index = input.id.match(/\d+/)[0];
        const checkInInput = document.getElementById(`accommodation_check_in_${index}`);
        const checkOutInput = document.getElementById(`accommodation_check_out_${index}`);

        if (!checkInInput || !checkOutInput) return;

        const checkInDate = checkInInput.value ? new Date(checkInInput.value) : null;
        const checkOutDate = checkOutInput.value ? new Date(checkOutInput.value) : null;
        const claimFromDate = this.claimDateFrom ? new Date(this.claimDateFrom) : null;
        const claimToDate = this.claimDateTo ? new Date(this.claimDateTo) : null;

        // Reset validation state
        input.classList.remove('border-red-500');
        
        if (type === 'check-in') {
            if (checkInDate && checkOutDate && checkInDate > checkOutDate) {
                input.value = '';
            } else if (checkInDate && claimFromDate && checkInDate < claimFromDate) {
                input.value = '';
            }
        } else if (type === 'check-out') {
            if (checkOutDate && checkInDate && checkOutDate < checkInDate) {
                input.value = '';
            } else if (checkOutDate && claimToDate && checkOutDate > claimToDate) {
                input.value = '';
            }
        }
    }

    addAccommodation() {
        const container = document.getElementById('accommodations-container');
        const template = this.getAccommodationTemplate();
        
        container.insertAdjacentHTML('beforeend', template);
        
        // Initialize new date inputs
        const checkInInput = document.getElementById(`accommodation_check_in_${this.accommodationIndex}`);
        const checkOutInput = document.getElementById(`accommodation_check_out_${this.accommodationIndex}`);
        
        this.setupDateValidation(checkInInput, 'check-in');
        this.setupDateValidation(checkOutInput, 'check-out');
        
        this.initializeAutocomplete(this.accommodationIndex);
        this.accommodationIndex++;
        this.updateAccommodationsData();
    }

    removeAccommodation(index) {
        const entry = document.querySelector(`.accommodation-entry[data-index="${index}"]`);
        if (entry) {
            entry.remove();
            this.updateAccommodationsData();
            
            // Reindex remaining entries
            const entries = document.querySelectorAll('.accommodation-entry');
            entries.forEach((entry, newIndex) => {
                entry.dataset.index = newIndex;
                entry.querySelector('h3').textContent = `Entry #${newIndex + 1}`;
                
                // Update remove button onclick
                const removeButton = entry.querySelector('button');
                if (removeButton) {
                    removeButton.setAttribute('onclick', `accommodationManager.removeAccommodation(${newIndex})`);
                }
            });
            
            // Update accommodationIndex
            this.accommodationIndex = entries.length;
        }
    }

    updateFileName(index, input) {
        const nameElement = document.getElementById(`accommodation_receipt_name_${index}`);
        if (nameElement && input.files.length > 0) {
            const file = input.files[0];
            nameElement.innerHTML = `
                <div class="flex items-center justify-between mt-2">
                    <div class="flex items-center space-x-2">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-gray-600">${file.name}</span>
                    </div>
                    <button type="button" onclick="this.closest('.document-upload-area').querySelector('input[type=file]').value = ''; document.getElementById('accommodation_receipt_name_${index}').innerHTML = '';" 
                        class="text-sm text-red-500 hover:text-red-700">
                        Remove
                    </button>
                </div>`;
        } else if (nameElement) {
            nameElement.innerHTML = '';
        }
    }

    setupFormNavigationListener() {
        // Listen for form navigation events
        document.addEventListener('click', (e) => {
            const nextButton = e.target.closest('[onclick*="nextStep"]');
            const prevButton = e.target.closest('[onclick*="previousStep"]');
            
            if (nextButton || prevButton) {
                this.updateAccommodationsData();
            }
        });

        // Also update when inputs change
        document.getElementById('accommodations-container')?.addEventListener('change', (e) => {
            if (e.target.matches('input')) {
                this.updateAccommodationsData();
            }
        });
    }

    updateAccommodationsData() {
        ErrorHandler.handle(() => {
            const accommodations = [];
            const entries = document.querySelectorAll('.accommodation-entry');
            
            entries.forEach((entry, index) => {
                const locationInput = document.getElementById(`accommodation_location_${index}`);
                const priceInput = document.getElementById(`accommodation_price_${index}`);
                const checkInInput = document.getElementById(`accommodation_check_in_${index}`);
                const checkOutInput = document.getElementById(`accommodation_check_out_${index}`);
                const receiptInput = document.getElementById(`accommodation_receipt_${index}`);
                
                // Skip if any required field is missing or empty
                if (!locationInput?.value || 
                    !priceInput?.value || 
                    !checkInInput?.value || 
                    !checkOutInput?.value) {
                    console.warn('Skipping incomplete accommodation entry', { index });
                    return;
                }
                
                // Create accommodation entry
                const accommodation = {
                    location: locationInput.value.trim(),
                    price: parseFloat(priceInput.value),
                    check_in: checkInInput.value,
                    check_out: checkOutInput.value
                };
                
                // Add receipt if present
                if (receiptInput?.files?.length > 0) {
                    accommodation.receipt = receiptInput.files[0];
                    accommodation.receipt_name = receiptInput.files[0].name;
                }
                
                // Validate data
                if (accommodation.price <= 0) {
                    console.warn('Skipping accommodation entry with invalid price', { index, price: accommodation.price });
                    return;
                }
                
                // Validate dates
                const checkIn = new Date(accommodation.check_in);
                const checkOut = new Date(accommodation.check_out);
                const claimDateFrom = this.claimDateFrom ? new Date(this.claimDateFrom) : null;
                const claimDateTo = this.claimDateTo ? new Date(this.claimDateTo) : null;
                
                if (checkIn > checkOut || 
                    (claimDateFrom && checkIn < claimDateFrom) || 
                    (claimDateTo && checkOut > claimDateTo)) {
                    console.warn('Skipping accommodation entry with invalid dates', {
                        index,
                        check_in: accommodation.check_in,
                        check_out: accommodation.check_out,
                        claim_date_from: this.claimDateFrom,
                        claim_date_to: this.claimDateTo
                    });
                    return;
                }
                
                accommodations.push(accommodation);
            });
            
            // Update hidden input with validated data
            const accommodationsInput = document.getElementById('accommodations-data');
            if (accommodationsInput) {
                accommodationsInput.value = JSON.stringify(accommodations);
                console.log('Updated accommodations data', { count: accommodations.length, data: accommodations });
            }
            
            // Update draft data
            const draftDataInput = document.getElementById('draftData');
            if (draftDataInput) {
                try {
                    const draftData = JSON.parse(draftDataInput.value || '{}');
                    draftData.accommodations = accommodations;
                    draftDataInput.value = JSON.stringify(draftData);
                } catch (error) {
                    console.error('Error updating draft data with accommodations', error);
                }
            }
        }, 'AccommodationManager.updateAccommodationsData');
    }

    initializeAutocomplete(index) {
        const input = document.getElementById(`accommodation_location_${index}`);
        if (!input) return;

        try {
            // Initialize Google Places Autocomplete
            this.locationManager.initializeAutocomplete(input, {
                onPlaceChanged: (place) => {
                    if (!place.geometry) {
                        console.warn("No location data available for this place");
                        return;
                    }

                    const formattedAddress = place.formatted_address;
                    input.value = formattedAddress;

                    console.info('Accommodation location selected', {
                        index,
                        address: formattedAddress,
                        lat: place.geometry.location.lat(),
                        lng: place.geometry.location.lng()
                    });

                    this.updateAccommodationsData();
                }
            });

            // Add input event listener for better UX
            input.addEventListener('input', () => {
                // Ensure the input stays visible while typing
                const pac_container = document.querySelector('.pac-container');
                if (pac_container) {
                    pac_container.style.zIndex = '9999';
                }
            });

            // Add click handler for the location picker button
            const locationPickerBtn = input.parentElement.querySelector('button');
            if (locationPickerBtn) {
                locationPickerBtn.addEventListener('click', (e) => {
                    e.preventDefault(); // Prevent form submission
                    this.locationManager.showMapPicker(input, {
                        onPlaceChanged: (place) => {
                            if (place.geometry) {
                                input.value = place.formatted_address;
                                this.updateAccommodationsData();
                            }
                        }
                    });
                });
            }

        } catch (error) {
            console.error('Error initializing autocomplete', {
                index,
                error: error.message
            });
        }
    }

    async initializeExistingAutocomplete() {
        await ErrorHandler.handle(async () => {
            const existingInputs = document.querySelectorAll('.location-autocomplete');
            const initPromises = Array.from(existingInputs).map(input => {
                const index = input.dataset.accommodationIndex;
                if (index) {
                    return this.initializeAutocomplete(index);
                }
                return Promise.resolve();
            });

            await Promise.all(initPromises);
            
            console.info('Existing autocomplete initialized', {
                count: existingInputs.length
            });
        }, 'AccommodationManager.initializeExistingAutocomplete');
    }

    getAccommodationTemplate() {
        return `
            <div class="mb-4 accommodation-entry overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" data-index="${this.accommodationIndex}">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">Accommodation Entry #${this.accommodationIndex + 1}</p>
                        <button type="button" onclick="window.accommodationManager.removeAccommodation(${this.accommodationIndex})" 
                            class="text-red-600 hover:text-red-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Location -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="accommodation_location_${this.accommodationIndex}">
                                Location
                            </label>
                            <div class="relative">
                                <input type="text" 
                                    id="accommodation_location_${this.accommodationIndex}"
                                    name="accommodations[${this.accommodationIndex}][location]"
                                    class="location-autocomplete form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500 pr-10"
                                    data-accommodation-index="${this.accommodationIndex}"
                                    placeholder="Enter or select location"
                                    required>
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <button type="button" class="h-full px-2 text-gray-400 hover:text-gray-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="accommodation_price_${this.accommodationIndex}">
                                Price (RM)
                            </label>
                            <input type="number" 
                                step="0.01"
                                id="accommodation_price_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][price]"
                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                required>
                        </div>

                        <!-- Check-in Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="accommodation_check_in_${this.accommodationIndex}">
                                Check-in Date
                            </label>
                            <input type="date" 
                                id="accommodation_check_in_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][check_in]"
                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                required>
                        </div>

                        <!-- Check-out Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="accommodation_check_out_${this.accommodationIndex}">
                                Check-out Date
                            </label>
                            <input type="date" 
                                id="accommodation_check_out_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][check_out]"
                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                required>
                        </div>

                        <!-- Receipt Upload -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Receipt
                            </label>
                            <div class="document-upload-area">
                                <input type="file" 
                                    id="accommodation_receipt_${this.accommodationIndex}"
                                    name="accommodations[${this.accommodationIndex}][receipt]"
                                    class="hidden"
                                    onchange="window.accommodationManager.updateFileName(${this.accommodationIndex}, this)"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <label for="accommodation_receipt_${this.accommodationIndex}"
                                    class="document-upload-label block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors hover:border-indigo-400"
                                    ondragover="event.preventDefault(); event.stopPropagation(); this.classList.add('border-indigo-400');"
                                    ondragleave="event.preventDefault(); event.stopPropagation(); this.classList.remove('border-indigo-400');"
                                    ondrop="event.preventDefault(); event.stopPropagation(); this.classList.remove('border-indigo-400'); const input = document.getElementById('accommodation_receipt_${this.accommodationIndex}'); input.files = event.dataTransfer.files; window.accommodationManager.updateFileName(${this.accommodationIndex}, input);">
                                    <div class="space-y-2 text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <div class="flex flex-col items-center text-sm">
                                            <div>
                                                <span class="font-medium text-indigo-600">Click to upload</span>
                                                <span class="text-gray-500"> or drag and drop</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, JPEG or PNG</p>
                                        </div>
                                    </div>
                                </label>
                                <div id="accommodation_receipt_name_${this.accommodationIndex}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    validateAccommodationEntry(entry) {
        const inputs = entry.querySelectorAll('input');
        let filledFields = 0;
        
        inputs.forEach(input => {
            if (input.value.trim() !== '') filledFields++;
        });

        // Require all fields if any are filled, but allow completely empty
        if (filledFields > 0 && filledFields < inputs.length) {
            this.showError('Please fill all fields or remove the accommodation entry');
            return false;
        }
        
        return true;
    }
}

// Initialize when document is ready and Google Maps is loaded
document.addEventListener('DOMContentLoaded', () => {
    ErrorHandler.handle(async () => {
        await new Promise(resolve => {
            if (window.google && window.google.maps) {
                resolve();
            } else {
                // Wait for Google Maps to be loaded
                const checkGoogleMaps = setInterval(() => {
                    if (window.google && window.google.maps) {
                        clearInterval(checkGoogleMaps);
                        resolve();
                    }
                }, 100);
            }
        });

        // Store instance globally for access from ClaimForm
        window.accommodationManagerInstance = new AccommodationManager();
    }, 'AccommodationManager.initialization');
});

export default AccommodationManager;

// Export the required functions
export function addAccommodationInput() {
    if (window.accommodationManager) {
        window.accommodationManager.addAccommodation();
    }
}

export function loadAccommodations(accommodations) {
    // This function will be called when loading existing accommodations
    if (window.accommodationManager) {
        accommodations.forEach(() => {
            window.accommodationManager.addAccommodation();
        });
    }
} 