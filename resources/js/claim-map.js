import { MAP_CONFIG, MARKER_ICONS, RATE_PER_KM } from './config.js';

export class LocationManager {
    constructor() {
        this.map = null;
        this.markers = [];
        this.directionsService = null;
        this.directionsRenderer = null;
        this.locationCount = 1;
        this.MAX_WAYPOINTS = 10;
        this.initialized = false;
        this.boundAddLocation = this.addLocationInput.bind(this);
        this.totalDistance = 0;
        this.totalDuration = '0 min';
        this.totalCost = 0;
        this.geocoder = null;
        this.nextButton = null;
        this.loadingOverlay = null;
        this.pendingRequests = 0;

        // Store marker icons configuration
        this.markerIcons = {
            START: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: '#4285F4',
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: '#FFFFFF',
                scale: 8
            },
            END: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: '#DB4437',
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: '#FFFFFF',
                scale: 8
            },
            WAYPOINT: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: '#F4B400',
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: '#FFFFFF',
                scale: 8
            }
        };

        // Add this color mapping with basic Tailwind colors suitable for white text
        this.letterColors = {
            'A': 'blue',
            'B': 'red',
            'C': 'green', 
            'D': 'yellow',
            'E': 'blue',
            'F': 'red',
            'G': 'green',
            'H': 'yellow',
            'I': 'blue',
            'J': 'red',
            'K': 'green',
            'L': 'yellow',
            'M': 'blue',
            'N': 'red',
            'O': 'green',
            'P': 'yellow',
            'Q': 'blue',
            'R': 'red',
            'S': 'green',
            'T': 'yellow',
            'U': 'blue',
            'V': 'red',
            'W': 'green',
            'X': 'yellow',
            'Y': 'blue',
            'Z': 'red'
        };

        this.routeColors = [
            '#3B82F6', // blue-500
            '#EF4444', // red-500 
            '#22C55E', // green-500
            '#EAB308', // yellow-500
            '#3B82F6', // blue-500
            '#EF4444', // red-500
            '#22C55E', // green-500
            '#EAB308', // yellow-500
            '#3B82F6', // blue-500
            '#EF4444', // red-500
            '#22C55E', // green-500
            '#EAB308', // yellow-500
            '#3B82F6', // blue-500
            '#EF4444', // red-500
            '#22C55E', // green-500
            '#EAB308', // yellow-500
            '#3B82F6', // blue-500
            '#EF4444', // red-500
            '#22C55E', // green-500
            '#EAB308', // yellow-500
            '#3B82F6', // blue-500
            '#EF4444', // red-500
            '#22C55E', // green-500
            '#EAB308', // yellow-500
            '#3B82F6', // blue-500
            '#EF4444'  // red-500
        ];

        // Initialize markers array
        this.markers = [];
        this.directionsRenderer = null;

        // Define a minimal marker style with dark gray background
        this.markerStyle = {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#374151', // Gray-700 for a neutral dark background
            fillOpacity: 0.95,
            strokeWeight: 1,
            strokeColor: '#FFFFFF',
            scale: 14 // Slightly larger to accommodate letter
        };

        // Add location button
        const addLocationBtn = document.getElementById('add-location-btn');
        if (addLocationBtn) {
            addLocationBtn.addEventListener('click', () => this.addLocationInput());
        }

        // Initialize first location input
        this.addLocationInput();
    }

    async initialize() {
        try {
            this.loadingOverlay = document.getElementById('map-loading-overlay');

            const mapElement = document.getElementById('map');
            if (!mapElement) {
                console.error('Map element not found');
                return;
            }

            // Initialize Google Maps services
            this.map = new google.maps.Map(mapElement, {
                center: { lat: 3.140853, lng: 101.693207 },
                zoom: 12,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true
            });

            this.directionsService = new google.maps.DirectionsService();
            this.directionsRenderer = new google.maps.DirectionsRenderer({
                map: this.map,
                suppressMarkers: true // We'll handle markers manually
            });
            this.geocoder = new google.maps.Geocoder();

            this.initializeEventListeners();
            
            // Add initial location input if none exists
            const locationInputs = document.querySelectorAll('.location-input');
            if (locationInputs.length === 0) {
                this.addLocationInput();
            }

            // Load saved data after a short delay
            setTimeout(() => this.loadSavedData(), 500);
            
            this.initialized = true;
        } catch (error) {
            console.error('Error initializing LocationManager:', error);
        }
    }

    async getLocationLatLng(address) {
        if (!address) return null;
        
        try {
            this.showLoading();
            const result = await new Promise((resolve, reject) => {
                this.geocoder.geocode({ address }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        resolve(results[0].geometry.location);
                    } else if (status === 'ZERO_RESULTS') {
                        resolve(null); // Don't throw error, just return null
                    } else {
                        reject(new Error(`Geocoding failed: ${status}`));
                    }
                });
            });
            return result;
        } catch (error) {
            console.warn(`Warning: Could not geocode "${address}"`, error);
            return null;
        } finally {
            this.hideLoading();
        }
    }

    async updateMarkers(locations) {
        this.clearMarkers();
        
        for (let i = 0; i < locations.length; i++) {
            const coords = await this.getLocationLatLng(locations[i]);
            if (coords) {
                const color = this.routeColors[i % this.routeColors.length];
                const marker = new google.maps.Marker({
                    position: coords,
                    map: this.map,
                    label: {
                        text: String.fromCharCode(65 + i),
                        color: '#FFFFFF',
                        fontSize: '11px',
                        fontWeight: '500'
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: color,
                        fillOpacity: 1,
                        strokeWeight: 0,
                        scale: 12
                    }
                });
                this.markers.push(marker);
            }
        }
    }

    showLoading() {
        this.pendingRequests++;
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.remove('hidden');
        }
    }

    hideLoading() {
        this.pendingRequests--;
        if (this.pendingRequests <= 0) {
            this.pendingRequests = 0;
            if (this.loadingOverlay) {
                this.loadingOverlay.classList.add('hidden');
            }
        }
    }

    destroy() {
        if (this.map) {
            this.boundEventListeners.forEach((listener, element) => {
                element.removeEventListener(...listener);
            });
            
            this.markers.forEach(marker => marker.setMap(null));
            this.markers = [];
            
            if (this.directionsRenderer) {
                this.directionsRenderer.setMap(null);
            }
            
            this.map = null;
        }
    }

    initializeEventListeners() {
        // Remove any existing listeners first
        const addButton = document.getElementById('add-location-btn');
        if (addButton) {
            // Remove any existing listeners
            addButton.replaceWith(addButton.cloneNode(true));
            const newAddButton = document.getElementById('add-location-btn');
            newAddButton.addEventListener('click', this.boundAddLocation);
        }
    }

    loadSavedLocations() {
        const draftDataElement = document.getElementById('draftData');
        if (!draftDataElement) return;

        try {
            const draftData = JSON.parse(draftDataElement.value);
            let locations = [];
            
            // Handle different possible formats of saved locations
            if (draftData.locations) {
                try {
                    locations = typeof draftData.locations === 'string' 
                        ? JSON.parse(draftData.locations) 
                        : draftData.locations;
                } catch (e) {
                    console.error('Error parsing locations:', e);
                    locations = [];
                }
            }

            // Only proceed if we have valid locations
            if (Array.isArray(locations) && locations.length > 0) {
                console.log('Loading saved locations:', locations);

                // Set first location
                const firstInput = document.querySelector('.location-input');
                if (firstInput && locations[0]) {
                    firstInput.value = locations[0];
                }

                // Add additional locations
                for (let i = 1; i < locations.length; i++) {
                    if (locations[i] && locations[i].trim() !== '') {
                        this.addLocationInput();
                        const inputs = document.querySelectorAll('.location-input');
                        if (inputs[i]) {
                            inputs[i].value = locations[i];
                        }
                    }
                }

                // Update the location count
                this.locationCount = document.querySelectorAll('.location-input').length;
                
                // Update the route after a short delay
                setTimeout(() => {
                    this.updateRoute();
                    this.updateRemoveButtonState();
                }, 500);
            }
        } catch (error) {
            console.error('Error loading saved locations:', error);
        }
    }

    addLocationInput() {
        const locationInputs = document.querySelectorAll('.location-input');
        if (locationInputs.length >= this.MAX_WAYPOINTS) {
            alert('Maximum number of locations reached');
            return;
        }

        const wrapper = this.createLocationInputElement(locationInputs.length);
        document.getElementById('location-inputs').appendChild(wrapper);
        
        const newInput = wrapper.querySelector('.location-input');
        if (newInput) {
            this.initializeLocationInput(newInput);
        }

        this.updateRemoveButtonState();
    }

    createLocationInputElement(index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'location-pair relative';
        
        const letter = String.fromCharCode(65 + index);
        const letterColor = this.letterColors[letter] || '#374151'; // Fallback to gray if no color defined
        
        wrapper.innerHTML = `
            <div class="form-group flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location ${index + 1}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <div class="w-6 h-6 bg-${letterColor}-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-xs font-medium">${letter}</span>
                            </div>
                        </div>
                        <input type="text"
                               class="location-input block w-full pl-12 pr-10 py-2 rounded-md border border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Enter location"
                               required>
                    </div>
                </div>
                ${index > 0 ? `
                    <button type="button" 
                            class="delete-location-btn mt-6 p-1.5 text-gray-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-colors duration-150"
                            title="Remove location">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                ` : ''}
            </div>
        `;
    
        // Add event listener for delete button if it exists
        const deleteBtn = wrapper.querySelector('.delete-location-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.removeLocation(wrapper));
        }
    
        return wrapper;
    }

    removeLocation(wrapper) {
        wrapper.remove();
        this.updateRoute();
        this.updateNextButtonState();
        
        // Update location numbers
        const locationInputs = document.querySelectorAll('.location-pair');
        locationInputs.forEach((input, index) => {
            const label = input.querySelector('label');
            if (label) {
                label.textContent = `Location ${index + 1}`;
            }
            const letterSpan = input.querySelector('.rounded-full span');
            if (letterSpan) {
                letterSpan.textContent = String.fromCharCode(65 + index);
            }
        });
    }

    updateRemoveButtonState() {
        const removeButton = document.getElementById('remove-location-btn');
        const locationInputs = document.querySelectorAll('.location-pair');
        
        if (removeButton) {
            removeButton.disabled = locationInputs.length <= 1;
            removeButton.classList.toggle('opacity-50', locationInputs.length <= 1);
            removeButton.classList.toggle('cursor-not-allowed', locationInputs.length <= 1);
        }
    }

    saveLocations() {
        const locations = Array.from(document.querySelectorAll('.location-input'))
            .map(input => input.value)
            .filter(value => value);
        
        document.getElementById('locations').value = JSON.stringify(locations);
    }

    async updateRoute() {
        const locationInputs = document.querySelectorAll('.location-input');
        const locations = Array.from(locationInputs)
            .map(input => input.value.trim())
            .filter(value => value !== '');

        if (locations.length < 2) {
            this.clearRoute();
            return;
        }

        try {
            // Validate all locations first
            const validLocations = await Promise.all(
                locations.map(async location => {
                    const coords = await this.getLocationLatLng(location);
                    return { location, coords };
                })
            );

            // Check if any location failed to geocode
            const invalidLocations = validLocations.filter(loc => !loc.coords);
            if (invalidLocations.length > 0) {
                throw new Error(`Unable to find some locations: ${invalidLocations.map(loc => loc.location).join(', ')}`);
            }

            // Create multiple DirectionsRenderer instances for each segment
            if (this.directionsRenderers) {
                this.directionsRenderers.forEach(renderer => renderer.setMap(null));
            }
            this.directionsRenderers = [];

            // Create separate routes for each segment
            for (let i = 0; i < locations.length - 1; i++) {
                const renderer = new google.maps.DirectionsRenderer({
                    map: this.map,
                    suppressMarkers: true,
                    polylineOptions: {
                        strokeColor: this.routeColors[i % this.routeColors.length],
                        strokeWeight: 5,
                        strokeOpacity: 0.8
                    }
                });
                this.directionsRenderers.push(renderer);

                const request = {
                    origin: locations[i],
                    destination: locations[i + 1],
                    travelMode: google.maps.TravelMode.DRIVING,
                    region: 'MY'
                };

                const result = await new Promise((resolve, reject) => {
                    this.directionsService.route(request, (response, status) => {
                        if (status === 'OK') {
                            resolve(response);
                        } else {
                            reject(new Error(`Unable to calculate route segment ${i + 1}: ${status}`));
                        }
                    });
                });

                renderer.setDirections(result);
            }

            // Update markers with colors matching the routes
            await this.updateMarkers(locations);

            // Collect all route data
            const allRouteData = await this.collectRouteData(locations);
            
            // Update displays with combined data
            this.updateTotals(allRouteData);
            this.updateRouteDisplay(allRouteData);
            this.updateSegmentInfo(allRouteData);
            this.saveRouteData(allRouteData);
            this.updateNextButtonState();

        } catch (error) {
            console.error('Error updating route:', error);
            this.showError(error.message);
        }
    }

    // Add new method to collect route data
    async collectRouteData(locations) {
        const legs = [];
        let totalDistance = 0;
        let totalDuration = 0;

        for (let i = 0; i < locations.length - 1; i++) {
            const request = {
                origin: locations[i],
                destination: locations[i + 1],
                travelMode: google.maps.TravelMode.DRIVING,
                region: 'MY'
            };

            const result = await new Promise((resolve, reject) => {
                this.directionsService.route(request, (response, status) => {
                    if (status === 'OK') {
                        resolve(response);
                    } else {
                        reject(new Error(`Unable to calculate route segment ${i + 1}: ${status}`));
                    }
                });
            });

            legs.push({
                ...result.routes[0].legs[0],
                color: this.routeColors[i % this.routeColors.length]
            });

            totalDistance += result.routes[0].legs[0].distance.value / 1000; // Convert to km
            totalDuration += result.routes[0].legs[0].duration.value / 60; // Convert to minutes
        }

        return {
            routes: [{ legs }],
            totalDistance,
            totalDuration
        };
    }

    updateSegmentInfo(result) {
        const segmentDetails = document.getElementById('segment-details');
        const container = document.getElementById('location-pairs-info');
        
        if (!segmentDetails || !container || !result.routes[0].legs) {
            console.error('Required elements not found or no route legs');
            return;
        }

        // Clear existing content
        segmentDetails.innerHTML = '';
        const legs = result.routes[0].legs;
        
        // Initialize total cost
        let totalCost = 0;
        
        // Prepare segments data array
        const segmentsData = [];
        
        // Create segments for each leg
        legs.forEach((leg, index) => {
            const fromLetter = String.fromCharCode(65 + index);
            const toLetter = String.fromCharCode(65 + index + 1);
            const fromLetterColor = this.letterColors[fromLetter] || '#374151';
            const toLetterColor = this.letterColors[toLetter] || '#374151';
            const distance = (leg.distance.value / 1000).toFixed(2);
            const segmentCost = (parseFloat(distance) * 0.60).toFixed(2);
            
            // Add to total cost
            totalCost += parseFloat(segmentCost);

            // Create segment data object
            const segmentData = {
                order: index + 1,
                from_location: {
                    address: leg.start_address,
                    lat: leg.start_location.lat(),
                    lng: leg.start_location.lng(),
                    marker: fromLetter
                },
                to_location: {
                    address: leg.end_address,
                    lat: leg.end_location.lat(),
                    lng: leg.end_location.lng(),
                    marker: toLetter
                },
                distance: parseFloat(distance),
                duration: leg.duration.text,
                duration_seconds: leg.duration.value,
                cost: parseFloat(segmentCost)
            };
            
            // Add to segments array
            segmentsData.push(segmentData);

            // Create segment element with data attribute
            const segmentElement = document.createElement('div');
            segmentElement.className = 'segment-detail bg-white rounded-lg shadow-sm mb-4 overflow-hidden transition-all duration-200 hover:shadow-md border-2 border-indigo-200';
            segmentElement.setAttribute('data-segment-info', JSON.stringify(segmentData));
            
            segmentElement.innerHTML = `
                <div class="flex flex-row justify-between items-center p-4">
                    <!-- Location markers and addresses -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-${fromLetterColor}-500 text-white font-medium">
                            </span>
                            <span class="text-xs text-gray-700">${leg.start_address}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-${toLetterColor}-500 text-white font-medium">
                            </span>
                            <span class="text-xs text-gray-700">${leg.end_address}</span>
                        </div>
                    </div>

                    <!-- Route information -->
                    <div class="grid grid-cols-3 gap-4 mt-6">
                        <div class="flex items-center space-x-2">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Distance</p>
                                <p class="text-xs font-medium text-gray-900">${distance} km</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Duration</p>
                                <p class="text-xs font-medium text-gray-900">${leg.duration.text}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Cost</p>
                                <p class="text-xs font-medium text-gray-900">RM ${segmentCost}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            segmentDetails.appendChild(segmentElement);
        });

        // Store segments data in hidden input
        const segmentsInput = document.getElementById('segments-data');
        if (segmentsInput) {
            segmentsInput.value = JSON.stringify(segmentsData);
            console.log('Stored segments data:', segmentsData);
        }

        // Update total cost input
        const totalCostInput = document.getElementById('total-cost-input');
        if (totalCostInput) {
            totalCostInput.value = totalCost.toFixed(2);
        }

        // Show/hide the container
        container.style.display = legs.length > 0 ? 'block' : 'none';
    }

    updateRouteDisplay(result) {
        const distanceDisplay = document.getElementById('total-distance');
        const durationDisplay = document.getElementById('total-duration');
        const costDisplay = document.getElementById('total-cost');

        if (distanceDisplay) {
            distanceDisplay.textContent = this.totalDistance.toFixed(2);
        }
        if (durationDisplay) {
            const hours = Math.floor(this.totalDuration / 60);
            const minutes = Math.round(this.totalDuration % 60);
            durationDisplay.textContent = hours > 0 
                ? `${hours} hr ${minutes} min` 
                : `${minutes} min`;
        }
        if (costDisplay) {
            costDisplay.textContent = (this.totalDistance * 0.60).toFixed(2);
        }
    }

    saveRouteData() {
        const locations = Array.from(document.querySelectorAll('.location-input'))
            .map(input => input.value.trim())
            .filter(Boolean);
        
        const totalDistance = parseFloat(document.getElementById('total-distance')?.textContent || '0');
        const totalCost = parseFloat(document.getElementById('total-cost')?.textContent || '0');
        
        // Update hidden inputs if they exist
        const inputs = {
            'locations': JSON.stringify(locations),
            'total_distance': totalDistance,
            'total_cost': totalCost
        };

        Object.entries(inputs).forEach(([name, value]) => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input) {
                input.value = value;
            }
        });

        // Also save to session storage as backup
        sessionStorage.setItem('routeData', JSON.stringify({
            locations,
            totalDistance,
            totalCost
        }));

        return {
            locations,
            totalDistance,
            totalCost
        };
    }

    loadSavedData() {
        const draftDataElement = document.getElementById('draftData');
        if (!draftDataElement) return;

        try {
            const draftData = JSON.parse(draftDataElement.value);
            if (draftData.locations) {
                const locations = typeof draftData.locations === 'string' 
                    ? JSON.parse(draftData.locations) 
                    : draftData.locations;

                if (Array.isArray(locations) && locations.length > 0) {
                    // Clear existing inputs except the first one
                    const container = document.getElementById('location-inputs');
                    const firstInput = container.querySelector('.location-input');
                    container.innerHTML = '';
                    if (firstInput) {
                        container.appendChild(firstInput.closest('.location-pair'));
                    }

                    // Populate locations
                    locations.forEach((location, index) => {
                        if (index === 0) {
                            const firstInput = document.querySelector('.location-input');
                            if (firstInput) {
                                firstInput.value = location;
                            }
                        } else {
                            this.addLocationInput();
                            const inputs = document.querySelectorAll('.location-input');
                            const lastInput = inputs[inputs.length - 1];
                            if (lastInput) {
                                lastInput.value = location;
                            }
                        }
                    });

                    // Update route after a short delay
                    setTimeout(() => this.updateRoute(), 500);
                }
            }
        } catch (error) {
            console.error('Error loading saved locations:', error);
        }
    }

    updateDisplays() {
        const displays = {
            'total-distance': this.totalDistance.toFixed(2),
            'total-duration': this.totalDuration,
            'total-cost': this.totalCost.toFixed(2)
        };

        Object.entries(displays).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
    }

    formatDuration(minutes) {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = Math.round(minutes % 60);
        
        if (hours > 0) {
            return `${hours} ${hours === 1 ? 'hour' : 'hours'} ${remainingMinutes} ${remainingMinutes === 1 ? 'min' : 'mins'}`;
        }
        return `${remainingMinutes} ${remainingMinutes === 1 ? 'min' : 'mins'}`;
    }

    addMarker(position, label) {
        return new google.maps.Marker({
            position: position,
            map: this.map,
            icon: this.markerStyle,
            label: {
                text: label,
                color: '#FFFFFF',
                fontSize: '12px',
                fontWeight: 'bold'
            }
        });
    }

    clearMarkers() {
        this.markers.forEach(marker => marker.setMap(null));
        this.markers = [];
    }

    updateTotals(result) {
        let totalDistance = 0;
        let totalDuration = 0;
        let totalCost = 0;
        
        result.routes[0].legs.forEach(leg => {
            const legDistance = leg.distance.value / 1000; // Convert to kilometers
            totalDistance += legDistance;
            totalDuration += leg.duration.value;
            totalCost += legDistance * 0.60; // Calculate cost per segment
        });

        // Update displays
        const distanceKm = totalDistance.toFixed(2);
        document.getElementById('total-distance').textContent = distanceKm;
        document.getElementById('total-distance-input').value = distanceKm;

        document.getElementById('total-cost').textContent = totalCost.toFixed(2);
        document.getElementById('total-cost-input').value = totalCost.toFixed(2);

        const hours = Math.floor(totalDuration / 3600);
        const minutes = Math.floor((totalDuration % 3600) / 60);
        document.getElementById('total-duration').textContent = 
            `${hours}h ${minutes}m`;

        this.saveLocations();
    }

    resetDisplays() {
        document.getElementById('total-distance').textContent = '0';
        document.getElementById('total-distance-input').value = '0';
        document.getElementById('total-cost').textContent = '0.00';
        document.getElementById('total-duration').textContent = '0h 0m';
        
        // Clear the route from the map
        this.directionsRenderer.setDirections({ routes: [] });
        this.clearMarkers();
    }

    // Newly added clearRoute method
    clearRoute() {
        if (this.directionsRenderer) {
            this.directionsRenderer.setDirections({ routes: [] });
        }
        this.clearMarkers();
        
        // Reset totals
        this.totalDistance = 0;
        this.totalDuration = 0;
        this.totalCost = 0;
        
        // Update displays
        const displays = {
            'total-distance': '0.00',
            'total-duration': '0 mins',
            'total-cost': '0.00'
        };

        Object.entries(displays).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });

        // Update hidden inputs
        const inputs = {
            'total-distance-input': '0.00',
            'total-duration-input': '0 mins',
            'total-cost-input': '0.00'
        };

        Object.entries(inputs).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.value = value;
        });

        this.updateNextButtonState();
    }

    // Add this new method to validate locations
    async validateLocations(locations) {
        const geocoder = new google.maps.Geocoder();
        
        try {
            // Validate each location
            const validationPromises = locations.map(location => {
                return new Promise((resolve) => {
                    geocoder.geocode({ address: location, region: 'MY' }, (results, status) => {
                        resolve(status === 'OK');
                    });
                });
            });

            const validationResults = await Promise.all(validationPromises);
            return validationResults.every(result => result === true);
        } catch (error) {
            console.error('Error validating locations:', error);
            return false;
        }
    }

    initializeLocationInput(input) {
        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'MY' },
            fields: ['address_components', 'formatted_address', 'geometry', 'name'],
            types: ['geocode', 'establishment']
        });

        autocomplete.addListener('place_changed', async () => {
            const place = autocomplete.getPlace();
            
            if (!place.geometry) {
                // If no geometry, try geocoding the input value
                const geocodeResult = await this.getLocationLatLng(input.value);
                if (!geocodeResult) {
                    input.value = ''; // Clear invalid input
                    this.showError('Please select a valid location from the suggestions');
                    return;
                }
            }
            
            // Use formatted_address if available, otherwise use the input value
            input.value = place.formatted_address || input.value;
            
            // Update route with delay to ensure value is set
            setTimeout(() => this.updateRoute(), 100);
        });
    }

    initializeNextButtonState() {
        // Disable next button initially if less than 2 locations
        if (this.nextButton) {
            this.nextButton.disabled = true;
            this.nextButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    updateNextButtonState() {
        const nextButton = document.getElementById('next-step-button');
        if (!nextButton) return;

        const locationInputs = document.querySelectorAll('.location-input');
        const locations = Array.from(locationInputs)
            .map(input => input.value.trim())
            .filter(value => value !== '');

        // Enable the next button if:
        // 1. There are at least 2 locations
        // 2. All locations are filled (no empty values between filled values)
        // 3. Route has been calculated (totalDistance > 0)
        const totalDistance = parseFloat(document.getElementById('total-distance')?.textContent || '0');
        const allLocationsFilled = locations.length === locationInputs.length;
        const hasValidRoute = locations.length >= 2 && totalDistance > 0;

        nextButton.disabled = !(allLocationsFilled && hasValidRoute);
    }

    loadExistingLocations(locations) {
        if (typeof locations === 'string') {
            try {
                locations = JSON.parse(locations);
            } catch (error) {
                console.error('Error parsing locations:', error);
                return;
            }
        }

        if (!Array.isArray(locations)) return;

        // Remove existing locations except the first one
        const container = document.getElementById('location-inputs');
        while (container.children.length > 1) {
            container.removeChild(container.lastChild);
        }

        // Fill first location
        if (locations.length > 0) {
            const firstInput = container.querySelector('.location-input');
            if (firstInput) firstInput.value = locations[0];
        }

        // Add only non-empty additional locations
        for (let i = 1; i < locations.length; i++) {
            if (locations[i] && locations[i].trim() !== '') {
                this.addLocationInput(locations[i]);
            }
        }

        // Update the route after loading locations
        this.updateRoute();
        
        // Update button states
        this.updateNextButtonState();
        this.updateRemoveButtonState();
    }

    formatAddress(address, maxLength = 50) {
        if (!address) return '';
        return address.length > maxLength 
            ? address.substring(0, maxLength) + '...'
            : address;
    }

    removeLocation(wrapper) {
        if (document.querySelectorAll('.location-pair').length <= 2) {
            alert('Minimum two locations required');
            return;
        }

        wrapper.remove();
        this.updateRemoveButtonState();
        this.updateRoute();
        this.updateNextButtonState();
    }

    async calculateRoute(locations) {
        if (!this.directionsService || locations.length < 2) {
            return null;
        }

        try {
            const waypoints = locations.slice(1, -1).map(location => ({
                location: location,
                stopover: true
            }));

            const request = {
                origin: locations[0],
                destination: locations[locations.length - 1],
                waypoints: waypoints,
                travelMode: google.maps.TravelMode.DRIVING,
                optimizeWaypoints: false
            };

            const result = await new Promise((resolve, reject) => {
                this.directionsService.route(request, (response, status) => {
                    if (status === 'OK') {
                        resolve(response);
                    } else {
                        reject(new Error(`Directions request failed: ${status}`));
                    }
                });
            });

            // Calculate total distance and duration
            let totalDistance = 0;
            let totalDuration = 0;
            const segments = [];

            result.routes[0].legs.forEach((leg, index) => {
                totalDistance += leg.distance.value;
                totalDuration += leg.duration.value;
                segments.push({
                    from: locations[index],
                    to: locations[index + 1],
                    distance: (leg.distance.value / 1000).toFixed(2),
                    duration: leg.duration.text
                });
            });

            // Convert to kilometers and calculate cost
            this.totalDistance = totalDistance / 1000;
            this.totalDuration = this.formatDuration(totalDuration);
            this.totalCost = this.calculateCost(this.totalDistance);

            return {
                result,
                segments,
                totalDistance: this.totalDistance,
                totalDuration: this.totalDuration,
                totalCost: this.totalCost
            };

        } catch (error) {
            console.error('Error calculating route:', error);
            return null;
        }
    }

    calculateCost(distance) {
        const ratePerKm = 0.60; // Corrected rate per kilometer
        return Math.round((distance * ratePerKm) * 100) / 100; // Round to 2 decimal places
    }

    updateRouteDisplay(routeData) {
        if (!routeData || !routeData.result) return;

        // Display the route on the map
        this.directionsRenderer.setDirections(routeData.result);

        // Update distance, duration, and cost displays
        const distanceDisplay = document.getElementById('total-distance');
        const durationDisplay = document.getElementById('total-duration');
        const costDisplay = document.getElementById('total-cost');
        
        if (distanceDisplay) {
            distanceDisplay.textContent = routeData.totalDistance.toFixed(2);
        }
        if (durationDisplay) {
            durationDisplay.textContent = routeData.totalDuration;
        }
        if (costDisplay) {
            costDisplay.textContent = routeData.totalCost.toFixed(2);
        }

        // Update segments display if it exists
        this.updateSegmentsDisplay(routeData.segments);
    }

    // Add new method to show errors
    showError(message) {
        // Create or update error message element
        let errorElement = document.getElementById('map-error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = 'map-error-message';
            errorElement.className = 'bg-red-50 text-red-600 p-3 rounded-lg mt-2 text-sm';
            document.getElementById('map').parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');

        // Hide error after 5 seconds
        setTimeout(() => {
            errorElement.classList.add('hidden');
        }, 5000);
    }

    updateRouteInfo() {
        // Get all segment costs and sum them up
        const totalCost = this.getSegmentCosts().reduce((sum, cost) => sum + cost, 0);
        
        // Update the total cost display
        const costDisplays = document.querySelectorAll('[data-cost-display]');
        costDisplays.forEach(element => {
            element.textContent = totalCost.toFixed(2);
        });

        // Update hidden input
        const totalCostInput = document.getElementById('total-cost-input');
        if (totalCostInput) {
            totalCostInput.value = totalCost.toFixed(2);
        }
    }

    getSegmentCosts() {
        const costs = [];
        const segments = document.querySelectorAll('[data-segment-cost]');
        segments.forEach(segment => {
            const cost = parseFloat(segment.getAttribute('data-segment-cost') || '0');
            if (!isNaN(cost)) {
                costs.push(cost);
            }
        });
        return costs;
    }

}

// Initialize LocationManager when needed
if (document.querySelector('#map')) {
    window.locationManager = new LocationManager();
    window.locationManager.initialize();
}