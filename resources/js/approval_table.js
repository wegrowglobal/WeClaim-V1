document.addEventListener("DOMContentLoaded", function () {
   const table = document.getElementById("claimsTable");
   const sortSelect = document.getElementById("sortSelect");
   const sortAsc = document.getElementById("sortAsc");
   const sortDesc = document.getElementById("sortDesc");
   let currentSortColumn = "";
   let isAscending = true;

   function sortTable(column, ascending) {
      const rows = Array.from(table.querySelectorAll("tbody tr"));
      const columnIndex = getColumnIndex(column);

      rows.sort((a, b) => {
         const aValue = a.cells[columnIndex].textContent.trim();
         const bValue = b.cells[columnIndex].textContent.trim();

         if (column === "submitted_at" || column === "date_from" || column === "date_to") {
            return ascending
               ? new Date(aValue) - new Date(bValue)
               : new Date(bValue) - new Date(aValue);
         } else {
            return ascending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
         }
      });

      const tbody = table.querySelector("tbody");
      rows.forEach((row) => tbody.appendChild(row));
   }

   function getColumnIndex(column) {
      switch (column) {
         case "status":
            return 6;
         case "submitted_at":
            return 1;
         case "user":
            return 2;
         case "title":
            return 3;
         case "date_from":
            return 4;
         case "date_to":
            return 5;
         default:
            return 0;
      }
   }

   sortSelect.addEventListener("change", function () {
      currentSortColumn = this.value;
      if (currentSortColumn) {
         sortTable(currentSortColumn, isAscending);
      }
   });

   sortAsc.addEventListener("click", function () {
      isAscending = true;
      if (currentSortColumn) {
         sortTable(currentSortColumn, isAscending);
      }
   });

   sortDesc.addEventListener("click", function () {
      isAscending = false;
      if (currentSortColumn) {
         sortTable(currentSortColumn, isAscending);
      }
   });
});
