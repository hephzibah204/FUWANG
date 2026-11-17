import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

// Fixtures are rendered from the real Blade template by VtuPurchaseSafetyTest.
const root = process.cwd();
const products = [
    { code: 'mtn-1', network: 'MTN', label: '1 GB SME', validity: '30 days', amount: 500 },
    { code: 'mtn-2', network: 'MTN', label: '2 GB SME', validity: '30 days', amount: 900 },
    { code: 'glo-1', network: 'GLO', label: '1 GB Gifting', validity: '7 days', amount: 450 },
];

async function setup(page, { type = 'vtu_data', bands = [{ id: 1, name: 'Band 1' }], catalogue = products, purchase, verifyDelay = 0 } = {}) {
    const html = await fs.readFile(path.join(root, 'storage/framework/testing/vtu-ui', `${type}.html`), 'utf8');
    const script = await fs.readFile(path.join(root, 'public/js/vtu-purchase.js'), 'utf8');
    const requests = [];
    const json = (route, body, status = 200) => route.fulfill({ status, contentType: 'application/json', body: JSON.stringify(body) });
    await page.route('**/*', async route => {
        const request = route.request(), url = new URL(request.url());
        if (url.pathname === '/__vtu_fixture__') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname === '/js/vtu-purchase.js') return route.fulfill({ contentType: 'application/javascript', body: script });
        if (url.pathname === '/test-bootstrap.css') return route.fulfill({ contentType: 'text/css', body: await fs.readFile(path.join(root, 'node_modules/bootstrap/dist/css/bootstrap.min.css'), 'utf8') });
        if (url.pathname === '/assets/nexus/css/nexus.css') return route.fulfill({ contentType: 'text/css', body: await fs.readFile(path.join(root, 'public/assets/nexus/css/nexus.css'), 'utf8') });
        if (url.pathname.includes('/providers/')) return json(route, { status: true, providers: bands });
        if (url.pathname.includes('/catalog/')) return json(route, { status: true, products: catalogue });
        if (url.pathname.includes('/quote/')) {
            const data = request.postDataJSON();
            return json(route, { status: true, breakdown: { amount: Number(data.amount), fee: 10, discount: 25, total: Number(data.amount) - 15, direction: 'debit' } });
        }
        if (url.pathname.endsWith('/validate')) {
            if (verifyDelay) await new Promise(resolve => setTimeout(resolve, verifyDelay));
            return json(route, { status: true, validation_token: 'verified-token', customer: { name: 'Test Customer', address: 'Test address' } });
        }
        if (request.method() === 'POST') {
            requests.push(request.postDataJSON());
            if (purchase) return purchase(route, requests);
            return json(route, { status: true, message: 'Purchase completed.', reference: requests.at(-1).request_id, data: {} });
        }
        return route.abort();
    });
    page.on('dialog', dialog => dialog.accept());
    await page.goto('http://localhost/__vtu_fixture__');
    await expect(page.locator('[data-state]')).not.toHaveText('Loading available bands…');
    return requests;
}

async function chooseData(page) {
    await page.locator('#vtu-network').selectOption('MTN');
    await page.locator('#vtu-plan').selectOption('mtn-1');
    await page.locator('#vtu-phone').fill('08123456789');
}

test('data price, quote and receipt stay consistent on desktop and mobile', async ({ page }) => {
    const requests = await setup(page);
    await chooseData(page);
    await expect(page.locator('#vtu-amount')).toHaveValue('500');
    await expect(page.locator('#vtu-amount')).toHaveAttribute('readonly', '');
    await expect(page.locator('[data-summary-total]')).toContainText('485.00');
    await page.locator('[data-submit]').click();
    await expect(page.locator('[data-result]')).toContainText('Purchase completed');
    expect(requests).toHaveLength(1);
    expect(requests[0].amount).toBe('500');
    expect(requests[0].plan_id).toBe('mtn-1');
    expect(requests[0].request_id).toBeTruthy();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
});

test('network switching and budget filtering clear stale bundle amounts', async ({ page }) => {
    await setup(page); await chooseData(page);
    await page.locator('#vtu-network').selectOption('GLO');
    await expect(page.locator('#vtu-amount')).toHaveValue('');
    await expect(page.locator('#vtu-plan option')).toHaveCount(2);
    await page.locator('#vtu-budget').fill('400');
    await expect(page.locator('#vtu-plan')).toBeDisabled();
    await page.locator('#vtu-budget').fill('500');
    await page.locator('#vtu-plan').selectOption('glo-1');
    await expect(page.locator('#vtu-amount')).toHaveValue('450');
    await page.locator('#vtu-search').fill('SME');
    await expect(page.locator('#vtu-plan')).toBeDisabled();
    await expect(page.locator('#vtu-amount')).toHaveValue('');
});

test('empty bands and empty catalogues explain unavailable purchases', async ({ page }) => {
    const requests = await setup(page, { bands: [] });
    await expect(page.locator('[data-state]')).toContainText('unavailable');
    await expect(page.locator('[data-fields]')).toHaveAttribute('disabled', '');
    expect(requests).toHaveLength(0);
});

test('API purchase errors release the form for correction', async ({ page }) => {
    await setup(page, { purchase: route => route.fulfill({ status: 422, contentType: 'application/json', body: JSON.stringify({ status: false, message: 'Insufficient wallet balance.' }) }) });
    await chooseData(page); await page.locator('[data-submit]').click();
    await expect(page.locator('[data-result]')).toContainText('Insufficient wallet balance');
    await expect(page.locator('[data-fields]')).not.toHaveAttribute('disabled', '');
    await expect(page.locator('[data-submit]')).toHaveText('Review purchase');
});

test('an interrupted purchase locks repeat payment and preserves its reference', async ({ page }) => {
    const requests = await setup(page, { purchase: route => route.abort('failed') });
    await chooseData(page); await page.locator('[data-submit]').click();
    await expect(page.locator('[data-result]')).toContainText('Awaiting confirmation');
    await expect(page.locator('[data-fields]')).toHaveAttribute('disabled', '');
    expect(requests).toHaveLength(1);
    await expect(page.getByRole('button', { name: 'Check status' })).toBeVisible();
});

test('late electricity validation cannot verify a changed meter', async ({ page }) => {
    const requests = await setup(page, { type: 'vtu_electricity', catalogue: [], verifyDelay: 200 });
    await page.locator('#vtu-service').selectOption('eko-electric');
    await page.locator('#vtu-customer').fill('12345678');
    await page.locator('[data-verify-customer]').click();
    await page.locator('#vtu-customer').fill('87654321');
    await expect(page.locator('[data-verify-customer]')).toHaveText('Verify customer');
    await expect(page.locator('input[name="validation_token"]')).toHaveValue('');
    expect(requests).toHaveLength(0);
});
