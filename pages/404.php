<?php
/** 404 (and 410 via $GLOBALS['__status']) page. */
if (!defined('BASE_PATH')) { exit; }

$is410 = ($GLOBALS['__status'] ?? 404) === 410;
if (!headers_sent()) {
    http_response_code($is410 ? 410 : 404);
}

seo_set([
    'title'  => $is410 ? 'Page removed' : 'Page not found',
    'robots' => 'noindex, follow',
    'canonical' => url(ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/')),
]);

$active_nav = '';
include BASE_PATH . '/templates/header.php';
?>
<section class="section">
  <div class="container text-center" style="max-width:620px;">
    <p class="eyebrow" style="justify-content:center;"><?= $is410 ? 'Gone' : 'Error 404' ?></p>
    <h1 style="font-size:clamp(3rem,10vw,6rem);margin-bottom:.2rem;"><?= $is410 ? '410' : '404' ?></h1>
    <h2 style="font-size:1.4rem;"><?= $is410 ? 'This page has been removed.' : 'This page can\'t be found.' ?></h2>
    <p class="muted"><?= $is410 ? 'The page you\'re looking for is no longer available.' : 'The link may be broken or the page may have moved. Let\'s get you back on track.' ?></p>
    <div class="hero-actions" style="justify-content:center;margin-top:1.5rem;">
      <a href="<?= attr(path()) ?>" class="btn btn-primary">Back to home</a>
      <a href="<?= attr(path('services')) ?>" class="btn btn-ghost">View services</a>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
