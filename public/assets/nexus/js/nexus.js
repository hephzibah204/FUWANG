document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const minimizeSidebarBtn = document.getElementById('minimizeSidebarBtn');
    const isMobileViewport = () => window.matchMedia('(max-width: 991.98px)').matches;
    const getSavedMinimized = () => {
        try {
            return localStorage.getItem('sidebar-minimized') === 'true';
        } catch (e) {
            return false;
        }
    };
    const setSavedMinimized = (value) => {
        try {
            localStorage.setItem('sidebar-minimized', value ? 'true' : 'false');
        } catch (e) {
            // ignore storage failure
        }
    };
    const syncSidebarViewportState = () => {
        if (!sidebar) {
            return;
        }
        // On mobile, force expanded drawer behavior (no minimized rail state).
        if (isMobileViewport()) {
            sidebar.classList.remove('minimized');
            return;
        }
        sidebar.classList.toggle('minimized', getSavedMinimized());
    };

    // Dashboard sidebar toggle (works for both user and admin layouts)
    if (sidebarToggleBtn && sidebar) {
        window.__NEXUS_SIDEBAR_HANDLED = true;
        sidebarToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            syncSidebarViewportState();
            const isOpen = sidebar.classList.toggle('open');
            sidebarToggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth >= 992) {
                return;
            }
            const target = e.target;
            if (!(target instanceof Element)) {
                return;
            }
            if (sidebar.classList.contains('open') && !sidebar.contains(target) && !sidebarToggleBtn.contains(target)) {
                sidebar.classList.remove('open');
                sidebarToggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Desktop collapse toggle fallback in external JS
    if (minimizeSidebarBtn && sidebar) {
        window.__NEXUS_SIDEBAR_HANDLED = true;
        const collapseIcon = minimizeSidebarBtn.querySelector('.collapse-icon');
        const applyMinimizedState = (isMinimized) => {
            if (!isMobileViewport()) {
                sidebar.classList.toggle('minimized', isMinimized);
            }
            if (collapseIcon) {
                collapseIcon.style.transform = isMinimized ? 'rotate(180deg)' : 'rotate(0)';
            }
        };

        applyMinimizedState(getSavedMinimized());

        minimizeSidebarBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isMinimized = !sidebar.classList.contains('minimized');
            applyMinimizedState(isMinimized);
            setSavedMinimized(isMinimized);
        });
    }

    if (sidebar) {
        syncSidebarViewportState();
        window.addEventListener('resize', syncSidebarViewportState);
    }
    
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
            if (!navLinks) {
                return;
            }
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
        if (!navbar) {
            return;
        }
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(3, 7, 18, 0.95)';
            navbar.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
        } else {
            navbar.style.background = 'var(--clr-bg-nav)';
            navbar.style.boxShadow = 'none';
        }
    });

});
