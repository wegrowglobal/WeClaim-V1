function setView(view) {
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    const tableBtn = document.getElementById('tableViewBtn');
    const gridBtn = document.getElementById('gridViewBtn');

    if (view === 'table') {
        tableView.classList.remove('hidden');
        gridView.classList.add('hidden');
        tableBtn.classList.add('active');
        gridBtn.classList.remove('active');
        localStorage.setItem('claimsView', 'table');
    } else {
        gridView.classList.remove('hidden');
        tableView.classList.add('hidden');
        gridBtn.classList.add('active');
        tableBtn.classList.remove('active');
        localStorage.setItem('claimsView', 'grid');
    }
}

// Set initial view based on localStorage or default to table
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('claimsView') || 'table';
    setView(savedView);
});

// Make setView function globally available
window.setView = setView; 