<?php
/** 403 forbidden page. */
if (!defined('BASE_PATH')) { exit; }
if (!headers_sent()) { http_response_code(403); }
seo_set(['title' => 'Access denied', 'robots' => 'noindex, nofollow']);
$active_nav = '';
include BASE_PATH . '/templates/header.php';
?>
<section class="section">
  <div class="container text-center" style="max-width:620px;">
    <p class="eyebrow" style="justify-content:center;">Error 403</p>
    <h1 style="font-size:clamp(3rem,10vw,6rem);margin-bottom:.2rem;">403</h1>
    <h2 style="font-size:1.4rem;">You don't have access to this page.</h2>
    <p class="muted">If you think this is a mistake, get in touch.</p>
    <div class="hero-actions" style="justify-content:center;margin-top:1.5rem;">
      <a href="<?= attr(path()) ?>" class="btn btn-primary">Back to home</a>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
