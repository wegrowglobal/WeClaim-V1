import { LocationManager } from './utils/location-manager';
import ErrorHandler from './utils/error-handler';

class AccommodationManager {
    constructor() {
        this.accommodationIndex = 0;
        this.locationManager = new LocationManager();
        
        // Bind methods to this instance
        this.addAccommodation = this.addAccommodation.bind(this);
        this.removeAccommodation = this.removeAccommodation.bind(this);
        this.updateFileName = this.updateFileName.bind(this);
        
        // Initialize
        this.init();

        // Add event listener for form navigation
        this.setupFormNavigationListener();
    }

    init() {
        ErrorHandler.handle(async () => {
            // Load existing accommodations from draft data
            const draftDataInput = document.getElementById('draftData');
            if (draftDataInput) {
                try {
                    const draftData = JSON.parse(draftDataInput.value || '{}');
                    console.log('Initializing AccommodationManager with draft data:', draftData);
                    
                    let accommodations = [];
                    if (draftData.accommodations) {
                        // Handle both string and array formats
                        accommodations = typeof draftData.accommodations === 'string' 
                            ? JSON.parse(draftData.accommodations) 
                            : draftData.accommodations;
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
                                
                                // Populate the fields
                                const locationInput = document.getElementById(`accommodation_location_${index}`);
                                const priceInput = document.getElementById(`accommodation_price_${index}`);
                                const checkInInput = document.getElementById(`accommodation_check_in_${index}`);
                                const checkOutInput = document.getElementById(`accommodation_check_out_${index}`);
                                const receiptNameElement = document.getElementById(`accommodation_receipt_name_${index}`);
                                
                                if (locationInput) locationInput.value = accommodation.location || '';
                                if (priceInput) priceInput.value = accommodation.price || '';
                                if (checkInInput) checkInInput.value = accommodation.check_in || '';
                                if (checkOutInput) checkOutInput.value = accommodation.check_out || '';
                                if (receiptNameElement && accommodation.receipt_name) {
                                    receiptNameElement.textContent = accommodation.receipt_name;
                                }
                            }
                        });
                        
                        // Update index for new entries
                        this.accommodationIndex = accommodations.length;
                        
                        // Initialize autocomplete for all entries
                        await this.initializeExistingAutocomplete();
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
                existingAccommodations: this.accommodationIndex
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

    addAccommodation() {
        const container = document.getElementById('accommodations-container');
        const template = this.getAccommodationTemplate();
        
        container.insertAdjacentHTML('beforeend', template);
        this.initializeAutocomplete(this.accommodationIndex);
        this.accommodationIndex++;
        this.updateAccommodationsData();
    }

    removeAccommodation(index) {
        const entry = document.querySelector(`.accommodation-entry[data-index="${index}"]`);
        if (entry) {
            entry.remove();
            this.updateAccommodationsData();
        }
    }

    updateFileName(index, input) {
        const nameElement = document.getElementById(`accommodation_receipt_name_${index}`);
        if (nameElement && input.files.length > 0) {
            nameElement.textContent = input.files[0].name;
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
            console.log('UpdateAccommodationsData - Starting update');
            
            const entries = document.querySelectorAll('.accommodation-entry');
            const data = Array.from(entries).map(entry => {
                const index = entry.dataset.index;
                const locationInput = document.getElementById(`accommodation_location_${index}`);
                const receiptNameElement = document.getElementById(`accommodation_receipt_name_${index}`);
                
                const accommodationData = {
                    location: locationInput?.value || '',
                    location_address: locationInput?.value || '',
                    price: document.getElementById(`accommodation_price_${index}`)?.value || '',
                    check_in: document.getElementById(`accommodation_check_in_${index}`)?.value || '',
                    check_out: document.getElementById(`accommodation_check_out_${index}`)?.value || '',
                    receipt_name: receiptNameElement?.textContent?.trim() === 'No file selected' ? '' : receiptNameElement?.textContent || ''
                };

                console.log(`Accommodation ${index} data:`, accommodationData);
                return accommodationData;
            });

            // Update both hidden inputs
            const accommodationsDataInput = document.getElementById('accommodations-data');
            const draftDataInput = document.getElementById('draftData');
            
            if (accommodationsDataInput) {
                accommodationsDataInput.value = JSON.stringify(data);
            }
            
            if (draftDataInput) {
                try {
                    const draftData = JSON.parse(draftDataInput.value || '{}');
                    draftData.accommodations = data;
                    draftDataInput.value = JSON.stringify(draftData);
                    
                    console.log('Draft data updated with accommodations:', {
                        accommodationsCount: data.length,
                        accommodations: data,
                        fullDraftData: draftData
                    });
                } catch (error) {
                    console.error('Error updating draft data:', error);
                }
            }
        }, 'AccommodationManager.updateAccommodationsData');
    }

    initializeAutocomplete(index) {
        const input = document.getElementById(`accommodation_location_${index}`);
        if (!input) return;

        try {
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
            <div class="accommodation-entry rounded-lg border border-gray-200 bg-white p-6" data-index="${this.accommodationIndex}">
                <!-- Header with Entry # and Delete -->
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">Entry #${this.accommodationIndex + 1}</h3>
                    <button type="button" onclick="window.accommodationManager.removeAccommodation(${this.accommodationIndex})" 
                        class="text-gray-400 hover:text-red-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Location - Full Width -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700" for="accommodation_location_${this.accommodationIndex}">
                            Location
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                id="accommodation_location_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][location]"
                                class="location-autocomplete form-input block w-full rounded-md border-gray-200 text-sm"
                                data-accommodation-index="${this.accommodationIndex}"
                                placeholder="Enter or select location"
                                required>
                        </div>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700" for="accommodation_price_${this.accommodationIndex}">
                            Price (RM)
                        </label>
                        <div class="mt-1">
                            <input type="number" 
                                step="0.01"
                                id="accommodation_price_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][price]"
                                class="form-input block w-full rounded-md border-gray-200 text-sm"
                                placeholder="0.00"
                                required>
                        </div>
                    </div>

                    <!-- Check-in Date -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700" for="accommodation_check_in_${this.accommodationIndex}">
                            Check-in Date
                        </label>
                        <div class="mt-1">
                            <input type="date" 
                                id="accommodation_check_in_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][check_in]"
                                class="form-input block w-full rounded-md border-gray-200 text-sm"
                                required>
                        </div>
                    </div>

                    <!-- Check-out Date -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700" for="accommodation_check_out_${this.accommodationIndex}">
                            Check-out Date
                        </label>
                        <div class="mt-1">
                            <input type="date" 
                                id="accommodation_check_out_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][check_out]"
                                class="form-input block w-full rounded-md border-gray-200 text-sm"
                                required>
                        </div>
                    </div>

                    <!-- Receipt Upload -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Receipt</label>
                        <div class="mt-1">
                            <input type="file" 
                                id="accommodation_receipt_${this.accommodationIndex}"
                                name="accommodations[${this.accommodationIndex}][receipt]"
                                class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onchange="window.accommodationManager.updateFileName(${this.accommodationIndex}, this)">
                            <label for="accommodation_receipt_${this.accommodationIndex}"
                                class="group flex items-center h-[38px] w-full px-3 border border-gray-300 rounded-md hover:border-gray-400 cursor-pointer bg-white">
                                <div class="flex items-center space-x-2 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <span class="text-xs">
                                        <span class="text-indigo-600 font-medium hover:text-indigo-500">Upload</span>
                                        or Drag
                                    </span>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-xs text-gray-500 truncate" id="accommodation_receipt_name_${this.accommodationIndex}">
                                        No file selected
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        `;
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