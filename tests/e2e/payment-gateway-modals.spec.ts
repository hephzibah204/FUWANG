import { expect, test } from '@playwright/test';

const gateways = [
  { name: 'paystack', label: 'Paystack', type: 'sdk' as const },
  { name: 'flutterwave', label: 'Flutterwave', type: 'sdk' as const },
  { name: 'monnify', label: 'Monnify', type: 'sdk' as const },
];

test.describe('Wallet funding gateway modals', () => {
  test.beforeEach(async ({ page }) => {
    await page.addInitScript(() => {
      (window as any).__sdkCalls = [];

      (window as any).PaystackPop = {
        setup: (params: any) => {
          (window as any).__sdkCalls.push({ provider: 'paystack', params, at: performance.now() });
          return {
            openIframe: () => {
              (window as any).__sdkCalls.push({ provider: 'paystack.openIframe', at: performance.now() });
            },
          };
        },
      };

      (window as any).FlutterwaveCheckout = (params: any) => {
        (window as any).__sdkCalls.push({ provider: 'flutterwave', params, at: performance.now() });
      };

      (window as any).MonnifySDK = {
        initialize: (params: any) => {
          (window as any).__sdkCalls.push({ provider: 'monnify', params, at: performance.now() });
        },
      };
    });
  });

  test('blocks modal when API keys are missing', async ({ page }) => {
    await page.route('**/payment/validate-config', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ status: false, message: 'No API key set for selected provider' }),
      });
    });

    await page.route('**/payment/gateways', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          status: true,
          gateways: [
            { name: 'paystack', display_name: 'Paystack', logo_url: '', config: { public_key: 'pk_test_x' } },
            { name: 'flutterwave', display_name: 'Flutterwave', logo_url: '', config: { public_key: 'FLWPUBK_TEST-x' } },
            { name: 'monnify', display_name: 'Monnify', logo_url: '', config: { api_key: 'MK_TEST_x', contract_code: '123' } },
          ],
        }),
      });
    });

    await page.goto('/__e2e/payment-harness');
    await page.getByRole('button', { name: /open payment modal/i }).click();
    await page.waitForSelector('#payModalOverlay.open');

    await page.getByText('Paystack', { exact: true }).click();
    await page.getByRole('button', { name: /validate & proceed/i }).click();

    const calls = await page.evaluate(() => (window as any).__sdkCalls);
    expect(calls.length).toBe(0);
  });

  for (const g of gateways) {
    test(`${g.label}: triggers native SDK with correct parameters within 500ms`, async ({ page }) => {
      await page.route('**/payment/validate-config', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: true, message: 'Configuration validated' }),
        });
      });

      await page.route('**/payment/intents', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: true, reference: 'NXS-E2E-REF123', amount_expected: 1500, currency: 'NGN' }),
        });
      });

      await page.route('**/payment/gateways', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            status: true,
            gateways: [
              { name: 'paystack', display_name: 'Paystack', logo_url: '', config: { public_key: 'pk_test_x' } },
              { name: 'flutterwave', display_name: 'Flutterwave', logo_url: '', config: { public_key: 'FLWPUBK_TEST-x' } },
              { name: 'monnify', display_name: 'Monnify', logo_url: '', config: { api_key: 'MK_TEST_x', contract_code: '123' } },
            ],
          }),
        });
      });

      await page.goto('/__e2e/payment-harness');
      await page.getByRole('button', { name: /open payment modal/i }).click();
      await page.waitForSelector('#payModalOverlay.open');

      const t0 = await page.evaluate(() => performance.now());
      await page.getByText(g.label, { exact: true }).click();

      await page.getByRole('button', { name: /validate & proceed/i }).click();

      await page.waitForFunction(
        (provider) => {
          const calls = (window as any).__sdkCalls || [];
          return calls.some((c: any) => c.provider === provider);
        },
        g.name,
      );

      const calls = await page.evaluate(() => (window as any).__sdkCalls);
      const call = calls.find((c: any) => c.provider === g.name);
      expect(call).toBeTruthy();

      if (g.name === 'paystack') {
        expect(call.params.currency).toBe('NGN');
        expect(call.params.amount).toBe(1500 * 100);
        expect(String(call.params.email || '')).toContain('@');
        expect(String(call.params.key || '')).toBeTruthy();
        expect(String(call.params.ref || '')).toBeTruthy();
      }
      if (g.name === 'flutterwave') {
        expect(call.params.currency).toBe('NGN');
        expect(call.params.amount).toBe(1500);
        expect(String(call.params.public_key || '')).toBeTruthy();
        expect(String(call.params.tx_ref || '')).toBeTruthy();
      }
      if (g.name === 'monnify') {
        expect(call.params.currency).toBe('NGN');
        expect(call.params.amount).toBe(1500);
        expect(String(call.params.apiKey || '')).toBeTruthy();
        expect(String(call.params.reference || '')).toBeTruthy();
      }

      const t1 = call.at as number;
      expect(t1 - t0).toBeLessThan(500);

      await page.screenshot({ path: `test-results/modal-${g.name}-${test.info().project.name}.png`, fullPage: true });
    });
  }

  test('security: unauthenticated users cannot validate gateway config', async ({ request }) => {
    const res = await request.post('/payment/validate-config', { data: { gateway: 'paystack' } });
    expect([302, 401, 419]).toContain(res.status());
  });
});
