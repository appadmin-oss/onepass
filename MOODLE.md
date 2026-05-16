# Moodle integration — how it actually works

This document explains the Moodle integration honestly: what the code does,
what Moodle has to be configured to do, and what is still manual today.

---

## The shape of the integration

Afrostrength does **not** embed or fork Moodle. We run two separate apps:

| App                          | Lives at                                         |
|------------------------------|--------------------------------------------------|
| Marketing + admin (this repo) | `https://afrostrength.com`                       |
| Moodle (the LMS itself)       | `https://academy.afrostrength.com/lms` (or wherever you install it) |

The two communicate over Moodle's built-in **REST web services**. There is
no shared database, no shared session. The marketing site authenticates
itself to Moodle using a single secret **web-service token**, and Moodle
authenticates students using its own login (manual or auth_email).

```
┌──────────────────────┐      HTTPS POST + wstoken      ┌─────────────────────┐
│  afrostrength.com    │ ──────────────────────────────►│  Moodle LMS         │
│  (this repo)         │   /webservice/rest/server.php  │  academy.../lms     │
│                      │ ◄──────────────────────────────│                     │
│  src/core/Lms.php    │           JSON                 │  REST API           │
└──────────────────────┘                                └─────────────────────┘
```

`src/core/Lms.php` is the only file in this repo that knows how to talk
to Moodle. Everything else (controllers, views, the admin dashboard) goes
through it.

---

## What `Lms.php` does

| Method                                  | Moodle function called                  | What we use it for                                  |
|-----------------------------------------|-----------------------------------------|-----------------------------------------------------|
| `Lms::selfTest()`                       | `core_webservice_get_site_info`         | Health check — appears on `/api/health/lms`         |
| `Lms::listCourses()`                    | `core_course_get_courses_by_field`      | Fallback catalog when local DB is empty             |
| `Lms::getCourse($id)`                   | `core_course_get_courses_by_field`      | Course detail page                                  |
| `Lms::findUserByEmail($email)`          | `core_user_get_users`                   | Find a Moodle account by the student's email        |
| `Lms::createUser($student, $password)`  | `core_user_create_users`                | Create a Moodle user when a student is admitted     |
| `Lms::enrolUser($uid, $courseId)`       | `enrol_manual_enrol_users`              | Drop the new user into their cohort course          |
| `Lms::coursesForUser($uid)`             | `core_enrol_get_users_courses`          | "Continue your course" links on the dashboard       |
| `Lms::mirrorStudent($student, $courseId)` | (composite)                           | Find-or-create + (optional) enrol in one call       |
| `Lms::courseUrl($id)` / `enrolUrl($id)` | (no API call — URL builder)             | Deep-links into the Moodle player                   |

Everything returns `[]` or `null` when `LMS_ENABLED=0` or the token is
missing, so the marketing site renders perfectly even with no Moodle
configured. We never crash because Moodle is down.

---

## What you have to do in Moodle (one time)

1. **Install Moodle** somewhere (we recommend a subdomain like
   `academy.afrostrength.com/lms`). Standard tarball install on cPanel works.

2. **Site administration → Plugins → Web services → Overview** — follow the
   nine numbered steps Moodle shows you. You need to:
   - Enable web services.
   - Enable the **REST protocol** under *Manage protocols*.
   - Create a dedicated user (e.g. `afrostrength_api`) — give them the
     **manager** role at the system context (it's the smallest role that
     can both read courses and create+enrol users).
   - Create a custom **external service** (call it *Afrostrength Bridge*).
     Tick *Authorised users only*, then add the api user as an authorised user.
   - Add these **functions** to that service:
     - `core_webservice_get_site_info`
     - `core_course_get_courses_by_field`
     - `core_user_get_users`
     - `core_user_create_users`
     - `enrol_manual_enrol_users`
     - `core_enrol_get_users_courses`
   - **Manage tokens → Create token** for that user against that service.
     Copy the token.

3. **Drop the values into the environment** of the marketing site:
   ```env
   LMS_ENABLED=1
   LMS_BASE_URL=https://academy.afrostrength.com/lms
   LMS_TOKEN=<the token from step 2>
   ```
   On cPanel you can set these in *Setup PHP Environment* (LiteSpeed) or
   in `.htaccess` via `SetEnv`. They're declared in `config/app.php`.

4. **Verify** by hitting `https://afrostrength.com/api/health/lms` — you
   should see `{"ok":true,"mode":"live","detail":"Connected to <site> ..."}`.
   If it says `mode:demo`, the *detail* field tells you exactly why
   (token rejected, host unreachable, env missing, etc.).

---

## What happens on a real signup

Today (after the changes in this branch):

1. A visitor finishes `/academy/apply`. We persist the row to the local
   `students` table and bcrypt-hash their password.
2. If `LMS_ENABLED=1`, `OnboardingController` calls `Lms::mirrorStudent()`
   right after `Student::create()`. That creates the Moodle user (using
   `createpassword=1`, so Moodle emails a "set your password" link).
3. When the studio confirms the seat in `/admin/academy/enrollments` and
   sets a `track_slug`, the next step (manual, today) is to set the
   Moodle course ID on the local course row. Then `Lms::enrolUser()`
   drops the student into the cohort.

What's **not yet automated** (and I won't pretend it is):

- We don't have a single sign-on (SSO) bridge yet. Students log in to
  the marketing site at `/login` (their Afrostrength account) and into
  Moodle at the LMS's own `/login` (the Moodle account). The two share
  an email but not a session.
  - To add SSO later, install one of: the `auth_userkey` plugin
    (token-based one-shot login), the `auth_oauth2` plugin (proper OIDC),
    or the `auth_oidc` plugin from Microsoft. Moodle 4.x ships
    `auth_oauth2` in core; you can point it at our Firebase project
    (since we already have Google sign-in) and Moodle will accept the
    same Google identity.
- We don't push the student's chosen `track_slug` as the Moodle course
  ID, because the mapping (Afrostrength track → Moodle course id) is
  set by the studio in the admin. Once that mapping is in the DB, the
  onboarding flow will auto-enrol.
- We don't sync grades or progress back from Moodle to the marketing
  dashboard yet. The dashboard's "Continue your course" links deep-link
  to Moodle and let it handle progress.

---

## Customising Moodle for Afrostrength

The brand styling lives entirely in a custom Moodle **theme** (`/lms/theme/afrostrength/`).
It is not part of this repo, by design — that codebase belongs in the
Moodle install, not here. The two visual systems share tokens:

- Garet for display headings.
- Montserrat for body.
- `--crimson #C0392B`, `--maroon #5C0000`, `--bone #FAFAFA`, `--ink #0A0A0A`.

If you want a fast start, fork the Moodle **Boost** theme and override:

```scss
$brand-primary:   #C0392B;
$brand-secondary: #5C0000;
$body-bg:         #FAFAFA;
$body-color:      #0A0A0A;
$font-family-sans-serif: 'Montserrat', system-ui, sans-serif;
$headings-font-family:   'Garet', sans-serif;
```

For the academy logo (top-left in Moodle), upload the same crimson-circle
SVG we use in this site: `<svg ... fill="#C0392B"><path d="M12 2 …"/></svg>`.
The favicon code in our `src/views/layouts/main.php` is the same mark.

---

## Where to look in this codebase

| Concern                | File                                                            |
|------------------------|-----------------------------------------------------------------|
| Talking to Moodle      | `src/core/Lms.php`                                              |
| Env vars               | `config/app.php` (`LMS_*` block)                                |
| Onboarding mirror      | `src/controllers/OnboardingController.php` (after `Student::create`) |
| Health check JSON      | `src/controllers/HealthController.php::lms()`                   |
| Dashboard deep-links   | `src/controllers/StudentController.php::dashboard()`            |
| Live-class platform    | `src/core/Jaas.php` (separate from Moodle; uses Jitsi/JaaS)     |

---

## Honest limitations

- `Lms::mirrorStudent()` is the new code path. It has been written carefully
  and reviewed, but has **not** been integration-tested against a live
  Moodle install in this branch — there isn't one in CI. Test on a staging
  Moodle before pointing production at it.
- Web-service tokens give the api user a lot of power. Treat the token
  like a database password. Rotate it if you suspect it leaked.
- Moodle errors return as `{exception: ..., message: ...}` in the body
  with HTTP 200. We detect this in `Lms::call()` and downgrade to `[]`
  so the marketing site never blows up — but you will only see the
  underlying message in `error_log`. Set `AFS_DEBUG=1` while wiring it up.
