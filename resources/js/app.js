import './bootstrap';
import { initializeTableFilters } from './components/table-filter';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Initialize table filters when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTableFilters();
});

// Re-initialize when Livewire updates the DOM
document.addEventListener('livewire:initialized', function() {
    initializeTableFilters();
});