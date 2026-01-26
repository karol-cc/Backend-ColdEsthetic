# MySQL Workbench setup (Cold Esthetic)

## 1) Create DB + user
- Open MySQL Workbench and connect (usually as `root`).
- Open and run: `scripts/mysql_setup.sql`
- This creates:
  - Database: `coldesthetic`
  - User: `coldesthetic`
  - Password: `CHANGE_ME_DEV_PASSWORD` (replace in the SQL before running)

## 2) Switch Laravel to MySQL
Edit `.env`:
- Set:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=coldesthetic`
  - `DB_USERNAME=coldesthetic`
  - `DB_PASSWORD=CHANGE_ME_DEV_PASSWORD`

Optional but recommended while debugging connectivity:
- Keep:
  - `SESSION_DRIVER=file`
  - `CACHE_STORE=file`
  - `QUEUE_CONNECTION=sync`

## 3) Clear config cache + migrate
From `Backend-ColdEsthetic/`:
- `php artisan optimize:clear`
- `php artisan migrate`

## 4) Start server
- `php artisan serve --host=127.0.0.1 --port=8000`

If you get `Access denied`, the MySQL user/password or host permissions are not matching.
