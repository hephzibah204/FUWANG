import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

async function openDataPage(page, { empty = false, cancelFirst = false } = {}) {
    const html = await fs.readFile(path.join(process.cwd(), 'storage/framework/testing/vtu-ui/data-current.html'), 'utf8');
    const purchases = [];
    let confirmations = 0;
    page.on('dialog', dialog => {
        if (dialog.type() === 'confirm' && cancelFirst && confirmations++ === 0) return dialog.dismiss();
        return dialog.accept();
    });
    await page.route('**/*', async route => {
        const request = route.request(), url = new URL(request.url());
        const json = (data) => route.fulfill({ contentType: 'application/json', body: JSON.stringify(data) });
        if (url.pathname === '/__data_band_test__') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname === '/test-bootstrap.css') return route.fulfill({ contentType: 'text/css', body: await fs.readFile('node_modules/bootstrap/dist/css/bootstrap.min.css', 'utf8') });
        if (url.pathname === '/assets/nexus/css/nexus.css') return route.fulfill({ contentType: 'text/css', body: await fs.readFile('public/assets/nexus/css/nexus.css', 'utf8') });
        if (url.pathname.includes('/catalog/')) {
            expect(url.searchParams.get('provider_id')).toBe('2');
            return json({ status: true, products: empty ? [] : [
                { code: '17', network: 'MTN', label: 'Band 2 MTN 1 GB', amount: 650, validity: '30 days' },
                { code: '18', network: 'GLO', label: 'Band 2 GLO 1 GB', amount: 550, validity: '30 days' },
            ] });
        }
        if (url.pathname.includes('/quote/')) {
            const payload = request.postDataJSON();
            return json({ status: true, breakdown: { amount: Number(payload.amount), total: Number(payload.amount), fee: 0, discount: 0 } });
        }
        if (url.pathname.endsWith('/data/buy')) {
            purchases.push(request.postDataJSON());
            return json({ status: true, message: 'Data delivered.' });
        }
        return route.abort();
    });
    await page.goto('http://localhost/__data_band_test__');
    return purchases;
}

test('the actual data page exposes bands and submits the selected band and catalogue ID', async ({ page }) => {
    const purchases = await openDataPage(page);
    await expect(page.getByLabel('Network band', { exact: true })).toBeVisible();
    await expect(page.locator('#dataBand option')).toHaveCount(2);
    await expect(page.locator('#dataBand option:checked')).toHaveText('Datasub247 (Default)');
    await page.locator('#planSelect').selectOption('7');
    await page.locator('#dataBand').selectOption('2');
    await expect(page.locator('#amountInput')).toHaveValue('');
    await expect(page.locator('#planSelect option')).toHaveCount(2);
    await page.locator('#planSelect').selectOption('17');
    await expect(page.locator('#sumAmountDisplay')).toContainText('650.00');
    await page.locator('#phone').fill('08123456789');
    await page.locator('#buy-btn').click();
    await expect(page.locator('#phone')).toHaveValue('');
    expect(purchases).toHaveLength(1);
    expect(purchases[0]).toMatchObject({ provider_id: '2', plan_id: '17', amount: '650', network: 'MTN' });
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
});

test('network changes clear the previous band package and price', async ({ page }) => {
    await openDataPage(page);
    await page.locator('#dataBand').selectOption('2');
    await page.locator('#planSelect').selectOption('17');
    await page.locator('label[for="net_GLO"]').click();
    await expect(page.locator('#planSelect')).toHaveValue('');
    await expect(page.locator('#amountInput')).toHaveValue('');
    await page.locator('#planSelect').selectOption('18');
    await expect(page.locator('#sumAmountDisplay')).toContainText('550.00');
});

test('bands with no packages cannot submit sample plans', async ({ page }) => {
    const purchases = await openDataPage(page, { empty: true });
    await page.locator('#dataBand').selectOption('2');
    await expect(page.locator('#dataPlanStatus')).toContainText('No bundles are available');
    await expect(page.locator('#buy-btn')).toBeDisabled();
    await expect(page.locator('#dataBand')).toBeEnabled();
    await page.locator('label[for="net_GLO"]').click();
    await expect(page.locator('#net_GLO')).toBeChecked();
    expect(purchases).toHaveLength(0);
});

test('cancelling confirmation releases the form without submitting payment', async ({ page }) => {
    const purchases = await openDataPage(page, { cancelFirst: true });
    await page.locator('#planSelect').selectOption('7');
    await page.locator('#phone').fill('08123456789');
    await page.locator('#buy-btn').click();
    await expect(page.locator('#dataBand')).toBeEnabled();
    await expect(page.locator('#buy-btn')).toBeEnabled();
    expect(purchases).toHaveLength(0);
});
