<?php
/** Dynamically generated robots.txt. */
if (!defined('BASE_PATH')) { exit; }

if (!headers_sent()) {
    header('Content-Type: text/plain; charset=utf-8');
}

$base = rtrim(setting('site_url', defined('SITE_URL') ? SITE_URL : ''), '/');
?>
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /install/
Disallow: /includes/
Disallow: /config/
Disallow: /uploads/thumbs/

Sitemap: <?= $base ?>/sitemap.xml
