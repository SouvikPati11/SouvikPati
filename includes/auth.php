<?php
/**
 * Admin authentication: login, logout, brute-force throttling, guards.
 */

declare(strict_types=1);

const AUTH_MAX_ATTEMPTS   = 6;      // per window, per IP
const AUTH_WINDOW_SECONDS = 900;    // 15 minutes

/**
 * The currently authenticated admin (or null).
 */
function current_admin(): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    if (empty($_SESSION['admin_id'])) {
        return $cache = null;
    }
    $stmt = db()->prepare('SELECT * FROM admins WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    return $cache = ($admin ?: null);
}

function is_logged_in(): bool
{
    return current_admin() !== null;
}

/**
 * Require a logged-in admin or redirect to the login page.
 */
function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/admin/';
        redirect('/admin/login.php');
    }
    return $admin;
}

/**
 * How many failed attempts from this IP within the window.
 */
function auth_recent_failures(string $ip): int
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE ip_address = ? AND successful = 0 AND created_at > (NOW() - INTERVAL ? SECOND)'
    );
    $stmt->execute([$ip, AUTH_WINDOW_SECONDS]);
    return (int) $stmt->fetchColumn();
}

function auth_is_locked(string $ip): bool
{
    return auth_recent_failures($ip) >= AUTH_MAX_ATTEMPTS;
}

function auth_log_attempt(string $ip, ?string $username, bool $success): void
{
    $stmt = db()->prepare(
        'INSERT INTO login_attempts (ip_address, username, successful) VALUES (?, ?, ?)'
    );
    $stmt->execute([$ip, $username !== null ? mb_substr($username, 0, 180) : null, $success ? 1 : 0]);
}

/**
 * Attempt a login. Returns [success, message].
 */
function auth_attempt(string $identifier, string $password): array
{
    $ip = client_ip();
    if (auth_is_locked($ip)) {
        return [false, 'Too many failed attempts. Please wait a few minutes and try again.'];
    }

    $stmt = db()->prepare(
        'SELECT * FROM admins WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1'
    );
    $stmt->execute([$identifier, $identifier]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        auth_log_attempt($ip, $identifier, false);
        return [false, 'Incorrect username or password.'];
    }

    // Rehash if the algorithm/cost has changed.
    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        $upd = db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $upd->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    }

    // New session id on privilege change.
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['_created'] = time();

    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
    auth_log_attempt($ip, $identifier, true);

    return [true, 'Welcome back.'];
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Occasionally purge old login attempt rows.
 */
function auth_gc(): void
{
    if (random_int(1, 20) !== 1) {
        return;
    }
    try {
        db()->exec('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)');
    } catch (PDOException $e) {
        // non-critical
    }
}
