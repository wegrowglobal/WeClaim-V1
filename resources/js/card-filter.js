document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("cardSearchInput");
    const statusButtons = document.querySelectorAll('.status-filter-btn');
    const claimsGrid = document.getElementById("claimsGrid");
    let selectedStatuses = ['all'];
    
    // Store original cards
    const originalCards = Array.from(claimsGrid.children);

    function updateCards() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        const filteredCards = originalCards.filter(card => {
            const cardText = card.textContent.toLowerCase();
            const statusBadge = card.querySelector('.status-badge');
            const statusText = statusBadge.querySelector('span').textContent.trim();
            
            // Convert status text to match the Claim model format
            let cardStatus;
            if (statusText.startsWith('Approved')) {
                cardStatus = 'Approved ' + statusText.split(' ')[1];
            } else {
                cardStatus = statusText;
            }
            
            const matchesSearch = searchTerm === '' || cardText.includes(searchTerm);
            const matchesStatus = selectedStatuses.includes('all') || selectedStatuses.includes(cardStatus);

            return matchesSearch && matchesStatus;
        });

        claimsGrid.innerHTML = '';
        filteredCards.forEach(card => claimsGrid.appendChild(card.cloneNode(true)));
    }

    window.toggleStatusFilter = function(button, status) {
        if (status === 'all') {
            selectedStatuses = ['all'];
            statusButtons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.status === 'all') {
                    btn.classList.add('active');
                }
            });
        } else {
            const allButton = document.querySelector('.status-filter-btn[data-status="all"]');
            allButton.classList.remove('active');
            
            if (button.classList.contains('active')) {
                button.classList.remove('active');
                selectedStatuses = selectedStatuses.filter(s => s !== status);
                if (selectedStatuses.length === 0) {
                    selectedStatuses = ['all'];
                    allButton.classList.add('active');
                }
            } else {
                button.classList.add('active');
                selectedStatuses = selectedStatuses.filter(s => s !== 'all');
                selectedStatuses.push(status);
            }
        }
        updateCards();
    }

    // Add event listeners
    searchInput.addEventListener("input", updateCards);
}); 