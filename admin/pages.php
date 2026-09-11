<?php
/** Admin: static / legal pages list. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    if (post_str('action') === 'delete') {
        $id = post_int('id');
        // System pages can be emptied but are safe to delete too; allow it.
        $pdo->prepare('DELETE FROM pages WHERE id=?')->execute([$id]);
        flash('success', 'Page deleted.');
    }
    redirect(admin_url('pages.php'));
}

$rows = $pdo->query('SELECT * FROM pages ORDER BY is_system DESC, title ASC')->fetchAll();

$page_title = 'Pages';
$active = 'pages';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="panel-head"><h2 style="margin:0;">Static pages</h2><a href="<?= attr(admin_url('page-edit.php')) ?>" class="btn btn-primary">+ New page</a></div>
<p class="help mb-2">Legal pages (Privacy, Terms, Disclaimer) and any other static content live here. The About page can also be overridden by creating a page with the slug <code>about</code>.</p>
<?php if ($rows): ?>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Title</th><th>URL</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td class="cell-title"><?= e($r['title']) ?><?= $r['is_system'] ? ' <span class="pill pill-gray">system</span>' : '' ?></td>
        <td class="cell-sub">/<?= e($r['slug']) ?></td>
        <td><span class="pill <?= $r['status']==='published'?'pill-green':'pill-gray' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><div class="row-actions">
          <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('page-edit.php?id=' . (int) $r['id'])) ?>">Edit</a>
          <a class="btn btn-ghost btn-sm" href="<?= attr(url($r['slug'])) ?>" target="_blank" rel="noopener">View</a>
          <form method="post" style="display:inline;" data-confirm="Delete this page?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
        </div></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="panel"><div class="admin-empty"><h3>No pages yet</h3><p>Create your privacy policy, terms and other static pages.</p><a href="<?= attr(admin_url('page-edit.php')) ?>" class="btn btn-primary">+ New page</a></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
