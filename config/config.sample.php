<?php
/**
 * Sample configuration file.
 *
 * The installer (/install) generates the real config/config.php from this
 * template. If you prefer to configure manually, copy this file to
 * config/config.php and fill in the values below.
 */

// ---- Database -------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

// ---- Site -----------------------------------------------------------------
// Absolute base URL, no trailing slash, e.g. https://souvikpati.in
define('SITE_URL', 'https://souvikpati.in');

// ---- Security -------------------------------------------------------------
// A long random string. The installer generates one automatically.
define('APP_KEY', 'change-this-to-a-long-random-string');

// ---- Environment ----------------------------------------------------------
// 'production' hides errors from visitors. Use 'development' locally.
define('APP_ENV', 'production');
