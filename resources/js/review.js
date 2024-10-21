const MARKER_COLORS = ['#4285F4', '#DB4437', '#F4B400', '#0F9D58', '#AB47BC', '#00ACC1', '#FF7043', '#9E9E9E'];

const {ColorScheme} = await google.maps.importLibrary("core");

class ClaimReviewMap {

    ////////////////////////////////////////////////////////////////////////

    constructor(locations) {
        this.locations = locations;
        this.map = null;
        this.markers = [];
        this.directionsService = null;
        this.directionsRenderer = null;
        this.routeInfoPanel = null;
        this.infoWindows = [];
    }

    ////////////////////////////////////////////////////////////////////////

    init() {
        this.initMap();
        
        this.plotLocations();

        if (this.locations.length > 1) {
            this.drawRoute();
        }
    }

    ////////////////////////////////////////////////////////////////////////

    initMap() {
        const defaultCenter = { lat: 3.0311070837055487, lng: 101.61629987586117 };
        let mapCenter = this.getValidCoordinates(this.locations[0]) || defaultCenter;

        this.map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: mapCenter,
            disableDefaultUI: true,
            zoomControl: true,
            mapTypeControl: false,
            streetViewControl: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            colorScheme: ColorScheme.LIGHT,
        });

        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            map: this.map,
            suppressMarkers: true,
        });
    }

    ////////////////////////////////////////////////////////////////////////

    addMarker(location, number) {
        const marker = new google.maps.Marker({
            position: location,
            map: this.map,
            label: {
                text: number.toString(),
                color: 'white'
            },
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: MARKER_COLORS[(number - 1) % MARKER_COLORS.length],
                fillOpacity: 1,
                strokeWeight: 0,
                scale: 10
            }
        });
        this.markers.push(marker);
    }
    
    ////////////////////////////////////////////////////////////////////////

    addSegmentInfoBoxes(route) {
        if (this.routeInfoPanel) {
            this.map.controls[google.maps.ControlPosition.TOP_RIGHT].pop();
        }
        this.routeInfoPanel = this.createRouteInfoPanel(route);
        this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(this.routeInfoPanel);
    }

    ////////////////////////////////////////////////////////////////////////
    
    createRouteInfoPanel(route) {
        const panel = document.createElement('div');
        panel.className = 'route-info-panel';
        panel.style.backgroundColor = 'white';
        panel.style.margin = '10px';
        panel.style.padding = '10px';
        panel.style.borderRadius = '2px';
        panel.style.maxHeight = '300px';
        panel.style.overflowY = 'auto';
    
        for (let i = 0; i < route.legs.length; i++) {
            const leg = route.legs[i];
            const legDecimal = leg.distance.value / 1000;
            console.log(legDecimal);
            const segment = document.createElement('div');
            segment.style.marginBottom = '10px';
            segment.innerHTML = `
                <strong>Location ${i + 1} - Location ${i + 2}</strong><br>${legDecimal.toFixed(2)} km<br>
                Distance: <br>
                Duration: ${leg.duration.text}
            `;
            panel.appendChild(segment);
        }
        
    
        const totalDistance = route.legs.reduce((total, leg) => total + leg.distance.value, 0);
        const totalDistanceKm = Math.floor(totalDistance / 10) / 100;
        const totalDuration = route.legs.reduce((total, leg) => total + leg.duration.value, 0);
        const totalHours = Math.floor(totalDuration / 3600);
        const totalMinutes = Math.floor((totalDuration % 3600) / 60);
    
        const totalInfo = document.createElement('div');
        totalInfo.style.borderTop = '1px solid #ccc';
        totalInfo.style.paddingTop = '10px';
        totalInfo.style.marginTop = '10px';
        totalInfo.innerHTML = `
        <strong>Total Distance:</strong> ${totalDistanceKm.toFixed(2)} km<br>
        <strong>Total Duration:</strong> ${totalHours}h ${totalMinutes}m<br>
        <strong>Total Cost:</strong> RM${(totalDistanceKm * 0.6).toFixed(2)}
        `;
        panel.appendChild(totalInfo);
    
        console.log(totalDistance);
        console.log(totalDistanceKm);

        return panel;


    }

    ////////////////////////////////////////////////////////////////////////

    // Use Marker Clusterer Library to Improve Performance
    // https://developers.google.com/maps/documentation/javascript/marker-clustering

    plotLocations() {
        const bounds = new google.maps.LatLngBounds();
        this.locations.forEach((location, index) => {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: location.location }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const position = results[0].geometry.location;
                    this.addMarker(position, index + 1);
                    bounds.extend(position);
                    
                    if (index === this.locations.length - 1) {
                        this.map.fitBounds(bounds);
                    }
                }
            });
        });
    }
    
    ////////////////////////////////////////////////////////////////////////

    getValidCoordinates(location) {
        const lat = parseFloat(location.lat || location.latitude);
        const lng = parseFloat(location.lng || location.longitude);
        if (isFinite(lat) && isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) {
            return { lat, lng };
        }
        return null;
    }

    ////////////////////////////////////////////////////////////////////////

    drawRoute() {
        const origin = this.locations[0].location;
        const destination = this.locations[this.locations.length - 1].location;
        const waypoints = this.locations.slice(1, -1).map(loc => ({
            location: loc.location,
            stopover: true
        }));
    
        this.directionsService.route({
            origin: origin,
            destination: destination,
            waypoints: waypoints,
            travelMode: 'DRIVING'
        }, (response, status) => {
            if (status === 'OK') {
                this.directionsRenderer.setDirections(response);
                this.addSegmentInfoBoxes(response.routes[0]);
            }
        });
    }
    
}

////////////////////////////////////////////////////////////////////////

document.addEventListener('DOMContentLoaded', () => {
        if (typeof claimLocations !== 'undefined') {
            const claimReviewMap = new ClaimReviewMap(claimLocations);
            claimReviewMap.init();
        }
});
