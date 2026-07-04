# Déploiement (Dockploy)

Guide pas à pas pour déployer le site CSPI10 sur [Dockploy](https://dockploy.com).

## 1. Prérequis

- Compte Dockploy configuré
- Dépôt Git connecté
- Domaine configuré (optionnel en preview)

## 2. Configuration du service

| Paramètre | Valeur |
|-----------|--------|
| Type de build | Dockerfile |
| Dockerfile | `./Dockerfile` (racine) |
| **Container Port** | **`8080`** (interne — pas de bind sur le port 80 de l'hôte) |
| Health check | `GET /health` |

> **Important** : le conteneur écoute sur le port **8080** en interne. Dockploy / Traefik route le trafic HTTPS (443) vers ce port via le réseau Docker — **ne publiez pas** de port sur l'hôte dans Dockploy.

## 3. Volumes persistants

| Chemin conteneur | Usage |
|------------------|-------|
| `/var/www/html/public/uploads` | Images uploadées (biens, actualités, partenaires) |
| `/var/www/html/database/data` | Base SQLite (si pas Turso) |

## 4. Variables d'environnement

Copier depuis `.env.example` et renseigner :

> **HTTPS** : `APP_URL` et `SITE_URL` doivent commencer par `https://` en production.
> Dans Dockploy, activez HTTPS + Let's Encrypt sur le domaine (Container Port **8080**).

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.fr
APP_SECRET=<32+ caractères aléatoires>

# Option A — SQLite (recommandé)
DATABASE_PATH=database/data/cspi.db

RESEND_API_KEY=<clé Resend — jamais dans le code>
CONTACT_FROM_EMAIL=no-reply@votre-domaine.fr
CONTACT_TO_EMAIL=contact@votre-domaine.fr
CONTACT_FROM_NAME="Site CSPI10"
ESPACE_ADHERENT_PASSWORD=<mot de passe fort>
SITE_URL=https://votre-domaine.fr
```

Voir [docs/security.md](security.md) pour la gestion des secrets et la rotation des clés.

## 5. Premier déploiement

Au démarrage, le conteneur exécute automatiquement :

1. Copie `.env.example` → `.env` si absent
2. `php scripts/migrate.php` (schéma + seed admin)

**Changez immédiatement le mot de passe admin** :

```bash
docker exec -it <container> php scripts/reset-admin-password.php VotreMotDePasse
```

## 6. Vérification post-déploiement

- [ ] `https://votre-domaine.fr/` — page d'accueil
- [ ] `https://votre-domaine.fr/health` — `{"status":"ok"}`
- [ ] `/admin/login` — connexion admin
- [ ] Formulaire de contact (avec Resend configuré)
- [ ] Upload d'image dans l'admin

## 7. Mises à jour

Push sur la branche connectée → Dockploy rebuild et redéploie.

Les volumes persistent les uploads et la base SQLite entre les déploiements.
