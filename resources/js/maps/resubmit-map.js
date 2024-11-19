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

        try {
            await this.initialize();
            this.setupEventListeners();
            await this.loadLocations(window.existingLocations);
            this.initialized = true;
        } catch (error) {
            console.error('Error initializing resubmit map:', error);
        }
    }

    async loadLocations(locations) {
        if (!locations || !locations.length) return;

        return await ErrorHandler.handle(async () => {
            const container = document.getElementById('location-inputs');
            if (!container) return;

            container.innerHTML = '';
            this.clearMarkers();

            locations.forEach((location, index) => {
                const showDelete = index >= 2;
                const wrapper = this.locationManager.createLocationInput(
                    index,
                    location.from_location,
                    showDelete
                );
                container.appendChild(wrapper);
                
                const input = wrapper.querySelector('.location-input');
                if (input) {
                    input.value = index === locations.length - 1 ? 
                        location.to_location : 
                        location.from_location;
                    this.initializeLocationAutocomplete(input);
                }
            });

            this.initializeSortable(container);
            await this.updateRoute();
        }, 'loading locations');
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        window.resubmitMap = new ResubmitMap();
        window.resubmitMap.init();
    }
});