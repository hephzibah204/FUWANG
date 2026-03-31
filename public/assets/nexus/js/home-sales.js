document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('[data-home-more]');
    const panel = document.getElementById('homeMoreServices');
    if (!btn || !panel) {
        return;
    }

    function setExpanded(expanded) {
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        panel.hidden = !expanded;
        if (expanded) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    btn.addEventListener('click', () => {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        setExpanded(!expanded);
    });
});

