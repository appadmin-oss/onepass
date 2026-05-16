# Afrostrength — deployment guide

A production-ready, full-stack PHP/MySQL site with an Academy subdomain
backed by **Moodle** (LMS) and **Jitsi-as-a-Service** (live sessions).
Built for shared cPanel hosting.

---

## 1. What's in this repo

```
/                        repo root  → upload to /public_html on cPanel
  index.php              front controller
  .htaccess              rewrite + gzip + cache + security headers
  /assets                css, js, fonts, svg, images
  /src                   core, controllers, models, views
  /config                app / database / mail / routes
  /database              schema.sql, seed.sql
  /scripts               create-admin.php
  /storage               writable: mail.log, jaas-private-key.pem
  /vendor/phpmailer      vendored (drop in — see step 5)
```

---

## 2. cPanel upload

1. **Create the database**
   - cPanel → MySQL Databases → create database `cpaneluser_afrostrength`.
   - Create a DB user, give it ALL privileges on the database.
2. **Upload the repo**
   - cPanel → File Manager → `public_html/` → upload the repo as a zip and extract,
     or use SFTP / rsync.
3. **Permissions**
   - Directories: `755`
   - Files: `644`
   - `storage/` must be writable by PHP: `chmod 775 storage`

## 3. Configure

Open `config/database.php` and set credentials, **or** set environment
variables in cPanel → "Setup PHP App" / `.htaccess`:

```ini
SetEnv DB_HOST     localhost
SetEnv DB_NAME     cpaneluser_afrostrength
SetEnv DB_USER     cpaneluser_afsuser
SetEnv DB_PASS     <password>
SetEnv AFS_URL     https://afrostrength.com
SetEnv AFS_DEBUG   0
```

Open `config/mail.php` (or use env vars):

```
MAIL_ENABLED      = 1
MAIL_HOST         = smtp.brevo.com   (or your provider)
MAIL_PORT         = 587
MAIL_ENCRYPTION   = tls
MAIL_USERNAME     = <smtp username>
MAIL_PASSWORD     = <smtp password>
MAIL_FROM         = studio@afrostrength.com
MAIL_FROM_NAME    = Afrostrength Studio
MAIL_INBOX        = studio@afrostrength.com
```

## 4. Import the database

cPanel → phpMyAdmin → select the database → Import:

1. `database/schema.sql`
2. `database/seed.sql`

Or via SSH:

```sh
mysql -u <user> -p <database> < database/schema.sql
mysql -u <user> -p <database> < database/seed.sql
```

## 5. PHPMailer

The Mailer wrapper soft-fails to `storage/mail.log` if the vendor library
is missing — useful while you're still configuring SMTP. To enable real
email:

```sh
mkdir -p vendor/phpmailer/src
curl -L https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.tar.gz \
  | tar -xz --strip-components=2 -C vendor/phpmailer/src \
    PHPMailer-6.9.1/src/PHPMailer.php \
    PHPMailer-6.9.1/src/SMTP.php \
    PHPMailer-6.9.1/src/Exception.php
```

That's three files, no Composer required.

## 6. Create the first admin

SSH into the host, then:

```sh
cd public_html
php scripts/create-admin.php admin <chosen-password>
```

Sign in at `https://afrostrength.com/admin/login`.

## 7. DNS & subdomain

The `academy.afrostrength.com` subdomain serves the same PHP application
(the academy nav layout activates on `/academy/*` paths or when
`HTTP_HOST` starts with `academy.`).

In cPanel → Domains → Subdomains:

1. Add `academy.afrostrength.com` and point the document root to
   the **same** `public_html` (so it shares the codebase) — or to a
   separate dir if you'd rather keep them split.
2. Issue an SSL certificate (Let's Encrypt via cPanel).

---

## 8. LMS — Moodle integration

Afrostrength Academy uses **Moodle** as the LMS. Our PHP front-end is
the marketing layer + catalog; the LMS lives at
`https://academy.afrostrength.com/lms/`.

### 8.1 Install Moodle

cPanel → Softaculous → Moodle. Install into the
`public_html/_academy_lms/` directory and route `academy.afrostrength.com/lms`
to it via a small alias (or install directly into a separate
subdomain `lms.afrostrength.com` and adjust `LMS_BASE_URL`).

Alternatively run the Moodle CLI installer if you prefer.

### 8.2 Enable web services

In Moodle:

1. Site administration → **Plugins → Web services → Manage protocols** → enable **REST**.
2. Site administration → **Plugins → Web services → Manage services** → "Custom service" with these functions:
   - `core_webservice_get_site_info`
   - `core_course_get_courses_by_field`
   - `core_user_create_users`
   - `core_user_get_users_by_field`
   - `enrol_manual_enrol_users`
3. Site administration → **Plugins → Web services → Manage tokens** → create a token for that service, scoped to a service-account user with the right capabilities.
4. Copy the token.

### 8.3 Wire the token into Afrostrength

```ini
SetEnv LMS_ENABLED  1
SetEnv LMS_BASE_URL https://academy.afrostrength.com/lms
SetEnv LMS_TOKEN    <the token from step 8.2>
```

After setting these, `Course::all()` switches from local seed data to the
live Moodle catalog, and the **Admin → Academy → Courses** screen shows
that the LMS is wired.

### 8.4 Customising Moodle to match the brand

The Moodle theme **Boost** (built-in) supports drop-in CSS overrides.
Put this into Site administration → Appearance → Boost → Raw initial SCSS:

```scss
$primary: #C0392B;
$brand-primary: #8B0000;
body { font-family: 'Montserrat', sans-serif; }
.h1, .h2, .h3 { font-family: 'Garet', sans-serif; letter-spacing: -0.02em; }
```

That's enough to tie the LMS visually back to the studio.

---

## 9. JaaS — live sessions

Live sessions run on **Jitsi as a Service** (`jaas.8x8.vc`). The studio
maintains a moderated tenant; we mint short-lived JWTs server-side per
viewer and embed the room via JaaS's External API.

### 9.1 Provision the tenant

1. Sign up at https://jaas.8x8.vc and create an "App ID" (looks like
   `vpaas-magic-cookie-…`).
2. Generate an API key — JaaS will give you the **API key ID** plus a
   downloadable **RSA private key (.pem)**.

### 9.2 Drop the key on the server

Place the private key at `storage/jaas-private-key.pem` (outside the
public document root for the file itself thanks to the `.htaccess`
deny rule on `/storage`).

### 9.3 Wire the env vars

```ini
SetEnv JAAS_ENABLED      1
SetEnv JAAS_APP_ID       vpaas-magic-cookie-xxxxxxxxxxxx
SetEnv JAAS_API_KEY      vpaas-magic-cookie-xxxxxxxxxxxx/abcdef
SetEnv JAAS_PRIVATE_KEY  /home/<cpaneluser>/public_html/storage/jaas-private-key.pem
SetEnv JAAS_DOMAIN       8x8.vc
```

`/academy/live-sessions` will now embed a JaaS room with a JWT minted
for the visitor. The Admin dashboard's "JaaS · live stage" card flips
to active.

### 9.4 Moderator tokens

The current build mints attendee tokens. For instructors, extend
`AcademyController::liveSessions()` to call `Jaas::token($user, $room, true)`
when the viewing user is an authenticated instructor — the
`moderator: true` flag in the JWT enables livestream + recording.

---

## 10. Operational notes

- **Sessions** — PHP's native session store is fine on a single-host
  cPanel box. For multi-host, switch to a database session handler.
- **Logs** — `storage/mail.log` records every outbound email when the
  Mailer can't reach SMTP. Rotate it monthly.
- **Backups** — `database/` + `storage/` + `assets/images/` are the
  only stateful surfaces. Everything else is in source control.
- **Upgrades** — pull the repo, re-import `schema.sql` (idempotent),
  re-import `seed.sql` if you want to refresh content.
- **Security headers** — set in `.htaccess`. CSP is intentionally not
  on by default because Tailwind CDN, Fontshare, JaaS, and SVG.js all
  need explicit allow-lists. Add a CSP header once your CDN posture
  is stable.

---

## 11. Verification checklist

- [ ] Homepage renders, hero word-cascade animates, marquee scrolls
- [ ] Mobile drawer opens/closes from the hamburger
- [ ] `/services/brand-development` renders the full process + deliverables
- [ ] `/projects/mwanga-luminaire-identity` renders detail page
- [ ] `/contact` form submission lands in `inquiries` table
- [ ] `/academy/live-sessions` embeds the Jitsi room (with JaaS configured)
- [ ] `/admin` requires login; dashboard shows correct counts
- [ ] `prefers-reduced-motion: reduce` disables aurora, marquee, word reveal
- [ ] Lighthouse desktop ≥ 90, mobile ≥ 85

---

## Subdomain layout (cPanel + DNS)

The site is host-aware: the front controller rewrites the URL path
based on the incoming `Host:` header, so a single document root serves
the main site, the Academy, the free tools, and the public status
page — each on its own subdomain when you want one.

### What you point at the same document root

| Subdomain                           | Internal scope | What visitors see                       |
|-------------------------------------|----------------|-----------------------------------------|
| `afrostrength.com`                  | `main`         | Everything (no scoping)                 |
| `academy.afrostrength.com`          | `academy`      | Only `/academy/*` paths                 |
| `tools.afrostrength.com`            | `tools`        | Only `/tools/*` paths                   |
| `status.afrostrength.com`           | `status`       | Only `/status` + `/status/health.json`  |

### cPanel recipe (5 minutes)

1. **DNS** — add A / CNAME records for `academy`, `tools` and `status`
   that resolve to the same origin as `afrostrength.com`. If you use
   Cloudflare DNS, set each to *Proxied* (orange cloud) so they
   inherit your existing TLS + caching policy.

2. **cPanel → Subdomains** — for each of `academy`, `tools`, `status`:
   - Subdomain: e.g. `academy`
   - Domain: `afrostrength.com`
   - **Document root: the SAME path as your main domain** (typically
     `public_html`). Do not create a separate folder.

   That's the whole trick. Because the document root is shared, every
   subdomain hits the same `index.php` → `Router::dispatch()` →
   `afs_host_scope()` reads the host header and prefixes the path.

3. **TLS** — cPanel AutoSSL usually picks up the new subdomains within
   ~5 minutes. Verify each one loads under HTTPS before changing the
   DNS away from the staging origin.

4. **Smoke-test** locally first by adding to `/etc/hosts`:
   ```
   127.0.0.1   academy.afrostrength.local
   127.0.0.1   tools.afrostrength.local
   127.0.0.1   status.afrostrength.local
   ```
   …and run `php -S 0.0.0.0:8000 index.php`. Hit each host header and
   confirm scoping.

### How the URLs you publish change

You don't have to change anything. The same code generates the same
links because `url('/academy/courses')` returns an absolute path; the
browser keeps that relative to whatever host it loaded. If you want
canonical URLs on the academy site to use `academy.afrostrength.com`,
set `AFS_URL=https://academy.afrostrength.com` in the env for that
subdomain only — but for a shared document root, leave `AFS_URL`
unset and let the auto-detect in `config/app.php` resolve it
per-request.

### Public status page on a separate domain (optional)

For a high-trust public outage page, point `status.afrostrength.com`
at a *separate* small server (or a Cloudflare Worker) that proxies
`/status/health.json` and serves a static fallback HTML during a full
origin outage. The endpoint here is monitor-friendly: a single GET,
no auth, `Cache-Control: max-age=15`. Out of scope for this codebase,
but the JSON shape is stable.
