(function () {
  if (typeof window === 'undefined' || window.csrfFetch) return;

  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  window.csrfFetch = async function csrfFetch(url, options) {
    const opts = options || {};
    const method = (opts.method || 'POST').toUpperCase();
    const timeoutMs = Number.isFinite(opts.timeoutMs) ? opts.timeoutMs : 60000;

    const headers = Object.assign({}, opts.headers || {});
    if (!headers.Accept) headers.Accept = 'application/json';

    const token = getCsrfToken();
    if (token && !headers['X-CSRF-TOKEN']) headers['X-CSRF-TOKEN'] = token;

    let body = opts.body;
    if (opts.data !== undefined) {
      if (!headers['Content-Type']) headers['Content-Type'] = 'application/json';
      body = JSON.stringify(opts.data);
    }

    const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
    const timer = controller ? setTimeout(() => controller.abort(), timeoutMs) : null;

    try {
      const res = await fetch(url, {
        method,
        headers,
        body,
        credentials: 'same-origin',
        signal: controller ? controller.signal : undefined
      });

      const text = await res.text();
      let json = null;
      try {
        json = text ? JSON.parse(text) : null;
      } catch (e) {
        json = null;
      }

      if (!res.ok) {
        const err = new Error((json && json.message) ? json.message : 'Request failed (' + res.status + ')');
        err.status = res.status;
        err.payload = json;
        throw err;
      }

      return json;
    } finally {
      if (timer) clearTimeout(timer);
    }
  };
})();

