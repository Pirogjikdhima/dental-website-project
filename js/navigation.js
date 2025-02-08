document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    navToggle.addEventListener('click', function() {
        navMenu.classList.toggle('active');

        document.addEventListener('click', function(event) {
            if (!event.target.closest('#navigation')) {
                navMenu.classList.remove('active');
            }
        });
    });
});