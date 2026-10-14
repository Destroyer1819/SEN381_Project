import { expect } from '@playwright/test';

export const PASSWORD = process.env.E2E_PASSWORD ?? process.env.SEED_PASSWORD ?? 'password';

// Accounts created by database/seeders/ScenarioSeeder.php (run: php artisan migrate:fresh --seed).
// Every seeded password is 'password'. Override with E2E_* variables to test against other accounts.
export const USERS = {
    requestor: process.env.E2E_REQUESTOR ?? 'thandi.nkosi@example.com', // requestor (user 1)
    staff: process.env.E2E_STAFF ?? 'lerato.mokoena@example.com', // staff (user 5)
    management: process.env.E2E_MANAGEMENT ?? 'grace.naidoo@example.com', // management (user 8)
};

export async function login(page, email) {
    await page.goto('/login');
    await page.getByLabel('Email address').fill(email);
    await page.getByLabel('Password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/requests/);
}

// Signing out through the user menu is covered by the starter kit; clearing cookies is the quickest way to switch role.
export async function logout(page) {
    await page.context().clearCookies();
    await page.goto('/login');
    await expect(page).toHaveURL(/\/login/);
}
