// Wait for Google Maps to load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form handling
    initializeResubmitForm();
    initializeLocationInputs();
    initializeDetailsToggle();
    initializeAccommodations();
});

// Global state for accommodations
window.accommodationState = {
    entries: new Set(),
    getNextNumber() {
        let number = 1;
        while (this.entries.has(number)) {
            number++;
        }
        this.entries.add(number);
        return number;
    },
    removeNumber(number) {
        this.entries.delete(number);
    }
};

function initializeLocationInputs() {
    // Initialize existing location inputs
    document.querySelectorAll('.location-input').forEach(input => {
        initializeAutocomplete(input);
        
        // Add input event listener
        input.addEventListener('input', () => {
            if (input.value.trim()) {
                calculateDistances();
            }
        });
    });

    // Initialize delete buttons for existing locations
    initializeDeleteButtons();

    // Calculate initial distances
    calculateDistances();

    // Add location button handler
    const addLocationBtn = document.getElementById('add-location-btn');
    if (addLocationBtn) {
        addLocationBtn.addEventListener('click', addLocationInput);
    }
}

function initializeAutocomplete(input) {
    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['establishment', 'geocode'],
        fields: ['formatted_address', 'geometry', 'name'],
        componentRestrictions: { country: 'MY' }
    });

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            console.warn("No location data available for this place");
            return;
        }
        input.value = place.formatted_address;
        calculateDistances();
    });
}

function addLocationInput() {
    const container = document.getElementById('location-inputs');
    const locationCount = container.querySelectorAll('.location-pair').length;
    
    const wrapper = document.createElement('div');
    wrapper.className = 'location-pair relative p-5';
    
    const inputHtml = `
        <div class="flex items-start gap-4">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full ${locationCount % 2 === 0 ? 'bg-indigo-100' : 'bg-rose-100'}">
                            <span class="text-sm font-medium ${locationCount % 2 === 0 ? 'text-indigo-600' : 'text-rose-600'}">${String.fromCharCode(65 + locationCount)}</span>
                        </div>
                        <label class="block text-sm font-medium text-gray-700">Location ${locationCount + 1}</label>
                    </div>
                    <button type="button" 
                        class="delete-location inline-flex items-center rounded-md text-gray-400 hover:text-rose-500 focus:outline-none transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-1">
                    <input type="text" 
                        class="location-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500" 
                        placeholder="Enter location"
                        required>
                </div>
            </div>
        </div>
        <div class="mt-4 flex items-center space-x-3 text-sm distance-info">
            <div class="flex items-center rounded-full bg-gray-100 px-3 py-1">
                <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span class="distance-display font-medium text-gray-600">Distance will be calculated</span>
            </div>
            <div class="flex items-center rounded-full bg-gray-100 px-3 py-1">
                <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="petrol-amount-display font-medium text-gray-600">Petrol cost will be calculated</span>
            </div>
        </div>
    `;
    
    wrapper.innerHTML = inputHtml;
    container.appendChild(wrapper);
    
    // Initialize autocomplete for new input
    const newInput = wrapper.querySelector('.location-input');
    initializeAutocomplete(newInput);
    
    // Initialize delete buttons after adding new location
    initializeDeleteButtons();

    // Add input event listener to trigger calculations
    newInput.addEventListener('input', () => {
        if (newInput.value.trim()) {
            calculateDistances();
        }
    });

    // Calculate distances and update visibility
    calculateDistances();
    updateDistanceVisibility();
}

// Update the updateStopNumbers function to handle minimum locations
function updateStopNumbers() {
    const locationPairs = document.querySelectorAll('.location-pair');
    locationPairs.forEach((pair, index) => {
        // Update stop letter
        const letterSpan = pair.querySelector('.rounded-full span');
        if (letterSpan) {
            letterSpan.textContent = String.fromCharCode(65 + index);
        }
        
        // Update stop number
        const label = pair.querySelector('label');
        if (label) {
            label.textContent = `Location ${index + 1}`;
        }
        
        // Update background colors
        const roundedDiv = pair.querySelector('.rounded-full');
        if (roundedDiv) {
            roundedDiv.className = `flex h-7 w-7 items-center justify-center rounded-full ${index % 2 === 0 ? 'bg-indigo-100' : 'bg-rose-100'}`;
            roundedDiv.querySelector('span').className = `text-sm font-medium ${index % 2 === 0 ? 'text-indigo-600' : 'text-rose-600'}`;
        }

        // Update delete button visibility
        const deleteBtn = pair.querySelector('.delete-location');
        if (deleteBtn) {
            if (locationPairs.length > 2 && index > 0) {
                deleteBtn.style.display = 'flex';
            } else {
                deleteBtn.style.display = 'none';
            }
        }
    });
}

// Modify the distance calculation to include final destination
function updateDistanceVisibility() {
    const locationPairs = document.querySelectorAll('.location-pair');

    locationPairs.forEach((pair, index) => {
        const distanceInfo = pair.querySelector('.distance-info');
        if (distanceInfo) {
            // Always show distance info for all locations
            distanceInfo.style.display = 'flex';
            
            // Update text only for the actual last location
            if (index === locationPairs.length - 1) {
                const distanceDisplay = distanceInfo.querySelector('.distance-display');
                const petrolDisplay = distanceInfo.querySelector('.petrol-amount-display');
                if (distanceDisplay) {
                    distanceDisplay.textContent = 'N/A';
                }
                if (petrolDisplay) {
                    petrolDisplay.textContent = 'N/A';
                }
            }
        }
    });
}

async function calculateDistances() {
    const locationInputs = document.querySelectorAll('.location-input');
    const locations = [];
    const distances = [];
    
    locationInputs.forEach((input, index) => {
        locations.push(input.value.trim());
        // Always calculate distance if there's a next location
        if (index < locationInputs.length - 1) {
            const distanceText = input.closest('.location-pair').querySelector('.distance-display')?.textContent || '';
            const distance = parseFloat(distanceText.match(/[\d.]+/) || 0);
            distances.push(distance);
        }
    });

    // Add validation for minimum 2 locations
    if (locations.length < 2) {
        throw new Error('At least 2 locations required');
    }

    const ratePerKm = parseFloat(document.getElementById('rate-per-km')?.value || 0.60);
    
    if (locations.length < 2) {
        updateDistanceVisibility();
        return;
    }

    const directionsService = new google.maps.DirectionsService();
    let totalDistance = 0;
    const segments = [];

    // Reset all displays first with improved validation
    document.querySelectorAll('.location-pair').forEach((pair, index) => {
        const distanceDisplay = pair.querySelector('.distance-display');
        const petrolDisplay = pair.querySelector('.petrol-amount-display');
        if (distanceDisplay) {
            distanceDisplay.textContent = index === locationInputs.length - 1 
                ? 'N/A' 
                : 'Calculating...';
        }
        if (petrolDisplay) {
            petrolDisplay.textContent = index === locationInputs.length - 1 
                ? 'N/A' 
                : 'Calculating...';
        }
    });

    // Add validation for location inputs
    const validLocations = locations.filter(loc => loc.length > 3);
    if (validLocations.length < 2) {
        console.warn('Not enough valid locations to calculate distances');
        return;
    }

    // Update the directionsService route call with better error handling
    for (let i = 0; i < validLocations.length - 1; i++) {
        try {
            const origin = validLocations[i];
            const destination = validLocations[i + 1];
            
            if (!origin || !destination) {
                throw new Error('Invalid location pair');
            }

            const result = await new Promise((resolve, reject) => {
                directionsService.route({
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.TravelMode.DRIVING,
                    region: 'MY',
                    provideRouteAlternatives: false
                }, (response, status) => {
                    if (status === 'OK') {
                        resolve(response);
                    } else {
                        reject({
                            status: status,
                            message: `Directions request failed: ${status}`,
                            origin: origin,
                            destination: destination
                        });
                    }
                });
            });

            const distance = result.routes[0].legs[0].distance.value / 1000; // Convert to km
            totalDistance += distance;

            // Store segment data
            segments.push({
                from_location: origin,
                to_location: destination,
                distance: distance,
                petrol_amount: distance * ratePerKm,
                order: i + 1
            });

            // Update display for the current pair
            const locationPairs = document.querySelectorAll('.location-pair');
            const currentPair = locationPairs[i];
            
            if (currentPair) {
                const distanceDisplay = currentPair.querySelector('.distance-display');
                const petrolDisplay = currentPair.querySelector('.petrol-amount-display');
                
                if (distanceDisplay) {
                    distanceDisplay.textContent = `${distance.toFixed(2)} km to next stop`;
                }
                if (petrolDisplay) {
                    petrolDisplay.textContent = `RM ${(distance * ratePerKm).toFixed(2)}`;
                }
            }
        } catch (error) {
            console.error('Error calculating distance:', error);
            const errorMessage = error.status === 'ZERO_RESULTS' 
                ? 'No route found between locations' 
                : 'Error calculating distance';
            
            const locationPairs = document.querySelectorAll('.location-pair');
            const currentPair = locationPairs[i];
            if (currentPair) {
                const distanceDisplay = currentPair.querySelector('.distance-display');
                const petrolDisplay = currentPair.querySelector('.petrol-amount-display');
                if (distanceDisplay) {
                    distanceDisplay.textContent = errorMessage;
                    distanceDisplay.classList.add('text-red-600');
                }
                if (petrolDisplay) {
                    petrolDisplay.textContent = 'N/A';
                }
            }
        }
    }

    // Update the last location pair to show N/A
    const lastLocationPair = document.querySelectorAll('.location-pair')[validLocations.length - 1];
    if (lastLocationPair) {
        const distanceDisplay = lastLocationPair.querySelector('.distance-display');
        const petrolDisplay = lastLocationPair.querySelector('.petrol-amount-display');
        if (distanceDisplay) {
            distanceDisplay.textContent = 'N/A';
        }
        if (petrolDisplay) {
            petrolDisplay.textContent = 'N/A';
        }
    }

    // Update totals
    const totalDistanceInput = document.getElementById('total-distance-input');
    const totalDistanceDisplay = document.getElementById('total-distance-display');
    if (totalDistanceInput) totalDistanceInput.value = totalDistance.toFixed(2);
    if (totalDistanceDisplay) totalDistanceDisplay.textContent = totalDistance.toFixed(2);
    
    const petrolAmount = totalDistance * ratePerKm;
    const petrolAmountInput = document.getElementById('petrol-amount-input');
    const petrolAmountDisplay = document.getElementById('petrol-amount-display');
    if (petrolAmountInput) petrolAmountInput.value = petrolAmount.toFixed(2);
    if (petrolAmountDisplay) petrolAmountDisplay.textContent = petrolAmount.toFixed(2);

    // Store segments data in hidden input
    const segmentsInput = document.getElementById('segments-input');
    if (segmentsInput) {
        segmentsInput.value = JSON.stringify(segments);
    }

    // Update visibility for the last location
    updateDistanceVisibility();
}

function initializeResubmitForm() {
    const form = document.getElementById('resubmit-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get rejection details from data attributes on the form
        const form = e.target;
        const requiresBasicInfo = form.dataset.requiresBasicInfo === 'true';
        const requiresTripDetails = form.dataset.requiresTripDetails === 'true';
        const requiresAccommodationDetails = form.dataset.requiresAccommodationDetails === 'true';
        const requiresDocuments = form.dataset.requiresDocuments === 'true';

        // Prepare locations and distances data
        const locationInputs = document.querySelectorAll('.location-input');
        const locations = Array.from(locationInputs).map(input => input.value.trim()).filter(Boolean);
        const distances = [];
        
        // Get distances from the display elements
        locationInputs.forEach((input, index) => {
            if (index < locationInputs.length - 1) {
                const distanceText = input.closest('.location-pair').querySelector('.distance-display')?.textContent || '';
                const distance = parseFloat(distanceText.match(/[\d.]+/) || 0);
                if (!isNaN(distance)) {
                    distances.push(distance);
                }
            }
        });

        // Create FormData object
        const formData = new FormData(form);

        // Add locations and distances as arrays
        formData.delete('locations');
        formData.delete('distances');

        // Add each location as a separate array item
        locations.forEach((location, index) => {
            formData.append(`locations[]`, location);
        });
        
        // Add each distance as a separate array item
        distances.forEach((distance, index) => {
            formData.append(`distances[]`, distance.toString());
        });

        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Confirm Resubmission',
            html: `
                <div class="text-left space-y-8">
                    <!-- Main Question -->
                    <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-blue-900">Are you sure you want to resubmit this claim?</p>
                    </div>
                    
                    <!-- Required Updates Section -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <h3 class="font-medium text-gray-900">Required Updates</h3>
                        </div>
                        <div class="space-y-2">
                            ${requiresBasicInfo ? `
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Basic Information</span>
                                </div>
                            ` : ''}
                            ${requiresTripDetails ? `
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Trip Details</span>
                                </div>
                            ` : ''}
                            ${requiresAccommodationDetails ? `
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Accommodation Details</span>
                                </div>
                            ` : ''}
                            ${requiresDocuments ? `
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Documents</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Updated Details Section -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="font-medium text-gray-900">Updated Details</h3>
                        </div>
                        <div class="space-y-3">
                            ${requiresBasicInfo ? `
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="text-sm">Company: <span class="font-medium">${document.querySelector('[name="claim_company"]')?.value || 'Not specified'}</span></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm">Date Range: <span class="font-medium">${document.querySelector('[name="date_from"]')?.value || 'Not specified'} to ${document.querySelector('[name="date_to"]')?.value || 'Not specified'}</span></span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                        </svg>
                                        <span class="text-sm">Remarks: <span class="font-medium">${document.querySelector('[name="description"]')?.value || 'Not specified'}</span></span>
                                    </div>
                                </div>
                            ` : ''}
                            ${requiresTripDetails ? `
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        <span class="text-sm">Total Distance: <span class="font-medium">${document.getElementById('total-distance-display')?.textContent || '0.00'} km</span></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm">Petrol Amount: <span class="font-medium">RM ${document.getElementById('petrol-amount-display')?.textContent || '0.00'}</span></span>
                                    </div>
                                </div>
                            ` : ''}
                            ${requiresAccommodationDetails ? `
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-sm">Accommodations: <span class="font-medium">${document.querySelectorAll('.accommodation-entry').length} entries</span></span>
                                </div>
                            ` : ''}
                            ${requiresDocuments ? `
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm">Documents have been updated</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `,
            width: '32rem',
            padding: '1.5rem',
            showCancelButton: true,
            confirmButtonText: 'Resubmit',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4F46E5',
            cancelButtonColor: '#DC2626',
            customClass: {
                container: 'resubmit-confirmation-dialog',
                popup: 'rounded-xl shadow-xl border border-gray-200',
                title: 'text-xl font-semibold text-gray-900 mb-2',
                htmlContainer: 'text-base text-gray-600',
                actions: 'space-x-3 mt-6',
                confirmButton: 'px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200',
                cancelButton: 'px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200'
            }
        });

        if (result.isConfirmed) {
            try {
                // Show loading state
                const loadingDialog = Swal.fire({
                    title: 'Resubmitting Claim',
                    html: 'Please wait while we process your resubmission...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit the form using FormData
                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                // Get the CSRF token
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Your claim has been resubmitted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#4F46E5',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'rounded-xl shadow-xl border border-gray-200',
                                title: 'text-xl font-semibold text-gray-900',
                                confirmButton: 'px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200'
                            }
                        }).then((result) => {
                            window.location.href = '/claims/dashboard';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'There was an error submitting your claim. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#4F46E5'
                        });
                    }
                };

                xhr.onerror = function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'There was an error submitting your claim. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#4F46E5'
                    });
                };

                xhr.send(formData);
            } catch (error) {
                console.error('Error submitting form:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'There was an error submitting your claim. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#4F46E5'
                });
            }
        }
    });
}

function initializeAccommodations() {
    const existingAccommodationsInput = document.getElementById('existing-accommodations');
    if (existingAccommodationsInput) {
        const accommodations = JSON.parse(existingAccommodationsInput.value || '[]');
        
        // Initialize accommodation container
        window.claimResubmit = {
            // Add a Set to track active indices
            activeIndices: new Set(),
            
            getNextIndex: function() {
                let index = 1;
                while (this.activeIndices.has(index)) {
                    index++;
                }
                return index;
            },
            
            addAccommodation: function(existingData = null) {
                const container = document.getElementById('accommodations-container');
                const index = this.getNextIndex();
                this.activeIndices.add(index);
                
                const html = `
                    <div class="accommodation-entry mb-4 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" data-index="${index}">
                        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">Accommodation Entry #${this.updateEntryNumbers()}</p>
                                <button type="button" onclick="window.claimResubmit.removeAccommodation(${index})" class="text-red-600 hover:text-red-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-4 space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                    <input type="text" 
                                        id="accommodation_location_${index}"
                                        name="accommodations[${index}][location]" 
                                        value="${existingData ? existingData.location : ''}"
                                        class="location-autocomplete mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                        placeholder="Enter or select location"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price (RM)</label>
                                    <input type="number" name="accommodations[${index}][price]" step="0.01"
                                        value="${existingData ? existingData.price : ''}"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Check-in Date</label>
                                    <input type="date" 
                                        id="accommodation_check_in_${index}"
                                        name="accommodations[${index}][check_in]"
                                        value="${existingData ? existingData.check_in : ''}"
                                        min="${document.querySelector('[name="date_from"]')?.value || ''}"
                                        max="${document.querySelector('[name="date_to"]')?.value || ''}"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Check-out Date</label>
                                    <input type="date" 
                                        id="accommodation_check_out_${index}"
                                        name="accommodations[${index}][check_out]"
                                        value="${existingData ? existingData.check_out : ''}"
                                        min="${document.querySelector('[name="date_from"]')?.value || ''}"
                                        max="${document.querySelector('[name="date_to"]')?.value || ''}"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Receipt</label>
                                <input type="file" name="accommodations[${index}][receipt]" accept=".pdf,.jpg,.jpeg,.png"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', html);
                
                // Initialize Google Maps autocomplete for the new location input
                const locationInput = document.getElementById(`accommodation_location_${index}`);
                if (locationInput) {
                    initializeAccommodationAutocomplete(locationInput);
                }
            },
            
            removeAccommodation: function(index) {
                const entry = document.querySelector(`.accommodation-entry[data-index="${index}"]`);
                if (entry) {
                    entry.remove();
                    this.activeIndices.delete(index);
                    this.updateEntryNumbers();
                }
            },
            
            updateEntryNumbers: function() {
                const entries = document.querySelectorAll('.accommodation-entry');
                entries.forEach((entry, i) => {
                    const titleElement = entry.querySelector('p');
                    if (titleElement) {
                        titleElement.textContent = `Accommodation Entry #${i + 1}`;
                    }
                });
                return entries.length + 1; // Return next number for new entries
            },
            
            removeAllAccommodations: function() {
                const container = document.getElementById('accommodations-container');
                container.innerHTML = '';
                this.activeIndices.clear();
                this.updateEntryNumbers();
                document.getElementById('remove_all_accommodations').value = '1';
            }
        };

        // Populate existing accommodations
        accommodations.forEach(accommodation => {
            window.claimResubmit.addAccommodation(accommodation);
        });
    }
}

// Add these new functions for accommodation features
function initializeAccommodationAutocomplete(input) {
    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['establishment', 'geocode'],
        fields: ['formatted_address', 'geometry', 'name'],
        componentRestrictions: { country: 'MY' }
    });

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            console.warn("No location data available for this place");
            return;
        }
        input.value = place.formatted_address;
    });
}

function validateAccommodationDates(index) {
    const checkInInput = document.getElementById(`accommodation_check_in_${index}`);
    const checkOutInput = document.getElementById(`accommodation_check_out_${index}`);
    const claimDateFrom = document.querySelector('[name="date_from"]')?.value;
    const claimDateTo = document.querySelector('[name="date_to"]')?.value;

    if (checkInInput && checkOutInput && claimDateFrom && claimDateTo) {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        const dateFrom = new Date(claimDateFrom);
        const dateTo = new Date(claimDateTo);

        // Validate check-in date
        if (checkIn < dateFrom || checkIn > dateTo) {
            checkInInput.setCustomValidity('Check-in date must be within the claim period');
        } else {
            checkInInput.setCustomValidity('');
        }

        // Validate check-out date
        if (checkOut < checkIn || checkOut > dateTo) {
            checkOutInput.setCustomValidity('Check-out date must be after check-in and within the claim period');
        } else {
            checkOutInput.setCustomValidity('');
        }

        // Show validation messages
        checkInInput.reportValidity();
        checkOutInput.reportValidity();
    }
}

function initializeToggleDetails() {
    const toggleButtons = document.querySelectorAll('[data-toggle-details]');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const details = document.getElementById('originalDetails');
            if (!details) return;

            const isHidden = details.classList.contains('hidden');
            details.classList.toggle('hidden');
            this.textContent = isHidden ? 'Hide Details' : 'Show Details';
        });
    });
}

// Export functions for global use
window.claimResubmit = {
    removeAccommodation,
    addAccommodation,
    removeAllAccommodations: () => accommodationManager.removeAllAccommodations()
};

// Add this new function for details toggle
function initializeDetailsToggle() {
    const toggleButton = document.querySelector('[data-toggle-details]');
    const detailsSection = document.getElementById('originalDetails');
    
    if (toggleButton && detailsSection) {
        toggleButton.addEventListener('click', function() {
            const isHidden = detailsSection.classList.contains('hidden');
            
            // Toggle visibility
            detailsSection.classList.toggle('hidden');
            
            // Update button text
            toggleButton.textContent = isHidden ? 'Hide Details' : 'Show Details';
        });
    }
}

function updateTotalDistance() {
    const locationPairs = document.querySelectorAll('.location-pair');
    const ratePerKm = parseFloat(document.getElementById('rate-per-km')?.value || 0.60);
    let totalDistance = 0;

    locationPairs.forEach((pair, index) => {
        if (index < locationPairs.length - 1) {
            const distanceText = pair.querySelector('.distance-display')?.textContent || '';
            const distance = parseFloat(distanceText.match(/[\d.]+/) || 0);
            totalDistance += distance;
        }
    });

    // Update total distance display
    const totalDistanceInput = document.getElementById('total-distance-input');
    const totalDistanceDisplay = document.getElementById('total-distance-display');
    if (totalDistanceInput) totalDistanceInput.value = totalDistance.toFixed(2);
    if (totalDistanceDisplay) totalDistanceDisplay.textContent = totalDistance.toFixed(2);

    // Update petrol amount
    const petrolAmount = totalDistance * ratePerKm;
    const petrolAmountInput = document.getElementById('petrol-amount-input');
    const petrolAmountDisplay = document.getElementById('petrol-amount-display');
    if (petrolAmountInput) petrolAmountInput.value = petrolAmount.toFixed(2);
    if (petrolAmountDisplay) petrolAmountDisplay.textContent = petrolAmount.toFixed(2);
}

class AccommodationManager {
    constructor() {
        this.container = document.getElementById('accommodations-container');
        this.template = this.createTemplate();
        this.counter = 0;
        
        // Initialize existing accommodations
        const existingData = document.getElementById('existing-accommodations');
        if (existingData && existingData.value) {
            const accommodations = JSON.parse(existingData.value);
            accommodations.forEach(acc => {
                this.addAccommodation(acc);
            });
        }
        
        if (this.container.children.length === 0) {
            this.addAccommodation(); // Add one empty accommodation if none exists
        }
    }

    createTemplate() {
        return `
            <div class="accommodation-entry border rounded-lg p-4 relative">
                <button type="button" class="remove-accommodation absolute top-4 right-4 text-gray-400 hover:text-red-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="accommodations[{index}][location]" required
                            class="form-input block w-full rounded-lg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Check In</label>
                        <input type="date" name="accommodations[{index}][check_in]" required
                            class="form-input block w-full rounded-lg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Check Out</label>
                        <input type="date" name="accommodations[{index}][check_out]" required
                            class="form-input block w-full rounded-lg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Price (RM)</label>
                        <input type="number" name="accommodations[{index}][price]" required
                            step="0.01" class="form-input block w-full rounded-lg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Receipt</label>
                        <input type="file" name="accommodations[{index}][receipt]"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="form-input block w-full rounded-lg">
                    </div>
                </div>
            </div>
        `;
    }

    addAccommodation(data = null) {
        const index = this.counter++;
        const newEntry = this.template.replace(/{index}/g, index);
        this.container.insertAdjacentHTML('beforeend', newEntry);
        
        // Ensure numeric indexes
        const entry = this.container.lastElementChild;
        entry.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/{index}/g, index);
        });
        
        if (data) {
            const entry = this.container.lastElementChild;
            entry.querySelector('[name$="[location]"]').value = data.location;
            entry.querySelector('[name$="[check_in]"]').value = data.check_in.split('T')[0];
            entry.querySelector('[name$="[check_out]"]').value = data.check_out.split('T')[0];
            entry.querySelector('[name$="[price]"]').value = data.price;
        }
        
        this.bindEvents();
    }

    bindEvents() {
        this.container.querySelectorAll('.remove-accommodation').forEach(button => {
            button.onclick = (e) => {
                const entry = e.target.closest('.accommodation-entry');
                if (this.container.children.length > 1) {
                    entry.remove();
                }
            };
        });
    }

    removeAllAccommodations() {
        this.container.innerHTML = '';
        this.counter = 0;
        this.entries.clear();
        document.getElementById('remove_all_accommodations').value = '1';
    }
}

// Initialize accommodation manager when the page loads
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('accommodations-container')) {
        window.claimResubmit = new AccommodationManager();
    }
});

// Add new function to initialize delete buttons
function initializeDeleteButtons() {
    const container = document.getElementById('location-inputs');
    const locationPairs = container.querySelectorAll('.location-pair');

    locationPairs.forEach((pair, index) => {
        const deleteBtn = pair.querySelector('.delete-location');
        // Only show delete button if not the first location and there are more than 2 locations
        if (index > 0) {
            if (!deleteBtn && locationPairs.length > 2) {
                // Create delete button if it doesn't exist
                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'delete-location inline-flex items-center rounded-md text-gray-400 hover:text-rose-500 focus:outline-none transition-colors';
                deleteButton.innerHTML = `
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
                const buttonContainer = pair.querySelector('.flex.items-center.justify-between');
                if (buttonContainer) {
                    buttonContainer.appendChild(deleteButton);
                }
            }
            // Add or update delete button event listener
            const currentDeleteBtn = deleteBtn || pair.querySelector('.delete-location');
            if (currentDeleteBtn) {
                currentDeleteBtn.style.display = locationPairs.length > 2 ? 'flex' : 'none';
                // Remove existing event listeners
                currentDeleteBtn.replaceWith(currentDeleteBtn.cloneNode(true));
                const newDeleteBtn = pair.querySelector('.delete-location');
                newDeleteBtn.addEventListener('click', () => {
                    if (locationPairs.length > 2) {
                        pair.remove();
                        updateStopNumbers();
                        calculateDistances();
                        updateDistanceVisibility();
                        // Reinitialize delete buttons after removal
                        initializeDeleteButtons();
                    } else {
                        Swal.fire({
                            title: 'Cannot Remove Location',
                            text: 'A minimum of 2 locations is required for the trip.',
                            icon: 'warning',
                            confirmButtonColor: '#4F46E5'
                        });
                    }
                });
            }
        }
    });
}