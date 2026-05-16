# Afrostrength — database

Two SQL files:

- `schema.sql` — table definitions. Idempotent (`CREATE TABLE IF NOT EXISTS`).
- `seed.sql`   — content. Idempotent (`REPLACE INTO`). Re-running is safe.

## Import (cPanel phpMyAdmin)

1. Create a database (e.g. `cpaneluser_afrostrength`) and a database user with full privileges on it.
2. In phpMyAdmin → Import → upload `schema.sql`. Run.
3. Repeat with `seed.sql`.
4. Update `/config/database.php` (or set `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS` env vars).

## Import (CLI)

```sh
mysql -u <user> -p <database> < database/schema.sql
mysql -u <user> -p <database> < database/seed.sql
```

## Create the first admin

```sh
php scripts/create-admin.php <username> <password>
```

The script bcrypts the password and writes the row directly.
