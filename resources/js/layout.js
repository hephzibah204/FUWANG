document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    if (!toggle) {
        return;
    }

    const targetId = toggle.getAttribute('aria-controls') || 'sidebar';
    const sidebar = document.getElementById(targetId);
    if (!sidebar) {
        return;
    }

    toggle.addEventListener('click', (e) => {
        if (typeof window.__toggleNexusSidebar === 'function') {
            window.__toggleNexusSidebar(e);
            return;
        }
        const now = Date.now();
        if (window.__lastSidebarToggleTs && (now - window.__lastSidebarToggleTs) < 300) {
            return;
        }
        window.__lastSidebarToggleTs = now;

        const nextOpen = !sidebar.classList.contains('open');
        sidebar.classList.toggle('open', nextOpen);
        toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
    });
});

