# Payment Gateway Integration Documentation

This document outlines the integration methods, API endpoints, and SDK implementations for the supported payment gateways in the Fuwa.NG platform.

## 1. Paystack
- **Integration Method**: Inline SDK
- **SDK URL**: `https://js.paystack.co/v1/inline.js`
- **Implementation**:
  ```javascript
  const handler = PaystackPop.setup({
      key: 'PUBLIC_KEY',
      email: 'customer@email.com',
      amount: amountInKobo,
      ref: 'UNIQUE_REFERENCE',
      callback: function(response) {
          // Verify on server
      }
  });
  handler.openIframe();
  ```
- **Verification Endpoint**: `https://api.paystack.co/transaction/verify/{reference}`

## 2. Flutterwave
- **Integration Method**: Inline Checkout
- **SDK URL**: `https://checkout.flutterwave.com/v3.js`
- **Implementation**:
  ```javascript
  FlutterwaveCheckout({
      public_key: "PUBLIC_KEY",
      tx_ref: "UNIQUE_REFERENCE",
      amount: amount,
      currency: "NGN",
      customer: { email: "customer@email.com" },
      callback: function (data) {
          // Verify on server
      }
  });
  ```
- **Verification Endpoint**: `https://api.flutterwave.com/v3/transactions/{id}/verify`

## 3. Monnify
- **Integration Method**: Inline SDK
- **SDK URL**: `https://sdk.monnify.com/plugin/monnify.js`
- **Implementation**:
  ```javascript
  MonnifySDK.initialize({
      amount: amount,
      currency: "NGN",
      reference: "UNIQUE_REFERENCE",
      customerEmail: "customer@email.com",
      apiKey: "API_KEY",
      contractCode: "CONTRACT_CODE",
      onComplete: function(response) {
          // Verify on server
      }
  });
  ```
- **Verification Endpoint**: `https://api.monnify.com/api/v1/merchant/transactions/query?transactionReference={reference}`

## 4. Payvessel
- **Integration Method**: Dynamic Virtual Accounts / API-driven
- **Implementation**: Primarily used for automated bank transfers. A payment intent is created, and the user is presented with a dedicated account number.
- **Verification Endpoint**: `https://api.payvessel.com/api/v1/transaction/status/{reference}`

## 5. PaymentPoint
- **Integration Method**: API-driven / USSD / Transfer
- **Implementation**: Similar to Payvessel, it provides USSD and Bank Transfer options.
- **Verification**: Handled via webhooks or status query API.

## Shared Implementation Logic
1. **Payment Intent**: Before any gateway is triggered, a `PaymentIntent` is created on the server to lock in the amount and reference.
2. **Native Modals**: The platform prioritizes official SDKs to ensure security and a consistent user experience.
3. **Verification**: All client-side success callbacks are verified on the server using the gateway's secret key before credit is applied to the user's wallet.
4. **Webhooks**: Webhooks are implemented as a fallback to ensure transactions are processed even if the user closes the browser before the callback completes.
