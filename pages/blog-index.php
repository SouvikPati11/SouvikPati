<?php
/** Blog listing with search + pagination. */
if (!defined('BASE_PATH')) { exit; }

$pdo = db();
$perPage = 9;
$page = max(1, (int) get('page', 1));
$q = trim((string) get('q', ''));

$where = repo_published_posts_query();
$params = [];
if ($q !== '') {
    $where .= ' AND (p.title LIKE ? OR p.excerpt LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts p WHERE $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pg = paginate($total, $perPage, $page);

$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id
        WHERE $where ORDER BY p.published_at DESC
        LIMIT {$pg['per_page']} OFFSET {$pg['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = repo_categories_with_counts();

$robots = 'index, follow';
if ($pg['current'] > 1 || $q !== '') {
    // Keep paginated / search result pages out of duplicate indexing noise.
    $robots = 'noindex, follow';
}

seo_set([
    'title'       => $q !== '' ? ('Search: ' . $q . ' — Blog') : 'Blog',
    'description' => 'Articles and practical notes on web development, apps, Telegram bots, automation and SEO.',
    'canonical'   => url('blog') . ($pg['current'] > 1 ? '?page=' . $pg['current'] : ''),
    'robots'      => $robots,
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Blog', 'url' => url('blog')],
    ],
]);

$active_nav = 'blog';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><span>Blog</span></nav>
    <div class="section-head">
      <span class="eyebrow">Blog</span>
      <h1>Notes on building for the web.</h1>
      <p class="lead">Practical articles on development, apps, automation and getting found online.</p>
    </div>

    <form class="flex gap-2 wrap" method="get" action="<?= attr(path('blog')) ?>" style="max-width:520px;margin-bottom:2rem;" role="search">
      <input class="input" type="search" name="q" value="<?= attr($q) ?>" placeholder="Search articles…" aria-label="Search articles" style="flex:1;">
      <button class="btn btn-primary" type="submit">Search</button>
    </form>

    <?php if ($categories): ?>
    <div class="tag-list mb-3">
      <?php foreach ($categories as $c): ?>
        <a href="<?= attr(path('blog/category/' . $c['slug'])) ?>"><?= e($c['name']) ?> (<?= (int)$c['post_count'] ?>)</a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section-tight" style="padding-top:0;">
  <div class="container">
    <?php if ($posts): ?>
    <div class="grid cols-3">
      <?php foreach ($posts as $p) { include BASE_PATH . '/pages/partials/post-card.php'; } ?>
    </div>
    <?php include BASE_PATH . '/pages/partials/pagination.php'; ?>
    <?php else: ?>
    <div class="empty-state">
      <h3><?= $q !== '' ? 'No articles matched your search.' : 'The blog is just getting started.' ?></h3>
      <p><?= $q !== '' ? 'Try a different keyword, or browse everything.' : 'New articles on web development, apps and automation are on the way.' ?></p>
      <?php if ($q !== ''): ?><a href="<?= attr(path('blog')) ?>" class="btn btn-ghost">View all articles</a><?php else: ?><a href="<?= attr(path('contact')) ?>" class="btn btn-primary">Work with me</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
