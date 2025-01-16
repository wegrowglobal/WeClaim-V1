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
        const loadingState = await SwalUtils.showMapLoading(mapContainer, 'Loading saved route...');

        try {
            await this.initialize();
            this.setupEventListeners();
            await this.loadLocations(window.existingLocations);
            this.initialized = true;
        } catch (error) {
            console.error('Error initializing resubmit map:', error);
            await SwalUtils.showError('Failed to initialize map', mapContainer);
        } finally {
            await loadingState.close();
        }
    }

    async loadLocations(locations) {
        if (!locations || !locations.length) return;

        return await ErrorHandler.handle(async () => {
            const container = document.getElementById('location-inputs');
            if (!container) return;

            container.innerHTML = '';
            this.clearMarkers();

            // Create inputs for each location
            locations.forEach((location, index) => {
                const showDelete = index >= 2;
                const wrapper = this.locationManager.createLocationInput(
                    index,
                    location.address,
                    showDelete
                );
                container.appendChild(wrapper);
                
                const input = wrapper.querySelector('.location-input');
                if (input) {
                    input.value = location.address;
                    // Store the coordinates for this location
                    input.dataset.latitude = location.lat;
                    input.dataset.longitude = location.lng;
                    this.initializeLocationAutocomplete(input);
                    
                    // Add marker if coordinates exist
                    if (location.lat && location.lng) {
                        this.addMarker({
                            lat: parseFloat(location.lat),
                            lng: parseFloat(location.lng)
                        });
                    }
                }
            });

            this.initializeSortable(container);
            await this.updateRoute();
        }, 'loading locations');
    }

    // Override the geocodeLocation method to use stored coordinates when available
    async geocodeLocation(address, input) {
        if (input.dataset.latitude && input.dataset.longitude) {
            return {
                lat: parseFloat(input.dataset.latitude),
                lng: parseFloat(input.dataset.longitude)
            };
        }
        return await super.geocodeLocation(address);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        window.resubmitMap = new ResubmitMap();
        window.resubmitMap.init();
    }
});
