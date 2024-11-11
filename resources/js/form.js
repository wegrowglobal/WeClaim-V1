import { MAP_CONFIG, MARKER_COLORS } from './config.js';

class FormManager {
    constructor() {
        this.locationCount = 1;
        this.markers = [];
        this.directionsService = null;
        this.travelMode = "DRIVING";
        this.directionsRenderer = null;
        this.geocodeCache = new Map();
        this.locationInputContainer = document.getElementById("location-input-container");
        this.addLocationBtn = document.getElementById("add-location-btn");
        this.removeLocationBtn = document.getElementById("remove-location-btn");
        this.trafficLayer = null;
        this.totalDistanceInput = document.getElementById("total-distance-input");
        this.debouncedUpdateMap = this.debounce(this.updateMap.bind(this), 500);
        this.initAutocomplete();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.handleResize = this.handleResize.bind(this);
        window.addEventListener('resize', this.handleResize);
        this.distances = [];
    }

    ///////////////////////////////////////////////////////////////////

    initMap() {
        this.map = new google.maps.Map(document.getElementById("map"), {
            center: MAP_CONFIG.initialCenter,
            zoom: MAP_CONFIG.initialZoom,
            disableDefaultUI: true,
        });

        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            polylineOptions: {
                strokeColor: "#4285F4",
                strokeWeight: 5,
            },
            suppressMarkers: true,
            preserveViewport: true,
            routeIndex: 0,
        });

        this.directionsRenderer.setMap(this.map);

        this.initMapControls(); // Call this after map is initialized
    }

    ///////////////////////////////////////////////////////////////////

    initMapControls() {
        const controlDiv = document.createElement("div");
        controlDiv.classList.add("wgg-flex-row", "gap-2");
        controlDiv.style.padding = "10px";
        controlDiv.id = "map-controls";

        this.clearRouteBtn = document.createElement("button");
        this.clearRouteBtn.textContent = "Clear Route";
        this.clearRouteBtn.className = "btn btn-primary w-fit text-xs";
        this.clearRouteBtn.type = "button";
        this.clearRouteBtn.disabled = true;
        this.clearRouteBtn.addEventListener("click", () => this.clearRoute());
        this.clearRouteBtn.setAttribute("aria-label", "Clear Route");

        this.reverseRouteBtn = document.createElement("button");
        this.reverseRouteBtn.textContent = "Reverse Route";
        this.reverseRouteBtn.className = "btn btn-primary w-fit text-xs";
        this.reverseRouteBtn.type = "button";
        this.reverseRouteBtn.addEventListener("click", () => this.reverseRoute());
        this.reverseRouteBtn.setAttribute("aria-label", "Reverse Route");

        this.trafficToggleBtn = document.createElement("button");
        this.trafficToggleBtn.textContent = "Toggle Traffic";
        this.trafficToggleBtn.className = "btn btn-primary w-fit text-xs";
        this.trafficToggleBtn.type = "button";
        this.trafficToggleBtn.addEventListener("click", () => this.toggleTrafficLayer());
        this.trafficToggleBtn.setAttribute("aria-label", "Toggle Traffic Layer");

        controlDiv.appendChild(this.clearRouteBtn);
        controlDiv.appendChild(this.reverseRouteBtn);
        controlDiv.appendChild(this.trafficToggleBtn);

        this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(controlDiv);
    }

    ///////////////////////////////////////////////////////////////////

    async updateMap() {
        this.showLoading();
        this.clearMarkers();
        this.directionsRenderer.setDirections({ routes: [] });

        const inputs = document.querySelectorAll(".location-input");
        const waypoints = [];
        const bounds = new google.maps.LatLngBounds();

        let origin = null;
        let destination = null;

        try {
            for (let i = 0; i < inputs.length; i++) {
                const input = inputs[i];
                if (input.value) {
                    try {
                        const location = await this.geocodeAddress(input.value);
                        if (location) {
                            this.addMarker(location, i + 1);
                            bounds.extend(location);

                            if (i === 0) {
                                origin = location;
                            } else if (i === inputs.length - 1) {
                                destination = location;
                            } else {
                                waypoints.push({
                                    location: location,
                                    stopover: true,
                                });
                            }
                        }
                    } catch (error) {
                        console.error("Error geocoding address:", error);
                        this.showError(`Error: ${error.message}`);
                    }
                }
            }

            if (this.markers.length > 0) {
                this.map.setCenter(this.markers[0].getPosition());
                this.map.setZoom(15);
            }

            if (origin && destination) {
                const request = {
                    origin: origin,
                    destination: destination,
                    waypoints: waypoints,
                    travelMode: google.maps.TravelMode[this.travelMode.toUpperCase()],
                };

                this.directionsService.route(request, (response, status) => {
                    if (status === "OK") {
                        this.directionsRenderer.setDirections(response);
                        this.updateDistances(response);
                        const totalDistanceKm = this.updateTotalDistance(response.routes[0]);
                        this.clearRouteBtn.disabled = false;
                        this.map.fitBounds(bounds);
                    }
                });
            }
        } catch (error) {
            console.error("Error updating map:", error);
            this.showError("An error occurred while updating the map. Please try again.");
        } finally {
            this.hideLoading();
        }
    }

    ///////////////////////////////////////////////////////////////////

    clearInfoWindows() {
        this.infoWindows.forEach((infoWindow) => infoWindow.close());
        this.infoWindows = [];
    }

    ///////////////////////////////////////////////////////////////////

    clearRoute() {
        this.directionsRenderer.setDirections({ routes: [] });
        const inputs = document.querySelectorAll(".location-input");
        inputs.forEach((input, index) => {
            if (index === 0) {
                input.value = "";
            } else {
                input.closest(".wgg-flex-col").remove();
            }
        });
        this.locationCount = 1;
        this.clearMarkers();
        if (this.routeInfoPanel) {
            this.map.controls[google.maps.ControlPosition.TOP_RIGHT].pop();
            this.routeInfoPanel = null;
        }
        this.clearRouteBtn.disabled = true;
        this.updateRemoveButtonState();

        this.map.setCenter(MAP_CONFIG.initialCenter);
        this.map.setZoom(MAP_CONFIG.initialZoom);
    }

    ///////////////////////////////////////////////////////////////////

    addMarker(location, number) {
        const marker = new google.maps.Marker({
            position: location,
            map: this.map,
            label: {
                text: number.toString(),
                color: "white",
            },
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: MARKER_COLORS[(number - 1) % MARKER_COLORS.length],
                fillOpacity: 1,
                strokeWeight: 0,
                scale: 10,
            },
        });
        this.markers.push(marker);
    }

    ///////////////////////////////////////////////////////////////////

    toggleTrafficLayer() {
        if (this.trafficLayer) {
            this.trafficLayer.setMap(null);
            this.trafficLayer = null;
        } else {
            this.trafficLayer = new google.maps.TrafficLayer();
            this.trafficLayer.setMap(this.map);
        }
    }

    ///////////////////////////////////////////////////////////////////

    clearMarkers() {
        this.markers.forEach((marker) => marker.setMap(null));
        this.markers = [];
    }

    ///////////////////////////////////////////////////////////////////

    updateLocationLabels() {
        document.querySelectorAll(".location-input").forEach((input, index) => {
            const label = input.previousElementSibling;
            label.textContent = `Location ${index + 1}`;
        });
    }

    ///////////////////////////////////////////////////////////////////

    makeLocationsDraggable() {
        new Sortable(this.locationInputContainer, {
            animation: 150,
            onEnd: () => {
                this.updateLocationLabels();
                this.updateLocationCount();
                this.updateRemoveButtonState();
                this.updateMap();
            },
        });
    }

    ///////////////////////////////////////////////////////////////////

    updateLocationCount() {
        this.locationCount = this.locationInputContainer.children.length;
    }

    ///////////////////////////////////////////////////////////////////

    attachInputListeners() {
        document.querySelectorAll(".location-input").forEach((input) => {
            input.addEventListener("change", () => this.debouncedUpdateMap());
        });
    }

    /////////////////////////////////////////////////////////////////// 

    debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    ///////////////////////////////////////////////////////////////////

    reverseRoute() {
        const inputs = document.querySelectorAll(".location-input");
        const values = Array.from(inputs)
            .map((input) => input.value)
            .reverse();
        inputs.forEach((input, index) => {
            input.value = values[index];
        });
        this.updateMap();
    }

    ///////////////////////////////////////////////////////////////////

    addSegmentInfoBoxes(route) {
        if (this.routeInfoPanel) {
            this.map.controls[google.maps.ControlPosition.TOP_RIGHT].pop();
        }
        this.routeInfoPanel = this.createRouteInfoPanel(route);
        this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(this.routeInfoPanel);
    }

    ///////////////////////////////////////////////////////////////////

    createRouteInfoPanel(route) {
        const panel = document.createElement("div");
        panel.className = "route-info-panel";
        panel.style.backgroundColor = "white";
        panel.style.margin = "10px";
        panel.style.padding = "10px";
        panel.style.borderRadius = "2px";
        panel.style.maxHeight = "300px";
        panel.style.overflowY = "auto";

        for (let i = 0; i < route.legs.length; i++) {
            let rawTotalDistance = 0;
            const rawDistance = Number((route.legs[i].distance.value / 1000).toFixed(2));
            const leg = route.legs[i];
            const segment = document.createElement("div");
            segment.style.marginBottom = "10px";
            segment.innerHTML = `
                <strong>Point ${i + 1} - Point ${i + 2}</strong><br>
                Distance: ${rawDistance} km<br>
                Duration: ${leg.duration.text}
            `;
            panel.appendChild(segment);
            rawTotalDistance += leg.distance.value;
        }

        const totalDistanceKm = this.updateTotalDistance(route);
        const totalDuration = route.legs.reduce((total, leg) => total + leg.duration.value, 0);
        const totalHours = Math.floor(totalDuration / 3600);
        const totalMinutes = Math.floor((totalDuration % 3600) / 60);

        console.log("Total Distance (km):", totalDistanceKm);

        const totalInfo = document.createElement("div");
        totalInfo.style.borderTop = "1px solid #ccc";
        totalInfo.style.paddingTop = "10px";
        totalInfo.style.marginTop = "10px";
        totalInfo.innerHTML = `
            <strong>Total Distance:</strong> ${totalDistanceKm.toFixed(2)} km<br>
            <strong>Total Duration:</strong> ${totalHours}h ${totalMinutes}m<br>
            <strong>Total Cost:</strong> RM${(totalDistanceKm * 0.6).toFixed(2)}
        `;
        panel.appendChild(totalInfo);

        return panel;
    }

    ///////////////////////////////////////////////////////////////////

    createInfoBox(leg) {
        const distanceKm = (leg.distance.value / 1000).toFixed(1);
        const duration = leg.duration.text;

        const infoBox = document.createElement("div");
        infoBox.className = "segment-info";
        infoBox.innerHTML = `
            <strong>${distanceKm} km</strong><br>
            ${duration}
        `;
        return infoBox;
    }

    ///////////////////////////////////////////////////////////////////

    getMidpoint(start, end) {
        return new google.maps.LatLng((start.lat() + end.lat()) / 2, (start.lng() + end.lng()) / 2);
    }

    ///////////////////////////////////////////////////////////////////

    async geocodeAddress(address) {
        if (this.geocodeCache.has(address)) {
            return this.geocodeCache.get(address);
        }

        return new Promise((resolve, reject) => {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address }, (results, status) => {
                if (status === "OK" && results[0]) {
                    const location = results[0].geometry.location;
                    this.geocodeCache.set(address, location);
                    resolve(location);
                } else {
                    reject(new Error(`Geocoding failed for address: ${address}`));
                }
            });
        });
    }

    ///////////////////////////////////////////////////////////////////

    addLocation() {
        this.locationCount++;
        const newInput = document.createElement("div");
        newInput.classList.add("wgg-flex-col", "gap-2");
        newInput.id = `location-wrapper-${this.locationCount}`;
        
        const inputWrapper = document.createElement("div");
        inputWrapper.classList.add("relative");
        
        inputWrapper.innerHTML = `
            <input 
                type="text" 
                name="location[]" 
                id="location-${this.locationCount}" 
                class="form-input location-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" 
                placeholder=" " 
                required
            >
            <label 
                for="location-${this.locationCount}" 
                class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3"
            >
                Location ${this.locationCount}
            </label>
        `;

        newInput.appendChild(inputWrapper);
        this.locationInputContainer.appendChild(newInput);

        // Initialize autocomplete for new input
        const newLocationInput = inputWrapper.querySelector('.location-input');
        new google.maps.places.Autocomplete(newLocationInput);

        this.updateRemoveButtonState();
        this.attachInputListeners();
        this.updateLocationLabels();

        // Update map if we already have markers
        if (this.markers.length > 0) {
            const currentBounds = this.map.getBounds();
            this.updateMap().then(() => {
                if (currentBounds) {
                    this.map.fitBounds(currentBounds);
                }
            });
        }
    }

    ///////////////////////////////////////////////////////////////////

    removeLocation() {
        if (this.locationCount > 1) {
            // Remove all distance inputs for the last location pair
            const lastLocationWrapper = this.locationInputContainer.lastChild;
            const distanceInputs = lastLocationWrapper.querySelectorAll('.location-distance');
            distanceInputs.forEach(input => input.remove());

            // Remove the location input
            this.locationInputContainer.removeChild(lastLocationWrapper);
            this.locationCount--;
            
            this.updateRemoveButtonState();
            this.updateLocationLabels();
            this.attachInputListeners();
            this.updateMap();
        }
    }

    ///////////////////////////////////////////////////////////////////

    updateRemoveButtonState() {
        if (this.locationCount > 1) {
            this.removeLocationBtn.disabled = false;
            this.removeLocationBtn.classList.remove(
                "disabled:opacity-50",
                "disabled:cursor-not-allowed",
                "disabled:bg-gray-300"
            );
        } else {
            this.removeLocationBtn.disabled = true;
            this.removeLocationBtn.classList.add(
                "disabled:opacity-50",
                "disabled:cursor-not-allowed",
                "disabled:bg-gray-300"
            );
        }
    }

    ///////////////////////////////////////////////////////////////////

    showLoading() {
        const loadingElement = document.createElement('div');
        loadingElement.id = 'loading-indicator';
        loadingElement.className = 'loading-indicator';
        loadingElement.textContent = 'Loading...';
        document.getElementById('map').appendChild(loadingElement);
    }

    ///////////////////////////////////////////////////////////////////

    showError(message) {
        const errorElement = document.createElement("div");
        errorElement.id = "error-message";
        errorElement.className = "fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg";
        errorElement.textContent = message;
        document.body.appendChild(errorElement);
        setTimeout(() => errorElement.remove(), 5000);
    }

    ///////////////////////////////////////////////////////////////////

    hideLoading() {
        const loadingElement = document.getElementById('loading-indicator');
        if (loadingElement) {
            loadingElement.remove();
        }
    }

    ///////////////////////////////////////////////////////////////////

    showError(message) {
        const errorElement = document.createElement("div");
        errorElement.id = "error-message";
        errorElement.textContent = message;
        errorElement.style.color = "red";
        document.body.appendChild(errorElement);
        setTimeout(() => errorElement.remove(), 5000);
    }

    ///////////////////////////////////////////////////////////////////

    validateLocationInput(input) {
        if (input.value.trim() === "") {
            input.setCustomValidity("Please enter a location");
            return false;
        }
        input.setCustomValidity("");
        return true;
    }

    ///////////////////////////////////////////////////////////////////  

    initAutocomplete() {
        const inputs = document.querySelectorAll('.location-input');
        inputs.forEach(input => {
            new google.maps.places.Autocomplete(input);
        });
    }

    ///////////////////////////////////////////////////////////////////

    debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    ///////////////////////////////////////////////////////////////////

    async sendAjaxRequest(url, method, data) {
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            this.showError('An error occurred while processing your request. Please try again.');
        }
    }

    ///////////////////////////////////////////////////////////////////

    init() {
        this.initMap();
        this.makeLocationsDraggable();
        this.attachInputListeners();
        this.initAutocomplete();
        this.updateMap();

        this.addLocationBtn.addEventListener("click", () => this.addLocation());
        this.removeLocationBtn.addEventListener("click", () => this.removeLocation());

        // Add form submission validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => this.validateForm(e));
        }

        const firstLocationInput = document.querySelector(".location-input");
        if (firstLocationInput) {
            new google.maps.places.Autocomplete(firstLocationInput);
        }
    }

    ///////////////////////////////////////////////////////////////////

    handleResize() {
        if (this.map) {
            google.maps.event.trigger(this.map, 'resize');
            if (this.markers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                this.markers.forEach(marker => bounds.extend(marker.getPosition()));
                this.map.fitBounds(bounds);
            }
        }
    }

    ///////////////////////////////////////////////////////////////////

    destroy() {
        window.removeEventListener('resize', this.handleResize);
        // ... any other cleanup code ...
    }

    ///////////////////////////////////////////////////////////////////

    updateDistances(response) {
        const legs = response.routes[0].legs;
        const locationInputs = document.querySelectorAll('.location-input');
        
        // Clear existing distance inputs
        document.querySelectorAll('.location-distance').forEach(input => input.remove());
        
        let totalDistance = 0;
        
        // Create location pairs and assign distances
        for (let i = 0; i < locationInputs.length - 1; i++) {
            const distance = legs[i].distance.value / 1000; // Convert to km
            totalDistance += distance;
            
            // Create hidden distance input
            const distanceInput = document.createElement('input');
            distanceInput.type = 'hidden';
            distanceInput.name = `distances[${i}]`;
            distanceInput.value = distance.toFixed(2);
            distanceInput.classList.add('location-distance');
            locationInputs[i].parentNode.appendChild(distanceInput);
        }
        
        // Update total distance
        const totalDistanceInput = document.getElementById('total-distance-input');
        if (totalDistanceInput) {
            totalDistanceInput.value = totalDistance.toFixed(2);
        }

        // Update route info panel with new distances
        this.addSegmentInfoBoxes(response.routes[0]);
    }

    updateTotalDistance(route) {
        const totalDistance = route.legs.reduce((total, leg) => total + leg.distance.value, 0);
        const totalDistanceKm = Number((totalDistance / 1000).toFixed(2));
        
        // Update hidden input
        const totalDistanceInput = document.getElementById("total-distance-input");
        if (totalDistanceInput) {
            totalDistanceInput.value = totalDistanceKm;
            // Dispatch an event to notify other parts of the application
            totalDistanceInput.dispatchEvent(new Event('change'));
        }
        
        return totalDistanceKm;
    }

    validateForm(e) {
        const distanceInputs = document.querySelectorAll('.location-distance');
        const locationInputs = document.querySelectorAll('.location-input');

        // Check if we have at least two locations
        if (locationInputs.length < 2) {
            e.preventDefault();
            this.showError('Please add at least two locations');
            return false;
        }

        // Check if we have all required distances
        if (distanceInputs.length < locationInputs.length - 1) {
            e.preventDefault();
            this.showError('Please wait for distance calculations to complete');
            return false;
        }

        // Check if total distance is calculated
        const totalDistanceInput = document.getElementById('total-distance-input');
        if (!totalDistanceInput || !totalDistanceInput.value) {
            e.preventDefault();
            this.showError('Total distance has not been calculated');
            return false;
        }

        return true;
    }

}

///////////////////////////////////////////////////////////////////////

function clearInputsOnReload() {
    const inputs = document.querySelectorAll(".location-input");
    inputs.forEach((input) => {
        input.value = "";
    });
}

///////////////////////////////////////////////////////////////////////

document.addEventListener("DOMContentLoaded", () => {
    clearInputsOnReload();
    const formManager = new FormManager();
    formManager.init();
});

function updateFileLabel(input, labelId) {
    const label = document.getElementById(labelId);
    if (input.files && input.files[0]) {
        label.textContent = input.files[0].name;
    } else {
        label.textContent = input.getAttribute('aria-label');
    }
}