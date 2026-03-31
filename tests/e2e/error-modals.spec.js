import { test, expect } from '@playwright/test';

test.describe('Global Error Handling Modals', () => {

    test('Intercepts insufficient balance error and shows polished modal', async ({ page }) => {
        // Navigate to a page that loads the app.js bundle (e.g., login or home)
        await page.goto('/');

        // Mock an API endpoint to return the insufficient balance error
        await page.route('**/api/vtu/airtime', async route => {
            const json = { status: false, message: 'Insufficient balance. Please fund your wallet.', code: 'INSUFFICIENT_BALANCE' };
            await route.fulfill({ json });
        });

        // Evaluate a fetch request in the browser context using the global axios instance
        await page.evaluate(() => {
            window.axios.post('/api/vtu/airtime').catch(() => {});
        });

        // Verify the SweetAlert modal appears with the correct content
        const modal = page.locator('.swal2-popup.accessible-error-modal');
        await expect(modal).toBeVisible();

        const title = page.locator('.swal2-title');
        await expect(title).toHaveText('Insufficient Balance');

        const text = page.locator('.swal2-html-container');
        await expect(text).toContainText('You do not have enough funds to complete this transaction. Please fund your wallet.');

        const confirmBtn = page.locator('.swal2-confirm');
        await expect(confirmBtn).toHaveText('Add Funds');

        // Visual Regression Test
        await expect(modal).toHaveScreenshot('insufficient-balance-modal.png', { maxDiffPixels: 100 });
    });

    test('Intercepts unauthorized error and shows login modal', async ({ page }) => {
        await page.goto('/');

        await page.route('**/api/user/profile', async route => {
            await route.fulfill({ status: 401, json: { message: 'Unauthenticated.' } });
        });

        await page.evaluate(() => {
            window.axios.get('/api/user/profile').catch(() => {});
        });

        const modal = page.locator('.swal2-popup.accessible-error-modal');
        await expect(modal).toBeVisible();

        const title = page.locator('.swal2-title');
        await expect(title).toHaveText('Unauthorized');

        const confirmBtn = page.locator('.swal2-confirm');
        await expect(confirmBtn).toHaveText('Log In');
    });

    test('Intercepts generic error and shows default modal', async ({ page }) => {
        await page.goto('/');

        await page.route('**/api/some-endpoint', async route => {
            await route.fulfill({ status: 500, json: { message: 'Internal Server Error' } });
        });

        await page.evaluate(() => {
            window.axios.get('/api/some-endpoint').catch(() => {});
        });

        const modal = page.locator('.swal2-popup.accessible-error-modal');
        await expect(modal).toBeVisible();

        const title = page.locator('.swal2-title');
        await expect(title).toHaveText('An Error Occurred');
        
        const text = page.locator('.swal2-html-container');
        await expect(text).toHaveText('Internal Server Error');
    });
});
