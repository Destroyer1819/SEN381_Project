import { expect } from '@playwright/test';

export const PASSWORD = process.env.E2E_PASSWORD ?? process.env.SEED_PASSWORD ?? 'password';

export const USERS = {
    requestor: 'requestor1@civicconnect.test',
    staff: 'staff1@civicconnect.test',
    management: 'management@civicconnect.test',
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
