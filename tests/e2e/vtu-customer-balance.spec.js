import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';

test('confirmed system balance failure shows a neutral error and releases the form', async ({ page }) => {
    const html = await fs.readFile('storage/framework/testing/vtu-ui/data-current.html', 'utf8');
    const dialogs = [];
    let submittedReference;
    page.on('dialog', async dialog => { dialogs.push(dialog.message()); await dialog.accept(); });
    await page.route('**/*', async route => {
        const request = route.request(), path = new URL(request.url()).pathname;
        if (path === '/__customer_balance__') return route.fulfill({ contentType: 'text/html', body: html });
        if (path === '/test-bootstrap.css') return route.fulfill({ contentType: 'text/css', body: await fs.readFile('node_modules/bootstrap/dist/css/bootstrap.min.css', 'utf8') });
        if (path.endsWith('/nexus.css')) return route.fulfill({ contentType: 'text/css', body: await fs.readFile('public/assets/nexus/css/nexus.css', 'utf8') });
        if (path.includes('/quote/')) return route.fulfill({ contentType: 'application/json', body: JSON.stringify({ status: true, breakdown: { amount: 230, total: 230 } }) });
        if (path.endsWith('/data/buy')) {
            submittedReference = request.postDataJSON().request_id;
            return route.fulfill({ status: 422, contentType: 'application/json', body: JSON.stringify({ status: false, pending: false,
                message: 'System balance is low. Please contact admin. Your wallet has been refunded.', reference: submittedReference }) });
        }
        return route.abort();
    });
    await page.goto('http://localhost/__customer_balance__');
    await expect(page.locator('body')).not.toContainText('Datasub');
    await page.locator('#planSelect').selectOption('1');
    await page.locator('input[name="phone"]').fill('07032295411');
    await page.locator('#buy-btn').click();
    await expect(page.locator('#dataPlanStatus')).toContainText('System balance is low. Please contact admin.');
    await expect(page.locator('#buy-btn')).toBeEnabled();
    await expect(page.locator('input[name="request_id"]')).not.toHaveValue(submittedReference);
    expect(dialogs.join(' ')).not.toContain('Awaiting provider');
});
