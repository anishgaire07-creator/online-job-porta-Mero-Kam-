# Mero Kam — Job Portal

Full-stack job portal for XAMPP: **React (Vite + Tailwind)** frontend, **PHP 8+ (PDO, MVC-style)** REST API, **MySQL** database.

## Requirements

- XAMPP (Apache + MySQL + PHP 8.0+)
- Node.js 18+ (for building the frontend)
- Modern browser

## 1. Database

1. Start **MySQL** in XAMPP.
2. Open **phpMyAdmin** → Import `database/mero_kam.sql`.
3. Edit `backend/config/database.php` if your MySQL user/password differs from `root` / empty.

## 2. Backend (Apache)

1. Copy the project folder to `C:\xampp\htdocs\mero-kam\` (or symlink).
2. Ensure PHP extensions **pdo_mysql** and **fileinfo** are enabled (`php.ini`).
3. Create writable uploads: `backend/uploads/` (included with `.gitkeep`).
4. Restart Apache.

API base URL (default): `http://localhost/mero-kam/backend/api/`

### Default logins (from seed SQL)

| Role     | Email                 | Password  |
|----------|----------------------|-----------|
| Admin    | admin@merokam.local  | password  |
| Employer | employer@merokam.local | password |
| Seeker   | seeker@merokam.local | password  |

Change passwords before production.

### Email (optional)

Edit `backend/config/mail.php`. PHP `mail()` depends on your system (Windows: configure SMTP or a local mail catcher). Application emails fire on **job apply** (admin + applicant).

## 3. Frontend

```bash
cd frontend
npm install
npm run build
```

Copy **everything inside** `frontend/dist/` to:

`htdocs/mero-kam/app/`

So you have e.g. `htdocs/mero-kam/app/index.html`.

The app expects to be served at **`http://localhost/mero-kam/app/`** (see `vite.config.js` `base` and React `basename`).

### Development with Vite

```bash
cd frontend
npm run dev
```

Open the URL Vite prints (port 5173). The dev server proxies `/mero-kam/backend/*` to Apache so API calls stay on the same host and session cookies work.

If the proxy fails, ensure Apache is running and the project path matches `/mero-kam/`.

**Environment override:** create `frontend/.env`:

```env
VITE_API_BASE=http://localhost/mero-kam/backend/api
```

## 4. API endpoints (JSON)

| File | Purpose |
|------|---------|
| `register.php`, `login.php`, `logout.php`, `me.php` | Auth & profile |
| `home.php`, `get_jobs.php`, `get_job.php`, `search_suggestions.php` | Public jobs |
| `get_companies.php` | Companies |
| `apply_job.php`, `save_job.php`, `dashboard_seeker.php`, `resume.php`, `job_alerts.php`, `recommendations.php` | Seeker |
| `company_profile.php`, `post_job.php`, `employer_jobs.php`, `applicants.php`, `application_status.php`, `dashboard_employer.php`, `upload_profile.php` | Employer |
| `messages.php` | Messaging |
| `payments.php` | Plans & payments (demo completion) |
| `admin_*.php` | Admin |

All responses use `{ "ok": true/false, ... }` and JSON bodies where applicable. Session cookies: `credentials: 'include'` from the frontend.

## 5. Project layout

```
mero-kam/
├── database/mero_kam.sql
├── backend/
│   ├── api/              # REST entry points (controllers)
│   ├── config/
│   ├── core/
│   ├── helpers/
│   ├── models/
│   ├── uploads/
│   └── bootstrap.php
└── frontend/             # React app
```

## License

Use and modify freely for learning and production after hardening security and replacing demo data.
