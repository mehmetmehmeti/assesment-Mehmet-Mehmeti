# Video Annotation Web App

PHP/MySQL web application for uploading videos, watching them in the browser, and creating timestamped bookmarks and visual annotations (rectangle, circle, arrow, freehand).

## Features

- User registration and login (session-based auth)
- Video upload with MIME validation (`mp4`, `webm`, `ogg`, `mov`)
- Personal dashboard with uploaded videos
- Video watch page with:
- Timestamp bookmarks
- Canvas annotations (rectangle, circle, arrow, freehand)
- Annotation popup playback at matching timestamps
- Multi-shape annotation drafts saved as grouped JSON
- Bulk delete selected annotations
- Admin dashboard with global stats and all videos overview

## Tech Stack

- Frontend: HTML, CSS, Vanilla JavaScript
- Backend: PHP (procedural API endpoints)
- Database: MySQL (PDO)
- Runtime: XAMPP (Apache + MySQL) or PHP built-in server

## Project Structure

- `public/` UI pages (`index.php`, `register.php`, `dashboard.php`, `upload.php`, `watch.php`, `admin.php`)
- `api/` JSON endpoints (auth, videos, bookmarks, annotations)
- `api/admin/` admin-only endpoints
- `assets/js/` frontend scripts
- `assets/css/` styles
- `config/db.php` database connection singleton (PDO)
- `config/auth.php` session/auth helpers
- `sql/schema.sql` database schema
- `public/uploads/videos/` uploaded video files (created automatically if missing)

## Prerequisites

- PHP 8.x recommended (PDO + sessions + file uploads enabled)
- MySQL / MariaDB
- Apache (XAMPP) or `php -S`
- PHP extensions:
- `pdo_mysql`
- `fileinfo` (used for MIME detection during upload)

## Quick Start (XAMPP)

1. Place the project in:

```text
C:\xampp\htdocs\assesment-Mehmet-Mehmeti
```

2. Start Apache and MySQL in XAMPP.

3. Create the database used by the app (default is `Project`).

4. Import the schema from `sql/schema.sql`.

5. Verify DB credentials in `config/db.php`:

- Host: `localhost`
- Database: `Project`
- Username: `root`
- Password: empty (`''`)

6. Open:

```text
http://localhost/assesment-Mehmet-Mehmeti/public/index.php
```

## Quick Start (PHP Built-in Server)

1. Configure `config/db.php` to point to a running MySQL instance.
2. From project root run:

```bash
php -S localhost:8000 -t public
```

3. Open:

```text
http://localhost:8000/index.php
```

## Database Setup

Example using MySQL CLI:

```sql
CREATE DATABASE Project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Project;
SOURCE sql/schema.sql;
```

## Default User Flow

1. Register an account (`/public/register.php`)
2. Log in (`/public/index.php`)
3. Upload a video (`/public/upload.php`)
4. Open a video from the dashboard (`/public/watch.php?id=<video_id>`)
5. Add bookmarks at current playback time
6. Add annotation text, draw one or more shapes, then press Play to save
7. Preview annotations from the Annotations tab

## Roles and Permissions

- `user` (default):
- Manage only their own videos/bookmarks/annotations
- Access dashboard, upload, watch pages
- `admin`:
- Access `/public/admin.php`
- View all videos and aggregate stats
- Can access videos/bookmarks/annotations across users via admin-aware API checks

### Promote a User to Admin (manual)

No seed admin is created by `sql/schema.sql`. Promote one manually:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

## API Endpoints (Current)

All endpoints return JSON. Most endpoints require an authenticated session cookie.

### Auth

- `POST /api/register.php`
- Body: JSON or form-data
- Fields: `email`, `password`, `confirm_password`
- `POST /api/login.php`
- Body: JSON or form-data
- Fields: `email`, `password`
- Starts PHP session on success

### Videos

- `POST /api/upload_video.php` (auth required)
- Form-data fields:
- `video` (file)
- `title` (optional; defaults to original filename)
- `description` (optional)
- `original_name` (optional compatibility field)
- Upload limits:
- Allowed MIME: `video/mp4`, `video/webm`, `video/ogg`, `video/quicktime`
- Max size: `100 MB`

- `GET /api/get_videos.php` (auth required)
- Returns current user's videos with `bookmark_count` and `annotation_count`

- `GET /api/get_single_video.php?id=<video_id>` (auth required)
- Users can access own videos; admins can access any video

### Bookmarks

- `POST /api/add_bookmark.php` (auth required)
- Body: JSON or form-data
- Fields: `video_id`, `timestamp`, `title`

- `GET /api/get_bookmarks.php?video_id=<video_id>` (auth required)
- Returns bookmarks sorted by timestamp and includes `time_formatted`

- `DELETE /api/delete_bookmark.php?id=<bookmark_id>` (auth required)
- Endpoint exists; current UI does not expose bookmark deletion

### Annotations

- `POST /api/add_annotation.php` (auth required)
- Body: JSON or form-data
- Fields: `video_id`, `timestamp`, `description`, `data`
- `data` can be JSON string or object/array; API normalizes and stores JSON

- `GET /api/get_annotations.php?video_id=<video_id>` (auth required)
- Returns annotations sorted by timestamp and includes `time_formatted`

- `DELETE /api/delete_annotation.php?id=<annotation_id>` (auth required)
- Used by watch page bulk deletion flow

### Admin

- `GET /api/admin/get_stats.php` (admin only)
- Returns counts for users, videos, bookmarks, annotations

- `GET /api/admin/get_all_videos.php` (admin only)
- Returns all videos with owner email and bookmark/annotation counts

## Data Model Summary

### `users`

- `email` unique
- `password` stores hash (`password_hash`)
- `role` is `user` or `admin`

### `videos`

- Belongs to `users`
- Stores generated `filename` and optional `original_name`
- Includes `title` and `description`

### `bookmarks`

- Belongs to `videos` and `users`
- Stores integer `timestamp` (seconds) and `title`

### `annotations`

- Belongs to `videos` and `users`
- Stores integer `timestamp` (seconds)
- `description` text (shown in UI popup/list)
- `data` JSON (shape metadata)

## Annotation Data Format (Stored in `annotations.data`)

The app stores drawing metadata as JSON. Examples:

Single rectangle:

```json
{
  "tool": "rectangle",
  "x": 120,
  "y": 80,
  "width": 200,
  "height": 90,
  "color": "#ef4444",
  "lineWidth": 3
}
```

Grouped multi-shape annotation (current UI behavior while drawing multiple shapes before save):

```json
{
  "tool": "group",
  "items": [
    { "tool": "arrow", "startX": 10, "startY": 20, "endX": 140, "endY": 90, "color": "#ef4444", "lineWidth": 3 },
    { "tool": "circle", "x": 220, "y": 140, "radius": 30, "color": "#3b82f6", "lineWidth": 3 }
  ]
}
```

## Configuration Notes

### Database (`config/db.php`)

Update these fields if your environment differs:

- `$host`
- `$dbname`
- `$username`
- `$password`

### Upload Storage

- Uploads are saved to `public/uploads/videos/`
- The API creates the directory if missing (`mkdir(..., 0755, true)`)
- The PHP/Apache process must have write permission

### PHP Upload Limits

App-level validation allows up to `100 MB`, but PHP config may block smaller limits first:

- `upload_max_filesize`
- `post_max_size`
- `max_file_uploads`

## Security / Implementation Notes

- Passwords are hashed using `password_hash()`
- `api/login.php` contains a legacy plain-text password fallback and upgrades to hash on successful login
- Authentication uses PHP sessions (no JWT)
- Access checks are enforced in API endpoints for user/admin ownership
- This is a local/dev-style app and is not production-hardened

## Limitations

- No video transcoding/processing; files are served as uploaded
- No cloud storage integration (local filesystem only)
- No migrations/seeders (schema import only)
- No CSRF protection or rate limiting
- Annotation coordinates are canvas pixel coordinates; resizing can affect alignment
- Bookmark deletion API exists but bookmark delete UI is not implemented in the current frontend

## Troubleshooting

### "Database connection failed"

- Check `config/db.php` credentials
- Confirm MySQL is running
- Confirm the `Project` database exists (or update `$dbname`)

### Upload fails with size error

- Increase PHP `upload_max_filesize` and `post_max_size`
- Confirm file is <= 100 MB (app validation)

### Upload fails with invalid file type

- The API validates MIME via `fileinfo`; accepted values are limited to MP4/WebM/OGG/MOV

### API redirects instead of returning JSON

- Auth-protected endpoints call `requireAuth()` from `config/auth.php`, which redirects to login when session is missing
- Log in from the UI first so the browser sends the session cookie

### Admin page not visible

- Ensure the logged-in user's `role` is `admin`
- Log out and log back in after updating the DB role so the session refreshes

## Suggested Improvements (Next)

- Add `.env` support for DB credentials
- Add bookmark delete UI
- Add video delete endpoint/UI (with file cleanup)
- Add migrations/seed data
- Add CSRF protection and stronger validation
- Add responsive canvas coordinate normalization
