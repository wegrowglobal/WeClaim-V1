export class LocationManager {
    constructor(maxWaypoints = 10) {
        this.MAX_WAYPOINTS = maxWaypoints;
        this.deleteCallback = null;
        this.routeColors = [
            '#3B82F6', '#EF4444', '#22C55E', '#EAB308',
            '#3B82F6', '#EF4444', '#22C55E', '#EAB308',
            '#3B82F6', '#EF4444'
        ];
    }

    createLocationInput(index, value = '', showDelete = false) {
        const wrapper = document.createElement('div');
        wrapper.className = 'location-pair relative flex gap-2 cursor-move';
        
        const letter = String.fromCharCode(65 + index);
        
        wrapper.innerHTML = `
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 cursor-move">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 14a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
                        </svg>
                    </div>
                    <div class="absolute inset-y-0 left-0 flex items-center pl-10">
                        <span class="w-5 h-5 flex items-center justify-center rounded-full text-xs font-medium text-white"
                              style="background-color: ${this.routeColors[index % this.routeColors.length]}">
                            ${letter}
                        </span>
                    </div>
                    <input type="text" 
                           class="location-input block w-full pl-20 pr-10 py-2 text-sm border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 rounded-lg transition-all" 
                           placeholder="Enter location"
                           value="${value}"
                           autocomplete="off">
                </div>
            </div>
            ${showDelete ? `
                <button type="button" 
                        class="delete-location-btn inline-flex items-center p-2 text-gray-400 hover:text-gray-500"
                        style="display: ${index <= 1 ? 'none' : 'flex'}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            ` : ''}`;

        return wrapper;
    }

    setDeleteCallback(callback) {
        this.deleteCallback = callback;
    }

    validateLocations(locations) {
        return locations && locations.length >= 2 && locations.every(loc => loc.trim().length > 0);
    }

    getLocationInputs() {
        return Array.from(document.querySelectorAll('.location-input'))
            .map(input => input.value.trim())
            .filter(value => value.length > 0);
    }

    setDeleteCallback(callback) {
        this.onDelete = callback;
    }
    
    getColorForIndex(index) {
        return this.routeColors[index % this.routeColors.length];
    }

    getLabelForIndex(index) {
        return String.fromCharCode(65 + index);
    }
} 