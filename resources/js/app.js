import './bootstrap';
import { initializeTableFilters } from './table-filter';

// Initialize table filters when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTableFilters();
});

// Re-initialize when Livewire updates the DOM
document.addEventListener('livewire:initialized', function() {
    initializeTableFilters();
});