document.addEventListener("DOMContentLoaded", function () {
   let table = document.getElementById("claimsTable");
   let sortSelect = document.getElementById("sortSelect");
   let sortOrderBtn = document.getElementById("sortOrderBtn");
   let searchInput = document.getElementById("searchInput");
   let sortOrder = "asc";

   searchInput.value = "";

   const statusOrder = [
      "SUBMITTED",
      "APPROVED_ADMIN",
      "APPROVED_DATUK",
      "APPROVED_HR",
      "APPROVED_FINANCE",
      "REJECTED",
      "DONE",
   ];

   // Store the original rows
   let originalRows = Array.from(table.querySelectorAll("tbody tr"));

   sortSelect.addEventListener("change", updateTable);
   sortOrderBtn.addEventListener("click", toggleSortOrder);
   searchInput.addEventListener("input", updateTable);

   function updateTable() {
      // Use a copy of the original rows
      const rows = originalRows.slice();
      const filteredRows = filterRows(rows);
      const sortedRows = sortRows(filteredRows);

      const tbody = table.querySelector("tbody");
      tbody.innerHTML = "";
      sortedRows.forEach((row) => tbody.appendChild(row));
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

   function sortRows(rows) {
      const column = sortSelect.value;
      if (!column) return rows;

      const columnIndex = getColumnIndex(column);

      const sortedRows = rows.sort((a, b) => {
         const aValue = a.cells[columnIndex].textContent.trim();
         const bValue = b.cells[columnIndex].textContent.trim();

         if (column === "status") {
            return compareStatus(aValue, bValue);
         } else if (column === "submitted_at" || column === "date_from" || column === "date_to") {
            return compareDates(aValue, bValue);
         }

         return aValue.localeCompare(bValue, undefined, {
            numeric: true,
            sensitivity: "base",
         });
      });

      return sortOrder === "desc" ? sortedRows.reverse() : sortedRows;
   }

   function toggleSortOrder() {
      sortOrder = sortOrder === "asc" ? "desc" : "asc";
      sortOrderBtn.textContent = sortOrder === "asc" ? "Ascending" : "Descending";
      updateTable();
   }

   function getColumnIndex(columnName) {
      const headers = table.querySelectorAll("th");
      for (let i = 0; i < headers.length; i++) {
         if (headers[i].textContent.trim().toLowerCase() === columnName.replace("_", " ")) {
            return i;
         }
      }
      return -1;
   }

   function compareDates(a, b) {
      const dateA = new Date(a.split("-").reverse().join("-"));
      const dateB = new Date(b.split("-").reverse().join("-"));
      return dateA - dateB;
   }

   function compareStatus(a, b) {
      const aIndex = statusOrder.indexOf(a.toUpperCase().replace(" ", "_"));
      const bIndex = statusOrder.indexOf(b.toUpperCase().replace(" ", "_"));
      return aIndex - bIndex;
   }

   // Initial table update
   updateTable();
});
