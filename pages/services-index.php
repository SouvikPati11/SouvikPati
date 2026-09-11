<?php
/** Services listing page. */
if (!defined('BASE_PATH')) { exit; }

$services = repo_active_services();
$siteName = setting('site_name', 'Souvik Pati');

seo_set([
    'title'       => 'Services & Pricing',
    'description' => 'Website design & development, e-commerce, Telegram bots and mini apps, Android apps, custom web applications, admin panels, API integration and automation — with transparent starting prices.',
    'canonical'   => url('services'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Services', 'url' => url('services')],
    ],
]);

$active_nav = 'services';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><span>Services</span>
    </nav>
    <div class="section-head">
      <span class="eyebrow">Services</span>
      <h1>What I can build for you.</h1>
      <p class="lead">Clear starting prices, individually scoped projects. Every quote is tailored after we talk through exactly what you need.</p>
    </div>
  </div>
</section>

<section class="section-tight" style="padding-top:0;">
  <div class="container">
    <?php if ($services): ?>
    <div class="grid cols-3">
      <?php foreach ($services as $svc): ?>
      <a class="card service-card reveal" href="<?= attr(path('services/' . $svc['slug'])) ?>">
        <span class="svc-icon"><?= icon_svg($svc['icon']) ?></span>
        <h3><?= e($svc['title']) ?></h3>
        <p><?= e($svc['short_description']) ?></p>
        <span class="svc-foot">
          <span class="price-tag">
            <small>Starting from</small>
            <?= e(format_price($svc['starting_price'] !== null ? (float)$svc['starting_price'] : null, $svc['price_unit'])) ?>
          </span>
          <span class="svc-arrow"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <h3>Services are being finalised.</h3>
      <p>The full list of services is on its way. In the meantime, tell me about your project and I'll let you know how I can help.</p>
      <a href="<?= attr(path('contact')) ?>" class="btn btn-primary">Get in touch</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Not sure which service fits?</h2>
      <p>Describe your idea and I'll recommend the right approach and a starting estimate.</p>
      <div class="hero-actions"><a href="<?= attr(path('contact')) ?>" class="btn btn-lg btn-primary">Tell me about your project</a></div>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
