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

    toggle.addEventListener('click', () => {
        const nextOpen = !sidebar.classList.contains('open');
        sidebar.classList.toggle('open', nextOpen);
        toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
    });
});

