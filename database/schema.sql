-- Schéma SQLite / Turso (libSQL) — CSPI10
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS biens (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  titre TEXT NOT NULL,
  type TEXT NOT NULL CHECK (type IN ('vente', 'location', 'location_etudiante')),
  adresse TEXT,
  adresse_publique TEXT,
  surface_m2 INTEGER,
  chambres INTEGER,
  salles_eau INTEGER,
  prix REAL,
  description TEXT,
  proprietaire_nom TEXT,
  proprietaire_prenom TEXT,
  proprietaire_adresse TEXT,
  proprietaire_email TEXT,
  proprietaire_telephone TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at TEXT
);

CREATE TABLE IF NOT EXISTS bien_images (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  bien_id INTEGER NOT NULL,
  url TEXT NOT NULL,
  is_primary INTEGER NOT NULL DEFAULT 0,
  position INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (bien_id) REFERENCES biens(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_bien_images_bien_id ON bien_images(bien_id);
CREATE INDEX IF NOT EXISTS idx_bien_images_primary ON bien_images(bien_id, is_primary);

CREATE TABLE IF NOT EXISTS actualites (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  titre TEXT NOT NULL,
  slug TEXT UNIQUE,
  categorie TEXT NOT NULL CHECK (categorie IN ('juridique', 'formation', 'evenement', 'autre')),
  extrait TEXT,
  contenu TEXT,
  publie_le TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at TEXT
);

CREATE TABLE IF NOT EXISTS actualite_images (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  actualite_id INTEGER NOT NULL,
  url TEXT NOT NULL,
  is_primary INTEGER NOT NULL DEFAULT 0,
  position INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (actualite_id) REFERENCES actualites(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_actualite_images_actualite_id ON actualite_images(actualite_id);

CREATE TABLE IF NOT EXISTS partenaires (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nom TEXT NOT NULL,
  description TEXT,
  logo_url TEXT,
  site_url TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at TEXT
);

CREATE TABLE IF NOT EXISTS administrateurs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS schema_migrations (
  version TEXT PRIMARY KEY,
  applied_at TEXT NOT NULL DEFAULT (datetime('now'))
);
