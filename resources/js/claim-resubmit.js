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
                    ${locationCount > 0 ? `
                        <button type="button" 
                            class="delete-location inline-flex items-center rounded-md text-gray-400 hover:text-rose-500 focus:outline-none transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    ` : ''}
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
    
    // Add delete handler
    const deleteBtn = wrapper.querySelector('.delete-location');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            wrapper.remove();
            // Recalculate distances and update stop numbers
            updateStopNumbers();
            calculateDistances();
            updateDistanceVisibility();
        });
    }

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

// Add this new function to update stop numbers
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
            if (index > 0) {
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
                    distanceDisplay.textContent = 'Add next location to calculate distance';
                }
                if (petrolDisplay) {
                    petrolDisplay.textContent = 'Add next location to calculate petrol cost';
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
                ? 'Add next location' 
                : 'Calculating...';
        }
        if (petrolDisplay) {
            petrolDisplay.textContent = index === locationInputs.length - 1 
                ? 'Add next location' 
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
    for (let i = 0; i < validLocations.length; i++) {
        try {
            const origin = validLocations[i];
            const destination = validLocations[i + 1] || validLocations[validLocations.length - 1];
            
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

        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
        `;

        try {
            const formData = new FormData(form);
            const locationInputs = document.querySelectorAll('.location-input');
            const locations = [];
            const distances = [];
            
            // Format locations and distances data
            locationInputs.forEach((input, index) => {
                locations.push(input.value.trim());
                // Always calculate distance if there's a next location
                if (index < locationInputs.length - 1) {
                    const distanceText = input.closest('.location-pair').querySelector('.distance-display')?.textContent || '';
                    const distance = parseFloat(distanceText.match(/[\d.]+/) || 0);
                    distances.push(distance);
                }
            });

            // Clear existing array values
            formData.delete('locations');
            formData.delete('distances');

            // Add locations and distances as array values
            locations.forEach((location, index) => {
                formData.append(`locations[${index}]`, location);
            });
            
            distances.forEach((distance, index) => {
                formData.append(`distances[${index}]`, distance);
            });

            const requiresTripDetails = document.querySelector('[data-requires-trip-details]') !== null;

            if (!requiresTripDetails) {
                // Add existing locations and distances from hidden inputs
                document.querySelectorAll('input[name="locations[]"]').forEach((input, index) => {
                    formData.append(`locations[${index}]`, input.value);
                });
                
                document.querySelectorAll('input[name="distances[]"]').forEach((input, index) => {
                    formData.append(`distances[${index}]`, input.value);
                });
            }
            
            const response = await axios.post(form.action, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                withCredentials: true
            });

            if (response.data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Claim has been resubmitted successfully.',
                    confirmButtonColor: '#4F46E5'
                });

                // Redirect to dashboard
                window.location.href = '/claims/dashboard';
            } else {
                throw new Error(response.data.message || 'Failed to resubmit claim');
            }
        } catch (error) {
            console.error('Resubmission error:', error);
            
            let errorMessage = 'Failed to resubmit claim. Please try again.';
            if (error.response?.data?.errors) {
                const errors = error.response.data.errors;
                errorMessage = Object.values(errors).flat().join('\n');
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                confirmButtonColor: '#4F46E5'
            });

            // Reset submit button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
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
                                        name="accommodation_location_${index}" 
                                        value="${existingData ? existingData.location : ''}"
                                        class="location-autocomplete mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                        placeholder="Enter or select location"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price (RM)</label>
                                    <input type="number" name="accommodation_price_${index}" step="0.01"
                                        value="${existingData ? existingData.price : ''}"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Check-in Date</label>
                                    <input type="date" 
                                        id="accommodation_check_in_${index}"
                                        name="accommodation_check_in_${index}"
                                        value="${existingData ? existingData.check_in : ''}"
                                        min="${document.querySelector('[name="date_from"]')?.value || ''}"
                                        max="${document.querySelector('[name="date_to"]')?.value || ''}"
                                        onchange="validateAccommodationDates(${index})"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Check-out Date</label>
                                    <input type="date" 
                                        id="accommodation_check_out_${index}"
                                        name="accommodation_check_out_${index}"
                                        value="${existingData ? existingData.check_out : ''}"
                                        min="${document.querySelector('[name="date_from"]')?.value || ''}"
                                        max="${document.querySelector('[name="date_to"]')?.value || ''}"
                                        onchange="validateAccommodationDates(${index})"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Receipt</label>
                                <input type="file" name="accommodation_receipt_${index}" accept=".pdf,.jpg,.jpeg,.png"
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
    removeAccommodation
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