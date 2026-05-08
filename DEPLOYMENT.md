# Codentra — Deployment Guide (Hostinger)

## 🌐 One-Time Setup

### 1. Domain → Hostinger
- In hPanel: **Domains → Manage → DNS** — confirm A record points to Hostinger's server IP.
- If domain registered elsewhere: update nameservers to `ns1.dns-parking.com` / `ns2.dns-parking.com` (or per Hostinger's instructions).

### 2. SSL Certificate
- hPanel → **Security → SSL** — install free Let's Encrypt for `codentra.pk` and `www.codentra.pk`.
- Enable **Force HTTPS**.

### 3. PHP Version
- hPanel → **Advanced → PHP Configuration** — select **PHP 8.2 or 8.3**.
- Enable extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd` (for WebP), `fileinfo`, `opcache`.

### 4. PHP / OPcache Tuning
hPanel → **PHP Configuration → PHP options** — set:
```
memory_limit = 256M
upload_max_filesize = 16M
post_max_size = 20M
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0   # turn ON only when actively deploying changes
```

### 5. MySQL Database
- hPanel → **Databases → MySQL** — Create:
  - DB name: `codentra_main` (Hostinger may prefix it like `u123_codentra_main`)
  - User: `codentra_user` with strong password
  - Grant: ALL on this DB
- Note the **host** (usually `localhost` for Hostinger), DB name, user, password.

### 6. Git Auto-Deploy
- hPanel → **Advanced → Git** — Connect your GitHub/GitLab repo:
  - Repository URL
  - Branch: `main`
  - Install path: `/public_html` (or whatever your domain points to)
- Enable **Auto-deploy on push**.
- After connect, click **Deploy now** for first sync.

---

## 🗂️ First Deploy Workflow

```bash
# Locally — first commit
git add .
git commit -m "Phase 1: foundation"
git push origin main
```

Hostinger pulls automatically. Then on Hostinger (one-time):

### A. Create `.env` on server
Use hPanel **File Manager** → navigate to project root → create `.env`:
```env
APP_ENV=production
APP_URL=https://codentra.pk
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=u123_codentra_main
DB_USER=u123_codentra_user
DB_PASS=YOUR_STRONG_PASSWORD

MAIL_FROM=info@codentra.pk
MAIL_FROM_NAME=Codentra

SESSION_SECURE=true
```

### B. Import database
- hPanel → **phpMyAdmin** → select your DB.
- **Import** tab → upload `sql/schema.sql` → Go.
- Then upload `sql/seed.sql` → Go.

### C. Set folder permissions
File Manager (or SSH if available):
```
chmod 755 cache/
chmod 755 cache/pages/
chmod 755 uploads/
chmod 644 .env
```

### D. Block direct PHP execution in upload dirs
Place this `.htaccess` inside `cache/` and `uploads/`:
```apache
<FilesMatch "\.(php|phtml|phar)$">
    Require all denied
</FilesMatch>
Options -Indexes
```

### E. Verify
- Visit `https://codentra.pk` — site loads, HTTPS green.
- Visit `https://codentra.pk/admin/login` — login screen renders.
- Login with seed credentials.
- Submit a test lead → check phpMyAdmin → `leads` table has the row.

---

## 🔁 Ongoing Workflow

```
Edit locally → Test locally → Commit → Push → Hostinger auto-deploys
```

**Recommended local test**:
```bash
php -S localhost:8000
# In a separate terminal, run a local MySQL or use SQLite for dev
```

**Quick deploy check after each push**:
1. Wait ~30s for Hostinger to pull.
2. Hard refresh browser (Cmd/Ctrl + Shift + R) to bypass cache.
3. If you changed CSS/JS and don't see updates, append `?v=timestamp` to asset URLs in `views/layouts/main.php` or bust your `cache/pages/` directory.

---

## 🗑️ Bust Page Cache

After admin updates a post or settings, the file cache should auto-bust. If it doesn't:
- File Manager → delete contents of `cache/pages/`
- Or build an admin button that runs `Cache::flush()`.

---

## 📧 Email (info@codentra.pk)

### Option A — Hostinger native email (easiest)
- hPanel → **Emails → Email Accounts** → create `info@codentra.pk`.
- PHP `mail()` works out-of-the-box on Hostinger using this address.

### Option B — SMTP via PHPMailer (more reliable)
- Add PHPMailer via Composer or as a single library file.
- Use the SMTP credentials from your email account.
- Recommended for production — better deliverability, error handling.

---

## 🧪 Post-Deploy Smoke Test

Run this checklist after every significant push:
- [ ] `https://codentra.pk` loads, HTTPS green padlock
- [ ] All nav links work (Home, Services, About, Blog, Contact)
- [ ] Privacy & Terms accessible from footer
- [ ] Contact form submits successfully → DB row + email
- [ ] `/admin/login` works with seed credentials
- [ ] Dashboard shows real stats
- [ ] Lead detail page editable
- [ ] Blog post creation works
- [ ] `/sitemap.xml` returns valid XML
- [ ] Mobile view at 375px looks correct
- [ ] Lighthouse score still 90+

---

## 🆘 Common Issues

| Issue | Fix |
|------|-----|
| 500 error after deploy | Check `error_log` in File Manager root — usually `.env` missing or DB creds wrong |
| Pages cached, changes not showing | Delete `cache/pages/` contents; in dev, set `opcache.validate_timestamps=1` |
| CSS not loading | Check `.htaccess` rewrite isn't catching `/public/*` — should have `RewriteCond %{REQUEST_FILENAME} !-f` |
| Form submits but no email | Verify `info@codentra.pk` exists in Hostinger email; check spam; switch to SMTP |
| Admin login loops | Session cookie issue — verify `SESSION_SECURE=true` only when HTTPS active |
| Git deploy stuck | hPanel Git panel → click **Pull** manually; check repo permissions |

---

## 🔐 Security Hardening Post-Launch

1. Change seed admin password immediately after first login.
2. Enable Cloudflare in front of Hostinger (free tier) for DDoS + edge caching.
3. Set up daily DB backup (hPanel → **Files → Backups**).
4. Add `Content-Security-Policy` header progressively (start report-only).
5. Run `https://securityheaders.com/?q=codentra.pk` — aim for A+.
6. Run `https://www.ssllabs.com/ssltest/?d=codentra.pk` — aim for A.
