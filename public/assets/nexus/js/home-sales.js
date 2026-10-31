/**
 * Hallmark Interactive Engine - Fuwa.NG
 * Provides high-craft, real-time client utility:
 * 1. Identity Verification Inspector (NIN, BVN, CAC with Slip Preview)
 * 2. Logistics Route & Rate Estimator
 * 3. Developer API Code Switcher & 1-Click Clipboard
 */

document.addEventListener('DOMContentLoaded', () => {
    initHeroInspector();
    initLogisticsCalculator();
    initDeveloperConsole();
});

/* ============================================================
 * 1. HERO IDENTITY VERIFICATION INSPECTOR
 * ============================================================ */
function initHeroInspector() {
    const tabs = document.querySelectorAll('[data-inspector-tab]');
    const input = document.getElementById('inspectorInput');
    const runBtn = document.getElementById('inspectorRunBtn');
    const demoPills = document.querySelectorAll('[data-demo-val]');
    const slipTabs = document.querySelectorAll('[data-slip-tier]');
    
    // Result fields
    const resStatus = document.getElementById('resStatus');
    const resRef = document.getElementById('resRef');
    const resLatency = document.getElementById('resLatency');
    const resName = document.getElementById('resName');
    const resIdMasked = document.getElementById('resIdMasked');
    const resDob = document.getElementById('resDob');
    const resPhone = document.getElementById('resPhone');
    const slipTierBadge = document.getElementById('slipTierBadge');
    const slipName = document.getElementById('slipName');
    const slipNin = document.getElementById('slipNin');

    let currentTab = 'nin';
    let currentTier = 'premium';

    const tabConfigs = {
        nin: {
            placeholder: 'Enter 11-digit NIN (e.g. 54928103947)',
            defaultVal: '54928103947',
            name: 'ADEBAYO OLUMIDE IBRAHIM',
            dob: '14 AUG 1992',
            phone: '0803 ••• ••84',
            refPrefix: 'NMC',
            masked: '5492 ••• ••47'
        },
        bvn: {
            placeholder: 'Enter 11-digit BVN (e.g. 22198471029)',
            defaultVal: '22198471029',
            name: 'CHUKWUDI EMMANUEL OKEKE',
            dob: '28 NOV 1989',
            phone: '0812 ••• ••19',
            refPrefix: 'NIB',
            masked: '2219 ••• ••29'
        },
        cac: {
            placeholder: 'Enter CAC RC Number (e.g. RC-1849204)',
            defaultVal: 'RC-1849204',
            name: 'FUWA LOGISTICS & VERIFICATION LTD',
            dob: 'INC: 12 JAN 2019',
            phone: 'STATUS: ACTIVE',
            refPrefix: 'CAC',
            masked: 'RC-1849204'
        }
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentTab = tab.getAttribute('data-inspector-tab');
            const cfg = tabConfigs[currentTab];
            if (input && cfg) {
                input.placeholder = cfg.placeholder;
                input.value = cfg.defaultVal;
                simulateVerification(false);
            }
        });
    });

    demoPills.forEach(pill => {
        pill.addEventListener('click', () => {
            const val = pill.getAttribute('data-demo-val');
            const targetTab = pill.getAttribute('data-demo-tab');
            if (targetTab) {
                const targetBtn = document.querySelector(`[data-inspector-tab="${targetTab}"]`);
                if (targetBtn) targetBtn.click();
            }
            if (input) {
                input.value = val;
                simulateVerification(true);
            }
        });
    });

    if (runBtn) {
        runBtn.addEventListener('click', () => simulateVerification(true));
    }

    if (input) {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                simulateVerification(true);
            }
        });
    }

    function simulateVerification(withDelay = false) {
        const cfg = tabConfigs[currentTab];
        if (!cfg) return;

        if (withDelay && resStatus) {
            resStatus.textContent = 'QUERYING GATEWAY...';
            resStatus.style.color = 'var(--hm-cyan)';
            if (runBtn) runBtn.disabled = true;

            setTimeout(() => {
                applyResult(cfg);
                if (runBtn) runBtn.disabled = false;
            }, 320);
        } else {
            applyResult(cfg);
        }
    }

    function applyResult(cfg) {
        const randomLatency = Math.floor(260 + Math.random() * 90);
        const randomSerial = Math.floor(10000 + Math.random() * 90000);

        if (resStatus) {
            resStatus.textContent = 'ACTIVE_VERIFIED';
            resStatus.style.color = 'var(--hm-emerald)';
        }
        if (resRef) resRef.textContent = `${cfg.refPrefix}-${randomSerial}`;
        if (resLatency) resLatency.textContent = `${randomLatency}ms`;
        if (resName) resName.textContent = cfg.name;
        if (resIdMasked) resIdMasked.textContent = cfg.masked;
        if (resDob) resDob.textContent = cfg.dob;
        if (resPhone) resPhone.textContent = cfg.phone;

        if (slipName) slipName.textContent = cfg.name;
        if (slipNin) slipNin.textContent = cfg.masked;
    }

    // Slip Tier Switcher
    slipTabs.forEach(sTab => {
        sTab.addEventListener('click', () => {
            slipTabs.forEach(t => t.classList.remove('active'));
            sTab.classList.add('active');
            currentTier = sTab.getAttribute('data-slip-tier');
            if (slipTierBadge) {
                slipTierBadge.textContent = `${currentTier.toUpperCase()} SLIP`;
            }
        });
    });
}

/* ============================================================
 * 2. INTERACTIVE LOGISTICS RATE & ROUTE ENGINE
 * ============================================================ */
function initLogisticsCalculator() {
    const originSel = document.getElementById('calcOrigin');
    const destSel = document.getElementById('calcDestination');
    const weightInput = document.getElementById('calcWeight');
    const speedBtns = document.querySelectorAll('[data-speed-mult]');

    const outBase = document.getElementById('calcOutBase');
    const outWeight = document.getElementById('calcOutWeight');
    const outHandling = document.getElementById('calcOutHandling');
    const outTotal = document.getElementById('calcOutTotal');
    const outEta = document.getElementById('calcOutEta');

    let currentMultiplier = 1.0;
    let currentEtaText = '24 - 48 hours';

    speedBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            speedBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentMultiplier = parseFloat(btn.getAttribute('data-speed-mult') || '1.0');
            currentEtaText = btn.getAttribute('data-eta') || '24 - 48 hours';
            computeRates();
        });
    });

    if (originSel) originSel.addEventListener('change', computeRates);
    if (destSel) destSel.addEventListener('change', computeRates);
    if (weightInput) weightInput.addEventListener('input', computeRates);

    function computeRates() {
        if (!originSel || !destSel || !weightInput) return;

        const origin = originSel.value;
        const dest = destSel.value;
        const weight = Math.max(0.5, parseFloat(weightInput.value) || 1.0);

        // Realistic regional base matrix
        let baseRate = 2200;
        if (origin === dest) {
            baseRate = 1200; // Intra-city
        } else if (
            (origin.includes('lagos') && dest.includes('lagos')) ||
            (origin === 'ibadan' && dest.includes('lagos')) ||
            (origin.includes('lagos') && dest === 'ibadan')
        ) {
            baseRate = 1600; // South-West regional
        } else if (
            (origin.includes('lagos') && dest === 'abuja') ||
            (origin === 'abuja' && dest.includes('lagos'))
        ) {
            baseRate = 2600; // Major Metro Trunk
        } else {
            baseRate = 3200; // Inter-state long haul
        }

        const adjustedBase = Math.round(baseRate * currentMultiplier);
        
        // Weight surcharge: ₦450 per kg beyond the 1st kg
        const extraKg = Math.max(0, weight - 1.0);
        const weightCharge = Math.round(extraKg * 450);

        // Fixed security handling & waybill QR label fee
        const handlingFee = 250;

        const total = adjustedBase + weightCharge + handlingFee;

        if (outBase) outBase.textContent = `₦${adjustedBase.toLocaleString('en-NG')}`;
        if (outWeight) outWeight.textContent = `₦${weightCharge.toLocaleString('en-NG')}`;
        if (outHandling) outHandling.textContent = `₦${handlingFee.toLocaleString('en-NG')}`;
        if (outTotal) outTotal.textContent = `₦${total.toLocaleString('en-NG')}`;
        if (outEta) outEta.textContent = `Estimated delivery: ${currentEtaText}`;
    }

    // Initialize first calculation
    computeRates();
}

/* ============================================================
 * 3. DEVELOPER API CONSOLE & 1-CLICK CLIPBOARD
 * ============================================================ */
function initDeveloperConsole() {
    const langBtns = document.querySelectorAll('[data-api-lang]');
    const codeBlock = document.getElementById('apiCodeBlock');
    const copyBtn = document.getElementById('apiCopyBtn');

    const snippets = {
        curl: `<span class="token-method">curl</span> -X POST <span class="token-url">"https://api.fuwa.ng/v1/kyc/nin/verify"</span> \\
  -H <span class="token-header">"Authorization: Bearer fk_live_948201a8f9024c"</span> \\
  -H <span class="token-header">"Content-Type: application/json"</span> \\
  -d <span class="token-str">'{
    "nin": "54928103947",
    "generate_slip": true,
    "slip_type": "premium"
  }'</span>`,

        node: `<span class="token-method">const</span> response = <span class="token-bool">await</span> fetch(<span class="token-url">'https://api.fuwa.ng/v1/kyc/nin/verify'</span>, {
  method: <span class="token-str">'POST'</span>,
  headers: {
    <span class="token-str">'Authorization'</span>: <span class="token-str">'Bearer fk_live_948201a8f9024c'</span>,
    <span class="token-str">'Content-Type'</span>: <span class="token-str">'application/json'</span>
  },
  body: JSON.stringify({
    nin: <span class="token-str">'54928103947'</span>,
    generate_slip: <span class="token-bool">true</span>,
    slip_type: <span class="token-str">'premium'</span>
  })
});
<span class="token-method">const</span> data = <span class="token-bool">await</span> response.json();`,

        python: `<span class="token-method">import</span> requests

response = requests.post(
    <span class="token-url">'https://api.fuwa.ng/v1/kyc/nin/verify'</span>,
    headers={
        <span class="token-str">'Authorization'</span>: <span class="token-str">'Bearer fk_live_948201a8f9024c'</span>,
        <span class="token-str">'Content-Type'</span>: <span class="token-str">'application/json'</span>
    },
    json={
        <span class="token-key">'nin'</span>: <span class="token-str">'54928103947'</span>,
        <span class="token-key">'generate_slip'</span>: <span class="token-bool">True</span>,
        <span class="token-key">'slip_type'</span>: <span class="token-str">'premium'</span>
    }
)
data = response.json()`
    };

    const rawSnippets = {
        curl: `curl -X POST "https://api.fuwa.ng/v1/kyc/nin/verify" \\
  -H "Authorization: Bearer fk_live_948201a8f9024c" \\
  -H "Content-Type: application/json" \\
  -d '{
    "nin": "54928103947",
    "generate_slip": true,
    "slip_type": "premium"
  }'`,
        node: `const response = await fetch('https://api.fuwa.ng/v1/kyc/nin/verify', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer fk_live_948201a8f9024c',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    nin: '54928103947',
    generate_slip: true,
    slip_type: 'premium'
  })
});
const data = await response.json();`,
        python: `import requests

response = requests.post(
    'https://api.fuwa.ng/v1/kyc/nin/verify',
    headers={
        'Authorization': 'Bearer fk_live_948201a8f9024c',
        'Content-Type': 'application/json'
    },
    json={
        'nin': '54928103947',
        'generate_slip': True,
        'slip_type': 'premium'
    }
)
data = response.json()`
    };

    let activeLang = 'curl';

    langBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            langBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeLang = btn.getAttribute('data-api-lang') || 'curl';
            if (codeBlock && snippets[activeLang]) {
                codeBlock.innerHTML = snippets[activeLang];
            }
        });
    });

    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            const textToCopy = rawSnippets[activeLang] || '';
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(showCopiedState);
            } else {
                const ta = document.createElement('textarea');
                ta.value = textToCopy;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    showCopiedState();
                } catch (err) {
                    console.error('Copy fallback failed', err);
                }
                document.body.removeChild(ta);
            }
        });
    }

    function showCopiedState() {
        if (!copyBtn) return;
        const originalHtml = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fa-solid fa-check text-success"></i> Copied!';
        copyBtn.style.borderColor = 'var(--hm-emerald)';
        setTimeout(() => {
            copyBtn.innerHTML = originalHtml;
            copyBtn.style.borderColor = '';
        }, 2000);
    }
}
