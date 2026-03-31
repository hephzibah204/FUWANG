import { test, expect } from '@playwright/test';

test.describe('Dynamic WhatsApp Widget', () => {

    test('Widget renders correctly based on API config', async ({ page }) => {
        // Mock the config API endpoint
        await page.route('**/api/whatsapp-widget/config', async route => {
            const json = {
                status: true,
                data: {
                    enabled: true,
                    number: '2348012345678',
                    position: 'bottom-right',
                    x_offset: 40,
                    y_offset: 50,
                    size: 70,
                    color: '#ff0000',
                    hover_color: '#cc0000',
                    animation: 'bounce',
                    display_pages: 'all',
                    hours_start: '00:00',
                    hours_end: '23:59',
                    server_time: '12:00',
                    timezone: 'Africa/Lagos',
                    prefilled_message: 'Test Message'
                }
            };
            await route.fulfill({ json });
        });

        // Navigate to the app (ensure app.js is loaded)
        await page.goto('/');

        // Wait for widget to be injected
        const widget = page.locator('#wa-widget-btn');
        await expect(widget).toBeVisible();

        // Check CSS styles injected correctly
        await expect(widget).toHaveCSS('background-color', 'rgb(255, 0, 0)');
        await expect(widget).toHaveCSS('width', '70px');
        await expect(widget).toHaveCSS('height', '70px');
        await expect(widget).toHaveCSS('bottom', '50px');
        await expect(widget).toHaveCSS('right', '40px');

        // Check URL and pre-filled message
        await expect(widget).toHaveAttribute('href', 'https://wa.me/2348012345678?text=Test%20Message');
    });

    test('Widget does not render when disabled', async ({ page }) => {
        await page.route('**/api/whatsapp-widget/config', async route => {
            await route.fulfill({ json: { status: true, data: { enabled: false } } });
        });

        await page.goto('/');
        
        // Ensure the container exists but the button is not injected
        const widget = page.locator('#wa-widget-btn');
        await expect(widget).toHaveCount(0);
    });

    test('Widget does not render outside operating hours', async ({ page }) => {
        await page.route('**/api/whatsapp-widget/config', async route => {
            await route.fulfill({ json: { 
                status: true, 
                data: { 
                    enabled: true, 
                    number: '123',
                    hours_start: '09:00', 
                    hours_end: '17:00', 
                    server_time: '20:00' // Out of hours
                } 
            } });
        });

        await page.goto('/');
        const widget = page.locator('#wa-widget-btn');
        await expect(widget).toHaveCount(0);
    });

    test('Widget click fires analytics API', async ({ page }) => {
        await page.route('**/api/whatsapp-widget/config', async route => {
            await route.fulfill({ json: { status: true, data: { enabled: true, number: '123', hours_start: '00:00', hours_end: '23:59', server_time: '12:00' } } });
        });

        let clickApiCalled = false;
        await page.route('**/api/whatsapp-widget/click', async route => {
            clickApiCalled = true;
            await route.fulfill({ json: { status: true } });
        });

        await page.goto('/');
        const widget = page.locator('#wa-widget-btn');
        await expect(widget).toBeVisible();

        // Prevent navigation on click
        await page.evaluate(() => {
            document.getElementById('wa-widget-btn').removeAttribute('target');
            document.getElementById('wa-widget-btn').removeAttribute('href');
        });

        await widget.click();
        
        // Wait briefly for the network request
        await page.waitForTimeout(500);
        
        expect(clickApiCalled).toBeTruthy();
    });
});
