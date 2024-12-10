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
        const loadingState = await SwalUtils.showMapLoading(mapContainer, 'Initializing map...');
        
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
        } finally {
            await loadingState.close();
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

        const showDelete = locationInputs.length >= 2;
        const wrapper = this.locationManager.createLocationInput(
            locationInputs.length,
            '',
            showDelete
        );
        
        const container = document.getElementById('location-inputs');
        if (container) {
            container.appendChild(wrapper);
            const input = wrapper.querySelector('.location-input');
            if (input) {
                // Mark as new stop before initializing autocomplete
                input.dataset.newStop = 'true';
                this.initializeLocationAutocomplete(input);
            }
        }
    }

    initializeLocationAutocomplete(input) {
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

            const isNewStop = input.dataset.newStop === 'true';
            delete input.dataset.newStop;
            await this.updateRoute(isNewStop);
        });

        // Store the autocomplete instance on the input
        input.autocomplete = autocomplete;
    }

    async updateRoute(isNewStop = false) {
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
                const draftDataInput = document.getElementById('draftData');
                const existingData = draftDataInput ? JSON.parse(draftDataInput.value) : null;

                if (!isNewStop && existingData?.total_distance && existingData?.total_cost) {
                    const newTotalDistance = routeData.legs.reduce((sum, leg) => sum + leg.distance.value, 0) / 1000;
                    const newTotalCost = newTotalDistance * parseFloat(document.getElementById('rate-per-km')?.value || 0.60);

                    const distanceDiff = Math.abs(newTotalDistance - parseFloat(existingData.total_distance));
                    
                    if (distanceDiff > 0.1) {
                        const result = await Swal.fire({
                            title: 'Different Route Found',
                            html: `
                                <div class="space-y-4">
                                    <div class="text-left">
                                        <p class="font-medium text-gray-900">Current Route:</p>
                                        <p class="text-sm text-gray-600">Distance: ${existingData.total_distance} km</p>
                                        <p class="text-sm text-gray-600">Cost: RM ${existingData.total_cost}</p>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-medium text-gray-900">New Route:</p>
                                        <p class="text-sm text-gray-600">Distance: ${newTotalDistance.toFixed(2)} km</p>
                                        <p class="text-sm text-gray-600">Cost: RM ${newTotalCost.toFixed(2)}</p>
                                    </div>
                                </div>
                            `,
                            icon: 'info',
                            showDenyButton: true,
                            confirmButtonText: 'Use New Route',
                            denyButtonText: 'Keep Current Route',
                            customClass: {
                                popup: 'rounded-lg shadow-xl border border-gray-200',
                                title: 'text-xl font-medium text-gray-900',
                                htmlContainer: 'text-base text-gray-600'
                            }
                        });

                        if (result.isDenied) {
                            this.updateRouteDisplay({
                                routes: routeData.routes,
                                legs: existingData.segments_data ? JSON.parse(existingData.segments_data) : routeData.legs,
                                useExistingData: true,
                                existingData
                            });
                            return;
                        }
                    }
                }

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

        // Clear existing renderers
        this.directionsRenderers.forEach(renderer => renderer.setMap(null));
        this.directionsRenderers = [];

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

        // Use existing data if specified
        if (routeData.useExistingData && routeData.existingData) {
            const { total_distance, total_cost, segments_data } = routeData.existingData;
            
            this.updateDisplay('total-distance', total_distance);
            this.updateDisplay('total-cost', total_cost);
            this.updateHiddenInput('total-distance-input', total_distance);
            this.updateHiddenInput('total-cost-input', total_cost);
            
            // Parse segments data if it's a string
            let parsedSegments;
            try {
                parsedSegments = typeof segments_data === 'string' ? 
                    JSON.parse(segments_data) : segments_data;
            } catch (error) {
                console.error('Error parsing segments data:', error);
                parsedSegments = [];
            }

            // Ensure segments data is valid and has required properties
            if (Array.isArray(parsedSegments) && parsedSegments.length > 0) {
                const validSegments = parsedSegments.map(segment => ({
                    start_address: segment.from_location,
                    end_address: segment.to_location,
                    distance: { value: segment.distance * 1000 }, // Convert back to meters
                    duration: { text: segment.duration },
                    cost: segment.cost
                }));

                this.updateSegmentInfo(validSegments);
                this.updateHiddenInput('segments-data', JSON.stringify(parsedSegments));
            }
        } else {
            // Calculate totals using all legs
            const totals = this.routeCalculator.calculateTotals(routeData.legs);
            
            Object.entries(totals).forEach(([key, value]) => {
                this.updateDisplay(`total-${key}`, value);
                this.updateHiddenInput(`total-${key}-input`, value);
            });

            this.updateSegmentInfo(routeData.legs);
            
            // Save the segments data
            const segmentsData = routeData.legs.map((leg, index) => ({
                from_location: leg.start_address,
                to_location: leg.end_address,
                distance: leg.distance.value / 1000,
                duration: leg.duration.text,
                cost: (leg.distance.value / 1000) * parseFloat(document.getElementById('rate-per-km')?.value || 0.60),
                order: index + 1
            }));
            
            this.updateHiddenInput('segments-data', JSON.stringify(segmentsData));
        }

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
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 space-y-4 sm:space-y-0">
                        <div class="space-y-3 w-full sm:w-auto">
                            <div class="flex items-center space-x-3">
                                <span class="from-location-dot inline-flex items-center justify-center w-2 h-2 rounded-full" style="background-color: ${color}"></span>
                                <span class="text-xs sm:text-sm text-gray-700 truncate max-w-[200px] sm:max-w-none">${leg.start_address}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="to-location-dot inline-flex items-center justify-center w-2 h-2 rounded-full" style="background-color: ${this.locationManager.routeColors[(index + 1) % this.locationManager.routeColors.length]}"></span>
                                <span class="text-xs sm:text-sm text-gray-700 truncate max-w-[200px] sm:max-w-none">${leg.end_address}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 w-full sm:w-auto">
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
    
    async compareRouteData(newRouteData, existingData) {
        if (!newRouteData || !existingData) return false;
    
        const newTotalDistance = newRouteData.legs.reduce((sum, leg) => sum + leg.distance.value, 0) / 1000;
        const existingTotalDistance = parseFloat(existingData.total_distance || 0);
    
        // Check if the difference is more than 0.1 km
        return Math.abs(newTotalDistance - existingTotalDistance) > 0.1;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement && !window.claimMap) {
        window.claimMap = new ClaimMap();
        window.claimMap.init();
    }
});
