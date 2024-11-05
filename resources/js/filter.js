document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("claimsTable");
    const searchInput = document.getElementById("searchInput");
    let sortDirection = {};
    
    // Status priority mapping for sorting
    const statusPriority = {
        'SUBMITTED': 1,
        'APPROVED_ADMIN': 2,
        'APPROVED_DATUK': 3,
        'APPROVED_HR': 4,
        'APPROVED_FINANCE': 5,
        'REJECTED': 6,
        'DONE': 7
    };

    // Store the original rows
    let originalRows = Array.from(table.querySelectorAll("tbody tr"));

    // Add click listeners to all sortable headers
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.style.cursor = 'pointer'; // Make it visually clickable
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-sort');
            sortTable(column);
            updateSortIcons(this);
        });
    });

    searchInput.addEventListener("input", updateTable);

    function updateTable() {
        const rows = originalRows.slice();
        const filteredRows = filterRows(rows);
        
        const tbody = table.querySelector("tbody");
        tbody.innerHTML = "";
        filteredRows.forEach((row) => tbody.appendChild(row));
    }

    function filterRows(rows) {
        const searchTerm = searchInput.value.toLowerCase().trim();

        if (searchTerm === "") {
            return rows;
        }

        return rows.filter((row) => {
            const rowText = Array.from(row.cells)
                .map((cell) => cell.textContent.trim().toLowerCase())
                .join(" ");
            return rowText.includes(searchTerm);
        });
    }

    function sortTable(column) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Toggle sort direction or set initial direction
        sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';

        rows.sort((a, b) => {
            let aValue, bValue;

            switch(column) {
                case 'id':
                    aValue = parseInt(a.cells[0].textContent.trim());
                    bValue = parseInt(b.cells[0].textContent.trim());
                    break;
                case 'status':
                    // Extract status text and convert to priority number
                    aValue = statusPriority[a.querySelector('.status-badge').textContent.trim().toUpperCase().replace(/ /g, '_')] || 0;
                    bValue = statusPriority[b.querySelector('.status-badge').textContent.trim().toUpperCase().replace(/ /g, '_')] || 0;
                    break;
                case 'submitted':
                case 'dateFrom':
                case 'dateTo':
                    // Convert DD-MM-YYYY to Date object
                    const cellIndex = column === 'submitted' ? 1 : (column === 'dateFrom' ? 4 : 5);
                    aValue = parseDate(a.cells[cellIndex].textContent.trim());
                    bValue = parseDate(b.cells[cellIndex].textContent.trim());
                    break;
                case 'user':
                    // Extract user name from the cell
                    aValue = a.cells[2].textContent.trim().toLowerCase();
                    bValue = b.cells[2].textContent.trim().toLowerCase();
                    break;
                default:
                    aValue = a.cells[getColumnIndex(column)].textContent.trim().toLowerCase();
                    bValue = b.cells[getColumnIndex(column)].textContent.trim().toLowerCase();
            }

            // Compare values based on sort direction
            if (sortDirection[column] === 'asc') {
                return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
            } else {
                return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
            }
        });

        // Clear and re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    }

    function parseDate(dateStr) {
        const [day, month, year] = dateStr.split('-');
        return new Date(year, month - 1, day);
    }

    function updateSortIcons(clickedHeader) {
        // Reset all icons
        headers.forEach(header => {
            const icon = header.querySelector('i');
            icon.className = 'fas fa-sort';
        });

        // Update clicked column icon
        const icon = clickedHeader.querySelector('i');
        icon.className = `fas fa-sort-${sortDirection[clickedHeader.getAttribute('data-sort')] === 'asc' ? 'up' : 'down'}`;
    }

    function getColumnIndex(column) {
        const columnMap = {
            'id': 0,
            'submitted': 1,
            'user': 2,
            'title': 3,
            'dateFrom': 4,
            'dateTo': 5,
            'status': 6
        };
        return columnMap[column] || 0;
    }

    // Initial table update
    updateTable();
});