<?php
/** Portfolio / work listing. Gracefully empty when there are no projects. */
if (!defined('BASE_PATH')) { exit; }

$projects = repo_projects(false, 60);

seo_set([
    'title'       => 'Selected Work',
    'description' => 'A selection of websites, apps and digital products I have designed and built.',
    'canonical'   => url('work'),
    'robots'      => $projects ? 'index, follow' : 'noindex, follow',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Work', 'url' => url('work')],
    ],
]);

$active_nav = 'work';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><span>Work</span></nav>
    <div class="section-head">
      <span class="eyebrow">Selected work</span>
      <h1>Projects I've built.</h1>
      <p class="lead">A closer look at recent websites, applications and digital products.</p>
    </div>
  </div>
</section>
<section class="section-tight" style="padding-top:0;">
  <div class="container">
    <?php if ($projects): ?>
    <div class="grid cols-2">
      <?php foreach ($projects as $p): ?>
      <a class="card post-card reveal" href="<?= attr(path('work/' . $p['slug'])) ?>">
        <?php if ($p['image']): ?><span class="post-thumb"><img src="<?= attr(absolute_media_url($p['image'])) ?>" alt="<?= attr($p['title']) ?>" loading="lazy"></span><?php endif; ?>
        <span class="post-body">
          <span class="post-meta"><?php if ($p['category']): ?><span class="tag-pill"><?= e($p['category']) ?></span><?php endif; ?></span>
          <h3><?= e($p['title']) ?></h3>
          <?php if ($p['technologies']): ?><p><?= e($p['technologies']) ?></p><?php endif; ?>
          <span class="read-more">View project →</span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <h3>Work is being published soon.</h3>
      <p>Case studies of recent projects are on the way. If you'd like to see relevant examples for your kind of project, just ask.</p>
      <a href="<?= attr(path('contact')) ?>" class="btn btn-primary">Get in touch</a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
