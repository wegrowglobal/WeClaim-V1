document.addEventListener('DOMContentLoaded', function() {
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.body;

    hamburgerMenu.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
        // Toggle no-scroll class on body
        body.classList.toggle('overflow-hidden');
    });

    const closeMobileMenu = document.getElementById('close-mobile-menu');
    closeMobileMenu.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
        // Remove no-scroll class when closing
        body.classList.remove('overflow-hidden');
    });
});