export const MAP_CONFIG = {
    apiKey: import.meta.env.GOOGLE_MAPS_API_KEY,
    mapOptions: {
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_TOP
        },
        styles: [
            {
                "featureType": "administrative",
                "elementType": "geometry",
                "stylers": [{ "visibility": "simplified" }]
            },
            {
                "featureType": "poi",
                "stylers": [{ "visibility": "off" }]
            }
        ]
    },
    rendererOptions: {
        preserveViewport: false,
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: '#4F46E5',
            strokeWeight: 4,
            strokeOpacity: 0.8
        }
    },
    autocompleteOptions: {
        bounds: new google.maps.LatLngBounds(
            new google.maps.LatLng(0.773131, 100.085756), // SW bound
            new google.maps.LatLng(7.363417, 119.267502)  // NE bound
        ),
        componentRestrictions: { country: 'MY' },
        fields: ['address_components', 'formatted_address', 'geometry', 'name'],
        strictBounds: false,
        types: ['establishment', 'geocode']
    },
    defaultCenter: {
        lat: 3.140853,
        lng: 101.693207 // Kuala Lumpur coordinates
    },
    defaultZoom: 12
};

export const MARKER_COLORS = [
    "#4285F4", // Google Blue
    "#DB4437", // Google Red
    "#F4B400", // Google Yellow
    "#0F9D58", // Google Green
    "#AB47BC", // Purple
    "#00ACC1", // Cyan
    "#FF7043", // Deep Orange
    "#9E9E9E", // Grey
];

// Add constants for distance calculations
export const RATE_PER_KM = 0.60; // RM per kilometer
export const MIN_DISTANCE = 0; // Minimum distance in km
export const MAX_DISTANCE = 5000; // Maximum distance in km

// Add constants for map markers
export const MARKER_ICONS = {
    START: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: '#4285F4',
        fillOpacity: 1,
        strokeWeight: 2,
        strokeColor: '#FFFFFF',
        scale: 8
    },
    END: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: '#DB4437',
        fillOpacity: 1,
        strokeWeight: 2,
        strokeColor: '#FFFFFF',
        scale: 8
    },
    WAYPOINT: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: '#F4B400',
        fillOpacity: 1,
        strokeWeight: 2,
        strokeColor: '#FFFFFF',
        scale: 8
    }
};

