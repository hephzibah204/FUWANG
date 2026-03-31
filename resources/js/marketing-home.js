document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('mkHome');
    if (!root) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const abUrl = root.getAttribute('data-ab-event-url') || '';
    const experiment = root.getAttribute('data-ab-experiment') || 'home_hero';
    const variant = root.getAttribute('data-ab-variant') || 'A';

    function getSessionId() {
        const k = 'mk_sid_v1';
        let v = localStorage.getItem(k);
        if (!v) {
            v = (crypto?.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2));
            localStorage.setItem(k, v);
        }
        return v;
    }

    function postEvent(eventName, meta) {
        if (!abUrl) {
            return;
        }

        const payload = {
            event_name: eventName,
            page: window.location.pathname,
            experiment,
            variant,
            session_id: getSessionId(),
            meta: meta || {},
        };

        try {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            if (navigator.sendBeacon) {
                navigator.sendBeacon(abUrl, blob);
                return;
            }
        } catch {
        }

        fetch(abUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload),
            keepalive: true,
        }).catch(() => {
        });
    }

    function tabPanelId(name) {
        return 'mkTab' + name.charAt(0).toUpperCase() + name.slice(1);
    }

    function setTab(name) {
        document.querySelectorAll('.mk-tab').forEach((t) => {
            const active = t.getAttribute('data-tab') === name;
            t.classList.toggle('active', active);
            t.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        const activeId = tabPanelId(name);
        document.querySelectorAll('.mk-panel').forEach((p) => p.classList.toggle('active', p.id === activeId));
        postEvent('demo_tab_change', { tab: name });
    }

    document.querySelectorAll('.mk-tab').forEach((btn) => {
        btn.addEventListener('click', () => setTab(btn.getAttribute('data-tab') || 'nin'));
    });

    document.querySelectorAll('[data-cta]').forEach((el) => {
        el.addEventListener('click', () => postEvent('cta_click', { cta: el.getAttribute('data-cta') }));
    });

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-copy') || '';
            const text = document.getElementById(id)?.innerText || '';
            try {
                await navigator.clipboard.writeText(text);
                postEvent('copy_snippet', { id });
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Copied',
                        text: 'Snippet copied to clipboard.',
                        background: '#141826',
                        color: '#fff',
                        timer: 1200,
                        showConfirmButton: false,
                    });
                }
            } catch {
                postEvent('copy_snippet_failed', { id });
            }
        });
    });

    postEvent('page_view', { ref: document.referrer || null });

    try {
        let clsValue = 0;
        const clsObs = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            }
        });
        clsObs.observe({ type: 'layout-shift', buffered: true });

        const lcpObs = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const last = entries[entries.length - 1];
            if (last) {
                postEvent('web_vitals', { lcp_ms: Math.round(last.startTime) });
            }
        });
        lcpObs.observe({ type: 'largest-contentful-paint', buffered: true });

        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                postEvent('web_vitals', { cls: Number(clsValue.toFixed(4)) });
            }
        });
    } catch {
    }
});

