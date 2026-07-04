# Espace adhérent

Section privée du site, accessible aux membres via un mot de passe partagé.

## URLs

| Page | URL |
|------|-----|
| Connexion | `/espace-adherent-login.php` |
| Contenu protégé | `/espace-adherent.php` |

## Configuration

Définir le mot de passe dans `.env` :

```env
ESPACE_ADHERENT_PASSWORD=votre_mot_de_passe_securise
```

La session expire après **24 heures** par défaut.

## Sécurité

- Ne jamais committer le mot de passe dans le code
- Utiliser un mot de passe fort en production
- Envisager un vrai système de comptes si le nombre d'adhérents augmente
