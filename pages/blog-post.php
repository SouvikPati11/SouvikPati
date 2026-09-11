<?php
/** Single blog post. */
if (!defined('BASE_PATH')) { exit; }

$slug = $GLOBALS['route_slug'] ?? '';
$post = repo_post_by_slug($slug);

if (!$post) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

// Increment view count (best-effort, not shown as a stat anywhere public).
try {
    db()->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);
} catch (PDOException $e) { /* ignore */ }

$tags     = repo_post_tags((int) $post['id']);
$related  = repo_related_posts((int) $post['id'], $post['category_id'] ? (int) $post['category_id'] : null, 3);
$author   = $post['author_name'] ?: ($post['admin_name'] ?: setting('site_name', 'Souvik Pati'));
$siteName = setting('site_name', 'Souvik Pati');
$image    = $post['featured_image'] ? absolute_media_url($post['featured_image']) : absolute_media_url(setting('default_og_image'));

$robots = $post['noindex'] ? 'noindex, follow' : 'index, follow';
$canonical = $post['canonical_url'] ?: url('blog/' . $post['slug']);

seo_set([
    'title'       => $post['seo_title'] ?: $post['title'],
    'description' => $post['seo_description'] ?: ($post['excerpt'] ?: excerpt_from((string) $post['content'], 200)),
    'canonical'   => $canonical,
    'robots'      => $robots,
    'og_type'     => 'article',
    'og_title'    => $post['og_title'] ?: null,
    'og_description' => $post['og_description'] ?: null,
    'og_image'    => $post['og_image'] ?: $post['featured_image'] ?: null,
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Blog', 'url' => url('blog')],
        ['name' => $post['title'], 'url' => url('blog/' . $post['slug'])],
    ],
]);

// Article structured data.
$articleSchema = [
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => $post['title'],
    'description'   => $post['excerpt'] ?: excerpt_from((string) $post['content'], 200),
    'datePublished' => iso_date($post['published_at']),
    'dateModified'  => iso_date($post['updated_at'] ?: $post['published_at']),
    'author'        => ['@type' => 'Person', 'name' => $author],
    'publisher'     => ['@type' => 'Person', 'name' => $siteName],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
    'url'           => url('blog/' . $post['slug']),
];
if ($image) {
    $articleSchema['image'] = $image;
}
seo_add_jsonld($articleSchema);

$active_nav = 'blog';
include BASE_PATH . '/templates/header.php';
?>
<article class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><a href="<?= attr(path('blog')) ?>">Blog</a><span class="sep">/</span><span><?= e($post['title']) ?></span></nav>

    <header class="article-header text-center">
      <?php if (!empty($post['category_name'])): ?>
        <a href="<?= attr(path('blog/category/' . $post['category_slug'])) ?>" class="tag-pill" style="color:var(--accent);font-weight:600;font-size:.85rem;"><?= e($post['category_name']) ?></a>
      <?php endif; ?>
      <h1 style="margin-top:.6rem;"><?= e($post['title']) ?></h1>
      <div class="post-meta" style="justify-content:center;margin-top:1rem;">
        <span>By <?= e($author) ?></span>
        <?php if ($post['published_at']): ?><time datetime="<?= attr(iso_date($post['published_at'])) ?>"><?= e(format_date($post['published_at'])) ?></time><?php endif; ?>
        <?php if ((int)$post['reading_time']): ?><span><?= (int)$post['reading_time'] ?> min read</span><?php endif; ?>
      </div>
    </header>

    <?php if ($post['featured_image']): ?>
    <figure class="article-hero">
      <img src="<?= attr(absolute_media_url($post['featured_image'])) ?>" alt="<?= attr($post['title']) ?>" width="980" height="551">
    </figure>
    <?php endif; ?>

    <div class="article">
      <div class="prose mt-4"><?= $post['content'] /* stored sanitised */ ?></div>

      <?php if ($tags): ?>
      <hr class="divider">
      <div class="tag-list">
        <?php foreach ($tags as $t): ?><a href="<?= attr(path('blog?q=' . urlencode($t['name']))) ?>">#<?= e($t['name']) ?></a><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</article>

<?php if ($related): ?>
<section class="section" style="background:var(--bg-elev);border-block:1px solid var(--border-soft);">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Keep reading</span><h2>Related articles</h2></div>
    <div class="grid cols-3">
      <?php foreach ($related as $p) { include BASE_PATH . '/pages/partials/post-card.php'; } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section-tight">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Have a project in mind?</h2>
      <p>If something here sparked an idea, let's talk about building it properly.</p>
      <div class="hero-actions"><a href="<?= attr(path('contact')) ?>" class="btn btn-lg btn-primary">Start a project</a></div>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
