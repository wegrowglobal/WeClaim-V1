document.addEventListener("DOMContentLoaded", function () {
    let table = document.getElementById("claimsTable");
    let searchInput = document.getElementById("searchInput");
 
    // Store the original rows
    let originalRows = Array.from(table.querySelectorAll("tbody tr"));
 
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
          return rows; // Return all rows if search is empty
       }
 
       return rows.filter((row) => {
          const rowText = Array.from(row.cells)
             .map((cell) => cell.textContent.trim().toLowerCase())
             .join(" ");
          return rowText.includes(searchTerm);
       });
    }
 
    // Initial table update
    updateTable();
 });