<?php
/** Single portfolio project. */
if (!defined('BASE_PATH')) { exit; }

$slug = $GLOBALS['route_slug'] ?? '';
$project = repo_project_by_slug($slug);

if (!$project) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

$tech = array_values(array_filter(array_map('trim', explode(',', (string) $project['technologies']))));

seo_set([
    'title'       => $project['seo_title'] ?: $project['title'],
    'description' => $project['seo_description'] ?: excerpt_from((string) $project['description'], 200),
    'canonical'   => url('work/' . $project['slug']),
    'robots'      => $project['noindex'] ? 'noindex, follow' : 'index, follow',
    'og_image'    => $project['og_image'] ?: $project['image'] ?: null,
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Work', 'url' => url('work')],
        ['name' => $project['title'], 'url' => url('work/' . $project['slug'])],
    ],
]);

$active_nav = 'work';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><a href="<?= attr(path('work')) ?>">Work</a><span class="sep">/</span><span><?= e($project['title']) ?></span></nav>
    <div class="section-head">
      <?php if ($project['category']): ?><span class="eyebrow"><?= e($project['category']) ?></span><?php endif; ?>
      <h1><?= e($project['title']) ?></h1>
    </div>
    <?php if ($project['image']): ?>
    <figure class="article-hero" style="margin-top:0;margin-bottom:2rem;"><img src="<?= attr(absolute_media_url($project['image'])) ?>" alt="<?= attr($project['title']) ?>"></figure>
    <?php endif; ?>
    <div class="split">
      <div class="prose"><?= $project['description'] /* stored sanitised */ ?></div>
      <aside>
        <div class="card aside-card">
          <?php if ($tech): ?>
          <h3 style="font-size:1.05rem;">Built with</h3>
          <div class="tag-list mt-2 mb-3"><?php foreach ($tech as $t): ?><a href="#" onclick="return false;" style="pointer-events:none;"><?= e($t) ?></a><?php endforeach; ?></div>
          <?php endif; ?>
          <?php if ($project['project_url']): ?>
            <a href="<?= attr($project['project_url']) ?>" target="_blank" rel="noopener nofollow" class="btn btn-primary btn-block">Visit project ↗</a>
          <?php endif; ?>
          <a href="<?= attr(path('contact')) ?>" class="btn btn-ghost btn-block mt-2">Build something similar</a>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
