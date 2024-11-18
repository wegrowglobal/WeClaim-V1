import { BaseMap } from './base-map.js';
import { LocationManager } from '../utils/location-manager';
import { SwalUtils } from '../utils/swal-utils';
import { RouteCalculator } from '../utils/route-calculator';

export class ReviewMap extends BaseMap {
    constructor(locations, options = {}) {
        super(options);
        this.locations = locations;
        this.locationManager = new LocationManager();
        this.routeCalculator = new RouteCalculator();
        this.updateLocationDots();
    }

    updateLocationDots() {
        // Wait for DOM to be ready
        setTimeout(() => {
            const segments = document.querySelectorAll('.segment-detail');
            segments.forEach((segment, index) => {
                const fromDot = segment.querySelector('.from-location-dot');
                const toDot = segment.querySelector('.to-location-dot');
                
                if (fromDot) {
                    fromDot.style.backgroundColor = this.locationManager.routeColors[index * 2 % this.locationManager.routeColors.length];
                }
                if (toDot) {
                    toDot.style.backgroundColor = this.locationManager.routeColors[(index * 2 + 1) % this.locationManager.routeColors.length];
                }
            });
        }, 0);
    }

    async init() {
        console.log('Initializing review map with locations:', this.locations);
        const mapContainer = document.getElementById('map');
        
        if (!mapContainer) {
            console.error('Map container not found');
            return;
        }
        
        try {
            await this.initialize();
            
            if (this.directionsRenderer) {
                this.directionsRenderer.setOptions({
                    polylineOptions: {
                        strokeColor: this.locationManager.routeColors[0],
                        strokeWeight: 4
                    },
                    suppressMarkers: true
                });
            }
            
            // Use the correct loading method
            const loadingState = await SwalUtils.showMapLoading(mapContainer, 'Loading route details...');
            
            try {
                const geocodingResults = await this.prepareLocations();
                const routeData = await this.calculateRoute();
                await this.renderMap(geocodingResults, routeData);
                loadingState.close();
            } catch (error) {
                loadingState.close();
                await SwalUtils.showError('Failed to load locations and calculate route', mapContainer);
                throw error;
            }
        } catch (error) {
            console.error('Error initializing review map:', error);
            await SwalUtils.showError('Failed to initialize map', mapContainer);
        }
    }

    async prepareLocations() {
        const geocodingPromises = [];

        this.locations.forEach((location, index) => {
            geocodingPromises.push(
                this.geocodeLocation(location.from_location)
                    .then(position => ({
                        position,
                        index,
                        isStart: true,
                        location: location.from_location
                    }))
            );

            if (index === this.locations.length - 1) {
                geocodingPromises.push(
                    this.geocodeLocation(location.to_location)
                        .then(position => ({
                            position,
                            index,
                            isStart: false,
                            location: location.to_location
                        }))
                );
            }
        });

        return Promise.all(geocodingPromises);
    }

    async calculateRoute() {
        if (this.locations.length < 1) return null;

        try {
            const response = await new Promise((resolve, reject) => {
                this.directionsService.route({
                    origin: this.locations[0].from_location,
                    destination: this.locations[this.locations.length - 1].to_location,
                    waypoints: this.locations.slice(1, -1).map(loc => ({
                        location: loc.to_location,
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

    async renderMap(geocodingResults, routeData) {
        const bounds = new google.maps.LatLngBounds();

        geocodingResults.forEach((result, index) => {
            if (result?.position) {
                const color = this.locationManager.routeColors[index % this.locationManager.routeColors.length];
                this.createMarker(result.position, {
                    map: this.map,
                    label: String.fromCharCode(65 + index),
                    color: color,
                    id: `location-${index}`,
                    title: result.location
                });
                bounds.extend(result.position);
            }
        });

        if (!bounds.isEmpty()) {
            this.map.fitBounds(bounds);
        }

        if (routeData) {
            this.directionsRenderer.setDirections(routeData);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof claimLocations !== "undefined") {
        const reviewMap = new ReviewMap(claimLocations);
        reviewMap.init();
    }
});