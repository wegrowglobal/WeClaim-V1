import { BaseMap } from './base-map.js';
import { MARKER_COLORS } from '../config.js';

export class ReviewMap extends BaseMap {
    constructor(locations, options = {}) {
        super(options);
        this.locations = locations;
        this.routeInfoPanel = null;
    }

    async init() {
        console.log('Initializing review map with locations:', this.locations);
        this.showLoading();
        
        try {
            await this.initialize();
            await this.plotLocations();
            
            if (this.locations.length > 0) {
                await this.drawRoute();
            } else {
                console.log('Not enough locations to draw a route');
            }
        } catch (error) {
            console.error('Error initializing review map:', error);
            this.showError('An error occurred while initializing the map. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    async plotLocations() {
        const bounds = new google.maps.LatLngBounds();
        const uniqueLocations = new Set();
        
        // Collect unique locations
        this.locations.forEach(location => {
            uniqueLocations.add(location.from_location);
            uniqueLocations.add(location.to_location);
        });

        try {
            // Create position map for quick lookup
            const positionMap = new Map();
            for (const address of uniqueLocations) {
                const position = await this.geocodeLocation(address);
                if (position) {
                    positionMap.set(address, position);
                }
            }

            // Add markers in correct order
            this.locations.forEach((locationPair, index) => {
                const fromPosition = positionMap.get(locationPair.from_location);
                const toPosition = positionMap.get(locationPair.to_location);
                
                if (fromPosition && index === 0) {
                    this.addMarker(fromPosition, {
                        label: { text: '1', color: 'white' },
                        icon: this.createMarkerIcon(0)
                    });
                    bounds.extend(fromPosition);
                }
                
                if (toPosition) {
                    this.addMarker(toPosition, {
                        label: { text: (index + 2).toString(), color: 'white' },
                        icon: this.createMarkerIcon(index + 1)
                    });
                    bounds.extend(toPosition);
                }
            });

            this.map.fitBounds(bounds);
        } catch (error) {
            console.error('Error plotting locations:', error);
            throw error;
        }
    }

    createMarkerIcon(index) {
        return {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: MARKER_COLORS[index % MARKER_COLORS.length],
            fillOpacity: 1,
            strokeWeight: 0,
            scale: 10
        };
    }

    async drawRoute() {
        if (this.locations.length < 1) {
            console.error('Not enough locations to draw a route');
            return;
        }

        const origin = this.locations[0].from_location;
        const destination = this.locations[this.locations.length - 1].to_location;
        const waypoints = this.locations.slice(0, -1).map(loc => ({
            location: loc.to_location,
            stopover: true
        }));

        try {
            const response = await new Promise((resolve, reject) => {
                this.directionsService.route({
                    origin,
                    destination,
                    waypoints,
                    travelMode: google.maps.TravelMode.DRIVING,
                }, (result, status) => {
                    if (status === "OK") {
                        resolve(result);
                    } else {
                        reject(new Error(`Direction service failed: ${status}`));
                    }
                });
            });

            this.directionsRenderer.setDirections(response);
            this.addSegmentInfoBoxes(response.routes[0]);
        } catch (error) {
            console.error('Error drawing route:', error);
            this.showError('Unable to calculate route. Please check your locations and try again.');
        }
    }

    addSegmentInfoBoxes(route) {
        if (this.routeInfoPanel) {
            this.map.controls[google.maps.ControlPosition.TOP_RIGHT].pop();
        }
        this.routeInfoPanel = this.createRouteInfoPanel(route);
        this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(this.routeInfoPanel);
    }

    createRouteInfoPanel(route) {
        const panel = document.createElement("div");
        panel.className = "route-info-panel";
        Object.assign(panel.style, {
            backgroundColor: "white",
            margin: "10px",
            padding: "10px",
            borderRadius: "2px",
            maxHeight: "300px",
            overflowY: "auto"
        });

        // Add segment details
        route.legs.forEach((leg, i) => {
            const segment = document.createElement("div");
            segment.style.marginBottom = "10px";
            const legDistance = leg.distance.value / 1000;
            segment.innerHTML = `
                <strong>Location ${i + 1} - Location ${i + 2}</strong><br>
                Distance: ${legDistance.toFixed(2)} km<br>
                Duration: ${leg.duration.text}
            `;
            panel.appendChild(segment);
        });

        // Add total information
        const totalDistance = route.legs.reduce((total, leg) => total + leg.distance.value, 0) / 1000;
        const totalDuration = route.legs.reduce((total, leg) => total + leg.duration.value, 0);
        const totalHours = Math.floor(totalDuration / 3600);
        const totalMinutes = Math.floor((totalDuration % 3600) / 60);

        const totalInfo = document.createElement("div");
        Object.assign(totalInfo.style, {
            borderTop: "1px solid #ccc",
            paddingTop: "10px",
            marginTop: "10px"
        });
        totalInfo.innerHTML = `
            <strong>Total Distance:</strong> ${totalDistance.toFixed(2)} km<br>
            <strong>Total Duration:</strong> ${totalHours}h ${totalMinutes}m<br>
            <strong>Total Cost:</strong> RM${(totalDistance * 0.6).toFixed(2)}
        `;
        panel.appendChild(totalInfo);

        return panel;
    }
}

// Initialize map when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    if (typeof claimLocations !== "undefined") {
        const reviewMap = new ReviewMap(claimLocations);
        reviewMap.init();
    }
}); 