<?php
/**
 * Application bootstrap.
 *
 * Loaded by index.php (front controller), the admin panel and the installer's
 * post-install pages. Sets up error handling, loads config, opens the database
 * connection, starts a secure session and loads shared helpers.
 */

declare(strict_types=1);

define('APP_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

// If the config is missing we are not installed yet — send the visitor to the
// installer instead of throwing a fatal database error.
$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    if (is_dir(BASE_PATH . '/install')) {
        header('Location: /install/');
        exit;
    }
    http_response_code(503);
    exit('Site not configured.');
}

require $configFile;

// ---- Error handling -------------------------------------------------------
$isProd = defined('APP_ENV') && APP_ENV === 'production';
error_reporting(E_ALL);
ini_set('display_errors', $isProd ? '0' : '1');
ini_set('log_errors', '1');

// Turn fatal, uncaught errors into a clean 500 page in production.
set_exception_handler(function ($e) use ($isProd) {
    error_log('[uncaught] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    $errorPage = BASE_PATH . '/pages/500.php';
    if (is_file($errorPage)) {
        include $errorPage;
    } else {
        echo 'Something went wrong.';
    }
    exit;
});

date_default_timezone_set('UTC');

// ---- Core includes --------------------------------------------------------
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/includes/functions.php';
require BASE_PATH . '/includes/session.php';
require BASE_PATH . '/includes/csrf.php';
require BASE_PATH . '/includes/settings.php';
require BASE_PATH . '/includes/seo.php';

// Load settings from the database into a global cache.
settings_load($pdo);
