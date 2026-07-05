-- Compte administrateur CSPI10 (production)
INSERT INTO administrateurs (id, email, password, created_at)
VALUES (
  1,
  'chambredesproprietaires10@gmail.com',
  '$2y$12$QaOo51viuy3nzGYyh8ACoea27gzZNnWy78lxp1ah5AXiAOmVZ98Ye',
  datetime('now')
)
ON CONFLICT(email) DO UPDATE SET
  password = excluded.password;
