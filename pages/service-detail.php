<?php
/** Single service detail page. */
if (!defined('BASE_PATH')) { exit; }

$slug = $GLOBALS['route_slug'] ?? '';
$service = repo_service_by_slug($slug);

if (!$service) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

$features = repo_service_features((int) $service['id']);
$faqs     = repo_faqs((int) $service['id']);
$related  = array_values(array_filter(repo_active_services(), fn($s) => $s['id'] != $service['id']));
$related  = array_slice($related, 0, 3);
$posts    = repo_recent_posts(3);
$siteName = setting('site_name', 'Souvik Pati');

$priceStr = format_price($service['starting_price'] !== null ? (float) $service['starting_price'] : null, $service['price_unit']);

seo_set([
    'title'       => $service['seo_title'] ?: $service['title'],
    'description' => $service['seo_description'] ?: $service['short_description'],
    'canonical'   => url('services/' . $service['slug']),
    'robots'      => $service['noindex'] ? 'noindex, follow' : 'index, follow',
    'og_title'    => $service['og_title'] ?: null,
    'og_description' => $service['og_description'] ?: null,
    'og_image'    => $service['og_image'] ?: $service['image'] ?: null,
    'og_type'     => 'website',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Services', 'url' => url('services')],
        ['name' => $service['title'], 'url' => url('services/' . $service['slug'])],
    ],
]);

// Service structured data (no fake ratings).
$serviceSchema = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Service',
    'name'        => $service['title'],
    'description' => $service['short_description'] ?: excerpt_from((string) $service['full_description'], 200),
    'url'         => url('services/' . $service['slug']),
    'serviceType' => $service['title'],
    'provider'    => ['@type' => 'Person', 'name' => $siteName, 'url' => url()],
    'areaServed'  => 'Worldwide',
];
if ($service['starting_price'] !== null) {
    $serviceSchema['offers'] = [
        '@type'         => 'Offer',
        'price'         => (string) (float) $service['starting_price'],
        'priceCurrency' => setting('currency_code', 'INR'),
        'url'           => url('services/' . $service['slug']),
    ];
}
seo_add_jsonld($serviceSchema);

$active_nav = 'services';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span>
      <a href="<?= attr(path('services')) ?>">Services</a><span class="sep">/</span>
      <span><?= e($service['title']) ?></span>
    </nav>
    <div class="split">
      <div>
        <span class="svc-icon" style="width:56px;height:56px;margin-bottom:1.2rem;"><?= icon_svg($service['icon']) ?></span>
        <h1><?= e($service['title']) ?></h1>
        <?php if ($service['short_description']): ?>
          <p class="lead"><?= e($service['short_description']) ?></p>
        <?php endif; ?>

        <?php if (trim((string) $service['full_description']) !== ''): ?>
          <div class="prose mt-3"><?= $service['full_description'] /* stored sanitised */ ?></div>
        <?php endif; ?>

        <?php if ($features): ?>
        <hr class="divider">
        <h2>What's included</h2>
        <ul class="checklist mt-2">
          <?php foreach ($features as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <hr class="divider">
        <h2>How we'll work together</h2>
        <div class="grid cols-2 mt-2">
          <?php
          $steps = [
            ['Scope', 'We define exactly what you need, agree deliverables and set a realistic timeline.'],
            ['Design & build', 'I build in focused iterations with previews so you can steer as we go.'],
            ['Review & test', 'Everything is tested on real devices and reviewed against the agreed scope.'],
            ['Launch & handover', 'Deployment, documentation and optional ongoing support.'],
          ];
          $i = 1;
          foreach ($steps as $s): ?>
          <div class="feature-item">
            <span class="fi-icon" style="font-family:var(--font-display);font-weight:800;"><?= $i++ ?></span>
            <div><h3><?= e($s[0]) ?></h3><p><?= e($s[1]) ?></p></div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($faqs): ?>
        <hr class="divider">
        <h2 class="mb-3">Frequently asked</h2>
        <?php include BASE_PATH . '/pages/partials/faq-list.php'; ?>
        <?php endif; ?>
      </div>

      <aside>
        <div class="card aside-card">
          <div class="price-tag" style="font-size:1.9rem;">
            <small style="font-size:.72rem;">Starting from</small>
            <?= e($priceStr) ?>
          </div>
          <?php if ($service['price_note']): ?>
            <p class="muted" style="font-size:.85rem;margin-top:.4rem;"><?= e($service['price_note']) ?></p>
          <?php else: ?>
            <p class="muted" style="font-size:.85rem;margin-top:.4rem;">Final quote depends on scope. Every project is priced individually.</p>
          <?php endif; ?>
          <a href="<?= attr(path('contact?service=' . urlencode($service['title']))) ?>" class="btn btn-primary btn-block mt-3">Request a quote</a>
          <?php if ($wa = setting('whatsapp')): ?>
          <a href="https://wa.me/<?= attr(preg_replace('/[^0-9]/', '', $wa)) ?>?text=<?= rawurlencode('Hi, I\'m interested in: ' . $service['title']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-block mt-2">Chat on WhatsApp</a>
          <?php endif; ?>
        </div>

        <?php if ($related): ?>
        <div class="card mt-3">
          <h3 style="font-size:1.05rem;">Related services</h3>
          <ul class="mt-2">
            <?php foreach ($related as $r): ?>
              <li style="padding:.5rem 0;border-top:1px solid var(--border-soft);">
                <a href="<?= attr(path('services/' . $r['slug'])) ?>" style="display:flex;align-items:center;gap:.6rem;color:var(--text-muted);">
                  <span style="color:var(--accent);"><?= icon_svg($r['icon'], 'icon') ?></span><?= e($r['title']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<?php if ($posts): ?>
<section class="section" style="background:var(--bg-elev);border-block:1px solid var(--border-soft);">
  <div class="container">
    <div class="section-head"><span class="eyebrow">From the blog</span><h2>Related reading</h2></div>
    <div class="grid cols-3">
      <?php foreach ($posts as $p) { include BASE_PATH . '/pages/partials/post-card.php'; } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section-tight">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Ready to build your <?= e($service['title']) ?>?</h2>
      <p>Share the details and I'll come back with a clear plan and a starting estimate.</p>
      <div class="hero-actions"><a href="<?= attr(path('contact?service=' . urlencode($service['title']))) ?>" class="btn btn-lg btn-primary">Request a quote</a></div>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
