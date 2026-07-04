-- Données initiales — mot de passe par défaut : admin (À CHANGER en production !)
-- Hash régénéré avec password_hash('admin', PASSWORD_BCRYPT)
INSERT OR IGNORE INTO administrateurs (id, email, password, created_at)
VALUES (
  1,
  'chambredesproprietaires10@gmail.com',
  '$2y$12$YyAMYLKVTYn1l0wgT/Glpu1lgKCZ7ujFw5qUElcb3XOgNsbJwIESG',
  datetime('now')
);
