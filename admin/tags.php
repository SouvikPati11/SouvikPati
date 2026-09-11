<?php
/** Admin: blog tags. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');
    if ($action === 'save') {
        $id = post_int('id');
        $name = post_str('name');
        if ($name !== '') {
            $slug = unique_slug($pdo, 'blog_tags', slugify(post_str('slug') ?: $name), $id ?: null);
            if ($id) {
                $pdo->prepare('UPDATE blog_tags SET name=?, slug=? WHERE id=?')->execute([$name, $slug, $id]);
            } else {
                $pdo->prepare('INSERT INTO blog_tags (name, slug) VALUES (?,?)')->execute([$name, $slug]);
            }
            flash('success', 'Tag saved.');
        } else { flash('error', 'Name is required.'); }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM blog_tags WHERE id=?')->execute([post_int('id')]);
        flash('success', 'Tag deleted.');
    }
    redirect(admin_url('tags.php'));
}

$edit = null;
if ($eid = (int) get('edit', 0)) {
    $stmt = $pdo->prepare('SELECT * FROM blog_tags WHERE id=?');
    $stmt->execute([$eid]);
    $edit = $stmt->fetch() ?: null;
}
$rows = $pdo->query('SELECT t.*, (SELECT COUNT(*) FROM post_tags pt WHERE pt.tag_id=t.id) AS n FROM blog_tags t ORDER BY t.name ASC')->fetchAll();

$page_title = 'Tags';
$active = 'tags';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="form-grid">
  <div>
    <?php if ($rows): ?>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td class="cell-title"><?= e($r['name']) ?></td>
            <td class="cell-sub"><?= e($r['slug']) ?></td>
            <td><?= (int) $r['n'] ?></td>
            <td><div class="row-actions">
              <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('tags.php?edit=' . (int) $r['id'])) ?>">Edit</a>
              <form method="post" style="display:inline;" data-confirm="Delete this tag?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
            </div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="panel"><div class="admin-empty"><h3>No tags yet</h3><p>Tags are also created automatically when you add them to a post.</p></div></div>
    <?php endif; ?>
  </div>
  <div>
    <div class="panel">
      <div class="panel-head"><h2><?= $edit ? 'Edit tag' : 'Add tag' ?></h2></div>
      <form method="post" action="<?= attr(admin_url('tags.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <div class="field"><label>Name</label><input class="input" type="text" name="name" required value="<?= attr((string) ($edit['name'] ?? '')) ?>"></div>
        <div class="field"><label>Slug <span class="hint">(optional)</span></label><input class="input" type="text" name="slug" value="<?= attr((string) ($edit['slug'] ?? '')) ?>" placeholder="auto"></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Add' ?></button><?php if ($edit): ?><a class="btn btn-ghost" href="<?= attr(admin_url('tags.php')) ?>">Cancel</a><?php endif; ?></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
