<?php
/**
 * Front controller / router.
 *
 * The .htaccess rewrites all non-file requests here. We parse the path and
 * dispatch to a handler in /pages. Clean URLs, no .php extensions.
 */

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require BASE_PATH . '/includes/repository.php';

// ---- Parse the request path ----------------------------------------------
$uri  = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

// Normalise: collapse duplicate slashes, strip a trailing slash (except root).
$path = preg_replace('#/+#', '/', $path) ?? '/';
if ($path !== '/' && str_ends_with($path, '/')) {
    // Redirect trailing-slash URLs to the canonical no-slash form (301).
    redirect(rtrim($path, '/'), 301);
}
$path = trim($path, '/');           // '' for home
$segments = $path === '' ? [] : explode('/', $path);

// ---- Custom redirects (301 / 302 / 410) ----------------------------------
try {
    $stmt = db()->prepare('SELECT target, status_code FROM redirects WHERE source = ? AND is_active = 1 LIMIT 1');
    $stmt->execute(['/' . $path]);
    if ($rd = $stmt->fetch()) {
        $code = (int) $rd['status_code'];
        if ($code === 410) {
            http_response_code(410);
            $GLOBALS['__status'] = 410;
            require BASE_PATH . '/pages/404.php';
            exit;
        }
        redirect($rd['target'] ?: '/', $code === 302 ? 302 : 301);
    }
} catch (PDOException $e) {
    // redirects table may not exist on a very old install; ignore.
}

// ---- Special dynamic files -----------------------------------------------
if ($path === 'sitemap.xml') {
    require BASE_PATH . '/pages/sitemap.php';
    exit;
}
if ($path === 'robots.txt') {
    require BASE_PATH . '/pages/robots.php';
    exit;
}

// ---- Route table ----------------------------------------------------------
$first = $segments[0] ?? '';

switch ($first) {
    case '':
        require BASE_PATH . '/pages/home.php';
        break;

    case 'services':
        if (isset($segments[1])) {
            $GLOBALS['route_slug'] = $segments[1];
            require BASE_PATH . '/pages/service-detail.php';
        } else {
            require BASE_PATH . '/pages/services-index.php';
        }
        break;

    case 'blog':
        if (($segments[1] ?? '') === 'category' && isset($segments[2])) {
            $GLOBALS['route_slug'] = $segments[2];
            require BASE_PATH . '/pages/blog-category.php';
        } elseif ($segments[1] ?? '') {
            $GLOBALS['route_slug'] = $segments[1];
            require BASE_PATH . '/pages/blog-post.php';
        } else {
            require BASE_PATH . '/pages/blog-index.php';
        }
        break;

    case 'work':
    case 'portfolio':
        if (isset($segments[1])) {
            $GLOBALS['route_slug'] = $segments[1];
            require BASE_PATH . '/pages/project-detail.php';
        } else {
            require BASE_PATH . '/pages/portfolio.php';
        }
        break;

    case 'about':
        require BASE_PATH . '/pages/about.php';
        break;

    case 'contact':
        require BASE_PATH . '/pages/contact.php';
        break;

    default:
        // Try a database-backed static page (privacy, terms, etc.).
        if (count($segments) === 1) {
            $page = repo_page_by_slug($first);
            if ($page) {
                $GLOBALS['route_page'] = $page;
                require BASE_PATH . '/pages/page.php';
                break;
            }
        }
        http_response_code(404);
        require BASE_PATH . '/pages/404.php';
        break;
}
