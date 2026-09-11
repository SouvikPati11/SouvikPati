<?php
/** Admin: services list + delete + reorder. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');
    if ($action === 'delete') {
        $id = post_int('id');
        $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
        flash('success', 'Service deleted.');
    } elseif ($action === 'toggle') {
        $id = post_int('id');
        $stmt = $pdo->prepare("UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ?");
        $stmt->execute([$id]);
        flash('success', 'Service status updated.');
    }
    redirect(admin_url('services.php'));
}

$services = $pdo->query('SELECT * FROM services ORDER BY sort_order ASC, id ASC')->fetchAll();

$page_title = 'Services';
$active = 'services';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="panel-head">
  <h2 style="margin:0;">All services</h2>
  <a href="<?= attr(admin_url('service-edit.php')) ?>" class="btn btn-primary">+ New service</a>
</div>

<?php if ($services): ?>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Title</th><th>Slug</th><th>Starting price</th><th>Order</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($services as $s): ?>
      <tr>
        <td><span class="cell-title"><?= e($s['title']) ?></span><br><span class="cell-sub"><?= e(excerpt_from((string) $s['short_description'], 60)) ?></span></td>
        <td><span class="cell-sub">/services/<?= e($s['slug']) ?></span></td>
        <td><?= e(format_price($s['starting_price'] !== null ? (float) $s['starting_price'] : null, $s['price_unit'])) ?></td>
        <td><?= (int) $s['sort_order'] ?></td>
        <td><span class="pill <?= $s['status'] === 'active' ? 'pill-green' : 'pill-gray' ?>"><?= e(ucfirst($s['status'])) ?></span></td>
        <td>
          <div class="row-actions">
            <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('service-edit.php?id=' . (int) $s['id'])) ?>">Edit</a>
            <form method="post" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="btn btn-ghost btn-sm" type="submit"><?= $s['status'] === 'active' ? 'Hide' : 'Show' ?></button></form>
            <form method="post" style="display:inline;" data-confirm="Delete this service? This cannot be undone."><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="panel"><div class="admin-empty"><h3>No services yet</h3><p>Add your first service so visitors know what you offer.</p><a href="<?= attr(admin_url('service-edit.php')) ?>" class="btn btn-primary">+ New service</a></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
