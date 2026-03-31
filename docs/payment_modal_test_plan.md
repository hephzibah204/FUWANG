# Payment Modal Test Plan

This document defines the expected behavior and the automated/manual test coverage for payment gateway native modal triggers.

## Scope
Gateways:
- Paystack (Inline)
- Flutterwave (Inline Checkout)
- Monnify (SDK)

Non-modal/hosted-page gateways (covered as redirect/manual where applicable):
- Payvessel
- PaymentPoint

## Expected User Flow
1. User clicks **Fund Wallet**.
2. User enters an amount.
3. User selects a payment provider.
4. System validates provider configuration on the server (`/payment/validate-config`).
5. If valid, system creates a `PaymentIntent` (`/payment/intents`).
6. System triggers the provider’s official modal/checkout UI using the provider SDK.

## Automated Coverage (Playwright)
File: `tests/e2e/payment-gateway-modals.spec.ts`

### Test Cases
- API key validation gate (invalid keys): provider selection + pay button must not invoke SDK.
- Provider click simulation: Paystack/Flutterwave/Monnify options are clicked.
- Native modal trigger verification: SDK functions are invoked with the expected parameters.
- Performance: SDK invocation must occur within 500ms.
- Cross-browser: Chromium/Firefox/WebKit.
- Responsive: Desktop + Mobile emulation.
- Security: unauthenticated access to `/payment/validate-config` must fail.

### Artifacts
Playwright outputs:
- HTML report: `playwright-report/`
- Screenshots: `test-results/`

## Manual / Live Provider Validation
Because real provider modals require live sandbox keys and third-party hosted UI, full integration validation is performed manually:

1. Configure sandbox keys in Admin → Settings → API Keys.
2. Ensure the gateway is **Active** in Admin → Settings → Payment Gateways.
3. Trigger a wallet funding flow.
4. Confirm:
   - modal opens
   - displayed amount and email are correct
   - transaction reference is present
5. Close the modal and confirm application remains stable.

## Pass/Fail Criteria
- **Pass**: SDK is invoked only when provider is active + keys are configured; no SDK call occurs otherwise.
- **Fail**: SDK invoked without validation, wrong params, modal takes > 500ms to trigger, or validation endpoint can be hit unauthenticated.

