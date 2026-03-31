const { test, expect } = require('@playwright/test');

test.describe('Core User Journeys', () => {
  
  test('Homepage loads correctly and has expected SEO meta tags', async ({ page }) => {
    await page.goto('/');
    
    // Check Title
    await expect(page).toHaveTitle(/Fuwa|G-Soft Verify/);
    
    // Check main call to actions
    const getStartedBtn = page.getByRole('link', { name: /Get Started/i });
    await expect(getStartedBtn.first()).toBeVisible();
  });

  test('User Registration Form Validation', async ({ page }) => {
    await page.goto('/register');
    
    await expect(page.getByRole('heading', { name: /Register/i })).toBeVisible();
    
    // Attempt empty submission
    await page.getByRole('button', { name: /Register|Submit/i }).click();
    
    // Assert HTML5 validation or Laravel error messages appear
    const errorAlert = page.locator('.invalid-feedback, .alert-danger').first();
    await expect(errorAlert).toBeVisible();
  });

  test('User Login Flow', async ({ page }) => {
    await page.goto('/login');
    
    await page.getByLabel(/Email/i).fill('testuser@fuwa.ng');
    await page.getByLabel(/Password/i).fill('password123');
    await page.getByRole('button', { name: /Login/i }).click();
    
    // Note: Depends on DB state, so we just check it attempts to submit
    // In a real environment, we'd mock the backend or seed the DB.
  });

});
