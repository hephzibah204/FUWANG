(() => {
    'use strict';
    const root = document.querySelector('[data-vtu-purchase]');
    if (!root) return;
    const form = root.querySelector('form');
    const fieldset = root.querySelector('[data-fields]');
    const submit = root.querySelector('[data-submit]');
    const state = root.querySelector('[data-state]');
    const band = form.elements.provider_id;
    const network = form.elements.network;
    const service = form.elements.serviceID;
    const plan = root.querySelector('#vtu-plan');
    const amount = form.elements.amount;
    const result = root.querySelector('[data-result]');
    const money = value => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(Number(value || 0));
    let products = [], selected = null, quote = null, busy = false, verified = '', catalogVersion = 0, revision = 0, quoteTimer;
    const csrf = form.elements._token.value;
    function notice(message, kind = 'info') { state.textContent = message; state.className = `alert alert-${kind}`; }
    function payload() { return Object.fromEntries(new FormData(form)); }
    async function api(url, data) {
        const response = await fetch(url, {
            method: data ? 'POST' : 'GET', credentials: 'same-origin',
            headers: { Accept: 'application/json', ...(data ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf } : {}) },
            ...(data ? { body: JSON.stringify(data) } : {})
        });
        let body;
        try { body = await response.json(); } catch { throw new Error('The service returned an unreadable response. Check your transaction history before retrying a payment.'); }
        if (!response.ok) { const error = new Error(body.message || Object.values(body.errors || {}).flat()[0] || 'Request could not be completed.'); error.body = body; throw error; }
        return body;
    }
    function newReference() {
        form.elements.request_id.value = globalThis.crypto?.randomUUID?.() || `VTU-${Date.now()}-${Math.random().toString(36).slice(2)}`;
        sessionStorage.removeItem(`vtu-pending-${root.dataset.type}`);
    }
    function resetQuote() {
        quote = null;
        for (const key of ['amount', 'discount', 'fee', 'total']) root.querySelector(`[data-summary-${key}]`).textContent = '—';
        root.querySelector('[data-quote-note]').textContent = 'Complete the form to receive an exact quote.';
    }
    function verificationKey() { return [band.value, service?.value, form.elements.variation_code?.value, root.querySelector('#vtu-customer')?.value].join('|'); }
    function invalidateCustomer() {
        verified = ''; form.elements.validation_token.value = '';
        const box = root.querySelector('[data-customer-result]'); if (box) box.textContent = '';
    }
    function ready() {
        return !busy && fieldset.disabled === false && (!plan || !!selected)
            && (!root.dataset.verify || verified === verificationKey()) && form.checkValidity();
    }
    async function refreshQuote() {
        const version = ++revision;
        resetQuote();
        if (!ready()) return;
        root.querySelector('[data-quote-note]').textContent = 'Calculating your total…';
        try {
            const response = await api(root.dataset.quote, payload());
            if (version !== revision || busy) return;
            quote = response.breakdown;
            for (const key of ['amount', 'discount', 'fee', 'total']) root.querySelector(`[data-summary-${key}]`).textContent = money(quote[key]);
            root.querySelector('[data-quote-note]').textContent = 'Confirmed by the server. Review again before payment.';
        } catch (error) { if (version === revision) root.querySelector('[data-quote-note]').textContent = error.message; }
    }
    function scheduleQuote() { ++revision; resetQuote(); clearTimeout(quoteTimer); quoteTimer = setTimeout(refreshQuote, 350); }
    function populatePlans() {
        if (!plan) return;
        selected = null; amount.value = ''; resetQuote();
        const q = (root.querySelector('#vtu-search')?.value || '').toLowerCase();
        const budgetValue = root.querySelector('#vtu-budget')?.value || '';
        const budget = budgetValue === '' ? Infinity : Number(budgetValue);
        const available = products.filter(p => (!network || (network.value && String(p.network).toUpperCase() === network.value))
            && (!service || (service.value && p.service_id === service.value))
            && `${p.label} ${p.validity || ''}`.toLowerCase().includes(q) && Number(p.amount) <= budget);
        plan.replaceChildren(new Option(available.length ? 'Choose a package' : 'No matching packages available', ''));
        available.forEach((p, index) => { const option = new Option(`${p.label}${p.validity ? ` · ${p.validity}` : ''} — ${money(p.amount)}`, String(p.code)); option.dataset.product = String(products.indexOf(p)); plan.add(option); });
        plan.disabled = !available.length;
        root.querySelector('[data-plan-help]').textContent = available.length ? `${available.length} package(s) available. Select a bundle to see its price.` : 'Choose a network/service or adjust your search. If none are available, try another band.';
        root.querySelector('[data-selection]').textContent = 'No package selected.';
    }
    async function loadCatalog() {
        const version = ++catalogVersion;
        products = []; selected = null; if (plan) { plan.replaceChildren(new Option('Loading packages…', '')); plan.disabled = true; amount.value = ''; }
        invalidateCustomer(); resetQuote();
        try {
            const response = await api(`${root.dataset.catalog}?provider_id=${encodeURIComponent(band.value)}`);
            if (version !== catalogVersion) return;
            products = response.products || [];
            if (service && !['vtu_cable_tv', 'vtu_electricity'].includes(root.dataset.type)) {
                const previous = service.value;
                service.replaceChildren(new Option('Choose a service', ''));
                [...new Set(products.map(p => p.service_id).filter(Boolean))].forEach(code => service.add(new Option(code.replaceAll('-', ' ').toUpperCase(), code)));
                if ([...service.options].some(o => o.value === previous)) service.value = previous;
            }
            populatePlans();
            notice(plan && !products.length ? 'No packages are configured for this band. Choose another band or contact support.' : 'Select your service and complete the details below.');
        } catch (error) { if (version === catalogVersion) notice(error.message, 'danger'); }
    }
    function showOutcome(response) {
        result.hidden = false; result.replaceChildren();
        const heading = document.createElement('h2'); heading.className = 'h5'; heading.textContent = response.pending ? 'Awaiting confirmation' : (response.status ? 'Purchase completed' : 'Purchase failed'); result.append(heading);
        const message = document.createElement('p'); message.textContent = response.message; result.append(message);
        if (response.reference) { const reference = document.createElement('p'); reference.style.overflowWrap = 'anywhere'; reference.textContent = `Reference: ${response.reference}`; result.append(reference); }
        const data = response.data || {};
        const token = response.token || data.token || data.data?.token;
        const pins = response.pin || data.pins || data.pin || data.data?.pins || data.data?.pin || data.TXN_EPIN || data.TXN_EPIN_DATABUNDLE;
        for (const [label, value] of [['Electricity token', token], ['Purchased PINs', pins]]) {
            if (!value) continue;
            const title = document.createElement('strong'); title.textContent = label; result.append(title);
            const pre = document.createElement('pre'); pre.style.whiteSpace = 'pre-wrap'; pre.style.overflowWrap = 'anywhere'; pre.textContent = typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value); result.append(pre);
            const copy = document.createElement('button'); copy.type = 'button'; copy.className = 'btn btn-sm btn-outline-primary mb-3'; copy.textContent = 'Copy'; copy.onclick = async () => { try { await navigator.clipboard.writeText(pre.textContent); copy.textContent = 'Copied'; } catch { copy.textContent = 'Select the text to copy'; } }; result.append(copy);
        }
        if (pins) { const print = document.createElement('button'); print.type = 'button'; print.className = 'btn btn-outline-primary mb-2'; print.textContent = 'Print PINs'; print.onclick = () => window.print(); result.append(print); }
        const link = document.createElement('a'); link.href = root.dataset.history; link.textContent = 'View transaction history'; link.className = 'd-block mt-2'; result.append(link);
        if (response.pending) {
            const check = document.createElement('button'); check.type = 'button'; check.className = 'btn btn-outline-primary mt-3'; check.textContent = 'Check status';
            check.onclick = async () => { check.disabled = true; try { const updated = await api(`${root.dataset.status}?order_id=${encodeURIComponent(response.reference)}`); showOutcome(updated); if (!updated.pending) { busy = false; newReference(); fieldset.disabled = false; submit.textContent = 'Review purchase'; } } catch (error) { notice(error.message, 'warning'); } finally { check.disabled = false; } };
            result.append(check);
        }
        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    band.addEventListener('change', loadCatalog);
    network?.addEventListener('change', populatePlans);
    service?.addEventListener('change', () => { invalidateCustomer(); populatePlans(); });
    for (const input of root.querySelectorAll('#vtu-search, #vtu-budget')) input.addEventListener('input', populatePlans);
    plan?.addEventListener('change', () => {
        const index = plan.selectedOptions[0]?.dataset.product;
        selected = index === undefined ? null : products[Number(index)];
        amount.value = selected ? selected.amount : '';
        root.querySelector('[data-selection]').textContent = selected ? `${selected.label}${selected.validity ? ` · ${selected.validity}` : ''}` : 'No package selected.';
    });
    form.addEventListener('input', event => {
        if (['serviceID', 'meter_number', 'smart_card_number'].includes(event.target.name) || (event.target.name === 'variation_code' && root.dataset.type === 'vtu_electricity')) invalidateCustomer();
        scheduleQuote();
    });
    form.addEventListener('change', scheduleQuote);
    root.querySelectorAll('[data-amount]').forEach(button => button.addEventListener('click', () => { amount.value = button.dataset.amount; scheduleQuote(); }));
    root.querySelector('[data-verify-customer]')?.addEventListener('click', async event => {
        const button = event.currentTarget, key = verificationKey();
        if (!service.value || !root.querySelector('#vtu-customer').value.trim()) { notice('Select a service and enter the customer number first.', 'warning'); return; }
        button.disabled = true; button.textContent = 'Verifying…'; invalidateCustomer();
        try {
            const response = await api(root.dataset.verify, payload());
            if (key !== verificationKey()) return;
            if (!response.status) throw new Error(response.message);
            verified = key; form.elements.validation_token.value = response.validation_token || '';
            root.querySelector('[data-customer-result]').textContent = [response.customer?.name, response.customer?.address].filter(Boolean).join(' · ');
            notice('Customer verified. Check the name before paying.', 'success'); scheduleQuote();
        } catch (error) { notice(error.message, 'danger'); } finally { button.disabled = false; button.textContent = 'Verify customer'; }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy) return;
        if (!form.reportValidity()) return;
        if (!ready()) { notice(root.dataset.verify ? 'Verify the customer before payment.' : 'Select an available package first.', 'warning'); return; }
        busy = true; submit.disabled = true; submit.textContent = 'Calculating total…';
        const submitted = payload();
        try {
            const response = await api(root.dataset.quote, submitted);
            const breakdown = response.breakdown;
            const details = `${selected?.label || root.querySelector('[data-selection]').textContent}\nRecipient: ${submitted.phone || submitted.meter_number || submitted.smart_card_number || submitted.customer_id || submitted.recipients || submitted.network}\nAmount: ${money(breakdown.amount)}\nDiscount: ${money(breakdown.discount)}\nFee: ${money(breakdown.fee)}\nTotal ${breakdown.direction === 'credit' ? 'expected credit' : 'debit'}: ${money(breakdown.total)}`;
            fieldset.disabled = true;
            const confirmed = window.Swal ? (await Swal.fire({ title: 'Confirm purchase', text: details, icon: 'question', showCancelButton: true, confirmButtonText: breakdown.direction === 'credit' ? 'Submit conversion' : `Pay ${money(breakdown.total)}` })).isConfirmed : window.confirm(details);
            if (!confirmed) { fieldset.disabled = false; return; }
            submit.textContent = 'Processing purchase…';
            sessionStorage.setItem(`vtu-pending-${root.dataset.type}`, JSON.stringify({ reference: submitted.request_id }));
            let outcome;
            try { outcome = await api(root.dataset.action, submitted); }
            catch (error) {
                outcome = error.body || { status: false, pending: true, reference: submitted.request_id, message: 'Connection interrupted. Check this reference before making another purchase.' };
            }
            showOutcome(outcome);
            if (outcome.pending) { notice('Purchase awaiting confirmation. Use Check status; do not make another payment.', 'warning'); return; }
            newReference(); fieldset.disabled = false;
            if (outcome.status) { notice('Purchase completed. Your receipt is shown below.', 'success'); }
            else notice(outcome.message || 'The purchase could not be completed.', 'danger');
        } catch (error) { notice(error.message, 'danger'); fieldset.disabled = false; }
        finally { busy = fieldset.disabled; submit.disabled = false; submit.textContent = busy ? 'Awaiting confirmation' : 'Review purchase'; }
    });
    (async () => {
        try {
            const response = await api(root.dataset.providers);
            const providers = response.providers || [];
            band.replaceChildren();
            providers.forEach(p => band.add(new Option(p.name, String(p.id))));
            if (!providers.length) { notice('This service is currently unavailable. No payment has been taken.', 'warning'); return; }
            band.required = providers.some(p => String(p.id) !== '');
            fieldset.disabled = false;
            await loadCatalog();
            const saved = sessionStorage.getItem(`vtu-pending-${root.dataset.type}`);
            if (saved) { const pending = JSON.parse(saved); const outcome = await api(`${root.dataset.status}?order_id=${encodeURIComponent(pending.reference)}`); showOutcome(outcome); if (outcome.pending) { busy = true; fieldset.disabled = true; } else newReference(); }
        } catch (error) { notice(error.message, 'danger'); }
    })();
})();
