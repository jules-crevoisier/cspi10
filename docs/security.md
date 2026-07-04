# Sécurité et secrets

## Règle d'or

**Aucun secret dans le code source.** Toutes les clés et mots de passe passent par `.env` (local) ou les variables d'environnement Dockploy (production).

Fichiers jamais commités :

- `.env`
- `database/data/*.db`
- `backup-*.sql`
- `public/uploads/`

## Variables sensibles

| Variable | Usage |
|----------|--------|
| `APP_SECRET` | Secret applicatif (sessions, futures signatures) |
| `RESEND_API_KEY` | API Resend — formulaire de contact |
| `ESPACE_ADHERENT_PASSWORD` | Mot de passe espace adhérent |
| `DATABASE_PATH` | Chemin SQLite (pas un secret, mais données sensibles) |

## Clé Resend exposée dans Git ?

Si GitGuardian a détecté une clé Resend dans l'historique Git :

1. **Révoquer immédiatement** l'ancienne clé sur [resend.com/api-keys](https://resend.com/api-keys)
2. **Créer une nouvelle clé** et la mettre dans Dockploy → `RESEND_API_KEY`
3. L'historique Git conserve l'ancienne clé tant qu'on ne réécrit pas l'historique — la rotation suffit en pratique

Pour purger l'historique (optionnel, avancé) :

```bash
# Après rotation de la clé — réécrit tout l'historique
git filter-repo --path app/config/config.php --invert-paths
# ou BFG Repo-Cleaner sur la chaîne leakée
```

## Docker (production)

- Port interne **8080** uniquement — pas de bind sur le port 80 de l'hôte
- Traefik / Dockploy route le trafic HTTPS vers le conteneur
- Filesystem **read-only** + volumes pour données persistantes
- `composer audit` + `npm audit` en CI (workflow `security.yml`)

## Vérification locale

Avant chaque push, chercher des fuites :

```powershell
git grep -i "re_" -- "*.php" "*.env" "*.sql"
git grep -i "password.*=" -- "app/" "public/"
```

Aucun résultat ne doit contenir de vraie clé API.

## Production (Dockploy)

Configure **uniquement** via l'interface Dockploy, pas via un fichier `.env` dans l'image Docker (`.env` est dans `.dockerignore`).
