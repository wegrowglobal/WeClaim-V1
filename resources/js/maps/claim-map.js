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
        // Comment out the initial map loading state
        // const loadingState = await SwalUtils.showMapLoading(mapContainer, 'Initializing map...');
        let loadingState = { close: () => {} }; // Mock close method for finally block
        
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
            // Ensure close is called even if loadingState was mocked
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
        // Commented out the loading state specific to route calculation
        // const loadingState = await SwalUtils.showMapLoading(mapContainer);
        
        try {
            await this.rateLimiter.acquire();
            await this.plotLocations(locations);
            const routeData = await this.calculateRoute(locations);
            
            if (routeData) {
                // Always use the new route data
                this.updateRouteDisplay(routeData);
                this.updateSegmentInfo(routeData.legs);
                this.saveRouteData(routeData);
            }
        } catch (error) {
            console.error('Error updating route:', error);
            await SwalUtils.showError('Unable to calculate route', mapContainer);
        } finally {
            // loadingState.close(); // Also comment out the closing if loading state is removed
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
                            const leg = result.routes[0].legs[0];
                            legs.push({
                                start_address: locations[i],
                                end_address: locations[i + 1],
                                distance: leg.distance,
                                duration: leg.duration
                            });
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

    createLocationsData(legs) {
        const inputs = Array.from(document.querySelectorAll('.location-input'));
        if (inputs.length < 2) return [];

        const locations = [];

        // Process each leg to create location pairs
        legs.forEach((leg, index) => {
            if (leg.start_address && leg.end_address) {
                locations.push({
                    from_location: leg.start_address,
                    to_location: leg.end_address,
                    distance: leg.distance.value / 1000,
                    order: index + 1
                });
            }
        });

        return locations;
    }

    updateRouteDisplay(routeData) {
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

        // Calculate totals using all legs
        const totals = this.routeCalculator.calculateTotals(routeData.legs);
        
        Object.entries(totals).forEach(([key, value]) => {
            this.updateDisplay(`total-${key}`, value);
            this.updateHiddenInput(`total-${key}-input`, value);
        });

        this.updateSegmentInfo(routeData.legs);
        
        // Create and save the locations data
        const locationsData = this.createLocationsData(routeData.legs);
        this.updateHiddenInput('segments-data', JSON.stringify(locationsData));
        this.updateHiddenInput('locations', JSON.stringify(locationsData));
        this.updateNextButtonState();
    }

    updateSegmentInfo(legs) {
        const segmentDetailsContainer = document.getElementById('segment-details');
        const segmentContainer = document.getElementById('location-pairs-info');
        
        if (!segmentDetailsContainer || !segmentContainer) return;

        segmentDetailsContainer.innerHTML = ''; // Clear previous details
        let totalCost = 0;
        const ratePerKm = parseFloat(document.getElementById('rate-per-km')?.value) || RATE_PER_KM;

        if (!legs || legs.length === 0) {
            segmentDetailsContainer.innerHTML = '<p class="text-gray-500">Segment details will appear here once the route is calculated.</p>';
            segmentContainer.style.display = 'none';
            return;
        }

        legs.forEach((leg, index) => {
            const segmentIndex = index + 1;
            const startAddress = leg.start_address || 'N/A';
            const endAddress = leg.end_address || 'N/A';
            const segmentDistanceKm = leg.distance ? (leg.distance.value / 1000) : 0;
            const segmentDistanceText = leg.distance ? leg.distance.text : 'N/A';
            const segmentDurationText = leg.duration ? leg.duration.text : 'N/A';
            const segmentCost = (segmentDistanceKm * ratePerKm).toFixed(2);
            totalCost += parseFloat(segmentCost);

            // New Segment HTML Structure
            const segmentHtml = `
            <div class="segment-entry border border-gray-200 rounded-lg bg-white mb-4 overflow-hidden shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-blue-600 rounded-full text-xs font-semibold text-white">${segmentIndex}</div> 
                        <h5 class="text-sm font-semibold leading-6 text-gray-900">Route Segment ${segmentIndex}</h5>
                    </div>
                    <div class="text-right">
                         <p class="text-sm font-semibold text-gray-900">RM ${segmentCost}</p>
                         <p class="text-xs text-gray-500">${segmentDurationText}</p>
                     </div>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div class="flex items-start gap-2">
                         <div class="flex-shrink-0 mt-0.5 h-3 w-3 rounded-full bg-blue-500 ring-1 ring-blue-600/30"></div>
                         <div>
                             <p class="font-medium text-gray-600">From</p>
                             <p class="text-gray-800">${startAddress}</p>
                         </div>
                    </div>
                     <div class="flex items-start gap-2">
                         <div class="flex-shrink-0 mt-0.5 h-3 w-3 rounded-full bg-red-500 ring-1 ring-red-600/30"></div>
                        <div>
                            <p class="font-medium text-gray-600">To</p>
                            <p class="text-gray-800">${endAddress}</p>
                        </div>
                    </div>
                     <div class="flex items-start gap-2">
                         <svg class="flex-shrink-0 h-4 w-4 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" /></svg>
                         <div>
                            <p class="font-medium text-gray-600">Distance</p>
                            <p class="text-gray-800">${segmentDistanceText}</p>
                         </div>
                    </div>
                </div>
            </div>
            `;

            segmentDetailsContainer.innerHTML += segmentHtml;
        });

        segmentContainer.style.display = 'block';
        // Update total cost display if it exists in this context (it's usually in step 3)
        // this.updateDisplay('total-cost', totalCost.toFixed(2));
        // this.updateHiddenInput('total-cost-input', totalCost.toFixed(2)); 
    }

    updateDisplay(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) element.textContent = value;
    };

    updateHiddenInput(elementId, value) {
        const input = document.getElementById(elementId);
        if (input) input.value = value;
    };

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
    };

    updateRemoveButtonState() {
        const locationInputs = document.querySelectorAll('.location-pair');
        const deleteButtons = document.querySelectorAll('.delete-location-btn');
        
        deleteButtons.forEach(button => {
            const disabled = locationInputs.length <= 2;
            button.disabled = disabled;
            button.classList.toggle('opacity-50', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
        });
    };

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
            console.error('Error loading saved data:', error);
        }
    };

    async loadLocations(locations) {
        return await ErrorHandler.handle(async () => {
            const container = document.getElementById('location-inputs');
            if (!container) {
                throw new Error('Location container not found');
            }

            container.innerHTML = ''; // Clear existing inputs
            this.clearMarkers();

            // Create a set of unique locations in order
            const uniqueLocations = new Set();
            
            // Add locations from segments
            locations.forEach(loc => {
                if (typeof loc === 'string') {
                    uniqueLocations.add(loc);
                } else {
                    // Handle location objects with from_location and to_location
                    if (loc.from_location) {
                        uniqueLocations.add(loc.from_location);
                    }
                    // Add the final to_location from the last segment
                    if (loc === locations[locations.length - 1] && loc.to_location) {
                        uniqueLocations.add(loc.to_location);
                    }
                }
            });

            // Convert back to array and create inputs
            Array.from(uniqueLocations).forEach((location, index) => {
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
            // Comment out loading state during initial load of saved locations
            // const loadingState = uniqueLocations.size >= 2 ? 
            //     await SwalUtils.showMapLoading(mapContainer) : 
            //     null;
            let loadingState = null; // Keep variable defined if needed by finally block

            try {
                await this.updateRoute(); // This will handle plotting and route calculation
            } finally {
                if (loadingState) {
                    loadingState.close();
                }
            }
        }, 'loading locations');
    };

    removeLocation(wrapper) {
        const locationInputs = document.querySelectorAll('.location-pair');
        if (locationInputs.length <= 2) {
            SwalUtils.showError('Minimum two locations required');
            return;
        }

        wrapper.remove();
        this.locationManager.reindexLocations();
        this.updateRoute();
    };

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

        // Create location pairs from the legs data
        const locationsData = this.createLocationsData(legs);
        this.updateHiddenInput('segments-data', JSON.stringify(locationsData));
        this.updateHiddenInput('locations', JSON.stringify(locationsData));

        this.updateNextButtonState();
    };

    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    };

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
    };

    chunkArray(array, size) {
        const chunks = [];
        for (let i = 0; i < array.length; i += size) {
            chunks.push(array.slice(i, i + size));
        }
        return chunks;
    };

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
    };

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
    };

    getValidLocationValues() {
        const locationInputs = document.querySelectorAll('.location-input');
        return Array.from(locationInputs)
            .map(input => input.value.trim())
            .filter(value => value.length > 0);
    };
    
    async compareRouteData(newRouteData, existingData) {
        if (!newRouteData || !existingData) return false;
    
        const newTotalDistance = newRouteData.legs.reduce((sum, leg) => sum + leg.distance.value, 0) / 1000;
        const existingTotalDistance = parseFloat(existingData.total_distance || 0);
    
        return Math.abs(newTotalDistance - existingTotalDistance) > 0.1;
    };
}

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement && !window.claimMap) {
        window.claimMap = new ClaimMap();
        window.claimMap.init();
    }
});
