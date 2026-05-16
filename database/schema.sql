-- Afrostrength — schema.sql
-- Compatible with MySQL 5.7+ / MariaDB 10.3+ as found on cPanel.
-- Import via cPanel phpMyAdmin or `mysql -u <user> -p <db> < schema.sql`.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----- Posts (Field notes) ------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(200) NOT NULL,
    `slug`            VARCHAR(200) NOT NULL,
    `excerpt`         VARCHAR(500) DEFAULT NULL,
    `body`            MEDIUMTEXT,
    `category`        VARCHAR(80)  DEFAULT 'Editorial',
    `tags`            VARCHAR(200) DEFAULT NULL,
    `featured_image`  VARCHAR(255) DEFAULT NULL,
    `author`          VARCHAR(120) DEFAULT 'Afrostrength Studio',
    `status`          ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `published_at`    DATETIME     DEFAULT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `posts_slug` (`slug`),
    KEY `posts_status_pubat` (`status`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Projects ----------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`          VARCHAR(200) NOT NULL,
    `slug`           VARCHAR(200) NOT NULL,
    `client`         VARCHAR(160) DEFAULT NULL,
    `year`           SMALLINT     DEFAULT NULL,
    `category`       VARCHAR(120) DEFAULT NULL,
    `tags`           VARCHAR(200) DEFAULT NULL,
    `summary`        VARCHAR(500) DEFAULT NULL,
    `problem`        TEXT,
    `solution`       TEXT,
    `outcome`        TEXT,
    `gallery_json`   TEXT,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `is_featured`    TINYINT(1)   NOT NULL DEFAULT 0,
    `status_label`   ENUM('live','production','case') NOT NULL DEFAULT 'case',
    `status`         ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `projects_slug` (`slug`),
    KEY `projects_status_featured` (`status`, `is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Services -----------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`               VARCHAR(120) NOT NULL,
    `slug`               VARCHAR(120) NOT NULL,
    `tagline`            VARCHAR(300) DEFAULT NULL,
    `overview`           TEXT,
    `who_for`            TEXT,
    `process_json`       TEXT,
    `deliverables_json`  TEXT,
    `icon`               VARCHAR(40)  DEFAULT 'brand',
    `timeline`           VARCHAR(40)  DEFAULT '4–6 WEEKS',
    `tag`                VARCHAR(20)  DEFAULT 'CORE',
    `sort`               INT NOT NULL DEFAULT 0,
    `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `services_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Testimonials -------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_name`  VARCHAR(120) NOT NULL,
    `role`         VARCHAR(120) DEFAULT NULL,
    `company`      VARCHAR(120) DEFAULT NULL,
    `quote`        TEXT NOT NULL,
    `rating`       TINYINT NOT NULL DEFAULT 5,
    `avatar`       VARCHAR(255) DEFAULT NULL,
    `service_slug` VARCHAR(120) DEFAULT NULL,
    `is_featured`  TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Inquiries (contact + consultation + enrolment) --------
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kind`           VARCHAR(40)  NOT NULL DEFAULT 'contact',
    `name`           VARCHAR(160) NOT NULL,
    `email`          VARCHAR(160) NOT NULL,
    `phone`          VARCHAR(40)  DEFAULT NULL,
    `company`        VARCHAR(160) DEFAULT NULL,
    `inquiry_type`   VARCHAR(80)  DEFAULT NULL,
    `service`        VARCHAR(120) DEFAULT NULL,
    `brand_stage`    VARCHAR(40)  DEFAULT NULL,
    `event_size`     VARCHAR(40)  DEFAULT NULL,
    `event_date`     DATE         DEFAULT NULL,
    `preferred_time` VARCHAR(40)  DEFAULT NULL,
    `message`        TEXT,
    `status`         ENUM('new','open','resolved') NOT NULL DEFAULT 'new',
    `suggested_route` VARCHAR(40)  DEFAULT NULL,
    `route_why`       VARCHAR(280) DEFAULT NULL,
    `route_conf`      FLOAT        DEFAULT NULL,
    `assigned_to`     VARCHAR(40)  DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `inquiries_status` (`status`),
    KEY `inquiries_assigned` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Admins ------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username`       VARCHAR(80)  NOT NULL,
    `password_hash`  VARCHAR(255) NOT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Content blocks (homepage hero copy etc.) --------------
CREATE TABLE IF NOT EXISTS `content_blocks` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key_name`   VARCHAR(80)  NOT NULL,
    `value_text` TEXT,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `content_blocks_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Academy: instructors --------------------------------
CREATE TABLE IF NOT EXISTS `instructors` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(160) NOT NULL,
    `slug`         VARCHAR(160) NOT NULL,
    `role`         VARCHAR(160) DEFAULT NULL,
    `bio`          TEXT,
    `avatar`       VARCHAR(255) DEFAULT NULL,
    `status_pill`  VARCHAR(20)  DEFAULT 'live',
    `status_label` VARCHAR(60)  DEFAULT 'Available',
    `skills`       VARCHAR(255) DEFAULT NULL,
    `sort`         INT NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `instructors_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Academy: courses ------------------------------------
CREATE TABLE IF NOT EXISTS `courses` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`          VARCHAR(200) NOT NULL,
    `slug`           VARCHAR(200) NOT NULL,
    `level`          VARCHAR(40)  DEFAULT 'Foundations',
    `weeks`          INT NOT NULL DEFAULT 6,
    `instructor_id`  INT UNSIGNED DEFAULT NULL,
    `instructor`     VARCHAR(160) DEFAULT NULL,
    `price_naira`    INT NOT NULL DEFAULT 0,
    `cover_image`    VARCHAR(255) DEFAULT NULL,
    `summary`        VARCHAR(500) DEFAULT NULL,
    `body`           MEDIUMTEXT,
    `syllabus_json`  TEXT,
    `status`         ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `courses_slug` (`slug`),
    KEY `courses_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Academy: certifications ------------------------------
CREATE TABLE IF NOT EXISTS `certifications` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(200) NOT NULL,
    `slug`            VARCHAR(200) NOT NULL,
    `summary`         VARCHAR(500) DEFAULT NULL,
    `requirements`    TEXT,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `certifications_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Academy: live sessions ------------------------------
CREATE TABLE IF NOT EXISTS `live_sessions` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(200) NOT NULL,
    `summary`          VARCHAR(400) DEFAULT NULL,
    `starts_at`        DATETIME NOT NULL,
    `duration_minutes` INT NOT NULL DEFAULT 60,
    `instructor_id`    INT UNSIGNED DEFAULT NULL,
    `instructor`       VARCHAR(160) DEFAULT NULL,
    `capacity`         INT NOT NULL DEFAULT 25,
    `status`           ENUM('upcoming','live','past') NOT NULL DEFAULT 'upcoming',
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Academy: enrolments ----------------------------------
CREATE TABLE IF NOT EXISTS `enrollments` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(160) NOT NULL,
    `email`        VARCHAR(160) NOT NULL,
    `phone`        VARCHAR(40)  DEFAULT NULL,
    `course_id`    INT UNSIGNED DEFAULT NULL,
    `course_title` VARCHAR(200) DEFAULT NULL,
    `status`       ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Academy: students (full account, onboarding flow) ----
CREATE TABLE IF NOT EXISTS `students` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(160) NOT NULL,
    `email`           VARCHAR(160) NOT NULL,
    `password_hash`   VARCHAR(255) NOT NULL DEFAULT '',
    `google_uid`      VARCHAR(128) DEFAULT NULL,
    `avatar_url`      VARCHAR(512) DEFAULT NULL,
    `totp_secret`     VARCHAR(64)  DEFAULT NULL,
    `totp_enabled_at` TIMESTAMP NULL DEFAULT NULL,
    `phone`           VARCHAR(40)  DEFAULT NULL,
    `country`         VARCHAR(80)  DEFAULT 'Nigeria',
    `current_role`    VARCHAR(120) DEFAULT NULL,
    `experience_level` ENUM('starting','some','experienced','advanced') DEFAULT 'starting',
    `track_slug`      VARCHAR(120) DEFAULT NULL,
    `goal`            TEXT,
    `cohort_pref`     VARCHAR(40)  DEFAULT 'next',
    `cohort_target_date` DATE NULL DEFAULT NULL,
    `profile_json`    TEXT NULL,
    `referral_source` VARCHAR(120) DEFAULT NULL,
    `marketing_opt_in` TINYINT(1) NOT NULL DEFAULT 1,
    `status`          ENUM('onboarding','active','paused','withdrawn') NOT NULL DEFAULT 'onboarding',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `students_email` (`email`),
    UNIQUE KEY `students_google_uid` (`google_uid`),
    KEY `students_track` (`track_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Short links (tool: /s/{code}) ------------------------
CREATE TABLE IF NOT EXISTS `short_links` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`       VARCHAR(40)  NOT NULL,
    `target_url` VARCHAR(2048) NOT NULL,
    `clicks`     INT UNSIGNED NOT NULL DEFAULT 0,
    `created_ip` VARCHAR(45)  DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_used_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `short_links_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Promotions (banners · cohorts · services · scholarships · interstitial) -----
CREATE TABLE IF NOT EXISTS `promotions` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug`         VARCHAR(120) NOT NULL,
    `kind`         ENUM('banner','cohort','service','scholarship','hackathon','interstitial','feature') NOT NULL DEFAULT 'banner',
    `eyebrow`      VARCHAR(120) DEFAULT NULL,
    `title`        VARCHAR(200) NOT NULL,
    `subtitle`     VARCHAR(300) DEFAULT NULL,
    `body`         TEXT,
    `image_url`    VARCHAR(2048) DEFAULT NULL,
    `tone`         ENUM('crimson','ink','peach','maroon','bone') NOT NULL DEFAULT 'crimson',
    `badge`        VARCHAR(60)  DEFAULT NULL,
    `cta_label`    VARCHAR(80)  DEFAULT NULL,
    `cta_href`     VARCHAR(500) DEFAULT NULL,
    `placements`   VARCHAR(255) DEFAULT 'interstitial',  -- comma-separated: ribbon, interstitial, home, academy, sidebar
    `starts_at`    DATETIME NULL DEFAULT NULL,
    `ends_at`      DATETIME NULL DEFAULT NULL,
    `sort`         INT NOT NULL DEFAULT 0,
    `status`       ENUM('draft','active','paused') NOT NULL DEFAULT 'draft',
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `promotions_slug` (`slug`),
    KEY `promotions_status` (`status`),
    KEY `promotions_kind`   (`kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Auth tokens (magic link + OTP) -----------------------
-- One row per issuance. We store ONLY the SHA-256 of the random
-- selector + the SHA-256 of the OTP; the plaintext leaves the
-- server exactly once (in the email). Single-use: consumed_at is
-- set the moment it's redeemed and the row is no longer accepted.
CREATE TABLE IF NOT EXISTS `auth_tokens` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`        VARCHAR(160) NOT NULL,
    `selector`     CHAR(24)     NOT NULL,            -- public lookup id (24 chars base64url)
    `verifier_hash` CHAR(64)    NOT NULL,            -- sha256 of the secret half of the link
    `otp_hash`     CHAR(64)     NOT NULL,            -- sha256 of the 6-digit OTP
    `purpose`      ENUM('login','signup','email_verify') NOT NULL DEFAULT 'login',
    `expires_at`   DATETIME     NOT NULL,
    `consumed_at`  DATETIME     NULL DEFAULT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `request_ip`   VARCHAR(45)  DEFAULT NULL,
    `request_ua`   VARCHAR(200) DEFAULT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `auth_tokens_selector` (`selector`),
    KEY `auth_tokens_email_expires` (`email`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Issued certificates ----------------------------------
-- Public, verifiable record of a completed certification. The
-- public verification page at /verify/{code} reads from here.
CREATE TABLE IF NOT EXISTS `issued_certificates` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`             VARCHAR(40)  NOT NULL,         -- e.g. AFS-2026-9P3Q-7HRX
    `student_id`       INT UNSIGNED NOT NULL,
    `student_name`     VARCHAR(160) NOT NULL,         -- denormalised, shown on /verify
    `student_email`    VARCHAR(160) NOT NULL,         -- not shown publicly; used for owner checks
    `certification_id` INT UNSIGNED DEFAULT NULL,     -- nullable: ad-hoc certs allowed
    `certification_title` VARCHAR(200) NOT NULL,      -- denormalised at issuance time
    `course_id`        INT UNSIGNED DEFAULT NULL,
    `course_title`     VARCHAR(200) DEFAULT NULL,
    `cohort`           VARCHAR(80)  DEFAULT NULL,     -- e.g. "Spring 2026"
    `grade`            VARCHAR(40)  DEFAULT NULL,     -- "Pass", "Distinction", etc. — never shown if blank
    `skills_json`      TEXT,                           -- array of strings shown on the cert page
    `issued_on`        DATE NOT NULL,
    `expires_on`       DATE NULL DEFAULT NULL,
    `revoked_at`       DATETIME NULL DEFAULT NULL,
    `revoked_reason`   VARCHAR(200) DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `issued_certificates_code` (`code`),
    KEY `issued_certificates_student` (`student_id`),
    KEY `issued_certificates_email`   (`student_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Status / Incidents ---------------------------------------------
-- Operator-authored incident log shown on /status. Active incidents
-- (resolved_at IS NULL) flip the relevant component to degraded/down
-- and trigger the AI incident summary at the top of the status page.
CREATE TABLE IF NOT EXISTS `incidents` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `public_id`      VARCHAR(60) NULL,                            -- URL slug for the permalink
    `title`          VARCHAR(200) NOT NULL,
    `body`           TEXT NULL,                                    -- markdown
    `postmortem`     TEXT NULL,                                    -- AI-drafted, operator-edited
    `kind`           ENUM('incident','maintenance') NOT NULL DEFAULT 'incident',
    `severity`       ENUM('minor','major','critical') NOT NULL DEFAULT 'minor',
    `components_csv` VARCHAR(200) NOT NULL DEFAULT '',             -- e.g. "lms,ai"
    `started_at`     DATETIME NOT NULL,
    `resolved_at`    DATETIME NULL DEFAULT NULL,
    `created_by`     VARCHAR(80) NULL,                             -- operator name/email
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `incidents_public_id` (`public_id`),
    KEY `incidents_active`   (`resolved_at`),
    KEY `incidents_started`  (`started_at`),
    KEY `incidents_kind`     (`kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- AI observability log ------------------------------------------
-- Optional DB mirror of storage/ailog.ndjson. Set AI_DB_LOG=1 to enable.
-- The NDJSON file remains the source of truth — this table is for SQL
-- analytics convenience (top intents, p95 latency, etc).
CREATE TABLE IF NOT EXISTS `ai_log` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `intent`       VARCHAR(60) NOT NULL,
    `provider`     VARCHAR(40) NULL,
    `ms`           INT UNSIGNED NULL,
    `ok`           TINYINT(1) NOT NULL,
    `cached`       TINYINT(1) NOT NULL DEFAULT 0,
    `bytes_in`     INT UNSIGNED NULL,
    `bytes_out`    INT UNSIGNED NULL,
    `error_code`   VARCHAR(40) NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `ai_log_intent` (`intent`, `created_at`),
    KEY `ai_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----- Status subscribers ---------------------------------------------
-- Plain emails. We never email at scale from this; it's a list operators
-- can pull for outage notifications via SMTP (see Mailer::sendTo).
CREATE TABLE IF NOT EXISTS `status_subscribers` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(190) NOT NULL,
    `verified_at` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `status_subscribers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
