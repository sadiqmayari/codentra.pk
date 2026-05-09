<?php
/**
 * Local-dev SQLite bootstrap. Creates storage/test.db with the tables
 * needed for end-to-end smoke testing (users, remember_tokens, leads,
 * rate_limits) and seeds the admin user.
 *
 * Usage:
 *   php -c tools/php-dev.ini tools/setup-test-db.php
 *
 * Production keeps using MySQL via the standard Database class — this
 * script and the DB_DRIVER=sqlite path are local-only.
 */
declare(strict_types=1);

$dbPath = __DIR__ . '/../storage/test.db';
@unlink($dbPath);

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec(<<<SQL
CREATE TABLE users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  name          TEXT NOT NULL,
  email         TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role          TEXT NOT NULL DEFAULT 'editor',
  last_login_at TEXT,
  created_at    TEXT NOT NULL,
  updated_at    TEXT NOT NULL,
  deleted_at    TEXT
);

CREATE TABLE remember_tokens (
  id               INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id          INTEGER NOT NULL,
  selector         TEXT NOT NULL UNIQUE,
  hashed_validator TEXT NOT NULL,
  expires_at       TEXT NOT NULL,
  created_at       TEXT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE leads (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  name        TEXT NOT NULL,
  email       TEXT NOT NULL,
  phone       TEXT,
  company     TEXT,
  service     TEXT NOT NULL DEFAULT 'other',
  budget      TEXT,
  message     TEXT NOT NULL,
  source      TEXT NOT NULL DEFAULT 'website',
  status      TEXT NOT NULL DEFAULT 'new',
  notes       TEXT,
  ip_address  TEXT,
  user_agent  TEXT,
  created_at  TEXT NOT NULL,
  updated_at  TEXT NOT NULL,
  deleted_at  TEXT
);

CREATE TABLE rate_limits (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  key_hash   TEXT NOT NULL,
  attempts   INTEGER NOT NULL DEFAULT 0,
  expires_at TEXT NOT NULL
);
SQL);

// Same Argon2id hash from sql/seed.sql — password is Cdt!ra#9X\$bozJ5nl8
$pdo->prepare(
    "INSERT INTO users (name, email, password_hash, role, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?)"
)->execute([
    'Codentra Admin',
    'admin@codentra.pk',
    '$argon2id$v=19$m=65536,t=3,p=4$CR087QHVUsOZJ5bF0Yyq+A$+dP4caDIU3Aeb2TzdJl6Q1BNO/GM/dqTKmid+YGPXdM',
    'admin',
    date('Y-m-d H:i:s'),
    date('Y-m-d H:i:s'),
]);

echo "✓ Bootstrapped test DB at: {$dbPath}\n";
echo "  Login: admin@codentra.pk / Cdt!ra#9X\$bozJ5nl8\n";
