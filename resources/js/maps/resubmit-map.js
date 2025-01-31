import { ClaimMap } from './claim-map.js';
import ErrorHandler from '../utils/error-handler';
import { SwalUtils } from '../utils/swal-utils';

export class ResubmitMap extends ClaimMap {
    constructor(options = {}) {
        super({ ...options, editable: true });
        this.initialized = false;
        this.locationManager.setDeleteCallback((wrapper) => this.removeLocation(wrapper));
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

    async init() {
        if (this.initialized) return;

        const mapContainer = document.getElementById('map');

        try {
            await this.initialize();
            this.setupEventListeners();
            await this.loadLocations();
            this.initialized = true;
        } catch (error) {
            console.error('Error initializing resubmit map:', error);
            await SwalUtils.showError('Failed to initialize map', mapContainer);
        }
    }

    async loadLocations() {
        return await ErrorHandler.handle(async () => {
            if (!window.existingLocations || !window.existingLocations.length) return;

            const locations = window.existingLocations;
            console.log('Loading locations into map:', locations);

            // Clear existing markers and route
            this.clearMarkers();
            if (this.directionsRenderer) {
                this.directionsRenderer.setMap(null);
            }

            // Create location inputs
            const container = document.getElementById('location-inputs');
            if (container) {
                container.innerHTML = '';
                
                locations.forEach((location, index) => {
                    const showDelete = index >= 2;
                    const wrapper = this.locationManager.createLocationInput(
                        index,
                        location.location,
                        showDelete
                    );
                    container.appendChild(wrapper);
                    
                    const input = wrapper.querySelector('.location-input');
                    if (input) {
                        input.value = location.location;
                        input.dataset.locationId = location.id;
                        this.initializeLocationAutocomplete(input);
                    }
                });

                // Initialize sortable
                this.initializeSortable(container);
            }

            // Get all location addresses for route calculation
            const locationAddresses = locations.map(loc => loc.location);

            // Calculate route between all locations
            if (locationAddresses.length >= 2) {
                try {
                    const routeData = await this.calculateRoute(locationAddresses);
                    if (routeData) {
                        this.updateRouteDisplay(routeData);
                        this.updateSegmentInfo(routeData.legs);
                        
                        // Update the bounds to fit all markers
                        const bounds = new google.maps.LatLngBounds();
                        this.markers.forEach(marker => bounds.extend(marker.getPosition()));
                        this.map.fitBounds(bounds);
                    }
                } catch (error) {
                    console.error('Error calculating route:', error);
                }
            }

            // Update the hidden input with the current locations
            document.getElementById('locations').value = JSON.stringify(locations);
        }, 'loading locations into map');
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
                const result = await new Promise((resolve, reject) => {
                    this.directionsService.route({
                        origin: locations[i],
                        destination: locations[i + 1],
                        travelMode: google.maps.TravelMode.DRIVING,
                        region: 'MY'
                    }, (response, status) => {
                        if (status === 'OK') {
                            resolve(response);
                        } else {
                            reject(new Error(`Directions request failed: ${status}`));
                        }
                    });
                });

                routes.push(result);
                if (result.routes[0] && result.routes[0].legs[0]) {
                    legs.push(result.routes[0].legs[0]);
                }
            }

            return { routes, legs };
        } catch (error) {
            console.error('Error calculating route:', error);
            throw error;
        }
    }

    setupEventListeners() {
        super.setupEventListeners();

        // Add location button
        const addLocationBtn = document.getElementById('add-location-btn');
        if (addLocationBtn) {
            addLocationBtn.addEventListener('click', () => {
                this.locationManager.addLocation();
            });
        }
    }

    async geocodeAddress(address) {
        return new Promise((resolve, reject) => {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address, region: 'MY' }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    resolve(results[0]);
                } else {
                    reject(new Error(`Geocoding failed for address: ${address}`));
                }
            });
        });
    }
}

// Initialize when document is ready
document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        window.resubmitMap = new ResubmitMap();
        window.resubmitMap.init();
    }
});
