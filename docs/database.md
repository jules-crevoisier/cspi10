# Base de données

Le site utilise **SQLite** par défaut : un fichier `.db` sur disque, sans serveur séparé.

## SQLite dans Docker (recommandé)

C'est la configuration la plus simple pour Dockploy ou Docker local.

```yaml
# docker-compose.yml (déjà configuré)
volumes:
  - cspi_data:/var/www/html/database/data
```

```env
DATABASE_PATH=database/data/cspi.db
```

Au premier démarrage, l'entrypoint copie `cspi.db` et les uploads depuis l'image (données versionnées dans Git), puis `scripts/migrate.php` applique le schéma. Le volume Docker **persiste** la base entre les redéploiements.

| Avantage | Détail |
|----------|--------|
| Simplicité | Un seul conteneur, pas de service BDD externe |
| Déploiement | `cspi.db` + `public/uploads/` commités dans Git |
| Sauvegarde | Copier le volume ou le fichier `cspi.db` |

## Turso — qu'est-ce que c'est ?

[Turso](https://turso.tech) est un **service cloud** basé sur **libSQL** (fork de SQLite). Ce n'est **pas** un simple fichier `.db` local : la base tourne sur les serveurs Turso et s'y connecte via HTTP avec un token.

| Turso | SQLite fichier |
|-------|----------------|
| Hébergé par Turso | Fichier dans votre conteneur |
| Réplication / edge | Volume Docker |
| URL + token requis | `DATABASE_PATH` suffit |

Turso est **optionnel** dans ce projet. Utilisez-le seulement si vous voulez une base managée multi-régions sans gérer vous-même les sauvegardes du fichier `.db`.

## Auto-héberger Turso / libSQL ?

Techniquement, Turso repose sur **libSQL/sqld** (`libsql-server`), que l'on peut auto-héberger. C'est **plus complexe** qu'un SQLite fichier :

- Service séparé à maintenir
- Configuration réseau, auth, sauvegardes
- Pas d'avantage par rapport à `DATABASE_PATH` + volume Docker pour un site mono-instance

**Conclusion** : pour votre cas (Docker + Dockploy), gardez **SQLite fichier dans le volume**. C'est l'équivalent « un `.db` dans le conteneur » que vous recherchez.

## Configuration `.env`

```env
# Par défaut — SQLite locale
DATABASE_PATH=database/data/cspi.db

# Option avancée — Turso cloud (désactive DATABASE_PATH)
# TURSO_DATABASE_URL=libsql://votre-db.turso.io
# TURSO_AUTH_TOKEN=votre_token
```

Si `TURSO_DATABASE_URL` et `TURSO_AUTH_TOKEN` sont définis, l'application se connecte à Turso. Sinon, elle utilise `DATABASE_PATH`.

## Migrations

```powershell
php scripts/migrate.php
```

Applique `database/schema.sql` puis `database/seed.sql` sur la base cible (SQLite ou Turso).

## Sauvegarde (SQLite Docker)

```powershell
# Copier depuis le conteneur
docker cp <container>:/var/www/html/database/data/cspi.db ./backup-cspi.db
```

Planifiez une copie régulière du volume ou du fichier en production.
