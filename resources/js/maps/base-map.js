import { MAP_CONFIG, MARKER_COLORS } from '../config.js';

export class BaseMap {
    constructor(options = {}) {
        this.defaultCenter = { 
            lat: 3.140853, 
            lng: 101.693207  // Coordinates for Kuala Lumpur
        };
        this.defaultZoom = 7; // Zoom level to show more of Malaysia

        this.map = null;
        this.markers = [];
        this.directionsService = null;
        this.directionsRenderer = null;
        this.geocoder = null;
        this.infoWindows = [];
        this.geocodeCache = new Map();
        this.options = {
            elementId: 'map',
            editable: false,
            ...options
        };
    }

    async initialize() {
        try {
            const mapElement = document.getElementById(this.options.elementId);
            if (!mapElement) {
                console.error('Map element not found');
                return;
            }

            // Get coordinates from data attributes if they exist, otherwise use defaults
            const centerLat = parseFloat(mapElement.dataset.centerLat) || this.defaultCenter.lat;
            const centerLng = parseFloat(mapElement.dataset.centerLng) || this.defaultCenter.lng;
            const zoom = parseInt(mapElement.dataset.zoom) || this.defaultZoom;

            // Initialize map with Malaysia-specific settings
            this.map = new google.maps.Map(mapElement, {
                zoom: zoom,
                center: { lat: centerLat, lng: centerLng },
                disableDefaultUI: true,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                // Add restrictions for Malaysia
                restriction: {
                    latLngBounds: {
                        north: 7.363417, // Northern limit of Malaysia
                        south: -3.974267, // Southern limit
                        east: 119.267578, // Eastern limit
                        west: 99.643066  // Western limit
                    },
                    strictBounds: false
                }
            });

            // Initialize services
            this.directionsService = new google.maps.DirectionsService();
            this.directionsRenderer = new google.maps.DirectionsRenderer({
                map: this.map,
                suppressMarkers: true,
                polylineOptions: {
                    strokeColor: "#4285F4",
                    strokeWeight: 5,
                }
            });
            this.geocoder = new google.maps.Geocoder();

            return true;
        } catch (error) {
            console.error('Error initializing map:', error);
            return false;
        }
    }

    // Shared utility methods
    showLoading() {
        const loadingElement = document.getElementById('map-loading-overlay');
        if (loadingElement) {
            loadingElement.classList.remove('hidden');
        }
    }

    hideLoading() {
        const loadingElement = document.getElementById('map-loading-overlay');
        if (loadingElement) {
            loadingElement.classList.add('hidden');
        }
    }

    clearMarkers() {
        this.markers.forEach(marker => marker.setMap(null));
        this.markers = [];
    }

    async geocodeLocation(address) {
        if (this.geocodeCache.has(address)) {
            return this.geocodeCache.get(address);
        }

        try {
            const result = await new Promise((resolve, reject) => {
                this.geocoder.geocode({ address }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        resolve(results[0].geometry.location);
                    } else {
                        reject(new Error(`Geocoding failed: ${status}`));
                    }
                });
            });

            this.geocodeCache.set(address, result);
            return result;
        } catch (error) {
            console.error(`Error geocoding address: ${address}`, error);
            return null;
        }
    }

    showError(message) {
        const errorElement = document.createElement("div");
        errorElement.className = "fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50";
        errorElement.textContent = message;
        document.body.appendChild(errorElement);
        setTimeout(() => errorElement.remove(), 5000);
    }

    addMarker(position, options = {}) {
        const marker = new google.maps.Marker({
            position,
            map: this.map,
            ...options
        });
        this.markers.push(marker);
        return marker;
    }

    clearRoute() {
        if (this.directionsRenderer) {
            this.directionsRenderer.setDirections({ routes: [] });
        }
        this.clearMarkers();
    }
} 