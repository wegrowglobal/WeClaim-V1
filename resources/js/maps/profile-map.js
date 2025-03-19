import { BaseMap } from './base-map.js';
import { MAP_CONFIG } from '../config.js';

export class ProfileMap extends BaseMap {
    constructor(options = {}) {
        super({ ...options, editable: true });
        this.autocomplete = null;
        this.initialized = false;
    }

    initializeAutocomplete() {
        const addressInput = document.getElementById('address');
        
        // Initialize autocomplete with Malaysia-specific settings
        this.autocomplete = new google.maps.places.Autocomplete(addressInput, {
            bounds: MAP_CONFIG.autocompleteOptions.bounds,
            componentRestrictions: { country: 'MY' },
            fields: ['address_components', 'formatted_address', 'geometry', 'name'],
            types: ['geocode', 'establishment']
        });

        // Handle place selection
        this.autocomplete.addListener('place_changed', () => {
            const place = this.autocomplete.getPlace();
            if (!place.geometry) {
                addressInput.value = '';
                this.showError('Please select a valid location from the suggestions');
                return;
            }
            
            // Update the address field with formatted address
            addressInput.value = place.formatted_address;

            // Distribute address components to other fields
            this.distributeAddressComponents(place.address_components);

            // Keep lazy mode on (removed auto-toggle)
            addressInput.focus();
        });

        // Remove default input styling that might interfere with autocomplete
        addressInput.setAttribute('autocomplete', 'off');
    }

    distributeAddressComponents(components) {
        const addressMap = {
            street_number: '',
            route: '',
            sublocality_level_1: '',
            locality: '',
            administrative_area_level_2: '',
            administrative_area_level_1: '',
            postal_code: '',
            country: ''
        };

        // Map components to their types
        components.forEach(component => {
            component.types.forEach(type => {
                if (addressMap.hasOwnProperty(type)) {
                    addressMap[type] = component.long_name;
                }
            });
        });

        // Fill in the form fields
        if (document.getElementById('city')) {
            document.getElementById('city').value = addressMap.locality || 
                                                  addressMap.administrative_area_level_2 || '';
        }
        
        if (document.getElementById('state')) {
            const stateSelect = document.getElementById('state');
            const stateValue = addressMap.administrative_area_level_1;
            
            Array.from(stateSelect.options).forEach(option => {
                if (option.text.toLowerCase().includes(stateValue.toLowerCase())) {
                    stateSelect.value = option.value;
                }
            });
        }
        
        if (document.getElementById('zip_code')) {
            document.getElementById('zip_code').value = addressMap.postal_code || '';
        }
        
        if (document.getElementById('country')) {
            document.getElementById('country').value = addressMap.country || '';
        }
    }

    toggleAutocomplete() {
        const addressInput = document.getElementById('address');
        const button = document.getElementById('location-picker-btn');
        const indicator = button.querySelector('.lazy-mode-indicator');
        const icon = document.querySelector('.lazy-mode-icon');
        const isEnabled = addressInput.getAttribute('data-autocomplete-enabled') === 'true';
        
        if (!isEnabled) {
            // Enable autocomplete
            this.initializeAutocomplete();
            addressInput.setAttribute('data-autocomplete-enabled', 'true');
            
            // Update button and input styles
            button.classList.add('text-indigo-700', 'bg-indigo-50', 'border-indigo-200');
            indicator.classList.remove('opacity-0');
            icon.classList.remove('opacity-0');
            addressInput.classList.add('bg-white', 'border-indigo-200', 'ring-1', 'ring-indigo-100/50');
            
            addressInput.focus();
        } else {
            // Disable autocomplete
            if (this.autocomplete) {
                google.maps.event.clearInstanceListeners(this.autocomplete);
                this.autocomplete = null;
            }
            
            // Reset button and input styles
            addressInput.setAttribute('data-autocomplete-enabled', 'false');
            button.classList.remove('text-indigo-700', 'bg-indigo-50', 'border-indigo-200');
            indicator.classList.add('opacity-0');
            icon.classList.add('opacity-0');
            addressInput.classList.remove('bg-white', 'border-indigo-200', 'ring-1', 'ring-indigo-100/50');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const locationPickerBtn = document.getElementById('location-picker-btn');
    if (locationPickerBtn) {
        const profileMap = new ProfileMap();
        locationPickerBtn.addEventListener('click', () => profileMap.toggleAutocomplete());
    }
});