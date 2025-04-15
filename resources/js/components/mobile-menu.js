document.addEventListener('DOMContentLoaded', function() {
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.body;

    hamburgerMenu.addEventListener('click', function() {
        // Get current scroll position
        const scrollY = window.scrollY;
        
        // Position the menu at current scroll position
        mobileMenu.style.top = `${scrollY}px`;
        mobileMenu.classList.toggle('hidden');
        
        // Toggle fixed position on body to prevent background scrolling
        if (!mobileMenu.classList.contains('hidden')) {
            body.style.position = 'fixed';
            body.style.top = `-${scrollY}px`;
            body.style.width = '100%';
        } else {
            const scrollY = parseInt(body.style.top || '0') * -1;
            body.style.position = '';
            body.style.top = '';
            body.style.width = '';
            window.scrollTo(0, scrollY);
        }
    });

    const closeMobileMenu = document.getElementById('close-mobile-menu');
    closeMobileMenu.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
        
        // Restore scroll position when closing
        const scrollY = parseInt(body.style.top || '0') * -1;
        body.style.position = '';
        body.style.top = '';
        body.style.width = '';
        window.scrollTo(0, scrollY);
    });
});