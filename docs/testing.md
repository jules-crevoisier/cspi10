# Tests E2E (Playwright)

Les tests end-to-end vérifient le site public et l'administration dans un vrai navigateur Chromium.

## Prérequis

- PHP 8.2+
- Composer (`composer install`)
- Node.js 20+ (`npm install`)

## Installation

```powershell
composer install
npm install
npm run test:e2e:install
```

## Lancer les tests

```powershell
npm run test:e2e
```

Playwright démarre automatiquement un serveur PHP sur le port **8765** avec une base SQLite isolée (`database/data/cspi-test.db`).

### Autres commandes

| Commande | Description |
|----------|-------------|
| `npm run test:e2e:ui` | Mode interactif avec interface graphique |
| `npm run test:e2e:headed` | Navigateur visible |
| `npm run test:e2e:report` | Ouvrir le rapport HTML après échec |

## Couverture des tests

| Fichier | Scénarios |
|---------|-----------|
| `e2e/public.spec.ts` | Accueil, navigation, pages publiques, assets, `/health` |
| `e2e/admin.spec.ts` | Login (succès/échec), redirection si non connecté, accès aux sections admin |
| `e2e/admin-crud.spec.ts` | **CRUD complet** : partenaires, actualités, biens (créer, modifier, supprimer + affichage public) |

### Détail CRUD (`admin-crud.spec.ts`)

Les tests s'enchaînent en série (même base de test) :

| Entité | Création | Modification | Suppression | Site public |
|--------|----------|--------------|-------------|-------------|
| Partenaires | formulaire create | edit + « Mettre à jour » | double confirmation | — |
| Actualités | formulaire create | edit + « Mettre à jour » | double confirmation | liste `/actualites` |
| Biens | formulaire create | edit + « Mettre à jour le bien » | page confirmation | liste `/biens` |

> Les uploads d'images ne sont pas testés (champs optionnels). Les formulaires sont remplis sans fichier.

## Compte de test

Créé automatiquement par `scripts/e2e-setup.php` :

| | |
|---|---|
| Email | `e2e-admin@cspi10.test` |
| Mot de passe | `admin` |

> Ce compte n'existe que dans la base de test. Il n'est jamais utilisé en production.

## CI (GitHub Actions)

Le workflow `.github/workflows/e2e.yml` exécute les tests E2E sur chaque push et pull request vers `main` / `develop`.

En cas d'échec, le rapport Playwright est disponible en artifact GitHub.

## Dépannage

**Port 8765 déjà utilisé** — arrêtez l'autre processus ou définissez `E2E_PORT=8766`.

**Tests admin échouent** — relancez le setup :

```powershell
php scripts/e2e-setup.php
```

**Tests CRUD instables** — relancez toute la suite (`npm run test:e2e`) : les tests CRUD dépendent de l'ordre d'exécution dans `admin-crud.spec.ts`.
