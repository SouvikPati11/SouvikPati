<?php
/**
 * CSRF protection using a per-session token.
 */

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Hidden input field for forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Validate the submitted token; timing-safe.
 */
function csrf_check(?string $token = null): bool
{
    $token = $token ?? ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || $token === '' || empty($_SESSION['_csrf'])) {
        return false;
    }
    return hash_equals($_SESSION['_csrf'], $token);
}

/**
 * Enforce CSRF on state-changing requests, or abort with 419.
 */
function csrf_verify(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    if (!csrf_check()) {
        if (wants_json()) {
            json_response(['ok' => false, 'error' => 'Invalid or expired security token. Please refresh and try again.'], 419);
        }
        http_response_code(419);
        exit('Invalid or expired security token. Please go back, refresh the page and try again.');
    }
}
