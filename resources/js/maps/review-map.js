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
        console.log('Initializing review map with locations:', this.locations);
        const mapContainer = document.getElementById('map');
        
        if (!mapContainer) {
            console.error('Map container not found');
            return;
        }
        
        try {
            await this.initialize();
            
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
        
        // Filter out incomplete locations
        const validLocations = this.locations.filter(location => 
            location.from_location && location.to_location
        );

        // Create markers for each unique location
        const uniqueLocations = [];
        validLocations.forEach((location, index) => {
            // Only add from_location if it's not already added
            if (index === 0 || location.from_location !== validLocations[index - 1].to_location) {
                uniqueLocations.push({
                    address: location.from_location,
                    index: uniqueLocations.length
                });
            }
            
            // Add to_location
            uniqueLocations.push({
                address: location.to_location,
                index: uniqueLocations.length
            });
        });

        // Create geocoding promises for unique locations
        uniqueLocations.forEach(loc => {
            geocodingPromises.push(
                this.geocodeLocation(loc.address)
                    .then(position => ({
                        position,
                        index: loc.index,
                        location: loc.address
                    }))
            );
        });

        return Promise.all(geocodingPromises);
    }

    async calculateRoute() {
        const validLocations = this.locations.filter(location => 
            location.from_location && location.to_location
        );
        
        if (validLocations.length < 1) return null;

        try {
            // Clear existing renderers
            if (this.directionsRenderers) {
                this.directionsRenderers.forEach(renderer => renderer.setMap(null));
            }
            this.directionsRenderers = [];

            const routes = [];
            const legs = [];

            // Calculate route for each segment, including the last one
            for (let i = 0; i < validLocations.length; i++) {
                if (i === validLocations.length - 1 && 
                    validLocations[i].from_location === validLocations[0].from_location) {
                    continue; // Skip if last location is same as first
                }

                const response = await new Promise((resolve, reject) => {
                    this.directionsService.route({
                        origin: validLocations[i].from_location,
                        destination: validLocations[i].to_location,
                        travelMode: google.maps.TravelMode.DRIVING,
                        region: 'MY'
                    }, (result, status) => {
                        if (status === 'OK') {
                            resolve(result);
                        } else {
                            reject(new Error(`Directions request failed: ${status}`));
                        }
                    });
                });
                routes.push(response);
                legs.push(...response.routes[0].legs);
            }

            // Calculate totals from all legs
            const totals = this.routeCalculator.calculateTotals(legs);
            this.updateAllMetrics(legs, totals);

            return routes;
        } catch (error) {
            console.error('Error calculating route:', error);
            return null;
        }
    }

    updateAllMetrics(legs, totals) {
        // Update segment details
        const durationElements = document.querySelectorAll('.segment-detail');
        legs.forEach((leg, index) => {
            const segment = durationElements[index];
            if (segment) {
                const durationEl = segment.querySelector('[data-duration]');
                const distanceEl = segment.querySelector('[data-distance]');
                const costEl = segment.querySelector('[data-cost]');

                if (durationEl) durationEl.textContent = this.routeCalculator.formatDuration(leg.duration.value);
                if (distanceEl) distanceEl.textContent = `${(leg.distance.value / 1000).toFixed(2)} km`;
                if (costEl) costEl.textContent = `RM ${((leg.distance.value / 1000) * this.routeCalculator.ratePerKm).toFixed(2)}`;
            }
        });

        // Update totals
        const totalDurationEl = document.getElementById('total-duration');
        const totalDistanceEl = document.getElementById('total-distance');
        const totalCostEl = document.getElementById('total-cost');

        if (totalDurationEl) totalDurationEl.textContent = totals.duration;
        if (totalDistanceEl) totalDistanceEl.textContent = totals.distance;
        if (totalCostEl) totalCostEl.textContent = `RM ${totals.cost}`;
    }

    async renderMap(geocodingResults, routeData) {
        const bounds = new google.maps.LatLngBounds();
        const markerPositions = new Map();

        // Create markers with offset for overlapping positions
        geocodingResults.forEach((result, index) => {
            if (result?.position) {
                const posKey = `${result.position.lat()},${result.position.lng()}`;
                let position = result.position;

                // If position already exists, offset the marker slightly
                if (markerPositions.has(posKey)) {
                    const offset = 0.0001 * markerPositions.get(posKey);
                    position = new google.maps.LatLng(
                        result.position.lat() + offset,
                        result.position.lng() + offset
                    );
                    markerPositions.set(posKey, markerPositions.get(posKey) + 1);
                } else {
                    markerPositions.set(posKey, 1);
                }

                const label = String.fromCharCode(65 + index);
                const color = this.locationManager.routeColors[index % this.locationManager.routeColors.length];
                
                this.createMarker(position, {
                    map: this.map,
                    label: label,
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

        // Render each route segment with its corresponding color
        if (routeData && Array.isArray(routeData)) {
            routeData.forEach((route, index) => {
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
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof claimLocations !== "undefined") {
        const reviewMap = new ReviewMap(claimLocations);
        reviewMap.init();
    }
});