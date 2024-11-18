import { BaseMap } from './base-map.js';
import { MARKER_COLORS } from '../config.js';

export class ReviewMap extends BaseMap {
    constructor(locations, options = {}) {
        super(options);
        this.locations = locations;
        this.routeColors = MARKER_COLORS;
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
                    fromDot.style.backgroundColor = MARKER_COLORS[index * 2 % MARKER_COLORS.length];
                }
                if (toDot) {
                    toDot.style.backgroundColor = MARKER_COLORS[(index * 2 + 1) % MARKER_COLORS.length];
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
            
            // Configure DirectionsRenderer with custom colors after successful initialization
            if (this.directionsRenderer) {
                this.directionsRenderer.setOptions({
                    polylineOptions: {
                        strokeColor: MARKER_COLORS[0],
                        strokeWeight: 4
                    },
                    suppressMarkers: true // We'll add our own custom markers
                });
            }
            
            // Show loading state in map container
            Swal.fire({
                title: 'Calculating Route',
                html: 'Please wait while we calculate your route...',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                target: mapContainer,
                customClass: {
                    container: 'relative flex items-center justify-center bg-gray-900/30 backdrop-blur-sm',
                    popup: 'bg-white rounded-lg shadow-lg p-4',
                    title: 'text-lg font-medium text-gray-900',
                    htmlContainer: 'text-sm text-gray-500',
                    timerProgressBar: 'bg-indigo-600'
                },
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Prepare all data before rendering
            const geocodingResults = await this.prepareLocations();
            const routeData = await this.calculateRoute();
            
            // Wait for Swal timer to complete
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            // After timer, render everything
            await this.renderMap(geocodingResults, routeData);
            
        } catch (error) {
            console.error('Error initializing review map:', error);
            
            Swal.fire({
                target: mapContainer,
                icon: 'error',
                title: 'Error',
                text: 'Failed to load locations and calculate route'
            });
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

        const origin = this.locations[0].from_location;
        const destination = this.locations[this.locations.length - 1].to_location;
        const waypoints = this.locations.slice(1, -1).map(loc => ({
            location: loc.to_location,
            stopover: true
        }));

        try {
            return await new Promise((resolve, reject) => {
                this.directionsService.route({
                    origin,
                    destination,
                    waypoints,
                    travelMode: google.maps.TravelMode.DRIVING,
                    region: 'MY'
                }, (result, status) => {
                    if (status === 'OK') resolve(result);
                    else reject(new Error(`Directions request failed: ${status}`));
                });
            });
        } catch (error) {
            console.error('Error calculating route:', error);
            return null;
        }
    }

    async renderMap(geocodingResults, routeData) {
        const bounds = new google.maps.LatLngBounds();

        // Add markers with proper colors
        geocodingResults.forEach((result, index) => {
            if (result && result.position) {
                const color = MARKER_COLORS[index % MARKER_COLORS.length];
                this.addMarker(result.position, {
                    label: {
                        text: String.fromCharCode(65 + index),
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
                bounds.extend(result.position);
            }
        });

        // Set map bounds
        if (!bounds.isEmpty()) {
            this.map.fitBounds(bounds);
        }

        // Render route with custom color
        if (routeData) {
            this.directionsRenderer.setOptions({
                polylineOptions: {
                    strokeColor: MARKER_COLORS[0],
                    strokeWeight: 4
                }
            });
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