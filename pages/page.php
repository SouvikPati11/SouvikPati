<?php
/** Generic database-backed static page (privacy, terms, disclaimer, etc.). */
if (!defined('BASE_PATH')) { exit; }

$page = $GLOBALS['route_page'] ?? null;
if (!$page) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

seo_set([
    'title'       => $page['seo_title'] ?: $page['title'],
    'description' => $page['seo_description'] ?: excerpt_from((string) $page['content'], 200),
    'canonical'   => $page['canonical_url'] ?: url($page['slug']),
    'robots'      => $page['noindex'] ? 'noindex, follow' : 'index, follow',
    'og_title'    => $page['og_title'] ?: null,
    'og_description' => $page['og_description'] ?: null,
    'og_image'    => $page['og_image'] ?: null,
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => $page['title'], 'url' => url($page['slug'])],
    ],
]);

$active_nav = '';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><span><?= e($page['title']) ?></span></nav>
    <div class="article">
      <div class="section-head"><h1><?= e($page['title']) ?></h1>
        <p class="muted" style="font-size:.85rem;">Last updated <?= e(format_date($page['updated_at'] ?: $page['created_at'])) ?></p>
      </div>
      <div class="prose"><?= $page['content'] /* stored sanitised */ ?></div>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
