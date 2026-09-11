<?php
/**
 * Dynamic SEO / metadata engine.
 *
 * A page sets its metadata with seo_set([...]) before including the header,
 * then the header calls seo_render_head() to emit all the tags.
 */

declare(strict_types=1);

$GLOBALS['__seo'] = [];
$GLOBALS['__jsonld'] = [];

/**
 * Merge metadata for the current page.
 *
 * Recognised keys: title, description, canonical, robots, og_title,
 * og_description, og_image, og_type, twitter_card, breadcrumbs (array of
 * ['name' => , 'url' => ]).
 */
function seo_set(array $data): void
{
    $GLOBALS['__seo'] = array_merge($GLOBALS['__seo'], array_filter($data, static fn($v) => $v !== null && $v !== ''));
    // breadcrumbs may be an empty-safe array; keep it even if "empty".
    if (array_key_exists('breadcrumbs', $data)) {
        $GLOBALS['__seo']['breadcrumbs'] = $data['breadcrumbs'];
    }
    if (array_key_exists('robots', $data)) {
        $GLOBALS['__seo']['robots'] = $data['robots'];
    }
}

/**
 * Add a JSON-LD structured data block (as a PHP array).
 */
function seo_add_jsonld(array $schema): void
{
    $GLOBALS['__jsonld'][] = $schema;
}

/**
 * Resolve the effective page title (with site name suffix).
 */
function seo_title(): string
{
    $siteName = setting('site_name', 'Souvik Pati');
    $title = $GLOBALS['__seo']['title'] ?? setting('default_seo_title', '');
    if ($title === '') {
        $title = $siteName . ' — ' . setting('tagline', '');
        return trim($title, ' —');
    }
    // Avoid duplicating the site name if already present.
    if (stripos($title, $siteName) !== false) {
        return $title;
    }
    return $title . ' — ' . $siteName;
}

/**
 * Current absolute URL (for canonical / og:url defaults).
 */
function current_url(): string
{
    $base = rtrim(setting('site_url', defined('SITE_URL') ? SITE_URL : ''), '/');
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Strip query string for canonical by default.
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    return $base . $path;
}

/**
 * Turn a stored image path/URL into an absolute URL.
 */
function absolute_media_url(?string $src): string
{
    if (!$src) {
        return '';
    }
    if (preg_match('#^https?://#i', $src)) {
        return $src;
    }
    return url($src);
}

/**
 * Emit all head metadata. Called once from the layout header.
 */
function seo_render_head(): void
{
    $s = $GLOBALS['__seo'];
    $siteName   = setting('site_name', 'Souvik Pati');
    $title      = seo_title();
    $description = $s['description'] ?? setting('default_meta_description', setting('short_bio', ''));
    $description = excerpt_from($description, 300);
    $canonical  = $s['canonical'] ?? current_url();
    $robots     = $s['robots'] ?? 'index, follow';
    $ogType     = $s['og_type'] ?? 'website';
    $ogTitle    = $s['og_title'] ?? $title;
    $ogDesc     = $s['og_description'] ?? $description;
    $ogImage    = absolute_media_url($s['og_image'] ?? setting('default_og_image', ''));
    $twitterCard = $s['twitter_card'] ?? ($ogImage ? 'summary_large_image' : 'summary');

    echo '<title>' . e($title) . "</title>\n";
    echo '<meta name="description" content="' . attr($description) . "\">\n";
    echo '<link rel="canonical" href="' . attr($canonical) . "\">\n";
    echo '<meta name="robots" content="' . attr($robots) . "\">\n";

    // Open Graph
    echo '<meta property="og:site_name" content="' . attr($siteName) . "\">\n";
    echo '<meta property="og:type" content="' . attr($ogType) . "\">\n";
    echo '<meta property="og:title" content="' . attr($ogTitle) . "\">\n";
    echo '<meta property="og:description" content="' . attr($ogDesc) . "\">\n";
    echo '<meta property="og:url" content="' . attr($canonical) . "\">\n";
    if ($ogImage) {
        echo '<meta property="og:image" content="' . attr($ogImage) . "\">\n";
    }

    // Twitter / X
    echo '<meta name="twitter:card" content="' . attr($twitterCard) . "\">\n";
    echo '<meta name="twitter:title" content="' . attr($ogTitle) . "\">\n";
    echo '<meta name="twitter:description" content="' . attr($ogDesc) . "\">\n";
    if ($ogImage) {
        echo '<meta name="twitter:image" content="' . attr($ogImage) . "\">\n";
    }

    // Search engine verification tags.
    if ($g = setting('google_verification')) {
        echo '<meta name="google-site-verification" content="' . attr($g) . "\">\n";
    }
    if ($b = setting('bing_verification')) {
        echo '<meta name="msvalidate.01" content="' . attr($b) . "\">\n";
    }

    // Breadcrumbs JSON-LD.
    if (!empty($s['breadcrumbs']) && is_array($s['breadcrumbs'])) {
        $items = [];
        $pos = 1;
        foreach ($s['breadcrumbs'] as $bc) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $bc['name'],
                'item'     => $bc['url'],
            ];
        }
        seo_add_jsonld([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ]);
    }

    // Emit all JSON-LD blocks.
    foreach ($GLOBALS['__jsonld'] as $schema) {
        echo '<script type="application/ld+json">'
            . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . "</script>\n";
    }
}

/**
 * Baseline Person + WebSite schema for the whole site.
 */
function seo_site_schema(): void
{
    $siteUrl = rtrim(setting('site_url', ''), '/');
    $name = setting('site_name', 'Souvik Pati');
    $sameAs = array_values(array_filter([
        setting('social_github'),
        setting('social_linkedin'),
        setting('social_x'),
        setting('social_instagram'),
    ]));

    $person = [
        '@context' => 'https://schema.org',
        '@type'    => 'Person',
        'name'     => $name,
        'url'      => $siteUrl . '/',
        'jobTitle' => 'Software & Web Developer',
        'description' => setting('short_bio'),
    ];
    if ($sameAs) {
        $person['sameAs'] = $sameAs;
    }
    if ($logo = setting('logo')) {
        $person['image'] = absolute_media_url($logo);
    }
    seo_add_jsonld($person);

    seo_add_jsonld([
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => $name,
        'url'      => $siteUrl . '/',
        'potentialAction' => [
            '@type'  => 'SearchAction',
            'target' => $siteUrl . '/blog?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ]);
}
