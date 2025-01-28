import { BaseMap } from './base-map.js';
import { LocationManager } from '../utils/location-manager';
import { SwalUtils } from '../utils/swal-utils';
import { MapUtils } from '../utils/map-utils';
import { RATE_PER_KM, formatCurrency, formatDistance } from '../utils/constants';
import Logger from '../utils/logger';
import ErrorHandler from '../utils/error-handler';

export class ReviewMap extends BaseMap {
    constructor(locations, options = {}) {
        super(options);
        this.locations = locations;
        this.locationManager = new LocationManager();
        this.updateLocationDots();
    }

    updateLocationDots() {
        setTimeout(() => {
            const segments = document.querySelectorAll('.segment-detail');
            segments.forEach((segment, index) => {
                const fromDot = segment.querySelector('.from-location-dot');
                const toDot = segment.querySelector('.to-location-dot');
                
                if (fromDot) {
                    fromDot.style.backgroundColor = this.locationManager.routeColors[index % this.locationManager.routeColors.length];
                }
                if (toDot) {
                    toDot.style.backgroundColor = this.locationManager.routeColors[(index + 1) % this.locationManager.routeColors.length];
                }
            });
        }, 0);
    }

    async init() {
        Logger.log('Initializing review map with locations:', this.locations);
        const mapContainer = document.getElementById('map');
        
        if (!mapContainer) {
            Logger.error('Map container not found');
            return;
        }

        try {
            await this.initialize();
            await this.renderStoredLocations();
            this.initialized = true;
        } catch (error) {
            Logger.error('Error initializing map:', error);
            throw error;
        }
    }

    async renderStoredLocations() {
        const bounds = new google.maps.LatLngBounds();
        const directionsService = new google.maps.DirectionsService();
        this.directionsRenderers = [];

        const geocodedLocations = [];
        const geocoder = new google.maps.Geocoder();

        // First, geocode all locations and create markers
        for (let i = 0; i < this.locations.length; i++) {
            const location = this.locations[i];
            try {
                const fromResult = await MapUtils.geocodeWithRetry(geocoder, location.from_location);
                if (fromResult?.geometry?.location) {
                    const fromPosition = fromResult.geometry.location;
                    this.createMarker(fromPosition, {
                        map: this.map,
                        label: this.locationManager.getLabelForIndex(i),
                        color: this.locationManager.getColorForIndex(i),
                        title: location.from_location
                    });
                    bounds.extend(fromPosition);
                    geocodedLocations.push(fromPosition);
                }

                if (i === this.locations.length - 1 && location.to_location) {
                    const toResult = await MapUtils.geocodeWithRetry(geocoder, location.to_location);
                    if (toResult?.geometry?.location) {
                        const toPosition = toResult.geometry.location;
                        this.createMarker(toPosition, {
                            map: this.map,
                            label: this.locationManager.getLabelForIndex(i + 1),
                            color: this.locationManager.getColorForIndex(i + 1),
                            title: location.to_location
                        });
                        bounds.extend(toPosition);
                        geocodedLocations.push(toPosition);
                    }
                }
            } catch (error) {
                Logger.error('Error geocoding location:', error);
            }
        }

        // Then render routes segment by segment with different colors
        if (geocodedLocations.length > 1) {
            const routePromises = [];
            for (let i = 0; i < geocodedLocations.length - 1; i++) {
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    map: this.map,
                    suppressMarkers: true,
                    polylineOptions: {
                        strokeColor: this.locationManager.routeColors[i % this.locationManager.routeColors.length],
                        strokeWeight: 4
                    }
                });
                this.directionsRenderers.push(directionsRenderer);

                try {
                    const result = await new Promise((resolve, reject) => {
                        directionsService.route({
                            origin: geocodedLocations[i],
                            destination: geocodedLocations[i + 1],
                            travelMode: google.maps.TravelMode.DRIVING,
                            region: 'MY'
                        }, (result, status) => {
                            if (status === 'OK') resolve(result);
                            else reject(new Error(`Directions request failed: ${status}`));
                        });
                    });

                    directionsRenderer.setDirections(result);
                } catch (error) {
                    Logger.error('Error rendering route segment:', error);
                }
            }
        }

        if (!bounds.isEmpty()) {
            this.map.fitBounds(bounds);
        }

        this.updateMetricsFromStoredData();
    }

    updateMetricsFromStoredData() {
        const segments = document.querySelectorAll('.segment-detail');
        this.locations.forEach((location, index) => {
            const segment = segments[index];
            if (segment) {
                const distanceEl = segment.querySelector('[data-distance]');
                const costEl = segment.querySelector('[data-cost]');

                if (distanceEl) distanceEl.textContent = formatDistance(location.distance);
                if (costEl) costEl.textContent = `RM ${formatCurrency(location.distance * RATE_PER_KM)}`;
            }
        });

        this.updateSummaryDisplays();
    }

    updateSummaryDisplays() {
        const totalDistance = this.locations.reduce((sum, loc) => sum + parseFloat(loc.distance), 0);
        const totalCost = totalDistance * RATE_PER_KM;

        ['mobile', 'desktop'].forEach(device => {
            const distanceEl = document.getElementById(`total-distance-${device}`);
            const costEl = document.getElementById(`total-cost-${device}`);

            if (distanceEl) distanceEl.textContent = formatDistance(totalDistance);
            if (costEl) costEl.textContent = `${formatCurrency(totalCost)}`;
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof claimLocations !== "undefined") {
        const reviewMap = new ReviewMap(claimLocations);
        reviewMap.init();
    }
});
