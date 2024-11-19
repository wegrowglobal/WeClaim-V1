export default class TableSorter {
    constructor() {
        this.table = document.getElementById("claimsTable");
        this.searchInput = document.getElementById("searchInput");
        this.sortDirection = {};
        this.statusPriority = {
            'SUBMITTED': 1,
            'APPROVED_ADMIN': 2,
            'APPROVED_DATUK': 3,
            'APPROVED_HR': 4,
            'APPROVED_FINANCE': 5,
            'REJECTED': 6,
            'DONE': 7
        };
        
        this.init();
    }

    init() {
        if (!this.table || !this.searchInput) return;
        
        // Store original rows
        this.originalRows = Array.from(this.table.querySelectorAll("tbody tr"));
        
        // Add click listeners to all sortable headers
        const headers = this.table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');
                this.sortTable(column);
                this.updateSortIcons(header);
            });
        });

        // Add search listener
        this.searchInput.addEventListener("input", () => this.updateTable());
        
        // Initial table update
        this.updateTable();
    }

    updateTable() {
        const searchTerm = this.searchInput.value.toLowerCase();
        const filteredRows = this.originalRows.filter(row => {
            return row.textContent.toLowerCase().includes(searchTerm);
        });
        
        const tbody = this.table.querySelector("tbody");
        tbody.innerHTML = "";
        filteredRows.forEach(row => tbody.appendChild(row.cloneNode(true)));
    }

    sortTable(column) {
        const tbody = this.table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        const currentDirection = this.sortDirection[column] || 'asc';
        
        rows.sort((a, b) => {
            let aValue = a.querySelector(`td:nth-child(${this.getColumnIndex(column)})`).textContent.trim();
            let bValue = b.querySelector(`td:nth-child(${this.getColumnIndex(column)})`).textContent.trim();
            
            if (column === 'status') {
                aValue = this.statusPriority[aValue] || 999;
                bValue = this.statusPriority[bValue] || 999;
            }
            
            if (currentDirection === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
        
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        
        this.sortDirection[column] = currentDirection === 'asc' ? 'desc' : 'asc';
    }

    getColumnIndex(column) {
        const headers = Array.from(this.table.querySelectorAll('th'));
        return headers.findIndex(header => header.dataset.sort === column) + 1;
    }

    updateSortIcons(clickedHeader) {
        // Add your existing icon update logic here
    }
}

// Add global initialization function
window.initializeTableSorting = function() {
    new TableSorter();
};

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.initializeTableSorting();
});