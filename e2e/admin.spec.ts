import { test, expect } from '@playwright/test';
import { loginAsAdmin, ADMIN_EMAIL, ADMIN_PASSWORD } from './helpers/admin-auth';

test.describe('Administration', () => {
  test('redirige vers login si non authentifié', async ({ page }) => {
    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/\/admin\/login/);
  });

  test('connexion avec identifiants valides', async ({ page }) => {
    await loginAsAdmin(page);
    await expect(page.getByRole('heading', { name: 'Tableau de bord' })).toBeVisible();
  });

  test('refuse un mot de passe incorrect', async ({ page }) => {
    await page.goto('/admin/login');
    await page.getByLabel('Adresse email').fill(ADMIN_EMAIL);
    await page.getByLabel('Mot de passe').fill('mauvais-mot-de-passe');
    await page.getByRole('button', { name: 'Se connecter' }).click();

    await expect(page.getByRole('alert')).toContainText(/incorrect/i);
    await expect(page).toHaveURL(/\/admin\/login/);
  });

  test('accès aux sections admin après connexion', async ({ page }) => {
    await loginAsAdmin(page);

    for (const route of ['/admin/biens', '/admin/actualites', '/admin/partenaires']) {
      await page.goto(route);
      await expect(page).toHaveURL(new RegExp(route.replace('/', '\\/')));
    }
  });
});
