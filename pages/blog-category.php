<?php
/** Blog posts within a category. */
if (!defined('BASE_PATH')) { exit; }

$slug = $GLOBALS['route_slug'] ?? '';
$pdo = db();

$catStmt = $pdo->prepare('SELECT * FROM blog_categories WHERE slug = ? LIMIT 1');
$catStmt->execute([$slug]);
$category = $catStmt->fetch();

if (!$category) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

$perPage = 9;
$page = max(1, (int) get('page', 1));
$where = repo_published_posts_query() . ' AND p.category_id = ?';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts p WHERE $where");
$countStmt->execute([$category['id']]);
$total = (int) $countStmt->fetchColumn();
$pg = paginate($total, $perPage, $page);

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id
    WHERE $where ORDER BY p.published_at DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
$stmt->execute([$category['id']]);
$posts = $stmt->fetchAll();

seo_set([
    'title'       => $category['name'] . ' — Articles',
    'description' => $category['description'] ?: ('Articles filed under ' . $category['name'] . '.'),
    'canonical'   => url('blog/category/' . $category['slug']) . ($pg['current'] > 1 ? '?page=' . $pg['current'] : ''),
    'robots'      => $pg['current'] > 1 ? 'noindex, follow' : 'index, follow',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Blog', 'url' => url('blog')],
        ['name' => $category['name'], 'url' => url('blog/category/' . $category['slug'])],
    ],
]);

$active_nav = 'blog';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><a href="<?= attr(path('blog')) ?>">Blog</a><span class="sep">/</span><span><?= e($category['name']) ?></span></nav>
    <div class="section-head">
      <span class="eyebrow">Category</span>
      <h1><?= e($category['name']) ?></h1>
      <?php if ($category['description']): ?><p class="lead"><?= e($category['description']) ?></p><?php endif; ?>
    </div>
  </div>
</section>
<section class="section-tight" style="padding-top:0;">
  <div class="container">
    <?php if ($posts): ?>
    <div class="grid cols-3"><?php foreach ($posts as $p) { include BASE_PATH . '/pages/partials/post-card.php'; } ?></div>
    <?php include BASE_PATH . '/pages/partials/pagination.php'; ?>
    <?php else: ?>
    <div class="empty-state"><h3>No articles here yet.</h3><p>Check back soon, or browse all articles.</p><a href="<?= attr(path('blog')) ?>" class="btn btn-ghost">All articles</a></div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
