import { BaseMap } from './base-map.js';
import { RATE_PER_KM } from '../config.js';
import ErrorHandler from '../utils/error-handler.js';
import { RateLimiter } from '../utils/rate-limiter.js';
import { SwalUtils } from '../utils/swal-utils';
import { LocationManager } from '../utils/location-manager';
import { RouteCalculator } from '../utils/route-calculator';

export class ClaimMap extends BaseMap {
    constructor(options = {}) {
        super({ ...options, editable: true });
        this.locationManager = new LocationManager(10);
        this.locationManager.setDeleteCallback((wrapper) => this.removeLocation(wrapper));
        this.routeCalculator = new RouteCalculator(RATE_PER_KM);
        this.initialized = false;
        this.rateLimiter = new RateLimiter(10, 1000);
    }

    async init() {
        if (this.initialized) {
            return;
        }

        const mapContainer = document.getElementById('map');
        
        try {
            await this.initialize();
            this.setupEventListeners();
            this.addInitialLocationInputs();
            this.initialized = true;
            
            return true;
        } catch (error) {
            console.error('Error initializing claim map:', error);
            await SwalUtils.showError('Failed to initialize map', mapContainer);
            return false;
        }
    }

    setupEventListeners() {
        const addButton = document.getElementById('add-location-btn');
        
        if (addButton) {
            addButton.onclick = (e) => {
                e.preventDefault();
                this.addLocationInput();
            };
        } else {
        }
    }

    addInitialLocationInputs() {
        const container = document.getElementById('location-inputs');
        if (container) {
            container.innerHTML = '';
            // First two inputs without delete button
            const firstWrapper = this.locationManager.createLocationInput(0, '', false);
            const secondWrapper = this.locationManager.createLocationInput(1, '', false);
            
            container.appendChild(firstWrapper);
            container.appendChild(secondWrapper);
            
            // Initialize autocomplete for both inputs
            container.querySelectorAll('.location-input').forEach(input => {
                this.initializeLocationAutocomplete(input);
            });
            
            this.initializeSortable(container);
            this.loadSavedData();
        }
    }

    initializeSortable(container) {
        new Sortable(container, {
            animation: 150,
            handle: '.location-pair',
            ghostClass: 'bg-gray-100',
            onEnd: () => {
                this.locationManager.reindexLocations();
                this.updateRoute();
            }
        });
    }

    addLocationInput() {
        const locationInputs = document.querySelectorAll('.location-pair');
        
        if (locationInputs.length >= this.locationManager.MAX_WAYPOINTS) {
            SwalUtils.showError('Maximum number of locations reached');
            return;
        }

        const showDelete = locationInputs.length >= 2; // Show delete button for 3rd input onwards
        const wrapper = this.locationManager.createLocationInput(
            locationInputs.length,
            '',
            showDelete
        );
        
        const container = document.getElementById('location-inputs');
        
        if (container) {
            container.appendChild(wrapper);
            const newInput = wrapper.querySelector('.location-input');
            if (newInput) {
                this.initializeLocationAutocomplete(newInput);
            }
        }
    }

    async initializeLocationAutocomplete(input) {
        if (!input) return;

        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'MY' },
            fields: ['formatted_address', 'geometry']
        });

        autocomplete.addListener('place_changed', async () => {
            await this.rateLimiter.acquire();
            const place = autocomplete.getPlace();
            
            if (!place.geometry) {
                this.showError('Please select a location from the dropdown');
                return;
            }

            await this.updateRoute();
        });
    }

    async updateRoute() {
        const locations = this.locationManager.getLocationInputs();

        if (!this.locationManager.validateLocations(locations)) {
            this.clearRoute();
            return;
        }

        const mapContainer = document.getElementById('map');
        const loadingState = await SwalUtils.showMapLoading(mapContainer);
        
        try {
            await this.rateLimiter.acquire();
            await this.plotLocations(locations);
            const routeData = await this.calculateRoute(locations);
            
            if (routeData) {
                this.updateRouteDisplay(routeData);
                this.updateSegmentInfo(routeData.legs);
                this.saveRouteData(routeData);
            }
        } catch (error) {
            console.error('Error updating route:', error);
            await SwalUtils.showError('Unable to calculate route', mapContainer);
        } finally {
            loadingState.close();
            this.updateNextButtonState();
        }
    }

    async plotLocations(locations) {
        this.clearMarkers();
        const bounds = new google.maps.LatLngBounds();

        for (let i = 0; i < locations.length; i++) {
            const position = await this.geocodeLocation(locations[i]);
            if (position) {
                await this.createMarker(position, {
                    map: this.map,
                    label: this.locationManager.getLabelForIndex(i),
                    color: this.locationManager.getColorForIndex(i),
                    id: `location-${i}`,
                    title: locations[i]
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
            // Clear existing renderers
            if (this.directionsRenderers) {
                this.directionsRenderers.forEach(renderer => renderer.setMap(null));
            }
            this.directionsRenderers = [];

            const routes = [];
            const legs = [];

            // Calculate route for each segment
            for (let i = 0; i < locations.length - 1; i++) {
                const response = await new Promise((resolve, reject) => {
                    this.directionsService.route({
                        origin: locations[i],
                        destination: locations[i + 1],
                        travelMode: google.maps.TravelMode.DRIVING,
                        region: 'MY'
                    }, (result, status) => {
                        if (status === 'OK') {
                            resolve(result);
                            legs.push(...result.routes[0].legs);
                        } else {
                            reject(new Error(`Directions request failed: ${status}`));
                        }
                    });
                });
                routes.push(response);
            }

            return { routes, legs };
        } catch (error) {
            console.error('Error calculating route:', error);
            return null;
        }
    }

    async updateRouteDisplay(routeData) {
        if (!routeData?.routes) return;

        // Render each route segment with its corresponding color
        routeData.routes.forEach((route, index) => {
            const renderer = new google.maps.DirectionsRenderer({
                map: this.map,
                directions: route,
                routeIndex: 0,
                suppressMarkers: true,
                polylineOptions: {
                    strokeColor: this.locationManager.routeColors[index % this.locationManager.routeColors.length],
                    strokeWeight: 4
                }
            });
            this.directionsRenderers.push(renderer);
        });

        // Calculate totals using all legs
        const totals = this.routeCalculator.calculateTotals(routeData.legs);
        
        // Update displays
        Object.entries(totals).forEach(([key, value]) => {
            this.updateDisplay(`total-${key}`, value);
            this.updateHiddenInput(`total-${key}-input`, value);
        });

        this.updateSegmentInfo(routeData.legs);
        this.updateNextButtonState();
    }

    createLocationsData(legs) {
        return Array.from(document.querySelectorAll('.location-input'))
            .map((input, index) => ({
                from_location: legs[index]?.start_address || input.value.trim(),
                to_location: legs[index]?.end_address || '',
                distance: (legs[index]?.distance.value || 0) / 1000,
                order: index + 1
            }))
            .filter(loc => loc.from_location);
    }

    updateSegmentInfo(legs) {
        const container = document.getElementById('segment-details');
        if (!container) return;

        container.innerHTML = '';
        
        legs.forEach((leg, index) => {
            const color = this.locationManager.routeColors[index % this.locationManager.routeColors.length];
            const segmentHtml = `
                <div class="segment-detail bg-white rounded-lg shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md border-2 border-indigo-200">
                    <div class="flex flex-row justify-between items-center p-4">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <span class="from-location-dot inline-flex items-center justify-center w-2 h-2 rounded-full" style="background-color: ${color}"></span>
                                <span class="text-xs text-gray-700">${leg.start_address}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="to-location-dot inline-flex items-center justify-center w-2 h-2 rounded-full" style="background-color: ${this.locationManager.routeColors[(index + 1) % this.locationManager.routeColors.length]}"></span>
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
                                    <p class="text-xs font-medium text-gray-900" data-distance>${(leg.distance.value / 1000).toFixed(2)} km</p>
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
                                    <p class="text-xs font-medium text-gray-900">RM ${((leg.distance.value / 1000 * parseFloat(document.getElementById('rate-per-km')?.value || 0.60))).toFixed(2)}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', segmentHtml);
        });

        document.getElementById('location-pairs-info').style.display = 'block';
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
            
            if (draftData.locations) {
                const locations = typeof draftData.locations === 'string' 
                    ? JSON.parse(draftData.locations) 
                    : draftData.locations;
    
                if (Array.isArray(locations) && locations.length > 0) {
                    this.loadLocations(locations);
                }
            }
        } catch (error) {

        }
    }

    async loadLocations(locations) {
        return await ErrorHandler.handle(async () => {
            const container = document.getElementById('location-inputs');
            if (!container) {
                throw new Error('Location container not found');
            }

            container.innerHTML = ''; // Clear existing inputs
            this.clearMarkers();

            // Create and populate location inputs
            locations.forEach((loc, index) => {
                const location = typeof loc === 'string' ? loc : loc.from_location;
                const showDelete = index >= 2;
                const wrapper = this.locationManager.createLocationInput(index, location, showDelete);
                container.appendChild(wrapper);
                
                const input = wrapper.querySelector('.location-input');
                if (input) {
                    this.initializeLocationAutocomplete(input);
                }
            });

            // Initialize sortable after creating inputs
            this.initializeSortable(container);

            const mapContainer = document.getElementById('map');
            const loadingState = locations.length >= 2 ? 
                await SwalUtils.showMapLoading(mapContainer) : 
                null;

            try {
                await this.updateRoute(); // This will handle plotting and route calculation
            } finally {
                if (loadingState) {
                    loadingState.close();
                }
            }
        }, 'loading locations');
    }

    removeLocation(wrapper) {
        const locationInputs = document.querySelectorAll('.location-pair');
        if (locationInputs.length <= 2) {
            SwalUtils.showError('Minimum two locations required');
            return;
        }

        wrapper.remove();
        this.locationManager.reindexLocations();
        this.updateRoute();
    }

    saveRouteData(routeData) {
        if (!routeData || !routeData.legs) return;

        const legs = routeData.legs;
        
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

    chunkArray(array, size) {
        const chunks = [];
        for (let i = 0; i < array.length; i += size) {
            chunks.push(array.slice(i, i + size));
        }
        return chunks;
    }

    showLoadingState() {
        return Swal.fire({
            title: 'Calculating Route',
            html: 'Please wait while we calculate your route...',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            target: document.getElementById('map'),
            didOpen: () => Swal.showLoading()
        });
    }

    async processLocation(location, index) {
        if (!location || typeof location !== 'string') {
            console.error('Invalid location:', location);
            return null;
        }
    
        try {
            const position = await this.geocodeLocation(location);
            if (!position) {
                console.error('Failed to geocode location:', location);
                return null;
            }
    
            await this.createMarker(position, {
                map: this.map,
                label: this.locationManager.getLabelForIndex(index),
                color: this.locationManager.getColorForIndex(index),
                id: `location-${index}`,
                title: location
            });
    
            return {
                address: location,
                position: position
            };
        } catch (error) {
            console.error('Error processing location:', error);
            return null;
        }
    }

    getValidLocationValues() {
        const locationInputs = document.querySelectorAll('.location-input');
        return Array.from(locationInputs)
            .map(input => input.value.trim())
            .filter(value => value.length > 0);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement && !window.claimMap) {
        window.claimMap = new ClaimMap();
        window.claimMap.init();
    }
});