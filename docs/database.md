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

Au premier démarrage, `scripts/migrate.php` crée le fichier et applique le schéma + seed admin. Le volume Docker **persiste** la base entre les redéploiements.

| Avantage | Détail |
|----------|--------|
| Simplicité | Un seul conteneur, pas de service BDD externe |
| Sauvegarde | Copier le volume ou le fichier `cspi.db` |
| Déploiement | Aucune dépendance cloud |

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
docker cp <container>:/var/www/html/public/uploads ./backup-uploads
```

Planifiez une copie régulière du volume ou du fichier en production.

## Restaurer une sauvegarde en production

Le script **écrase** la base et les uploads actuels (une copie de l'ancien état est gardée dans `storage/backups/`).

### 1. Préparer le backup sur votre machine

```
backup/
  cspi.db
  uploads/
    biens/
    actualites/
    partenaires/
```

Ou une archive : `backup.tar.gz` contenant ces fichiers.

### 2. Copier dans le conteneur

```powershell
$container = "cspi10-prod"

docker exec $container mkdir -p /tmp/restore/uploads
docker cp ./backup/cspi.db ${container}:/tmp/restore/cspi.db
docker cp ./backup/uploads/. ${container}:/tmp/restore/uploads/
```

### 3. Lancer la restauration

```powershell
docker exec $container bash docker/restore-backup.sh /tmp/restore
```

Alternative avec archive :

```powershell
docker cp ./backup.tar.gz ${container}:/tmp/backup.tar.gz
docker exec $container bash docker/restore-backup.sh /tmp/backup.tar.gz
```

### 4. Vérifier

- Site en ligne, actualités / biens / images visibles
- `/health` → `{"status":"ok"}`
- Connexion admin OK

L'ancienne base et les anciens uploads sont sauvegardés dans `/var/www/html/storage/backups/` (volume non monté par défaut — perdu au redeploy sauf si vous montez `storage/`).
