# CSPI10 — Site Web

Site de la **Chambre Syndicale des Propriétaires Immobiliers de l'Aube**.

## Stack

| Composant | Technologie |
|-----------|-------------|
| Backend | PHP 8.2+, architecture MVC légère |
| Base de données | SQLite (fichier `.db` dans Docker) — [Turso](https://turso.tech) optionnel |
| Admin | Bootstrap 5 |
| Emails | [Resend](https://resend.com) |
| Tests E2E | [Playwright](https://playwright.dev) |
| Déploiement | Docker → [Dockploy](https://dockploy.com) |

## Démarrage rapide

```powershell
copy .env.example .env
composer install
php scripts/migrate.php
php -S localhost:8000 -t public public/index.php
```

| Ressource | URL |
|-----------|-----|
| Site | http://localhost:8000 |
| Admin | http://localhost:8000/admin/login |
| Health | http://localhost:8000/health |

**Identifiants admin par défaut** (seed) — voir `database/seed.sql`. Changez-les en production :

```powershell
php scripts/reset-admin-password.php VotreMotDePasse
```

## Tests

```powershell
npm install
npm run test:e2e:install
npm run test:e2e
```

Documentation complète : [docs/testing.md](docs/testing.md)

## Documentation

| Fichier | Contenu |
|---------|---------|
| [docs/database.md](docs/database.md) | SQLite dans Docker vs Turso |
| [docs/deployment.md](docs/deployment.md) | Déploiement Dockploy |
| [docs/testing.md](docs/testing.md) | Tests E2E Playwright |
| [docs/espace-adherent.md](docs/espace-adherent.md) | Espace adhérent |
| [.env.example](.env.example) | Variables d'environnement |

## Structure du projet

```
app/
  Core/           Database, Security, Flash, ErrorHandler
  controller/     Logique métier
  models/         Accès données
  bootstrap.php   Initialisation applicative
public/           Document root (Apache / serveur PHP)
database/
  schema.sql      Schéma SQLite / Turso
  seed.sql        Données initiales (admin)
e2e/              Tests Playwright
scripts/          migrate, reset-admin, e2e-setup
docker/           Apache + entrypoint
docs/             Documentation
```

## Docker local

```powershell
docker compose up --build
```

Site accessible sur http://localhost:8080

La base SQLite est stockée dans le volume Docker `cspi_data` (`database/data/cspi.db`). Voir [docs/database.md](docs/database.md).

## Sécurité

- Secrets via variables d'environnement uniquement
- CSRF sur l'administration
- Auth obligatoire sur `/admin/*`
- HTML des actualités filtré (anti-XSS)
- Verrouillage après 5 tentatives de connexion

## Licence

Projet privé — CSPI10.
