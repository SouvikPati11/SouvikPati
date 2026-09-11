<?php
/**
 * Admin bootstrap + authentication guard.
 * Every admin page (except login) includes this first.
 */
declare(strict_types=1);

require dirname(__DIR__, 2) . '/includes/bootstrap.php';
require BASE_PATH . '/includes/repository.php';
require BASE_PATH . '/includes/auth.php';

auth_gc();

// Pages set $GUARD_PUBLIC = true before including this to skip the login check
// (used only by the login page itself).
if (empty($GUARD_PUBLIC)) {
    $CURRENT_ADMIN = require_admin();
}

/**
 * Convenience: verify CSRF on POST inside the admin, redirecting on failure.
 */
function admin_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_check()) {
        flash('error', 'Your session expired. Please try again.');
        redirect($_SERVER['REQUEST_URI'] ?? '/admin/');
    }
}

/** Admin-relative URL. */
function admin_url(string $p = ''): string
{
    return '/admin/' . ltrim($p, '/');
}
