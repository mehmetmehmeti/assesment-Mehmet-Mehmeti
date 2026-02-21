
# Video Annotation (HTML/CSS/JS + PHP)

## Quick start (local)

1) Create a MySQL database (default name in `config/db.php` is `assesment`).

2) Import the schema:

```sql
-- run in phpMyAdmin / MySQL client
SOURCE sql/schema.sql;
```

3) Update DB credentials in `config/db.php` if needed.

4) Serve the project:

### Option A: XAMPP / Laragon
- Put the project folder into your `htdocs` (or Laragon `www`).
- Open: `http://localhost/<project-folder>/public/index.php`

### Option B: PHP built-in server
From the project root:

```bash
php -S localhost:8000 -t public
```

Then open: `http://localhost:8000/index.php`

## Notes
- Passwords are stored **hashed**. If you had old plain-text users, first login will automatically upgrade them to a secure hash.

