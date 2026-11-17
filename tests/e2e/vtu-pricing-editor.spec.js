import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';

test('pricing editor preserves decimals and adds removable Datasub bundle rows', async ({ page }) => {
    const html = await fs.readFile('storage/framework/testing/vtu-ui/admin-pricing-current.html', 'utf8');
    await page.route('**/*', async route => {
        const path = new URL(route.request().url()).pathname;
        if (path === '/__pricing_test__') return route.fulfill({ contentType: 'text/html', body: html });
        if (path.endsWith('/js/vtu-data-catalogue.js')) return route.fulfill({ contentType: 'application/javascript', body: await fs.readFile('public/js/vtu-data-catalogue.js', 'utf8') });
        if (path === '/test-bootstrap.css') return route.fulfill({ contentType: 'text/css', body: await fs.readFile('node_modules/bootstrap/dist/css/bootstrap.min.css', 'utf8') });
        return route.abort();
    });
    await page.goto('http://localhost/__pricing_test__');
    await expect(page.locator('[name="vtu_waec_price"]')).toHaveValue('4301.25');
    await page.getByText('Add or update several bundles', { exact: true }).click();
    await page.locator('[data-add-plan]').click();
    await expect(page.locator('[data-catalogue-row]')).toHaveCount(2);
    await expect(page.locator('[name="plans[1][network]"]')).toHaveValue('MTN');
    await page.locator('[name="plans[1][amount]"]').fill('555.50');
    await page.locator('[data-remove-plan]').first().click();
    await expect(page.locator('[data-catalogue-row]')).toHaveCount(1);
    await expect(page.locator('[name="plans[1][amount]"]')).toHaveValue('555.50');
});
