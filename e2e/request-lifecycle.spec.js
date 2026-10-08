import { expect, test } from '@playwright/test';
import { USERS, login, logout } from './helpers.js';

// TC-E2E-01 | FR-001, FR-002, FR-003, FR-004, FR-005 | Critical journey across three screens and two roles:
// requestor submits -> staff finds, takes, works and resolves -> requestor sees the outcome and audit trail.
test('request is submitted, handled by staff and the requestor sees the result', async ({ page }) => {
    const marker = `E2E-${Date.now()}`;
    const description = `Projector in room 204 does not turn on (${marker})`;

    // --- requestor submits
    await login(page, USERS.requestor);
    await page.getByRole('link', { name: 'New request' }).first().click();
    await page.getByLabel('Category').selectOption({ label: 'IT support' });
    await page.getByLabel('Location').fill('Room 204');
    await page.getByLabel('Area').fill('Admin block');
    await page.getByLabel('Description').fill(description);
    await page.getByRole('button', { name: 'Submit request' }).click();

    await expect(page.getByRole('status')).toContainText('Request submitted');
    const reference = (await page.getByRole('heading', { level: 1 }).innerText()).trim();
    expect(reference).toMatch(/^CC-\d+$/);
    await expect(page.locator('[data-status="open"]')).toBeVisible();
    await logout(page);

    // --- staff finds it by searching, takes it, starts work, resolves it
    await login(page, USERS.staff);
    await page.getByLabel('Search').fill(marker);
    await page.getByRole('button', { name: 'Apply' }).click();
    await page.getByRole('link', { name: reference }).click();

    await page.getByRole('button', { name: 'Take this request' }).click();
    await expect(page.locator('[data-status="assigned"]')).toBeVisible();

    await page.getByLabel('Next step').selectOption({ label: 'Start work' });
    await page.getByRole('button', { name: 'Apply' }).click();
    await expect(page.locator('[data-status="in_progress"]')).toBeVisible();

    await page.getByLabel('Next step').selectOption({ label: 'Mark as resolved' });
    await page.getByLabel('Resolution note').fill('Replaced the power cable.');
    await page.getByRole('button', { name: 'Apply' }).click();
    await expect(page.locator('[data-status="resolved"]')).toBeVisible();
    await logout(page);

    // --- requestor sees the final state, the resolution note and the history
    await login(page, USERS.requestor);
    await page.getByRole('link', { name: reference }).click();
    await expect(page.locator('[data-status="resolved"]')).toBeVisible();
    await expect(page.getByText('Replaced the power cable.')).toBeVisible();
    await expect(page.getByText('In progress → Resolved')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Take this request' })).toHaveCount(0);
});

// TC-E2E-02 | FR-008 | Negative / unauthorised behaviour seen through the UI
test('a requestor is kept out of staff and management areas', async ({ page }) => {
    // not signed in
    await page.goto('/requests');
    await expect(page).toHaveURL(/\/login/);

    // signed in as requestor
    await login(page, USERS.requestor);
    await expect(page.getByRole('link', { name: 'Dashboard' })).toHaveCount(0);
    const response = await page.goto('/dashboard');
    expect(response?.status()).toBe(403);
});

// TC-E2E-03 | FR-007 | Management sees dashboard totals that respond to a category filter
test('management dashboard shows counts and can be filtered by category', async ({ page }) => {
    await login(page, USERS.management);
    await page.getByRole('link', { name: 'Dashboard' }).click();
    await expect(page.getByRole('heading', { name: 'Management dashboard' })).toBeVisible();

    const total = Number(await page.getByTestId('total').innerText());
    expect(total).toBeGreaterThan(0);

    await page.getByLabel('Category').selectOption({ label: 'Lost property' });
    await page.getByRole('button', { name: 'Apply' }).click();
    await expect(page).toHaveURL(/category_id=4/);
    const filtered = Number(await page.getByTestId('total').innerText());
    expect(filtered).toBeLessThanOrEqual(total);
});
