console.log("form.js loaded and running");

////////////////////////////////////////////////////////////////////////
/////////////////////////// Utilities //////////////////////////////////
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////

const MAP_CONFIG = {
   initialZoom: 14,
   initialCenter: { lat: 3.0311070837055487, lng: 101.61629987586117 },
};

const MARKER_COLORS = [
   "#4285F4",
   "#DB4437",
   "#F4B400",
   "#0F9D58",
   "#AB47BC",
   "#00ACC1",
   "#FF7043",
   "#9E9E9E",
];

////////////////////////////////////////////////////////////////////////

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

      this.clearRouteBtn = document.createElement("button");
      this.clearRouteBtn.textContent = "Clear Route";
      this.clearRouteBtn.className = "btn-primary";
      this.clearRouteBtn.type = "button";
      this.clearRouteBtn.disabled = true;
      this.clearRouteBtn.addEventListener("click", () => this.clearRoute());

      this.reverseRouteBtn = document.createElement("button");
      this.reverseRouteBtn.textContent = "Reverse Route";
      this.reverseRouteBtn.className = "btn-primary";
      this.reverseRouteBtn.type = "button";
      this.reverseRouteBtn.addEventListener("click", () => this.reverseRoute());

      this.trafficToggleBtn = document.createElement("button");
      this.trafficToggleBtn.textContent = "Toggle Traffic";
      this.trafficToggleBtn.className = "btn-primary";
      this.trafficToggleBtn.type = "button";
      this.trafficToggleBtn.addEventListener("click", () => this.toggleTrafficLayer());

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

            const result = await this.directionsService.route(request);
            this.directionsRenderer.setDirections(result);

            this.map.fitBounds(bounds);
            this.addSegmentInfoBoxes(result.routes[0]);
            this.clearRouteBtn.disabled = false;
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
            fillColor: MARKER_COLORS[number % MARKER_COLORS.length],
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
         input.addEventListener(
            "change",
            this.debounce(() => this.updateMap(), 500)
         );
         input.addEventListener("input", () => this.validateLocationInput(input));
      });
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
         const rawDistance = route.legs[i].distance.value / 1000;
         const leg = route.legs[i];
         const segment = document.createElement("div");
         segment.style.marginBottom = "10px";
         segment.innerHTML = `
                <strong>Point ${i + 1} - Point ${i + 2}</strong><br>
                Distance: ${rawDistance}<br>
                Duration: ${leg.duration.text}
            `;
         panel.appendChild(segment);
         rawTotalDistance += leg.distance.value;
      }

      const totalDistance = route.legs.reduce((total, leg) => total + leg.distance.value, 0);
      const totalDistanceKm = totalDistance / 1000;
      const totalDuration = route.legs.reduce((total, leg) => total + leg.duration.value, 0);
      const totalHours = Math.floor(totalDuration / 3600);
      const totalMinutes = Math.floor((totalDuration % 3600) / 60);

      console.log("Total Distance (km):", totalDistanceKm);

      document.getElementById("total-distance-input").value = totalDistanceKm;

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
      newInput.classList.add(`location-${this.locationCount}`, "relative");
      newInput.innerHTML = `
            <input type="text" name="location[]" id="location-${this.locationCount}" class="form-input location-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" placeholder=" " required>

            <label for="location-1" class="absolute text-sm text-wgg-black-400 font-wgg font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">
                Location ${this.locationCount}
            </label>
        `;
      this.locationInputContainer.appendChild(newInput);

      const newLocationInput = newInput.querySelector(".location-input");
      new google.maps.places.Autocomplete(newLocationInput);

      this.updateRemoveButtonState();
      this.attachInputListeners();

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
         this.locationInputContainer.removeChild(this.locationInputContainer.lastChild);
         this.locationCount--;
         this.updateRemoveButtonState();
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
      const loadingElement = document.createElement("div");
      loadingElement.id = "loading-indicator";
      loadingElement.textContent = "Loading...";
      document.body.appendChild(loadingElement);
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

   hideLoading() {
      const loadingElement = document.getElementById("loading-indicator");
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

   debounce(func, delay) {
      let timeoutId;
      return function (...args) {
         clearTimeout(timeoutId);
         timeoutId = setTimeout(() => func.apply(this, args), delay);
      };
   }

   ///////////////////////////////////////////////////////////////////

   init() {
      this.initMap();
      this.makeLocationsDraggable();
      this.attachInputListeners();
      this.updateMap();

      this.addLocationBtn.addEventListener("click", () => this.addLocation());
      this.removeLocationBtn.addEventListener("click", () => this.removeLocation());

      const firstLocationInput = document.querySelector(".location-input");
      if (firstLocationInput) {
         new google.maps.places.Autocomplete(firstLocationInput);
      }
   }

   ///////////////////////////////////////////////////////////////////
}

//////////////////////////////////////////////////////////////////////

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

///////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
////////////////// File Uploading Progress Bar Dummy ///////////////////
////////////////////////////////////////////////////////////////////////

function handleFileUpload(event, progressContainerId, progressBarId, fileLabelId) {
   const fileLabel = document.getElementById(fileLabelId);
   const fileName = event.target.files[0] ? event.target.files[0].name : "No File Selected";

   const progressContainer = document.getElementById(progressContainerId);
   const progressBar = document.getElementById(progressBarId);
   progressContainer.classList.remove("hidden");

   // Simulate progress for demonstration purposes
   progressBar.style.width = "100%";
   setTimeout(() => {
      progressContainer.classList.add("hidden");
      fileLabel.textContent = fileName;
   }, 1000);
}

document.getElementById("toll_report").addEventListener("change", (event) => {
   handleFileUpload(event, "toll_progress_container", "toll_progress_bar", "toll_file_label");
});

document.getElementById("email_report").addEventListener("change", (event) => {
   handleFileUpload(event, "email_progress_container", "email_progress_bar", "email_file_label");
});

// DEBUGGING

document.querySelector("form").addEventListener("submit", function (e) {
   alert(document.getElementById("total-distance-input"));
});
