<?php
/** About page. Content can be overridden by a DB page with slug 'about'. */
if (!defined('BASE_PATH')) { exit; }

$siteName = setting('site_name', 'Souvik Pati');
$custom = repo_page_by_slug('about');

seo_set([
    'title'       => $custom['seo_title'] ?? 'About',
    'description' => $custom['seo_description'] ?? ('About ' . $siteName . ' — an independent developer building websites, apps and digital products.'),
    'canonical'   => url('about'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'About', 'url' => url('about')],
    ],
]);

$active_nav = 'about';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><span>About</span></nav>

    <?php if ($custom && trim((string)$custom['content']) !== ''): ?>
      <div class="article">
        <div class="section-head"><span class="eyebrow">About</span><h1><?= e($custom['title'] ?: 'About') ?></h1></div>
        <div class="prose"><?= $custom['content'] ?></div>
      </div>
    <?php else: ?>
      <div class="split">
        <div>
          <span class="eyebrow">About</span>
          <h1>I build practical digital products.</h1>
          <p class="lead"><?= e(setting('short_bio', 'I\'m an independent developer who designs and builds websites, web applications, Telegram bots and Android apps.')) ?></p>
          <div class="prose mt-3">
            <p>I work directly with founders, small businesses and creators — no layers of account managers, no bloated process. You get a clear scope, honest timelines and code you actually own.</p>
            <p>My focus is on building things that hold up in the real world: fast, secure, easy to maintain and genuinely useful to the people who rely on them. Whether that's a marketing site that ranks, an internal admin panel that saves hours every week, or a Telegram bot that automates the boring parts of a business.</p>
            <p>If you're planning a new digital product, I'd like to hear about it.</p>
          </div>
          <div class="hero-actions mt-3">
            <a href="<?= attr(path('contact')) ?>" class="btn btn-primary">Start a project</a>
            <a href="<?= attr(path('services')) ?>" class="btn btn-ghost">See services</a>
          </div>
        </div>
        <aside>
          <div class="card aside-card">
            <h3 style="font-size:1.05rem;">At a glance</h3>
            <ul class="mt-2">
              <li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Role</strong><br><span class="muted">Independent developer &amp; product builder</span></li>
              <li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Based in</strong><br><span class="muted"><?= e(setting('location', 'India')) ?></span></li>
              <li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Works with</strong><br><span class="muted">Founders, SMBs, creators &amp; agencies</span></li>
              <li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Focus</strong><br><span class="muted">Web, apps, bots &amp; automation</span></li>
            </ul>
            <?php if ($e = setting('email')): ?><a href="mailto:<?= attr($e) ?>" class="btn btn-ghost btn-block mt-2">Email me</a><?php endif; ?>
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
