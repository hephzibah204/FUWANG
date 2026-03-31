document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Intersection Observer for scroll animations (fade-up elements)
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // only animate once
            }
            $('.submenu-toggle').on('click', function() {
        $(this).parent('.has-submenu').toggleClass('open');
        $(this).find('.fa-chevron-down').toggleClass('fa-chevron-up');
    });
});
    }, observerOptions);

    const fadeElements = document.querySelectorAll('.fade-up');
    fadeElements.forEach(el => observer.observe(el));


    // 2. Mouse tracking for glassmorphism hover glow effect on cards
    const cards = document.querySelectorAll('.service-card');
    
    // On mouse move over the document, we update coordinates relative to each card
    document.addEventListener('mousemove', e => {
        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left; // x position within the element.
            const y = e.clientY - rect.top;  // y position within the element.
            
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });

    // 3. Simple mobile menu toggle functionality
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            // Note: Currently hidden via display:none in css,
            // You can enhance this with a proper slide-out menu style in styles.css later
            if (navLinks.style.display === 'flex') {
                navLinks.style.display = 'none';
                mobileToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
            } else {
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '80px';
                navLinks.style.left = '0';
                navLinks.style.width = '100%';
                navLinks.style.background = 'var(--clr-bg-nav)';
                navLinks.style.padding = '2rem';
                navLinks.style.borderBottom = 'var(--border-glass)';
                mobileToggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            }
        });
    }

    // 4. Navbar scroll background effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(3, 7, 18, 0.95)';
            navbar.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
        } else {
            navbar.style.background = 'var(--clr-bg-nav)';
            navbar.style.boxShadow = 'none';
        }
    });

});
