/**
 * Table Filter Enhancement
 * 
 * This script enhances the Livewire table filter components with additional
 * client-side functionality.
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeTableFilters();
    
    // Re-initialize when Livewire updates the DOM
    document.addEventListener('livewire:initialized', function() {
        initializeTableFilters();
    });
});

/**
 * Initialize all table filter enhancements
 */
function initializeTableFilters() {
    initializeResponsiveFilters();
    initializeKeyboardShortcuts();
    initializeColumnHighlighting();
    initializeRowHoverEffects();
}

/**
 * Initialize responsive filter toggles for mobile
 */
function initializeResponsiveFilters() {
    const filterToggles = document.querySelectorAll('[data-toggle-filters]');
    
    filterToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-toggle-filters');
            const filterContainer = document.getElementById(targetId);
            
            if (filterContainer) {
                filterContainer.classList.toggle('hidden');
                
                // Update toggle button text
                const isHidden = filterContainer.classList.contains('hidden');
                this.querySelector('.toggle-text').textContent = isHidden ? 'Show Filters' : 'Hide Filters';
                
                // Update toggle button icon
                const icon = this.querySelector('svg');
                if (icon) {
                    if (isHidden) {
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />';
                    } else {
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />';
                    }
                }
            }
        });
    });
}

/**
 * Initialize keyboard shortcuts for table navigation
 */
function initializeKeyboardShortcuts() {
    // Add keyboard navigation for tables
    document.addEventListener('keydown', function(e) {
        // Only apply shortcuts when not in an input field
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
            return;
        }
        
        // Focus search box with '/' key
        if (e.key === '/' || e.key === 'f') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Clear filters with 'c' key
        if (e.key === 'c') {
            e.preventDefault();
            const clearButton = document.querySelector('[wire\\:click="clearFilters"]');
            if (clearButton) {
                clearButton.click();
            }
        }
        
        // Next page with 'n' key
        if (e.key === 'n') {
            e.preventDefault();
            const nextButton = document.querySelector('.pagination-links a[rel="next"]');
            if (nextButton) {
                nextButton.click();
            }
        }
        
        // Previous page with 'p' key
        if (e.key === 'p') {
            e.preventDefault();
            const prevButton = document.querySelector('.pagination-links a[rel="prev"]');
            if (prevButton) {
                prevButton.click();
            }
        }
    });
}

/**
 * Initialize column highlighting on hover
 */
function initializeColumnHighlighting() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        const cells = table.querySelectorAll('th, td');
        
        cells.forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                const cellIndex = Array.from(this.parentNode.children).indexOf(this);
                
                // Highlight all cells in this column
                table.querySelectorAll('tr').forEach(row => {
                    const targetCell = row.children[cellIndex];
                    if (targetCell) {
                        targetCell.classList.add('bg-gray-50');
                    }
                });
            });
            
            cell.addEventListener('mouseleave', function() {
                const cellIndex = Array.from(this.parentNode.children).indexOf(this);
                
                // Remove highlight from all cells in this column
                table.querySelectorAll('tr').forEach(row => {
                    const targetCell = row.children[cellIndex];
                    if (targetCell) {
                        targetCell.classList.remove('bg-gray-50');
                    }
                });
            });
        });
    });
}

/**
 * Initialize row hover effects
 */
function initializeRowHoverEffects() {
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.classList.add('bg-gray-50');
            
            // Show action buttons more prominently
            const actionButtons = this.querySelectorAll('button, a');
            actionButtons.forEach(button => {
                button.classList.add('shadow-md');
                button.classList.add('scale-105');
                button.classList.add('transform');
                button.classList.add('transition-transform');
            });
        });
        
        row.addEventListener('mouseleave', function() {
            this.classList.remove('bg-gray-50');
            
            // Reset action buttons
            const actionButtons = this.querySelectorAll('button, a');
            actionButtons.forEach(button => {
                button.classList.remove('shadow-md');
                button.classList.remove('scale-105');
                button.classList.remove('transform');
                button.classList.remove('transition-transform');
            });
        });
    });
}

// Export functions for use in other scripts
export {
    initializeTableFilters,
    initializeResponsiveFilters,
    initializeKeyboardShortcuts,
    initializeColumnHighlighting,
    initializeRowHoverEffects
}; 