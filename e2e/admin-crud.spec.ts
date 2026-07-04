import { test, expect, type Page } from '@playwright/test';
import { loginAsAdmin } from './helpers/admin-auth';

test.describe.configure({ mode: 'serial' });

const suffix = Date.now();
const partenaireNom = `Partenaire E2E ${suffix}`;
const partenaireNomEdit = `Partenaire E2E modifié ${suffix}`;
const actualiteTitre = `Actualité E2E ${suffix}`;
const actualiteTitreEdit = `Actualité E2E modifiée ${suffix}`;
const bienTitre = `Bien E2E ${suffix}`;
const bienTitreEdit = `Bien E2E modifié ${suffix}`;

function rowByText(page: Page, text: string) {
  return page.getByRole('row').filter({ hasText: text });
}

async function confirmDelete(page: Page): Promise<void> {
  await page.getByRole('button', { name: /Oui, supprimer définitivement/i }).click();
}

test.describe('CRUD Partenaires', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('crée un partenaire', async ({ page }) => {
    await page.goto('/admin/partenaires/create');
    await page.locator('#nom').fill(partenaireNom);
    await page.locator('#description').fill('Description créée par test E2E');
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(page.getByText(partenaireNom)).toBeVisible();
  });

  test('modifie un partenaire', async ({ page }) => {
    await page.goto('/admin/partenaires');
    await rowByText(page, partenaireNom).locator('a.btn-primary').click();
    await page.locator('#nom').fill(partenaireNomEdit);
    await page.getByRole('button', { name: 'Mettre à jour' }).click();
    await expect(page.getByText(partenaireNomEdit)).toBeVisible();
  });

  test('supprime un partenaire', async ({ page }) => {
    await page.goto('/admin/partenaires');
    page.once('dialog', (dialog) => dialog.accept());
    await rowByText(page, partenaireNomEdit).locator('a.btn-danger').click();
    await confirmDelete(page);
    await expect(page.getByText(partenaireNomEdit)).not.toBeVisible();
  });
});

test.describe('CRUD Actualités', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('crée une actualité', async ({ page }) => {
    await page.goto('/admin/actualites/create');
    await page.locator('#titre').fill(actualiteTitre);
    await page.locator('#categorie').selectOption('juridique');
    await page.locator('#contenu').fill('<p>Contenu de test E2E</p>');
    await page.locator('#publie_le').fill('2026-01-15');
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(page.getByText(actualiteTitre)).toBeVisible();
  });

  test('modifie une actualité', async ({ page }) => {
    await page.goto('/admin/actualites');
    await rowByText(page, actualiteTitre).locator('a.btn-primary').click();
    await page.locator('#titre').fill(actualiteTitreEdit);
    await page.getByRole('button', { name: 'Mettre à jour' }).click();
    await expect(page.getByText(actualiteTitreEdit)).toBeVisible();
  });

  test('affiche l\'actualité sur le site public', async ({ page }) => {
    await page.goto('/actualites');
    await expect(page.getByText(actualiteTitreEdit)).toBeVisible();
  });

  test('supprime une actualité', async ({ page }) => {
    await page.goto('/admin/actualites');
    page.once('dialog', (dialog) => dialog.accept());
    await rowByText(page, actualiteTitreEdit).locator('a.btn-danger').click();
    await confirmDelete(page);
    await expect(page.getByText(actualiteTitreEdit)).not.toBeVisible();
  });
});

test.describe('CRUD Biens immobiliers', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('crée un bien', async ({ page }) => {
    await page.goto('/admin/biens/create');
    await page.locator('#titre').fill(bienTitre);
    await page.locator('#type').selectOption('vente');
    await page.locator('#adresse').fill('10 rue de la Paix, 10000 Troyes');
    await page.locator('#prix').fill('150000');
    await page.locator('#description').fill('Description bien E2E');
    await page.locator('#proprietaire_nom').fill('Dupont');
    await page.locator('#proprietaire_prenom').fill('Jean');
    await page.locator('#proprietaire_adresse').fill('1 rue Test, 10000 Troyes');
    await page.locator('#proprietaire_email').fill('proprio@test.local');
    await page.locator('#proprietaire_telephone').fill('0612345678');
    await page.getByRole('button', { name: 'Ajouter le bien' }).click();
    await expect(page.getByText(bienTitre)).toBeVisible();
  });

  test('modifie un bien', async ({ page }) => {
    await page.goto('/admin/biens');
    await rowByText(page, bienTitre).getByTitle('Modifier').click();
    await page.locator('#titre').fill(bienTitreEdit);
    await page.getByRole('button', { name: 'Mettre à jour le bien' }).click();
    await expect(page.getByText(bienTitreEdit)).toBeVisible();
  });

  test('affiche le bien sur le site public', async ({ page }) => {
    await page.goto('/biens');
    await expect(page.getByText(bienTitreEdit)).toBeVisible();
  });

  test('supprime un bien', async ({ page }) => {
    await page.goto('/admin/biens');
    await rowByText(page, bienTitreEdit).getByTitle('Supprimer').click();
    await confirmDelete(page);
    await expect(page.getByText(bienTitreEdit)).not.toBeVisible();
  });
});
