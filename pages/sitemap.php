<?php
/**
 * Dynamically generated XML sitemap.
 * Excludes drafts, scheduled-future posts and any noindex content.
 */
if (!defined('BASE_PATH')) { exit; }

if (!headers_sent()) {
    header('Content-Type: application/xml; charset=utf-8');
}

$pdo = db();
$base = rtrim(setting('site_url', defined('SITE_URL') ? SITE_URL : ''), '/');

/** @var array<int,array{loc:string,lastmod?:string,changefreq?:string,priority?:string}> $urls */
$urls = [];

$add = function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.6') use (&$urls, $base) {
    $urls[] = [
        'loc'        => $base . $loc,
        'lastmod'    => $lastmod ? (new DateTime($lastmod))->format('Y-m-d') : null,
        'changefreq' => $changefreq,
        'priority'   => $priority,
    ];
};

// Core pages.
$add('/', null, 'weekly', '1.0');
$add('/services', null, 'monthly', '0.8');
$add('/about', null, 'monthly', '0.5');
$add('/contact', null, 'monthly', '0.5');
$add('/blog', null, 'daily', '0.7');
if (repo_has_projects()) {
    $add('/work', null, 'weekly', '0.6');
}

// Services (active, not noindex).
foreach ($pdo->query("SELECT slug, updated_at FROM services WHERE status='active' AND noindex=0 ORDER BY sort_order ASC") as $r) {
    $add('/services/' . $r['slug'], $r['updated_at'], 'monthly', '0.8');
}

// Blog posts (published, in the past, not noindex).
$stmt = $pdo->query("SELECT slug, COALESCE(updated_at, published_at) AS lm FROM blog_posts
    WHERE status='published' AND published_at IS NOT NULL AND published_at <= NOW() AND noindex=0
    ORDER BY published_at DESC");
foreach ($stmt as $r) {
    $add('/blog/' . $r['slug'], $r['lm'], 'monthly', '0.7');
}

// Blog categories that have visible posts.
foreach (repo_categories_with_counts() as $c) {
    $add('/blog/category/' . $c['slug'], null, 'weekly', '0.4');
}

// Portfolio projects (active, not noindex).
foreach ($pdo->query("SELECT slug, updated_at FROM portfolio_projects WHERE status='active' AND noindex=0 ORDER BY sort_order ASC") as $r) {
    $add('/work/' . $r['slug'], $r['updated_at'], 'monthly', '0.6');
}

// Published static pages (not noindex).
foreach ($pdo->query("SELECT slug, updated_at FROM pages WHERE status='published' AND noindex=0") as $r) {
    $add('/' . $r['slug'], $r['updated_at'], 'yearly', '0.3');
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . e($u['loc']) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . e($u['lastmod']) . "</lastmod>\n";
    }
    echo '    <changefreq>' . e($u['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . e($u['priority']) . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
