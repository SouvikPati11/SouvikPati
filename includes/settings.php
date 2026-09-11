<?php
/**
 * Site settings: a simple key/value store cached for the request.
 */

declare(strict_types=1);

$GLOBALS['__settings'] = [];

/**
 * Sensible defaults so the site renders even before settings are configured.
 */
function settings_defaults(): array
{
    return [
        'site_name'          => 'Souvik Pati',
        'site_url'           => defined('SITE_URL') ? SITE_URL : '',
        'tagline'            => 'Digital products, websites & apps built for real-world use.',
        'short_bio'          => 'I design and build websites, web applications, Telegram bots and Android apps for founders, businesses and creators.',
        'logo'               => '',
        'favicon'            => '',
        'email'              => '',
        'whatsapp'           => '',
        'telegram'           => '',
        'phone'              => '',
        'location'           => 'India',
        'currency_symbol'    => '₹',
        'social_github'      => '',
        'social_linkedin'    => '',
        'social_x'           => '',
        'social_instagram'   => '',
        'footer_text'        => '',
        'default_seo_title'  => '',
        'default_meta_description' => '',
        'default_og_image'   => '',
        'google_verification' => '',
        'bing_verification'  => '',
        'ga_measurement_id'  => '',
        'gtm_id'             => '',
        'canonical_host'     => '', // e.g. non-www or www — informational
        'contact_notify_email' => '',
        'enable_email_notifications' => '0',
    ];
}

/**
 * Load all settings rows into the cache, merged over defaults.
 */
function settings_load(PDO $pdo): void
{
    $data = settings_defaults();
    try {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        foreach ($rows as $row) {
            $data[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log('[settings] load failed: ' . $e->getMessage());
    }
    // Fall back to the config SITE_URL if the admin has not set one.
    if (empty($data['site_url']) && defined('SITE_URL')) {
        $data['site_url'] = SITE_URL;
    }
    $GLOBALS['__settings'] = $data;
}

/**
 * Read a single setting with an optional fallback.
 */
function setting(string $key, $default = ''): string
{
    $val = $GLOBALS['__settings'][$key] ?? $default;
    return $val === null ? '' : (string) $val;
}

/**
 * Whether a boolean-ish setting is enabled.
 */
function setting_bool(string $key): bool
{
    $v = strtolower(trim(setting($key, '0')));
    return in_array($v, ['1', 'true', 'yes', 'on'], true);
}

/**
 * Persist a single setting (upsert).
 */
function setting_save(PDO $pdo, string $key, ?string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
    $GLOBALS['__settings'][$key] = $value;
}
