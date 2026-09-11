-- =============================================================
--  souvikpati.in — Database schema
--  Engine: InnoDB · Charset: utf8mb4
--  Compatible with MySQL 5.7+ / MariaDB 10.3+ (Hostinger / cPanel)
--
--  NOTE: The installer (/install) creates the admin account and
--  writes the default settings rows for you. If you import this
--  file manually, run the installer afterwards (or insert an
--  admin row yourself) so you can log in.
-- =============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- -------------------------------------------------------------
--  Admin users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(190) NOT NULL,
  `username`      VARCHAR(60)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          VARCHAR(30)  NOT NULL DEFAULT 'admin',
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login_at` DATETIME     NULL DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admins_email` (`email`),
  UNIQUE KEY `uniq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Login attempts (brute-force throttling)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address`  VARCHAR(45)  NOT NULL,
  `username`    VARCHAR(190) NULL DEFAULT NULL,
  `successful`  TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempts_ip_time` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Site settings (key / value store)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(120) NOT NULL,
  `setting_value` LONGTEXT     NULL,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Services
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`            VARCHAR(160) NOT NULL,
  `slug`             VARCHAR(180) NOT NULL,
  `icon`             VARCHAR(60)  NULL DEFAULT NULL,
  `short_description` VARCHAR(400) NULL DEFAULT NULL,
  `full_description` LONGTEXT     NULL,
  `starting_price`   DECIMAL(12,2) NULL DEFAULT NULL,
  `price_unit`       VARCHAR(60)  NULL DEFAULT NULL,
  `price_note`       VARCHAR(190) NULL DEFAULT NULL,
  `image`            VARCHAR(255) NULL DEFAULT NULL,
  `status`           VARCHAR(20)  NOT NULL DEFAULT 'active',
  `sort_order`       INT          NOT NULL DEFAULT 0,
  `seo_title`        VARCHAR(190) NULL DEFAULT NULL,
  `seo_description`  VARCHAR(300) NULL DEFAULT NULL,
  `seo_keywords`     VARCHAR(255) NULL DEFAULT NULL,
  `og_title`         VARCHAR(190) NULL DEFAULT NULL,
  `og_description`   VARCHAR(300) NULL DEFAULT NULL,
  `og_image`         VARCHAR(255) NULL DEFAULT NULL,
  `noindex`          TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_services_slug` (`slug`),
  KEY `idx_services_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Service features (bullet points per service)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_features` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` INT UNSIGNED NOT NULL,
  `feature`    VARCHAR(255) NOT NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_features_service` (`service_id`, `sort_order`),
  CONSTRAINT `fk_features_service` FOREIGN KEY (`service_id`)
    REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Blog categories
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `slug`        VARCHAR(140) NOT NULL,
  `description` VARCHAR(400) NULL DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Blog tags
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Blog posts
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`          VARCHAR(200) NOT NULL,
  `slug`           VARCHAR(220) NOT NULL,
  `excerpt`        VARCHAR(500) NULL DEFAULT NULL,
  `content`        LONGTEXT     NULL,
  `featured_image` VARCHAR(255) NULL DEFAULT NULL,
  `category_id`    INT UNSIGNED NULL DEFAULT NULL,
  `author_id`      INT UNSIGNED NULL DEFAULT NULL,
  `author_name`    VARCHAR(120) NULL DEFAULT NULL,
  `reading_time`   INT          NULL DEFAULT NULL,
  `status`         VARCHAR(20)  NOT NULL DEFAULT 'draft',
  `published_at`   DATETIME     NULL DEFAULT NULL,
  `views`          INT UNSIGNED NOT NULL DEFAULT 0,
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(300) NULL DEFAULT NULL,
  `canonical_url`   VARCHAR(255) NULL DEFAULT NULL,
  `og_title`        VARCHAR(190) NULL DEFAULT NULL,
  `og_description`  VARCHAR(300) NULL DEFAULT NULL,
  `og_image`        VARCHAR(255) NULL DEFAULT NULL,
  `noindex`         TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_post_slug` (`slug`),
  KEY `idx_post_status_pub` (`status`, `published_at`),
  KEY `idx_post_category` (`category_id`),
  CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`)
    REFERENCES `blog_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`)
    REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Post <-> Tag pivot
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_tags` (
  `post_id` INT UNSIGNED NOT NULL,
  `tag_id`  INT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`),
  KEY `idx_pt_tag` (`tag_id`),
  CONSTRAINT `fk_pt_post` FOREIGN KEY (`post_id`)
    REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`)
    REFERENCES `blog_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Portfolio / projects
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio_projects` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(200) NOT NULL,
  `slug`          VARCHAR(220) NOT NULL,
  `description`   LONGTEXT     NULL,
  `category`      VARCHAR(120) NULL DEFAULT NULL,
  `technologies`  VARCHAR(400) NULL DEFAULT NULL,
  `image`         VARCHAR(255) NULL DEFAULT NULL,
  `project_url`   VARCHAR(255) NULL DEFAULT NULL,
  `status`        VARCHAR(20)  NOT NULL DEFAULT 'active',
  `is_featured`   TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order`    INT          NOT NULL DEFAULT 0,
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(300) NULL DEFAULT NULL,
  `og_image`        VARCHAR(255) NULL DEFAULT NULL,
  `noindex`         TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_project_slug` (`slug`),
  KEY `idx_project_status` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  FAQs (optionally scoped to a service)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question`   VARCHAR(300) NOT NULL,
  `answer`     TEXT         NOT NULL,
  `service_id` INT UNSIGNED NULL DEFAULT NULL,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faq_service` (`service_id`, `is_active`, `sort_order`),
  CONSTRAINT `fk_faq_service` FOREIGN KEY (`service_id`)
    REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Contact inquiries
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_inquiries` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(140) NOT NULL,
  `email`      VARCHAR(190) NOT NULL,
  `phone`      VARCHAR(60)  NULL DEFAULT NULL,
  `service`    VARCHAR(160) NULL DEFAULT NULL,
  `budget`     VARCHAR(80)  NULL DEFAULT NULL,
  `message`    TEXT         NOT NULL,
  `status`     VARCHAR(20)  NOT NULL DEFAULT 'new',
  `ip_address` VARCHAR(45)  NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiry_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Media library
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`   VARCHAR(255) NOT NULL,
  `filepath`   VARCHAR(255) NOT NULL,
  `mime_type`  VARCHAR(100) NULL DEFAULT NULL,
  `width`      INT          NULL DEFAULT NULL,
  `height`     INT          NULL DEFAULT NULL,
  `filesize`   INT UNSIGNED NULL DEFAULT NULL,
  `alt_text`   VARCHAR(255) NULL DEFAULT NULL,
  `uploaded_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Static / legal pages
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`          VARCHAR(200) NOT NULL,
  `slug`           VARCHAR(220) NOT NULL,
  `content`        LONGTEXT     NULL,
  `status`         VARCHAR(20)  NOT NULL DEFAULT 'published',
  `is_system`      TINYINT(1)   NOT NULL DEFAULT 0,
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(300) NULL DEFAULT NULL,
  `canonical_url`   VARCHAR(255) NULL DEFAULT NULL,
  `og_title`        VARCHAR(190) NULL DEFAULT NULL,
  `og_description`  VARCHAR(300) NULL DEFAULT NULL,
  `og_image`        VARCHAR(255) NULL DEFAULT NULL,
  `noindex`         TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_page_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Redirects (301 / 302 / 410)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `redirects` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source`     VARCHAR(255) NOT NULL,
  `target`     VARCHAR(255) NULL DEFAULT NULL,
  `status_code` SMALLINT    NOT NULL DEFAULT 301,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_redirect_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
