# Afrostrength

A production-ready, full-stack PHP/MySQL website for **Afrostrength** — the
brand studio behind *Building Brands, Strengthening Legacies*. Built for shared
cPanel hosting with no Node build step.

- **Frontend** — semantic HTML5, Tailwind CDN, hand-rolled `assets/css/custom.css`
  for the design tokens and components, vanilla JS for motion and forms,
  SVG.js + GraphicsJS for the animated illustrations.
- **Backend** — PHP 8.x front-controller MVC. PDO/MySQL. PHPMailer for SMTP.
- **Academy** — `/academy/*` (and `academy.afrostrength.com`) serves the
  Academy with a maroon-accented layout. Course catalog reads from
  **Moodle** via its REST web services. Live sessions embed via
  **Jitsi-as-a-Service** with server-minted RS256 JWTs.

## Quick start (local)

```sh
git clone <repo> afrostrength && cd afrostrength
mysql -u root -p afrostrength < database/schema.sql
mysql -u root -p afrostrength < database/seed.sql
php scripts/create-admin.php admin <password>
php -S localhost:8000 index.php
open http://localhost:8000
```

`AFS_DEBUG=1` in your shell env enables verbose error pages.

## Deploying

See [`DEPLOYMENT.md`](./DEPLOYMENT.md) — covers cPanel upload, DB import,
SMTP setup via PHPMailer, Moodle LMS integration, and JaaS configuration.

## Routes

- Public: `/`, `/services`, `/services/{slug}`, `/projects`, `/projects/{slug}`,
  `/blog`, `/blog/{slug}`, `/contact`, `/about/{section}`, `/faq`,
  `/testimonials`
- Academy: `/academy`, `/academy/courses`, `/academy/courses/{slug}`,
  `/academy/instructors`, `/academy/certifications`, `/academy/live-sessions`,
  `/academy/enroll`
- Admin: `/admin`, `/admin/posts`, `/admin/projects`, `/admin/services`,
  `/admin/inquiries`, `/admin/academy/{section}`, `/admin/content`
- API: `POST /api/inquiries`, `POST /api/enrollments`
- SEO: `/sitemap.xml`, `/robots.txt`

## Tech notes

- The visual system is anchored in `assets/css/custom.css` — all design
  tokens, components, and motion live there.
- Models gracefully fall back to seeded data when MySQL is unreachable,
  so the public site renders before the database is wired.
- The Mailer soft-fails to `storage/mail.log` if PHPMailer isn't vendored
  yet — useful while configuring SMTP.
- The Academy's Course model prefers Moodle data when `LMS_ENABLED=1`,
  otherwise falls through to the local seed.
- All forms POST to JSON endpoints with CSRF tokens minted from the PHP
  session and exposed via a `<meta name="csrf-token">` tag.
