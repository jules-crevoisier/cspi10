# Déploiement initial — restauration prod (Dockploy)
#
# 1. Créer l'archive (sur votre PC) :
#      .\scripts\pack-deploy-backup.ps1
#
# 2. Sur le serveur Dockploy, créer le dossier backup :
#      mkdir -p /var/dockploy/cspi10/backup
#
# 3. Envoyer l'archive :
#      scp deploy-restore.tar.gz root@IP_SERVEUR:/var/dockploy/cspi10/backup/
#
# 4. Dans Dockploy → cspi10-prod → Volumes, ajouter :
#      Host : /var/dockploy/cspi10/backup
#      Container : /backup
#
# 5. Redéployer. Au démarrage le conteneur :
#      - détecte /backup/deploy-restore.tar.gz
#      - écrase cspi.db + uploads
#      - renomme l'archive en .applied (pas de double restore)
#
# Volumes persistants habituels (à garder) :
#      /var/dockploy/cspi10/data     -> /var/www/html/database/data
#      /var/dockploy/cspi10/uploads  -> /var/www/html/public/uploads
#
# Note : la restauration écrit dans database/data et public/uploads (volumes montés).
