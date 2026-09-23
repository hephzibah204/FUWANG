import { test, expect } from '@playwright/test';

test.describe('Core User Journeys', () => {
  
  test('Homepage loads correctly and has expected SEO meta tags', async ({ page }) => {
    await page.goto('/');
    
    // Check Title
    await expect(page).toHaveTitle(/Fuwa.NG/);
    
    // Check main call to actions
    const getStartedBtn = page.getByRole('link', { name: /Get Started/i });
    await expect(getStartedBtn.first()).toBeVisible();
  });

  test('User Registration Form Validation', async ({ page }) => {
    await page.goto('/register');
    
    await expect(page.getByRole('heading', { name: /Register|Create your Account/i })).toBeVisible();
    
    // Check that required fields exist
    await expect(page.locator('#fullname')).toHaveAttribute('required', '');
    
    // Attempt submission with novalidate to test error response
    await page.$eval('#registerForm', form => form.noValidate = true);
    await page.getByRole('button', { name: /Register|Submit|Create Account/i }).click();
    
    // Assert error container appears
    const errorAlert = page.locator('#errorMsgContainer, .alert-danger, .invalid-feedback').first();
    await expect(errorAlert).toBeVisible();
  });

  test('User Login Flow', async ({ page }) => {
    await page.goto('/login');
    
    await page.getByLabel(/Email/i).fill('testuser@fuwa.ng');
    await page.locator('input[name="password"]').fill('password123');
    await page.getByRole('button', { name: /Login|Sign in/i }).click();
  });

});
