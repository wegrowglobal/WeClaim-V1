export class LocationManager {
    constructor(maxWaypoints = 10) {
        this.MAX_WAYPOINTS = maxWaypoints;
        this.onDelete = null;
        this.routeColors = [
            '#4285F4', // Google Blue
            '#DB4437', // Google Red
            '#F4B400', // Google Yellow
            '#0F9D58', // Google Green
            '#AB47BC', // Purple
            '#00ACC1', // Cyan
            '#FF7043', // Deep Orange
            '#9E9E9E', // Grey
        ];
        this.map = null;
        this.marker = null;
    }

    reindexLocations() {
        const locationPairs = document.querySelectorAll('.location-pair');
        locationPairs.forEach((pair, index) => {
            // Update the label letter
            const label = pair.querySelector('.rounded-full');
            if (label) {
                label.textContent = String.fromCharCode(65 + index);
                label.style.backgroundColor = this.getColorForIndex(index);
            }

            // Update delete button visibility
            const deleteButton = pair.querySelector('.delete-location-btn');
            if (deleteButton) {
                deleteButton.style.display = index >= 2 ? 'flex' : 'none';
            }
        });
    }

    createLocationInput(index, value = '', showDelete = false) {
        const wrapper = document.createElement('div');
        wrapper.className = 'location-pair bg-white shadow-sm ring-1 ring-black/5 p-4 relative flex flex-col gap-3';
        
        const letter = String.fromCharCode(65 + index);
        const color = this.routeColors[index % this.routeColors.length];
        
        wrapper.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white">
                        <span class="text-sm font-medium" style="color: ${color}">${letter}</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Location ${index + 1}</h3>
                        <p class="text-xs text-gray-500">Enter the address or select from map</p>
                    </div>
                </div>
                ${showDelete ? `
                    <button type="button" 
                            class="delete-location-btn inline-flex items-center rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                ` : ''}
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 cursor-move">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 14a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
                    </svg>
                </div>
                <input type="text" 
                       class="location-input block w-full pl-10 pr-10 py-2 text-sm border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 rounded-lg transition-all" 
                       placeholder="Enter location"
                       value="${value}"
                       autocomplete="off">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>`;

        if (showDelete) {
            const deleteButton = wrapper.querySelector('.delete-location-btn');
            if (deleteButton && this.onDelete) {
                deleteButton.addEventListener('click', () => this.onDelete(wrapper));
            }
        }

        return wrapper;
    }

    validateLocations(locations) {
        return locations && locations.length >= 2 && locations.every(loc => loc.trim().length > 0);
    }

    getLocationInputs() {
        return Array.from(document.querySelectorAll('.location-input'))
            .map(input => input.value.trim())
            .filter(value => value.length > 0);
    }

    setDeleteCallback(callback) {
        this.onDelete = callback;
    }
    
    getColorForIndex(index) {
        return this.routeColors[index % this.routeColors.length];
    }

    getLabelForIndex(index) {
        return String.fromCharCode(65 + index);
    }

    initializeAutocomplete(input, options = {}) {
        if (!window.google || !window.google.maps) {
            console.warn('Google Maps not loaded');
            return;
        }

        // Create map modal if it doesn't exist
        this.ensureMapModal();

        // Configure autocomplete with more options
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['establishment', 'geocode'], // Allow both establishments and addresses
            componentRestrictions: { country: 'MY' }, // Restrict to Malaysia
            fields: ['formatted_address', 'geometry', 'name', 'place_id'],
            strictBounds: false,
            sessionToken: new google.maps.places.AutocompleteSessionToken()
        });

        // Prevent form submission on enter
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Handle place selection from autocomplete
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                if (options.onPlaceChanged) {
                    options.onPlaceChanged(place);
                }
                // Update map marker if map is open
                if (this.map && this.marker) {
                    this.marker.setPosition(place.geometry.location);
                    this.map.setCenter(place.geometry.location);
                    this.map.setZoom(17);
                }
            }
        });

        return autocomplete;
    }

    ensureMapModal() {
        if (document.getElementById('map-picker-modal')) return;

        const modalHtml = `
            <div id="map-picker-modal" class="fixed inset-0 z-[9999] hidden">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                            <div class="absolute right-0 top-0 pr-4 pt-4">
                                <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Pick Location</h3>
                                    
                                    <!-- Search input -->
                                    <div class="mt-4 mb-4">
                                        <div class="relative">
                                            <input type="text" 
                                                id="map-search-input"
                                                class="block w-full rounded-lg border border-gray-200 bg-gray-50/50 pl-3 pr-10 py-2 text-sm transition-all focus:border-gray-400 focus:bg-white"
                                                placeholder="Search for a location">
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Map -->
                                    <div class="mt-4">
                                        <div id="map-picker" class="h-[400px] w-full rounded-lg"></div>
                                    </div>

                                    <!-- Selected Location Display -->
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900">Selected Location</p>
                                                <p class="mt-1 text-sm text-gray-500 break-words" id="selected-location-display">
                                                    No location selected
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="button" class="confirm-location inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                            Confirm Location
                                        </button>
                                        <button type="button" class="cancel-picker mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    showMapPicker(input, options) {
        const modal = document.getElementById('map-picker-modal');
        const mapDiv = modal.querySelector('#map-picker');
        const searchInput = modal.querySelector('#map-search-input');
        const locationDisplay = modal.querySelector('#selected-location-display');
        modal.classList.remove('hidden');

        // Initialize map if not already done
        if (!this.map) {
            this.map = new google.maps.Map(mapDiv, {
                zoom: 15,
                center: { lat: 3.1390, lng: 101.6869 },
                mapTypeControl: false,
                fullscreenControl: false,
                streetViewControl: false,
            });

            this.marker = new google.maps.Marker({
                map: this.map,
                draggable: true,
            });

            // Add click listener to map
            this.map.addListener('click', (e) => {
                this.marker.setPosition(e.latLng);
                this.updateLocationDisplay(locationDisplay);
            });

            // Add drag end listener to marker
            this.marker.addListener('dragend', () => {
                this.updateLocationDisplay(locationDisplay);
            });
        }

        // Initialize search box with autocomplete
        const searchAutocomplete = new google.maps.places.Autocomplete(searchInput, {
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'MY' },
            fields: ['formatted_address', 'geometry', 'name'],
        });

        // Handle search input enter key
        searchInput.addEventListener('keydown', async (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const place = searchAutocomplete.getPlace();
                if (place && place.geometry) {
                    this.marker.setPosition(place.geometry.location);
                    this.map.setCenter(place.geometry.location);
                    this.map.setZoom(17);
                    this.updateLocationDisplay(locationDisplay);
                } else {
                    // If no place details available, try geocoding the input value
                    const geocoder = new google.maps.Geocoder();
                    try {
                        const { results } = await geocoder.geocode({ address: searchInput.value });
                        if (results[0]) {
                            const location = results[0].geometry.location;
                            this.marker.setPosition(location);
                            this.map.setCenter(location);
                            this.map.setZoom(17);
                            this.updateLocationDisplay(locationDisplay);
                        }
                    } catch (error) {
                        console.error('Geocoding failed:', error);
                    }
                }
            }
        });

        // Handle place selection in search
        searchAutocomplete.addListener('place_changed', () => {
            const place = searchAutocomplete.getPlace();
            if (place.geometry) {
                this.marker.setPosition(place.geometry.location);
                this.map.setCenter(place.geometry.location);
                this.map.setZoom(17);
                this.updateLocationDisplay(locationDisplay);
            }
        });

        // Update the display immediately if we have an existing value
        if (input.value) {
            searchInput.value = input.value;
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: input.value })
                .then(({ results }) => {
                    if (results[0].geometry) {
                        const location = results[0].geometry.location;
                        this.marker.setPosition(location);
                        this.map.setCenter(location);
                        this.map.setZoom(17);
                        locationDisplay.textContent = results[0].formatted_address;
                    }
                })
                .catch(error => {
                    console.error('Geocoding failed:', error);
                });
        }

        // Handle modal close
        const closeModal = () => {
            modal.classList.add('hidden');
            // Clean up event listeners
            searchInput.value = '';
            locationDisplay.textContent = 'No location selected';
        };

        // Add event listeners
        const cancelBtn = modal.querySelector('.cancel-picker');
        const closeBtn = modal.querySelector('.absolute.right-0.top-0 button');
        const confirmBtn = modal.querySelector('.confirm-location');
        const overlay = modal.querySelector('.absolute.inset-0.bg-gray-500');

        // Remove existing listeners if any
        const newCancelBtn = cancelBtn.cloneNode(true);
        const newCloseBtn = closeBtn.cloneNode(true);
        const newConfirmBtn = confirmBtn.cloneNode(true);
        const newOverlay = overlay.cloneNode(true);

        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        overlay.parentNode.replaceChild(newOverlay, overlay);

        // Add new listeners
        newCancelBtn.onclick = closeModal;
        newCloseBtn.onclick = closeModal;
        newOverlay.onclick = closeModal;

        // Handle location confirmation
        newConfirmBtn.onclick = async () => {
            const position = this.marker.getPosition();
            try {
                const address = await this.geocodePosition(position);
                input.value = address;
                if (options.onPlaceChanged) {
                    options.onPlaceChanged({
                        formatted_address: address,
                        geometry: {
                            location: position,
                        },
                    });
                }
                closeModal();
            } catch (error) {
                console.error('Failed to get address:', error);
            }
        };

        // Trigger a resize event to ensure map renders correctly
        setTimeout(() => {
            google.maps.event.trigger(this.map, 'resize');
            if (this.marker.getPosition()) {
                this.map.setCenter(this.marker.getPosition());
            }
        }, 100);
    }

    async geocodePosition(pos) {
        const geocoder = new google.maps.Geocoder();
        try {
            const { results } = await geocoder.geocode({ location: pos });
            if (results[0]) {
                return results[0].formatted_address;
            }
            throw new Error('No results found');
        } catch (error) {
            console.warn('Geocoder failed:', error);
            return `${pos.lat()}, ${pos.lng()}`;
        }
    }

    async updateLocationDisplay(displayElement) {
        const position = this.marker.getPosition();
        displayElement.textContent = 'Getting address...';
        
        try {
            const address = await this.geocodePosition(position);
            displayElement.textContent = address;
        } catch (error) {
            console.error('Failed to update location display:', error);
            displayElement.textContent = 'Failed to get address';
        }
    }
} 