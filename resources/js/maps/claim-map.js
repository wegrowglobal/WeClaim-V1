import { BaseMap } from './base-map.js';
import { RATE_PER_KM } from '../config.js';

export class ClaimMap extends BaseMap {
    constructor(options = {}) {
        super({ ...options, editable: true });
        this.locationCount = 1;
        this.MAX_WAYPOINTS = 10;
        this.pendingRequests = 0;
        this.routeColors = [
            '#3B82F6', '#EF4444', '#22C55E', '#EAB308',
            '#3B82F6', '#EF4444', '#22C55E', '#EAB308',
            '#3B82F6', '#EF4444'
        ];
        this.initialized = false;
    }

    async init() {
        if (this.initialized) {
            console.log('Already initialized, skipping...');
            return;
        }

        try {
            await this.initialize();
            this.setupEventListeners();
            this.addInitialLocationInputs();
            this.initialized = true;
            console.log('ClaimMap initialized');
            
            return true;
        } catch (error) {
            console.error('Error initializing claim map:', error);
            this.showError('Failed to initialize map');
            return false;
        }
    }

    setupEventListeners() {
        console.log('Setting up event listeners');
        const addButton = document.getElementById('add-location-btn');
        
        if (addButton) {
            addButton.onclick = (e) => {
                e.preventDefault();
                this.addLocationInput();
            };
            console.log('Add button listener attached');
        } else {
            console.warn('Add location button not found');
        }
    }

    addInitialLocationInputs() {
        const container = document.getElementById('location-inputs');
        if (container) {
            container.innerHTML = '';
            this.addLocationInput();
            this.addLocationInput();
            this.loadSavedData(); // Add this line
        }
    }

    addLocationInput() {
        console.log('addLocationInput called');
        const locationInputs = document.querySelectorAll('.location-pair');
        console.log('Current location inputs:', locationInputs.length);

        if (locationInputs.length >= this.MAX_WAYPOINTS) {
            this.showError('Maximum number of locations reached');
            return;
        }

        const wrapper = this.createLocationInputElement(locationInputs.length);
        const container = document.getElementById('location-inputs');
        
        if (container) {
            container.appendChild(wrapper);
            const newInput = wrapper.querySelector('.location-input');
            if (newInput) {
                this.initializeLocationAutocomplete(newInput);
            }
            this.updateRemoveButtonState();
            console.log('Location input added. New total:', document.querySelectorAll('.location-pair').length);
        } else {
            console.error('Location inputs container not found');
        }
    }

    createLocationInputElement(index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'location-pair relative';
        
        const letter = String.fromCharCode(65 + index);
        const color = this.routeColors[index % this.routeColors.length];
        
        wrapper.innerHTML = `
            <div class="form-group flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location ${index + 1}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 style="background-color: ${color}">
                                <span class="text-white text-xs font-medium">${letter}</span>
                            </div>
                        </div>
                        <input type="text"
                               class="location-input block w-full pl-12 pr-10 py-2 rounded-md border border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Enter location"
                               required>
                    </div>
                </div>
                ${index > 0 ? this.createDeleteButton() : ''}
            </div>
        `;

        const deleteBtn = wrapper.querySelector('.delete-location-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.removeLocation(wrapper));
        }

        return wrapper;
    }

    createDeleteButton() {
        return `
            <button type="button" 
                    class="delete-location-btn mt-6 p-1.5 text-gray-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-colors duration-150"
                    title="Remove location">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        `;
    }

    initializeLocationAutocomplete(input) {
        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'MY' },
            fields: ['address_components', 'formatted_address', 'geometry', 'name'],
            types: ['geocode', 'establishment']
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                input.value = '';
                this.showError('Please select a valid location from the suggestions');
                return;
            }
            
            input.value = place.formatted_address;
            setTimeout(() => this.updateRoute(), 100);
        });
    }

    async updateRoute() {
        const locations = Array.from(document.querySelectorAll('.location-input'))
            .map(input => input.value.trim())
            .filter(Boolean);

        if (locations.length < 2) {
            this.clearRoute();
            return;
        }

        try {
            await this.plotLocations(locations);
            const routeData = await this.calculateRoute(locations);
            if (routeData) {
                this.updateRouteDisplay(routeData);
                this.updateSegmentInfo(routeData.routes[0]);
                this.saveRouteData(routeData);
            }
        } catch (error) {
            console.error('Error updating route:', error);
            this.showError('Unable to calculate route');
        } finally {
            this.updateNextButtonState();
        }
    }

    async plotLocations(locations) {
        this.clearMarkers();
        const bounds = new google.maps.LatLngBounds();

        for (let i = 0; i < locations.length; i++) {
            const position = await this.geocodeLocation(locations[i]);
            if (position) {
                const color = this.routeColors[i % this.routeColors.length];
                this.addMarker(position, {
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
                bounds.extend(position);
            }
        }

        if (!bounds.isEmpty()) {
            this.map.fitBounds(bounds);
        }
    }

    async calculateRoute(locations) {
        if (locations.length < 2) return null;

        try {
            const response = await new Promise((resolve, reject) => {
                this.directionsService.route({
                    origin: locations[0],
                    destination: locations[locations.length - 1],
                    waypoints: locations.slice(1, -1).map(location => ({
                        location,
                        stopover: true
                    })),
                    travelMode: google.maps.TravelMode.DRIVING,
                    region: 'MY'
                }, (result, status) => {
                    if (status === 'OK') resolve(result);
                    else reject(new Error(`Directions request failed: ${status}`));
                });
            });

            return response;
        } catch (error) {
            console.error('Error calculating route:', error);
            return null;
        }
    }

    updateRouteDisplay(routeData) {
        this.directionsRenderer.setDirections(routeData);
        
        const route = routeData.routes[0];
        let totalDistance = 0;
        let totalDuration = 0;

        route.legs.forEach(leg => {
            totalDistance += leg.distance.value;
            totalDuration += leg.duration.value;
        });

        const distanceKm = totalDistance / 1000;
        const cost = distanceKm * RATE_PER_KM;
        const hours = Math.floor(totalDuration / 3600);
        const minutes = Math.floor((totalDuration % 3600) / 60);

        this.updateDisplay('total-distance', distanceKm.toFixed(2));
        this.updateDisplay('total-duration', `${hours}h ${minutes}m`);
        this.updateDisplay('total-cost', cost.toFixed(2));

        this.updateHiddenInput('total-distance-input', distanceKm.toFixed(2));
        this.updateHiddenInput('total-cost-input', cost.toFixed(2));
    }

    updateSegmentInfo(route) {
        const container = document.getElementById('segment-details');
        if (!container) return;

        container.innerHTML = '';
        const ratePerKm = parseFloat(document.getElementById('rate-per-km')?.value || 0.60);

        route.legs.forEach((leg, index) => {
            const distance = leg.distance.value / 1000;
            const cost = distance * ratePerKm;
            container.appendChild(this.createSegmentElement(leg, index, distance, cost));
        });

        const infoContainer = document.getElementById('location-pairs-info');
        if (infoContainer) {
            infoContainer.style.display = 'block';
        }
    }

    createSegmentElement(leg, index, distance, cost) {
        const element = document.createElement('div');
        element.className = 'segment-detail bg-white rounded-lg shadow-sm mb-4 overflow-hidden transition-all duration-200 hover:shadow-md border-2 border-indigo-200';
        
        element.innerHTML = `
            <div class="flex flex-row justify-between items-center p-4">
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center justify-center w-2 h-2 rounded-full" 
                              style="background-color: ${this.routeColors[index % this.routeColors.length]}"></span>
                        <span class="text-xs text-gray-700">${leg.start_address}</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center justify-center w-2 h-2 rounded-full"
                              style="background-color: ${this.routeColors[(index + 1) % this.routeColors.length]}"></span>
                        <span class="text-xs text-gray-700">${leg.end_address}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex items-center space-x-2">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Distance</p>
                            <p class="text-xs font-medium text-gray-900">${distance.toFixed(2)} km</p>
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
                            <p class="text-xs font-medium text-gray-900">RM ${cost.toFixed(2)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return element;
    }

    updateDisplay(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) element.textContent = value;
    }

    updateHiddenInput(elementId, value) {
        const input = document.getElementById(elementId);
        if (input) input.value = value;
    }

    updateNextButtonState() {
        const nextButton = document.getElementById('next-step-button');
        if (!nextButton) return;

        const locationInputs = document.querySelectorAll('.location-input');
        const filledLocations = Array.from(locationInputs)
            .map(input => input.value.trim())
            .filter(Boolean);

        const totalDistance = parseFloat(document.getElementById('total-distance')?.textContent || '0');
        const allLocationsFilled = filledLocations.length === locationInputs.length;
        const hasValidRoute = filledLocations.length >= 2 && totalDistance > 0;

        nextButton.disabled = !(allLocationsFilled && hasValidRoute);
        nextButton.classList.toggle('opacity-50', !hasValidRoute);
        nextButton.classList.toggle('cursor-not-allowed', !hasValidRoute);
    }

    updateRemoveButtonState() {
        const locationInputs = document.querySelectorAll('.location-pair');
        const deleteButtons = document.querySelectorAll('.delete-location-btn');
        
        deleteButtons.forEach(button => {
            const disabled = locationInputs.length <= 2;
            button.disabled = disabled;
            button.classList.toggle('opacity-50', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
        });
    }

    loadSavedData() {
        const draftDataElement = document.getElementById('draftData');
        if (!draftDataElement) {
            console.warn('Draft data element not found');
            return;
        }
    
        try {
            const draftData = JSON.parse(draftDataElement.value);
            console.log('Parsed draft data:', draftData);
            
            if (draftData.locations) {
                const locations = typeof draftData.locations === 'string' 
                    ? JSON.parse(draftData.locations) 
                    : draftData.locations;
                
                console.log('Processed locations:', locations);
    
                if (Array.isArray(locations) && locations.length > 0) {
                    this.loadLocations(locations);
                } else {
                    console.warn('No valid locations found in draft data');
                }
            } else {
                console.warn('No locations found in draft data');
            }
        } catch (error) {
            console.error('Error loading saved data:', error);
        }
    }

    async loadLocations(locations) {
        console.log('Loading locations:', locations);
        const container = document.getElementById('location-inputs');
        if (!container) {
            console.error('Location container not found');
            return;
        }
        
        container.innerHTML = '';
        this.clearMarkers();
        
        // Show Swal loading in map container
        const mapContainer = document.getElementById('map');
        Swal.fire({
            title: 'Calculating Route',
            html: 'Please wait while we calculate your route...',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            target: mapContainer,
            customClass: {
                container: 'absolute inset-0 flex items-center justify-center bg-gray-900/30 backdrop-blur-sm',
                popup: 'bg-white rounded-lg shadow-lg p-4',
                title: 'text-lg font-medium text-gray-900',
                htmlContainer: 'text-sm text-gray-500',
                timerProgressBar: 'bg-indigo-600'
            },
            didOpen: () => {
                Swal.showLoading();
            }
        });
    
        // Create arrays to store promises and values
        const geocodingPromises = [];
        const locationValues = [];
    
        // Setup inputs and collect location values
        locations.forEach((location, index) => {
            this.addLocationInput();
            const inputs = document.querySelectorAll('.location-input');
            const input = inputs[inputs.length - 1];
            
            if (input) {
                let locationValue;
                if (typeof location === 'object') {
                    locationValue = location.from_location;
                    if (typeof locationValue === 'object') {
                        locationValue = locationValue.address;
                    }
                } else {
                    locationValue = location;
                }
                
                input.value = locationValue || '';
                this.initializeLocationAutocomplete(input);
                locationValues.push(locationValue);
    
                // Add geocoding promise without adding markers yet
                geocodingPromises.push(
                    new Promise((resolve) => {
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ address: locationValue }, (results, status) => {
                            if (status === 'OK' && results[0]) {
                                resolve({
                                    position: results[0].geometry.location,
                                    index: index
                                });
                            } else {
                                resolve(null);
                            }
                        });
                    })
                );
            }
        });
    
        try {
            // Wait for all geocoding results
            const positions = await Promise.all(geocodingPromises);
            
            // Wait for Swal timer to complete
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            // After timer, add markers and calculate route
            positions.forEach(result => {
                if (result) {
                    const color = this.routeColors[result.index % this.routeColors.length];
                    this.addMarker(result.position, {
                        label: {
                            text: String.fromCharCode(65 + result.index),
                            color: '#FFFFFF'
                        },
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            fillColor: color,
                            fillOpacity: 1,
                            strokeWeight: 0,
                            scale: 10
                        }
                    });
                }
            });
    
            // Calculate and display route
            const validLocationValues = locationValues.filter(Boolean);
            if (validLocationValues.length >= 2) {
                const routeData = await this.calculateRoute(validLocationValues);
                if (routeData) {
                    this.updateRouteDisplay(routeData);
                    this.saveRouteData(routeData);
                }
            }
        } catch (error) {
            console.error('Error in loadLocations:', error);
            Swal.fire({
                target: mapContainer,
                icon: 'error',
                title: 'Error',
                text: 'Failed to load locations and calculate route'
            });
        }
    }

    removeLocation(wrapper) {
        const locationInputs = document.querySelectorAll('.location-pair');
        if (locationInputs.length <= 2) {
            this.showError('Minimum two locations required');
            return;
        }

        wrapper.remove();
        this.updateRemoveButtonState();
        this.updateRoute();
    }

    saveRouteData(routeData) {
        if (!routeData || !routeData.routes || !routeData.routes[0]) return;
    
        const route = routeData.routes[0];
        const legs = route.legs;
    
        const totalDistance = legs.reduce((sum, leg) => sum + leg.distance.value, 0) / 1000;
        const totalDuration = legs.reduce((sum, leg) => sum + leg.duration.value, 0);
        const totalCost = totalDistance * parseFloat(document.getElementById('rate-per-km')?.value || 0.60);
    
        this.updateDisplay('total-distance', totalDistance.toFixed(2));
        this.updateDisplay('total-duration', this.formatDuration(totalDuration));
        this.updateDisplay('total-cost', totalCost.toFixed(2));
    
        this.updateHiddenInput('total-distance-input', totalDistance.toFixed(2));
        this.updateHiddenInput('total-duration-input', this.formatDuration(totalDuration));
        this.updateHiddenInput('total-cost-input', totalCost.toFixed(2));
    
        const locations = Array.from(document.querySelectorAll('.location-input'))
            .map((input, index) => ({
                from_location: legs[index]?.start_address || input.value.trim(),
                to_location: legs[index]?.end_address || '',
                distance: (legs[index]?.distance.value || 0) / 1000,
                order: index + 1
            }))
            .filter(loc => loc.from_location);
    
        this.updateHiddenInput('locations', JSON.stringify(locations));
    
        const segmentsData = legs.map((leg, index) => ({
            from_location: leg.start_address,
            to_location: leg.end_address,
            distance: leg.distance.value / 1000,
            duration: leg.duration.text,
            cost: (leg.distance.value / 1000) * parseFloat(document.getElementById('rate-per-km')?.value || 0.60),
            order: index + 1
        }));
        this.updateHiddenInput('segments-data', JSON.stringify(segmentsData));
    
        this.updateNextButtonState();
        this.updateSegmentInfo(route);
    }

    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }

    clearMapData() {
        // Clear the map
        if (this.directionsRenderer) {
            this.directionsRenderer.setMap(null);
        }
        
        // Clear location inputs
        const container = document.getElementById('location-inputs');
        if (container) {
            container.innerHTML = '';
        }
        
        // Reset displays
        this.updateDisplay('total-distance', '0.00');
        this.updateDisplay('total-duration', '0 min');
        this.updateDisplay('total-cost', '0.00');
        
        // Clear hidden inputs
        this.updateHiddenInput('locations', '[]');
        this.updateHiddenInput('segments-data', '[]');
        this.updateHiddenInput('total-distance-input', '0');
        this.updateHiddenInput('total-duration-input', '0');
        this.updateHiddenInput('total-cost-input', '0');
        
        // Add initial location inputs back
        this.addInitialLocationInputs();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement && !window.claimMap) {
        window.claimMap = new ClaimMap();
        window.claimMap.init();
    }
});