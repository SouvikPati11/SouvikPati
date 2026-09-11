<?php
/** Admin: blog posts list. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    if (post_str('action') === 'delete') {
        $pdo->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([post_int('id')]);
        flash('success', 'Post deleted.');
    }
    redirect(admin_url('posts.php'));
}

$filter = get('status', '');
$q = trim((string) get('q', ''));
$where = '1=1';
$params = [];
// Scheduled posts are stored as published with a future publish date.
if ($filter === 'draft') {
    $where .= " AND p.status = 'draft'";
} elseif ($filter === 'scheduled') {
    $where .= " AND p.status = 'published' AND p.published_at > NOW()";
} elseif ($filter === 'published') {
    $where .= " AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())";
}
if ($q !== '') { $where .= ' AND p.title LIKE ?'; $params[] = '%' . $q . '%'; }

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM blog_posts p
    LEFT JOIN blog_categories c ON c.id = p.category_id
    WHERE $where ORDER BY p.created_at DESC LIMIT 200");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$page_title = 'Blog Posts';
$active = 'posts';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="panel-head">
  <h2 style="margin:0;">All posts</h2>
  <a href="<?= attr(admin_url('post-edit.php')) ?>" class="btn btn-primary">+ New post</a>
</div>

<div class="panel">
  <form class="flex gap wrap center" method="get" action="<?= attr(admin_url('posts.php')) ?>">
    <input class="input" type="search" name="q" value="<?= attr($q) ?>" placeholder="Search titles…" style="max-width:240px;">
    <select class="select" name="status" style="max-width:180px;" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'scheduled' => 'Scheduled'] as $k => $lbl): ?>
        <option value="<?= $k ?>"<?= $filter === $k ? ' selected' : '' ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
  </form>
</div>

<?php if ($posts): ?>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($posts as $p):
        $isScheduled = $p['status'] === 'published' && $p['published_at'] && strtotime($p['published_at']) > time();
        $statusLabel = $isScheduled ? 'Scheduled' : ucfirst($p['status']);
        $pill = $p['status'] === 'published' ? ($isScheduled ? 'pill-amber' : 'pill-green') : 'pill-gray';
      ?>
      <tr>
        <td><span class="cell-title"><?= e($p['title']) ?></span><br><span class="cell-sub">/blog/<?= e($p['slug']) ?></span></td>
        <td><span class="cell-sub"><?= e($p['category_name'] ?: '—') ?></span></td>
        <td><span class="pill <?= $pill ?>"><?= e($statusLabel) ?></span></td>
        <td><span class="cell-sub"><?= e($p['published_at'] ? format_date($p['published_at'], 'M j, Y') : format_date($p['created_at'], 'M j, Y')) ?></span></td>
        <td>
          <div class="row-actions">
            <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('post-edit.php?id=' . (int) $p['id'])) ?>">Edit</a>
            <?php if ($p['status'] === 'published' && !$isScheduled): ?><a class="btn btn-ghost btn-sm" href="<?= attr(url('blog/' . $p['slug'])) ?>" target="_blank" rel="noopener">View</a><?php endif; ?>
            <form method="post" style="display:inline;" data-confirm="Delete this post permanently?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="panel"><div class="admin-empty"><h3>No posts found</h3><p>Write your first article to start building your blog.</p><a href="<?= attr(admin_url('post-edit.php')) ?>" class="btn btn-primary">+ New post</a></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
