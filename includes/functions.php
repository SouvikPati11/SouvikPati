<?php
/**
 * Shared helper functions.
 */

declare(strict_types=1);

/**
 * Escape a string for safe HTML output.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escape for use inside an HTML attribute (same as e(), named for clarity).
 */
function attr(?string $value): string
{
    return e($value);
}

/**
 * Build an absolute URL from a site-relative path.
 */
function url(string $path = ''): string
{
    $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    if ($path === '') {
        return $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}

/**
 * Site-relative path helper (leading slash, no host).
 */
function path(string $p = ''): string
{
    return '/' . ltrim($p, '/');
}

/**
 * Generate an ASCII, URL-safe slug from arbitrary text.
 */
function slugify(string $text): string
{
    $text = trim($text);
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item';
}

/**
 * Ensure a slug is unique within a table (appends -2, -3, ... if needed).
 */
function unique_slug(PDO $pdo, string $table, string $slug, ?int $ignoreId = null): string
{
    $allowed = ['services', 'blog_posts', 'blog_categories', 'blog_tags', 'portfolio_projects', 'pages'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Invalid table for slug uniqueness check.');
    }
    $base = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = $pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if ($stmt->fetch() === false) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
}

/**
 * Redirect helper.
 */
function redirect(string $to, int $code = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . $to, true, $code);
    }
    exit;
}

/**
 * Read a value from $_POST with a default.
 */
function post(string $key, $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

/**
 * Read a value from $_GET with a default.
 */
function get(string $key, $default = ''): mixed
{
    return $_GET[$key] ?? $default;
}

/**
 * Trimmed string from POST.
 */
function post_str(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

/**
 * Integer from POST.
 */
function post_int(string $key, int $default = 0): int
{
    return isset($_POST[$key]) && is_numeric($_POST[$key]) ? (int) $_POST[$key] : $default;
}

/**
 * Validate an email address.
 */
function is_valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Format a price using the site currency.
 */
function format_price(?float $amount, ?string $unit = null): string
{
    if ($amount === null) {
        return 'On request';
    }
    $symbol = setting('currency_symbol', '₹');
    // Trim trailing .00 for clean display.
    $formatted = number_format($amount, ($amount == floor($amount)) ? 0 : 2);
    $out = $symbol . $formatted;
    if ($unit) {
        $out .= ' ' . $unit;
    }
    return $out;
}

/**
 * Human friendly date.
 */
function format_date(?string $datetime, string $format = 'M j, Y'): string
{
    if (!$datetime || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    try {
        return (new DateTime($datetime))->format($format);
    } catch (Exception $e) {
        return '';
    }
}

/**
 * ISO 8601 date for machine-readable markup.
 */
function iso_date(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    try {
        return (new DateTime($datetime))->format('c');
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Estimate reading time (words per minute) from HTML content.
 */
function estimate_reading_time(string $html): int
{
    $text = trim(strip_tags($html));
    if ($text === '') {
        return 1;
    }
    $words = str_word_count($text);
    return max(1, (int) ceil($words / 200));
}

/**
 * Truncate plain text to a length, on a word boundary.
 */
function excerpt_from(string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $text = mb_substr($text, 0, $length);
    $lastSpace = mb_strrpos($text, ' ');
    if ($lastSpace !== false) {
        $text = mb_substr($text, 0, $lastSpace);
    }
    return $text . '…';
}

/**
 * The visitor's IP address (best effort).
 */
function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Sanitised, length-limited user agent string.
 */
function client_user_agent(): string
{
    return mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250);
}

/**
 * Render a service icon as inline SVG. Falls back to a generic icon.
 * Icons are simple, dependency-free line SVGs (24x24, currentColor stroke).
 */
function icon_svg(?string $name, string $class = 'icon'): string
{
    $paths = [
        'code'       => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'cart'       => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'bot'        => '<rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="8.5" cy="16" r="1"/><circle cx="15.5" cy="16" r="1"/><path d="M12 7v4M12 3v2"/>',
        'app'        => '<rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/>',
        'grid'       => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'dashboard'  => '<line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/><rect x="3" y="3" width="18" height="18" rx="2"/>',
        'plug'       => '<path d="M12 22v-5M9 8V2M15 8V2M18 8H6a2 2 0 0 0-2 2v1a5 5 0 0 0 5 5h6a5 5 0 0 0 5-5v-1a2 2 0 0 0-2-2z"/>',
        'automation' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'search'     => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'layers'     => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'zap'        => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'globe'      => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    ];
    $key = $name && isset($paths[$name]) ? $name : 'layers';
    return '<svg class="' . e($class) . '" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$key] . '</svg>';
}

/**
 * Available icon keys (for the admin picker).
 */
function icon_keys(): array
{
    return ['code', 'cart', 'bot', 'app', 'grid', 'dashboard', 'plug', 'automation', 'search', 'layers', 'zap', 'globe'];
}

/**
 * Simple pagination metadata builder.
 */
function paginate(int $total, int $perPage, int $currentPage): array
{
    $perPage = max(1, $perPage);
    $pages = max(1, (int) ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $pages));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'pages'       => $pages,
        'current'     => $currentPage,
        'offset'      => ($currentPage - 1) * $perPage,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $pages,
    ];
}

/**
 * Build a query string preserving existing GET params but overriding some.
 */
function query_with(array $overrides): string
{
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
}

/**
 * A conservative allow-list HTML sanitiser for rich content coming from the
 * admin editor. Admins are trusted, but this prevents accidental script
 * injection and keeps stored content clean. Uses DOMDocument.
 */
function sanitize_html(string $html): string
{
    if (trim($html) === '') {
        return '';
    }

    $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'ul', 'ol', 'li',
        'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'code',
        'img', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tr', 'th',
        'td', 'hr', 'span', 'div', 'iframe',
    ];
    $allowedAttrs = [
        'href', 'src', 'alt', 'title', 'class', 'width', 'height',
        'target', 'rel', 'colspan', 'rowspan', 'loading', 'allowfullscreen',
        'frameborder', 'allow',
    ];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    // Wrap so DOMDocument keeps a single root and UTF-8 is respected.
    $doc->loadHTML(
        '<?xml encoding="UTF-8"><div id="__root__">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $rootNode = $xpath->query('//*[@id="__root__"]')->item(0);
    $nodes = iterator_to_array($xpath->query('//*') ?: []);
    foreach ($nodes as $node) {
        if (!$node instanceof DOMElement) {
            continue;
        }
        $tag = strtolower($node->nodeName);
        if ($tag === 'div' && $node->getAttribute('id') === '__root__') {
            continue;
        }
        if (!in_array($tag, $allowedTags, true)) {
            // Script/style: drop the element and its contents entirely.
            if (in_array($tag, ['script', 'style', 'noscript', 'object', 'embed', 'form', 'input', 'button'], true)) {
                $node->parentNode?->removeChild($node);
                continue;
            }
            // Other disallowed elements: unwrap, keeping their text content.
            $text = $doc->createTextNode($node->textContent);
            $node->parentNode?->replaceChild($text, $node);
            continue;
        }
        // Strip disallowed attributes and dangerous URLs.
        foreach (iterator_to_array($node->attributes ?? []) as $attr) {
            $an = strtolower($attr->nodeName);
            if (!in_array($an, $allowedAttrs, true) || str_starts_with($an, 'on')) {
                $node->removeAttribute($attr->nodeName);
                continue;
            }
            if (in_array($an, ['href', 'src'], true)) {
                $val = trim($attr->nodeValue ?? '');
                if (preg_match('/^\s*javascript:/i', $val) || preg_match('/^\s*data:(?!image\/)/i', $val)) {
                    $node->removeAttribute($attr->nodeName);
                }
            }
        }
        // iframes: only allow trusted video hosts.
        if ($tag === 'iframe') {
            $src = $node->getAttribute('src');
            if (!preg_match('#^https://(www\.youtube(-nocookie)?\.com|player\.vimeo\.com)/#i', $src)) {
                $node->parentNode?->removeChild($node);
            }
        }
        // Harden external links.
        if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    if (!$rootNode instanceof DOMElement) {
        return '';
    }
    $out = '';
    foreach ($rootNode->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

/**
 * Send a JSON response and stop.
 */
function json_response(array $data, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Whether the current request expects JSON (AJAX).
 */
function wants_json(): bool
{
    $xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return $xhr || str_contains($accept, 'application/json');
}
