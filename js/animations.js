document.addEventListener('DOMContentLoaded', function () {
    const sections = document.querySelectorAll('.section');
    let lastScrollTop = 0;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const isScrollingDown = currentScrollTop > lastScrollTop;

            if (entry.isIntersecting && isScrollingDown) {
                entry.target.classList.add('animate-down');
                observer.unobserve(entry.target);
            }

            lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
        });
    }, {
        threshold: 0.1
    });

    sections.forEach(section => {
        observer.observe(section);
    });
});