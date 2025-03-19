import { MapUtils } from '../utils/map-utils';
import ErrorHandler from '../utils/error-handler';
import { MarkerView } from '../utils/marker-view.js';

export class BaseMap {
    constructor(options = {}) {
        this.options = {
            zoom: 12,
            center: { lat: 3.1390, lng: 101.6869 }, // KL center
            mapId: '6d6cdfc55d44d815',
            ...options
        };
        this.markers = new Map();
        this.markerView = new MarkerView();
        this.directionsService = null;
        this.directionsRenderer = null;
        this.map = null;
        this.geocoder = null;
    }

    async initialize() {
        const mapContainer = document.getElementById('map');
        if (!mapContainer) throw new Error('Map container not found');

        this.map = new google.maps.Map(mapContainer, this.options);
        this.geocoder = new google.maps.Geocoder();
        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            map: this.map,
            suppressMarkers: true
        });
    }

    async geocodeLocation(address) {
        return await ErrorHandler.handle(async () => {
            const result = await MapUtils.geocodeWithRetry(this.geocoder, address);
            return result.geometry.location;
        }, 'geocoding');
    }

    async createMarker(position, options = {}) {
        const { map = this.map, label, color = '#3B82F6' } = options;
        
        const marker = new google.maps.marker.AdvancedMarkerElement({
            map,
            position,
            content: this.markerView.createMarkerElement(label, color),
            title: options.title || '',
        });

        if (options.id) {
            this.markers.set(options.id, marker);
        }

        return marker;
    }

    clearMarkers() {
        this.markers.forEach(marker => marker.map = null);
        this.markers.clear();
    }

    clearRoute() {
        if (this.directionsRenderer) {
            this.directionsRenderer.setDirections({ routes: [] });
        }
    }
} 