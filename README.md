# Video Annotation Web App

Small PHP/MySQL web application for uploading videos, watching them in a browser, and adding bookmarks and visual annotations (rectangle, circle, arrow, freehand) at specific timestamps.

## What This Project Does

- User registration and login (session-based auth)
- Video upload (MP4/WebM/OGG/MOV)
- Personal dashboard with uploaded videos
- Video watch page with:
- Timestamp bookmarks
- Canvas-based annotations (rectangle, circle, arrow, freehand)
- Saved annotation metadata in MySQL (`annotations.data` JSON)
- Basic admin dashboard (stats + all videos overview)

## Technologies Used

- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP (procedural endpoints + helper classes/functions)
- Database: MySQL (via PDO)
- Environment: XAMPP / Apache + MySQL (also works with PHP built-in server for the `public` folder)

## Project Structure

- `public/` UI pages (`index.php`, `register.php`, `dashboard.php`, `upload.php`, `watch.php`, `admin.php`)
- `api/` JSON endpoints for auth, videos, bookmarks, annotations
- `api/admin/` admin-only endpoints
- `assets/js/` frontend scripts (`auth.js`, `dashboard.js`, `upload.js`, `video.js`, `admin.js`)
- `assets/css/` styles
- `config/` DB and auth helpers
- `sql/schema.sql` database schema

## How to Run the Project

### 1. Place the project in a PHP server directory

For XAMPP, put it in:

```text
C:\xampp\htdocs\assesment-Mehmet-Mehmeti
```

### 2. Create the MySQL database

Create a database (the current code expects the DB name from `config/db.php`).

Current default in `config/db.php`:

- Database name: `Project`
- Username: `root`
- Password: `` (empty)

If you want a different DB name, update `config/db.php` first.

### 3. Import the schema

Import `sql/schema.sql` using phpMyAdmin or MySQL CLI.

Example (MySQL CLI):

```sql
CREATE DATABASE Project;
USE Project;
SOURCE sql/schema.sql;
```

### 4. Verify PHP extensions / settings

- `pdo_mysql` enabled
- `fileinfo` enabled (used for MIME validation on upload)
- File uploads enabled in PHP (`file_uploads = On`)
- `upload_max_filesize` and `post_max_size` should support your upload size (app validation allows up to 100 MB)

### 5. Ensure upload directory is writable

Uploaded videos are stored in:

- `public/uploads/videos/`

The API creates the folder if it does not exist, but the web server/PHP process still needs write permission.

### 6. Start and open the app

#### Option A: XAMPP (Apache)

Open:

```text
http://localhost/assesment-Mehmet-Mehmeti/public/index.php
```

#### Option B: PHP built-in server

From the project root:

```bash
php -S localhost:8000 -t public
```

Then open:

```text
http://localhost:8000/index.php
```

## Default Usage Flow

1. Register a new account
2. Login
3. Upload a video from `Upload` page
4. Open a video from the dashboard
5. Add bookmarks at timestamps
6. Use drawing tools to create annotations
7. Open the Annotations tab and click an item to re-render a saved annotation

## Admin Access (Important)

Newly registered users are created with role `user`.
There is no admin seed user in `sql/schema.sql`, so to test the admin page you must manually promote a user in MySQL:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

Admin page route:

```text
/public/admin.php
```

## Assumptions, Shortcuts, and Limitations

- Assumption: Running in a local XAMPP-style environment (`localhost`, MySQL root user, session-based auth).
- Shortcut: No framework is used (plain PHP endpoints + vanilla JS) to keep setup simple and transparent.
- Shortcut: No migrations/seeder system; schema is imported directly from `sql/schema.sql`.
- Limitation: Annotation drawing is canvas-based and coordinates are stored in pixel values; behavior may vary if the video display size changes significantly.
- Limitation: Saved annotations are listed and can be re-rendered on click, but there is no automatic timeline-based replay of all annotations during video playback.
- Limitation: Basic validation is implemented, but there is no advanced rate limiting, CSRF protection, or production hardening.
- Limitation: Video processing/transcoding is not implemented; uploaded files are served as-is.
- Limitation: Storage is local filesystem only (`public/uploads/videos`), not cloud/object storage.

## Notes

- Passwords are stored using `password_hash()`.
- `login.php` contains a compatibility fallback that upgrades legacy plain-text passwords to hashes on successful login.
