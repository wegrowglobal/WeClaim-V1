import './bootstrap';
import { initializeTableFilters } from './components/table-filter';
import './auth/login-handler';

// Initialize table filters when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTableFilters();
});

// Re-initialize when Livewire updates the DOM
document.addEventListener('livewire:initialized', function() {
    initializeTableFilters();
});