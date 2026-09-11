<?php
/** Admin: portfolio projects list. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM portfolio_projects WHERE id=?')->execute([post_int('id')]);
        flash('success', 'Project deleted.');
    } elseif ($action === 'toggle') {
        $pdo->prepare("UPDATE portfolio_projects SET status=IF(status='active','inactive','active') WHERE id=?")->execute([post_int('id')]);
        flash('success', 'Project status updated.');
    }
    redirect(admin_url('portfolio.php'));
}

$rows = $pdo->query('SELECT * FROM portfolio_projects ORDER BY sort_order ASC, created_at DESC')->fetchAll();

$page_title = 'Portfolio';
$active = 'portfolio';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="panel-head">
  <h2 style="margin:0;">Projects</h2>
  <a href="<?= attr(admin_url('project-edit.php')) ?>" class="btn btn-primary">+ New project</a>
</div>

<?php if ($rows): ?>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Title</th><th>Category</th><th>Featured</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><span class="cell-title"><?= e($r['title']) ?></span><br><span class="cell-sub">/work/<?= e($r['slug']) ?></span></td>
        <td class="cell-sub"><?= e($r['category'] ?: '—') ?></td>
        <td><?= $r['is_featured'] ? '<span class="pill pill-blue">Featured</span>' : '<span class="cell-sub">—</span>' ?></td>
        <td><span class="pill <?= $r['status'] === 'active' ? 'pill-green' : 'pill-gray' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><div class="row-actions">
          <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('project-edit.php?id=' . (int) $r['id'])) ?>">Edit</a>
          <form method="post" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-ghost btn-sm"><?= $r['status'] === 'active' ? 'Hide' : 'Show' ?></button></form>
          <form method="post" style="display:inline;" data-confirm="Delete this project?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
        </div></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="panel"><div class="admin-empty"><h3>No projects yet</h3><p>Add real projects to showcase your work. Until then, the public "Work" section stays hidden — no placeholder content is shown.</p><a href="<?= attr(admin_url('project-edit.php')) ?>" class="btn btn-primary">+ New project</a></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
