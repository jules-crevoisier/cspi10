import { type Page, expect } from '@playwright/test';

export const ADMIN_EMAIL = 'e2e-admin@cspi10.test';
export const ADMIN_PASSWORD = 'admin';

/** Connecte l'utilisateur à l'administration. */
export async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/admin/login');
  await page.getByLabel('Adresse email').fill(ADMIN_EMAIL);
  await page.getByLabel('Mot de passe').fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: 'Se connecter' }).click();
  await expect(page).toHaveURL(/\/admin\/dashboard/);
}
