<?php
/** Homepage. */
if (!defined('BASE_PATH')) { exit; }

$services   = repo_active_services();
$projects   = repo_projects(true, 4);
if (!$projects) { $projects = repo_projects(false, 4); }
$posts      = repo_recent_posts(3);
$faqs       = repo_faqs(null);
$siteName   = setting('site_name', 'Souvik Pati');

seo_set([
    'title'       => setting('default_seo_title') ?: ($siteName . ' — ' . setting('tagline')),
    'description' => setting('default_meta_description') ?: setting('short_bio'),
    'canonical'   => url(),
    'og_type'     => 'website',
]);
seo_site_schema();

$active_nav = '';
include BASE_PATH . '/templates/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <span class="hero-badge"><span class="dot"></span> Available for new projects</span>
    <h1>Digital products, websites &amp; apps<br><span class="gradient-text">built for real-world use.</span></h1>
    <p class="hero-sub"><?= e(setting('short_bio', 'I design and build fast, reliable websites, web applications, Telegram bots and Android apps — from first idea to production launch.')) ?></p>
    <div class="hero-actions">
      <a href="<?= attr(path('contact')) ?>" class="btn btn-lg btn-primary">Start a project
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
      <a href="<?= attr(path('services')) ?>" class="btn btn-lg btn-ghost">Explore services</a>
    </div>
    <div class="hero-meta">
      <span><strong>End-to-end</strong> design &amp; development</span>
      <span><strong>Independent</strong> — you work directly with me</span>
      <span><strong>Clean, maintainable</strong> code you own</span>
    </div>
  </div>
</section>

<!-- Intro -->
<section class="section-tight">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">What I do</span>
      <h2>Practical software, thoughtfully built.</h2>
      <p>I help founders, small businesses and creators turn ideas into shipped products. No bloated retainers, no jargon — just clear scope, honest timelines and software that works the way you need it to.</p>
    </div>
  </div>
</section>

<!-- Services -->
<?php if ($services): ?>
<section class="section" id="services">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Services</span>
      <h2>Everything you need to launch and grow.</h2>
      <p>Transparent starting prices. Every project is scoped and quoted individually after we talk.</p>
    </div>
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
  </div>
</section>
<?php endif; ?>

<!-- Why work with me -->
<section class="section" style="background:var(--bg-elev);border-block:1px solid var(--border-soft);">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Why work with me</span>
      <h2>A direct line to the person building your product.</h2>
    </div>
    <div class="grid cols-3">
      <?php
      $whys = [
        ['zap', 'Ship faster', 'Small scope, tight feedback loops and no layers of account managers between you and the work.'],
        ['layers', 'Own your stack', 'Clean, documented code on standard PHP/MySQL or modern web tooling — nothing locked behind a proprietary platform.'],
        ['search', 'Built for search', 'SEO, performance and Core Web Vitals are considered from day one, not bolted on at the end.'],
        ['grid', 'End-to-end', 'Design, development, deployment and the small details in between — handled.'],
        ['plug', 'Integrations first', 'Payments, messaging, automation and third-party APIs wired in cleanly and reliably.'],
        ['automation', 'Long-term support', 'Clear handover, sensible architecture and the option of ongoing maintenance when you need it.'],
      ];
      foreach ($whys as $w): ?>
      <div class="feature-item reveal">
        <span class="fi-icon"><?= icon_svg($w[0]) ?></span>
        <div>
          <h3><?= e($w[1]) ?></h3>
          <p><?= e($w[2]) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Process -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">How it works</span>
      <h2>A simple, transparent process.</h2>
    </div>
    <div class="grid cols-4">
      <?php
      $steps = [
        ['Discover', 'We talk through your goals, users and constraints, then agree on a clear scope and timeline.'],
        ['Design', 'Structure, flows and interface come together — reviewed with you before a line of production code.'],
        ['Build', 'Development in focused iterations, with working previews so you always know where things stand.'],
        ['Launch', 'Testing, deployment and handover — plus documentation and optional ongoing support.'],
      ];
      $i = 1;
      foreach ($steps as $s): ?>
      <div class="card step reveal">
        <div class="step-num"><?= sprintf('%02d', $i++) ?></div>
        <h3><?= e($s[0]) ?></h3>
        <p><?= e($s[1]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Portfolio (only if real projects exist) -->
<?php if ($projects): ?>
<section class="section" style="background:var(--bg-elev);border-block:1px solid var(--border-soft);">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Selected work</span>
      <h2>A look at recent projects.</h2>
    </div>
    <div class="grid cols-2">
      <?php foreach ($projects as $p): ?>
      <a class="card post-card reveal" href="<?= attr(path('work/' . $p['slug'])) ?>">
        <?php if ($p['image']): ?>
        <span class="post-thumb"><img src="<?= attr(absolute_media_url($p['image'])) ?>" alt="<?= attr($p['title']) ?>" loading="lazy"></span>
        <?php endif; ?>
        <span class="post-body">
          <span class="post-meta"><?php if ($p['category']): ?><span class="tag-pill"><?= e($p['category']) ?></span><?php endif; ?></span>
          <h3><?= e($p['title']) ?></h3>
          <?php if ($p['technologies']): ?><p><?= e($p['technologies']) ?></p><?php endif; ?>
          <span class="read-more">View project →</span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4"><a href="<?= attr(path('work')) ?>" class="btn btn-ghost">See all work</a></div>
  </div>
</section>
<?php endif; ?>

<!-- Technologies -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Capabilities</span>
      <h2>Technologies I build with.</h2>
      <p>Chosen per project for reliability, performance and easy long-term maintenance.</p>
    </div>
    <div class="chips">
      <?php
      $tech = ['PHP','MySQL / MariaDB','JavaScript','HTML5 &amp; CSS3','REST APIs','Telegram Bot API','Android','Bootstrap / Tailwind','AJAX','Payment gateways','cPanel / Hostinger','Git','Automation','SEO &amp; Analytics','Webhooks','Cron jobs'];
      foreach ($tech as $t): ?>
        <span class="chip"><?= $t ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Blog -->
<?php if ($posts): ?>
<section class="section" style="background:var(--bg-elev);border-block:1px solid var(--border-soft);">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Writing</span>
      <h2>Notes &amp; articles.</h2>
      <p>Practical thoughts on building websites, apps and automation.</p>
    </div>
    <div class="grid cols-3">
      <?php foreach ($posts as $p): ?>
        <?php include BASE_PATH . '/pages/partials/post-card.php'; ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4"><a href="<?= attr(path('blog')) ?>" class="btn btn-ghost">Read the blog</a></div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<?php if ($faqs): ?>
<section class="section">
  <div class="container" style="max-width:820px;">
    <div class="section-head center">
      <span class="eyebrow">FAQ</span>
      <h2>Questions, answered.</h2>
    </div>
    <?php include BASE_PATH . '/pages/partials/faq-list.php'; ?>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section-tight">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Planning a new digital product?</h2>
      <p>Tell me what you're building. I'll reply with honest feedback, a suggested approach and a starting estimate — no obligation.</p>
      <div class="hero-actions">
        <a href="<?= attr(path('contact')) ?>" class="btn btn-lg btn-primary">Start the conversation</a>
        <?php if ($wa = setting('whatsapp')): ?>
        <a href="https://wa.me/<?= attr(preg_replace('/[^0-9]/', '', $wa)) ?>" target="_blank" rel="noopener" class="btn btn-lg btn-ghost">Message on WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include BASE_PATH . '/templates/footer.php'; ?>
