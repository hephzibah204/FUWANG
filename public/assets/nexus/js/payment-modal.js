/**
 * Fuwa.NG Payment Gateway Modal
 * Shared across all service pages — include after payment-modal.css
 */

(function () {
  // ── Inject HTML into body ──────────────────────────────────────────
  const html = `
<div class="pay-modal-overlay" id="payModalOverlay">
  <div class="pay-modal" role="dialog" aria-modal="true" aria-labelledby="payModalTitle">
    <button class="pay-modal-close" onclick="closePayModal()" aria-label="Close">&times;</button>

    <!-- Header -->
    <div class="pay-modal-title" id="payModalTitle">Complete Payment</div>
    <div class="pay-modal-sub" id="payModalSub">Choose your preferred payment method</div>

    <!-- Amount -->
    <div class="pay-amount-display">
      <div class="pay-label">Amount to Pay</div>
      <div class="pay-figure" id="payFigure">₦0.00</div>
      <div style="font-size:.78rem;color:rgba(255,255,255,.4);margin-top:4px;" id="payDesc">—</div>
    </div>

    <div style="padding: 0 24px 10px;">
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5); margin-bottom: 12px; font-weight: 500;">Select Payment Method</div>
        
        <!-- Gateway Selection (Dynamic) -->
        <div id="gateway-loader" style="display:none; text-align:center; font-size:0.8rem; color:rgba(255,255,255,0.5); margin-bottom:10px;">
           <i class="fa-solid fa-circle-notch fa-spin"></i> Loading providers...
        </div>
        <div id="gateway-options" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:20px;">
           <!-- Populated via JS -->
        </div>

        <!-- Wallet Option (Special) -->
        <div id="wallet-option-container" style="margin-bottom: 20px;">
            <div class="gateway-opt" id="wallet-gateway-opt" onclick="selectWalletMethod()" style="border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); border-radius: 12px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: all 0.2s;">
                <div style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #fff;">Fuwa.NG Wallet</div>
                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4);">Pay from your balance</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Provider Details Panel -->
    <div class="pay-panel active" id="pm-card" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
      
      <!-- Gateway Branding -->
      <div style="text-align:center; font-size:0.8rem; color:rgba(255,255,255,0.4); margin-bottom:16px; padding: 0 24px;">
        Selected: <strong id="active-gateway-brand" style="color:#fff;">Please select a provider</strong>
      </div>

      <div style="padding: 0 24px;">
          <button class="pay-btn-main" id="card-pay-btn" onclick="processPayment('card')" style="margin-bottom: 10px;">
            <i class="fa-solid fa-lock"></i> Validate & Proceed
          </button>
      </div>
    </div>

    <!-- Success Screen (hidden by default) -->
    <div class="pay-panel" id="pm-success">
      <div class="pay-success-overlay">
        <div class="pay-success-icon"><i class="fa-solid fa-check"></i></div>
        <div style="font-size:1.2rem;font-weight:800;color:#fff;margin-bottom:6px;">Payment Successful!</div>
        <div style="font-size:.9rem;color:rgba(255,255,255,.5);margin-bottom:20px;" id="success-desc">Your transaction has been processed.</div>
        <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:16px;margin-bottom:20px;text-align:left;">
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:.85rem;">
            <span style="color:rgba(255,255,255,.5);">Amount</span><strong id="success-amount" style="color:#fff;">—</strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:.85rem;border-top:1px solid rgba(255,255,255,.06);">
            <span style="color:rgba(255,255,255,.5);">Reference</span><strong id="success-ref" style="color:#3b82f6;">—</strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:.85rem;border-top:1px solid rgba(255,255,255,.06);">
            <span style="color:rgba(255,255,255,.5);">Status</span><strong style="color:#10b981;">Successful ✓</strong>
          </div>
        </div>
        <button class="pay-btn-main" onclick="closePayModal()"><i class="fa-solid fa-check-double"></i> Done</button>
        <button onclick="closePayModal()" style="width:100%;background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;margin-top:10px;font-size:.85rem;">Download Receipt</button>
      </div>
    </div>

    <div class="pay-security-note">
      <i class="fa-solid fa-lock"></i> 256-bit SSL encrypted · PCI DSS compliant · Powered by Fuwa.NG Gateway
    </div>
  </div>
</div>

<!-- Toast -->
<div class="nexus-toast" id="nexusToast">
  <i class="fa-solid fa-circle-check toast-icon" id="toast-icon" style="color:#10b981;"></i>
  <div><div class="toast-text" id="toast-text">Notification</div><div class="toast-sub" id="toast-sub"></div></div>
</div>`;

  document.body.insertAdjacentHTML('beforeend', html);

  // ── Timer for bank transfer ─────────────────────────────────────────
  let timerInterval = null;
  function startTimer() {
    let secs = 598;
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
      secs--;
      const m = Math.floor(secs / 60).toString().padStart(2, '0');
      const s = (secs % 60).toString().padStart(2, '0');
      const el = document.getElementById('txn-timer');
      if (el) el.textContent = m + ':' + s;
      if (secs <= 0) clearInterval(timerInterval);
    }, 1000);
  }

  // ── Public API ──────────────────────────────────────────────────────
  window._payCtx = {};
  window.currentGateways = [];
  window.selectedGateway = null;

  window.loadGateways = function() {
    const loader = document.getElementById('gateway-loader');
    const container = document.getElementById('gateway-options');
    
    // Only load if not already loaded or if needed
    if (window.currentGateways.length > 0) return;

    loader.style.display = 'block';
    container.innerHTML = '';

    fetch('/payment/gateways')
      .then(res => res.json())
      .then(data => {
        loader.style.display = 'none';
        if (data.status && data.gateways.length > 0) {
           window.currentGateways = data.gateways;
           renderGateways(data.gateways);
        } else {
           container.innerHTML = '<div style="font-size:0.8rem; color:rgba(255,255,255,0.5);">No payment gateways available.</div>';
        }
      })
      .catch(err => {
         console.error(err);
         loader.style.display = 'none';
         container.innerHTML = '<div style="font-size:0.8rem; color:#ef4444;">Failed to load gateways.</div>';
      });
  };

  window.renderGateways = function(gateways) {
     const container = document.getElementById('gateway-options');
     container.innerHTML = '';
     
     gateways.forEach((g, index) => {
        const div = document.createElement('div');
        div.className = `gateway-opt`;
        div.style.cssText = `
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03);
            border-radius: 12px; padding: 12px; cursor: pointer; text-align: center;
            transition: all 0.2s; display: flex; flex-direction: column; align-items: center; gap: 8px;
            justify-content: center; height: 80px;
        `;
        div.onclick = () => selectGateway(g, div);
        
        let logoHtml = '';
        if (g.logo_url) {
            logoHtml = `<img src="${g.logo_url}" alt="${g.display_name}" style="height:28px; width:100%; object-fit:contain; border-radius:4px;">`;
        } else {
            logoHtml = `<div style="width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.9rem; color:#fff; font-weight:700;">${g.display_name.charAt(0)}</div>`;
        }

        div.innerHTML = `
            ${logoHtml}
            <div style="font-size:0.75rem; font-weight:600; color:#fff;">${g.display_name}</div>
        `;
        
        container.appendChild(div);
     });
     
     window.selectedGateway = null;
     updateBranding();
  };

  window.selectWalletMethod = function() {
      // Clear gateway selection
      document.querySelectorAll('#gateway-options .gateway-opt').forEach(d => {
          d.classList.remove('sel');
          d.style.borderColor = 'rgba(255,255,255,0.1)';
          d.style.background = 'rgba(255,255,255,0.03)';
      });
      
      const el = document.getElementById('wallet-gateway-opt');
      el.classList.add('sel');
      el.style.borderColor = '#10b981';
      el.style.background = 'rgba(16,185,129,0.1)';
      
      window.selectedGateway = { name: 'wallet', display_name: 'Fuwa.NG Wallet' };
      updateBranding();
  };

  window.selectGateway = function(gateway, el) {
      // Clear wallet selection
      const walletEl = document.getElementById('wallet-gateway-opt');
      if (walletEl) {
          walletEl.classList.remove('sel');
          walletEl.style.borderColor = 'rgba(255,255,255,0.1)';
          walletEl.style.background = 'rgba(255,255,255,0.03)';
      }

      // Clear previous validation status
      const brandEl = document.getElementById('active-gateway-brand');
      if (brandEl) brandEl.innerHTML = `Validating ${gateway.display_name}... <i class="fa-solid fa-spinner fa-spin"></i>`;
      
      verifyOnServer('/payment/validate-config', { gateway: gateway.name })
          .then(res => {
              if (res && res.status) {
                  window.selectedGateway = gateway;
                  // Update UI
                  document.querySelectorAll('#gateway-options .gateway-opt').forEach(d => {
                      d.classList.remove('sel');
                      d.style.borderColor = 'rgba(255,255,255,0.1)';
                      d.style.background = 'rgba(255,255,255,0.03)';
                  });
                  el.classList.add('sel');
                  el.style.borderColor = '#3b82f6';
                  el.style.background = 'rgba(59,130,246,0.1)';
                  
                  updateBranding();
                  showToast('Configuration Verified', `API keys for ${gateway.display_name} are correctly set.`, 'success');
              } else {
                  window.selectedGateway = null;
                  updateBranding();
                  if (brandEl) brandEl.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-circle-exclamation"></i> ${res.message || 'Validation failed'}</span>`;
                  showToast('Configuration Error', res.message || 'No API key set for selected provider', 'error');
              }
          })
          .catch(err => {
              console.error(err);
              showToast('Error', 'Unable to validate provider configuration', 'error');
          });
  };

  window.updateBranding = function() {
      const brandEl = document.getElementById('active-gateway-brand');
      if (brandEl) {
          if (window.selectedGateway) {
              brandEl.textContent = window.selectedGateway.display_name;
              const name = window.selectedGateway.name.toLowerCase();
              if (name === 'blusalt') brandEl.style.color = '#3b82f6';
              else if (name === 'flutterwave') brandEl.style.color = '#f59e0b';
              else if (name === 'monnify') brandEl.style.color = '#0066ff';
              else brandEl.style.color = '#0ea5e9'; // Paystack/Default
          } else {
              brandEl.textContent = 'Please select a valid provider';
              brandEl.style.color = 'rgba(255,255,255,0.4)';
          }
      }
  };

  window.openPayModal = function (service, amount, description) {
    window._payCtx = { service, amount, description };
    const overlay = document.getElementById('payModalOverlay');

    // Load gateways if not loaded
    loadGateways();

    // Hide wallet option if funding wallet
    const walletOpt = document.getElementById('wallet-option-container');
    if (service === 'Wallet Funding' || service === 'Funding Wallet') {
        if (walletOpt) walletOpt.style.display = 'none';
    } else {
        if (walletOpt) walletOpt.style.display = 'block';
    }

    document.getElementById('payModalTitle').textContent = service || 'Complete Payment';
    document.getElementById('payFigure').textContent = amount ? '₦' + parseFloat(String(amount).replace(/[₦,]/g, '')).toLocaleString('en-NG', { minimumFractionDigits: 2 }) : '₦0.00';
    document.getElementById('payDesc').textContent = description || '';
    
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    
    // Reset selection on open
    window.selectedGateway = null;
    document.querySelectorAll('.gateway-opt').forEach(d => {
        d.classList.remove('sel');
        d.style.borderColor = 'rgba(255,255,255,0.1)';
        d.style.background = 'rgba(255,255,255,0.03)';
    });
    updateBranding();
  };

  window.closePayModal = function () {
    document.getElementById('payModalOverlay').classList.remove('open');
    document.body.style.overflow = '';
    clearInterval(timerInterval);
  };

  window.switchPayMethod = function (id, btn) {
    document.querySelectorAll('.pay-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.pay-method').forEach(b => b.classList.remove('sel'));
    
    // Hide wallet option if funding wallet
    const walletBtn = document.getElementById('pm-wallet-btn');
    if (window._payCtx && window._payCtx.service === 'Wallet Funding') {
        if (walletBtn) walletBtn.style.display = 'none';
        if (id === 'pm-wallet') id = 'pm-card'; // Fallback to card if somehow wallet was selected
    } else {
        if (walletBtn) walletBtn.style.display = 'flex';
    }

    const panel = document.getElementById(id);
    if (panel) panel.classList.add('active');
    if (btn) btn.classList.add('sel');
  };

  window.selectUssdBank = function (el, bank, code) {
    document.querySelectorAll('.bank-opt').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
    const amt = document.getElementById('payFigure').textContent.replace(/[₦,]/g, '');
    document.getElementById('ussd-display').textContent = code.replace('#', '') + '*' + Math.round(amt) + '#';
  };

  window.copyTransferAcct = function () {
    const acct = document.getElementById('transfer-acct').textContent.replace(/\s/g, '');
    navigator.clipboard.writeText(acct).then(() => {
      const btn = document.getElementById('copy-acct-btn');
      btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
      setTimeout(() => btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy', 2000);
    });
  };

  window.copyUssd = function () {
    const code = document.getElementById('ussd-display').textContent;
    navigator.clipboard.writeText(code).then(() => showToast('USSD Copied', 'Dial the code from your registered number', 'success'));
  };

  window.fmtCard = function (el) {
    let v = el.value.replace(/\D/g, '').slice(0, 16);
    el.value = v.match(/.{1,4}/g)?.join(' ') || v;
  };

  window.fmtExp = function (el) {
    let v = el.value.replace(/\D/g, '').slice(0, 4);
    if (v.length >= 2) v = v.slice(0, 2) + '/' + v.slice(2);
    el.value = v;
  };

  window.processPayment = function (method) {
    const btn = document.getElementById('card-pay-btn');
    const orig = btn.innerHTML;

    if (!window.selectedGateway) {
      showToast('Error', 'Please select a payment provider', 'error');
      return;
    }

    const activeGateway = window.selectedGateway.display_name;
    const gatewayName = window.selectedGateway.name.toLowerCase();
    const gatewayConfig = window.selectedGateway.config || {};

    const amountStr = document.getElementById('payFigure').textContent.replace(/[₦,]/g, '');
    const amount = parseFloat(amountStr);
    const email = window.authUserEmail || window._payCtx.email || '';

    if (!email) {
      showToast('Error', 'Missing customer email', 'error');
      return;
    }

    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Validating ${activeGateway}…`;
    btn.disabled = true;

    verifyOnServer('/payment/validate-config', { gateway: gatewayName })
      .then((res) => {
        if (!res || !res.status) {
          showToast('Configuration Error', (res && res.message) ? res.message : 'No API key set for selected provider', 'error');
          btn.innerHTML = orig;
          btn.disabled = false;
          return;
        }

        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Opening ${activeGateway}…`;

        return verifyOnServer('/payment/intents', { amount: amount, service: window._payCtx.service || '' })
          .then((intentRes) => {
            if (!intentRes || !intentRes.status || !intentRes.reference) {
              throw new Error('Could not create payment intent');
            }

            const intentRef = String(intentRes.reference);

            if (gatewayName === 'paystack') {
              if (!window.PaystackPop) throw new Error('Paystack SDK not loaded');
              const handler = PaystackPop.setup({
                key: gatewayConfig.public_key || '',
                email: email,
                amount: Math.round(amount * 100),
                currency: 'NGN',
                ref: intentRef,
                callback: function () {},
                onClose: function () {},
              });
              handler.openIframe();
            } else if (gatewayName === 'flutterwave') {
              if (!window.FlutterwaveCheckout) throw new Error('Flutterwave SDK not loaded');
              FlutterwaveCheckout({
                public_key: gatewayConfig.public_key || '',
                tx_ref: intentRef,
                amount: amount,
                currency: 'NGN',
                customer: { email: email },
                customizations: { title: window._payCtx.service || 'Payment', description: window._payCtx.description || '' },
                callback: function () {},
                onclose: function () {},
              });
            } else if (gatewayName === 'monnify') {
              if (!window.MonnifySDK) throw new Error('Monnify SDK not loaded');
              MonnifySDK.initialize({
                amount: amount,
                currency: 'NGN',
                reference: intentRef,
                customerName: window.authUserName || 'Customer',
                customerEmail: email,
                apiKey: gatewayConfig.api_key || '',
                contractCode: gatewayConfig.contract_code || '',
                paymentDescription: window._payCtx.service || 'Payment',
                isTestMode: true,
                onComplete: function () {},
                onClose: function () {},
              });
            } else {
              throw new Error('Selected provider does not support a native web modal.');
            }
          })
          .catch((e) => {
            showToast('Error', e.message || 'Unable to initialize payment modal', 'error');
          })
          .finally(() => {
            btn.innerHTML = orig;
            btn.disabled = false;
          });
      })
      .catch((err) => {
        console.error(err);
        showToast('Error', 'Unable to validate provider configuration', 'error');
        btn.innerHTML = orig;
        btn.disabled = false;
      });
  };

  function handleSuccess(ref, activeGateway) {
      document.getElementById('success-amount').textContent = document.getElementById('payFigure').textContent;
      document.getElementById('success-ref').textContent = ref;
      document.getElementById('success-desc').textContent = window._payCtx.service + ' payment processed successfully by ' + activeGateway + '.';
      showToast('Payment Successful!', ref, 'success');
  }

    async function verifyOnServer(url, payload) {
        if (window.csrfFetch) {
            return await window.csrfFetch(url, { data: payload || {} });
        }
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || ''
            },
            body: JSON.stringify(payload || {})
        });
        return await res.json();
    }

    // ── Toast ──────────────────────────────────────────────────────────
    window.showToast = function (text, sub, type) {
        const toast = document.getElementById('nexusToast');
        const icon = document.getElementById('toast-icon');
        document.getElementById('toast-text').textContent = text;
        document.getElementById('toast-sub').textContent = sub || '';
        icon.className = 'fa-solid toast-icon ' + (type === 'error' ? 'fa-circle-xmark' : 'fa-circle-check');
        icon.style.color = type === 'error' ? '#ef4444' : '#10b981';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    };

    // Close on backdrop click
    document.getElementById('payModalOverlay').addEventListener('click', function (e) {
        if (e.target === this) closePayModal();
    });

    // ESC key
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closePayModal(); });

})();
