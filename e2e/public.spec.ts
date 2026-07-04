import { test, expect } from '@playwright/test';

test.describe('Site public', () => {
  test('page d\'accueil se charge avec le titre CSPI10', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/CSPI10/i);
    await expect(page.getByRole('heading', { level: 1 })).toContainText(/Chambre Syndicale/i);
  });

  test('navigation principale accessible', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('menuitem', { name: 'Actualités' })).toBeVisible();
    await expect(page.getByRole('menuitem', { name: 'Contact' })).toBeVisible();
  });

  test('pages publiques répondent en 200', async ({ page }) => {
    const routes = ['/actualites', '/biens', '/partenaires', '/contact', '/adhesion', '/mentions-legales'];

    for (const route of routes) {
      const response = await page.goto(route);
      expect(response?.status(), `Route ${route}`).toBe(200);
    }
  });

  test('assets CSS et JS chargés', async ({ page }) => {
    const failedRequests: string[] = [];
    page.on('response', (response) => {
      const url = response.url();
      if ((url.includes('/asset/css/') || url.includes('/asset/js/')) && response.status() >= 400) {
        failedRequests.push(`${response.status()} ${url}`);
      }
    });

    await page.goto('/');
    expect(failedRequests).toEqual([]);
  });

  test('endpoint health retourne ok', async ({ request }) => {
    const response = await request.get('/health');
    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    expect(body.status).toBe('ok');
  });
});
