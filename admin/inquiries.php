<?php
/** Admin: contact inquiries. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');
    $id = post_int('id');
    if ($action === 'status') {
        $status = post_str('status');
        if (in_array($status, ['new', 'read', 'replied'], true)) {
            $pdo->prepare('UPDATE contact_inquiries SET status=? WHERE id=?')->execute([$status, $id]);
            flash('success', 'Inquiry marked as ' . $status . '.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM contact_inquiries WHERE id=?')->execute([$id]);
        flash('success', 'Inquiry deleted.');
        redirect(admin_url('inquiries.php'));
    }
    redirect(admin_url('inquiries.php' . ($id ? '?id=' . $id : '')));
}

// Detail view.
$viewId = (int) get('id', 0);
if ($viewId) {
    $stmt = $pdo->prepare('SELECT * FROM contact_inquiries WHERE id=? LIMIT 1');
    $stmt->execute([$viewId]);
    $inq = $stmt->fetch();
    if (!$inq) { flash('error', 'Inquiry not found.'); redirect(admin_url('inquiries.php')); }
    // Auto-mark as read on open.
    if ($inq['status'] === 'new') {
        $pdo->prepare("UPDATE contact_inquiries SET status='read' WHERE id=?")->execute([$viewId]);
        $inq['status'] = 'read';
    }

    $page_title = 'Inquiry';
    $active = 'inquiries';
    include __DIR__ . '/includes/admin-header.php';
    ?>
    <a href="<?= attr(admin_url('inquiries.php')) ?>" class="btn btn-ghost btn-sm mb-2">← Back to inquiries</a>
    <div class="form-grid">
      <div class="panel">
        <div class="panel-head"><h2><?= e($inq['name']) ?></h2><span class="pill <?= $inq['status'] === 'replied' ? 'pill-green' : ($inq['status'] === 'read' ? 'pill-gray' : 'pill-blue') ?>"><?= e(ucfirst($inq['status'])) ?></span></div>
        <p class="muted" style="white-space:pre-wrap;"><?= nl2br(e($inq['message'])) ?></p>
        <hr style="border:0;border-top:1px solid var(--border-soft);margin:1.2rem 0;">
        <div class="flex gap wrap">
          <a class="btn btn-primary" href="mailto:<?= attr($inq['email']) ?>?subject=<?= rawurlencode('Re: your inquiry — ' . setting('site_name')) ?>">Reply by email</a>
          <?php if ($inq['phone']): ?><a class="btn btn-ghost" href="https://wa.me/<?= attr(preg_replace('/[^0-9]/', '', $inq['phone'])) ?>" target="_blank" rel="noopener">WhatsApp</a><?php endif; ?>
          <form method="post" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int) $inq['id'] ?>"><input type="hidden" name="status" value="replied"><button class="btn btn-ghost">Mark replied</button></form>
          <form method="post" style="display:inline;" data-confirm="Delete this inquiry permanently?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $inq['id'] ?>"><button class="btn btn-danger">Delete</button></form>
        </div>
      </div>
      <div class="panel">
        <h2 style="font-size:1rem;">Details</h2>
        <ul class="recent-list" style="font-size:.9rem;">
          <li style="padding:.5rem 0;border-bottom:1px solid var(--border-soft);"><strong>Email</strong><br><a href="mailto:<?= attr($inq['email']) ?>" style="color:var(--accent);"><?= e($inq['email']) ?></a></li>
          <?php if ($inq['phone']): ?><li style="padding:.5rem 0;border-bottom:1px solid var(--border-soft);"><strong>Phone</strong><br><?= e($inq['phone']) ?></li><?php endif; ?>
          <?php if ($inq['service']): ?><li style="padding:.5rem 0;border-bottom:1px solid var(--border-soft);"><strong>Service</strong><br><?= e($inq['service']) ?></li><?php endif; ?>
          <?php if ($inq['budget']): ?><li style="padding:.5rem 0;border-bottom:1px solid var(--border-soft);"><strong>Budget</strong><br><?= e($inq['budget']) ?></li><?php endif; ?>
          <li style="padding:.5rem 0;border-bottom:1px solid var(--border-soft);"><strong>Received</strong><br><?= e(format_date($inq['created_at'], 'M j, Y · g:i a')) ?></li>
          <li style="padding:.5rem 0;"><strong>IP</strong><br><span class="dim"><?= e($inq['ip_address']) ?></span></li>
        </ul>
      </div>
    </div>
    <?php
    include __DIR__ . '/includes/admin-footer.php';
    return;
}

// List view.
$filter = get('status', '');
$q = trim((string) get('q', ''));
$where = '1=1';
$params = [];
if (in_array($filter, ['new', 'read', 'replied'], true)) { $where .= ' AND status = ?'; $params[] = $filter; }
if ($q !== '') { $where .= ' AND (name LIKE ? OR email LIKE ? OR message LIKE ?)'; $like = '%' . $q . '%'; array_push($params, $like, $like, $like); }

$stmt = $pdo->prepare("SELECT * FROM contact_inquiries WHERE $where ORDER BY created_at DESC LIMIT 300");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$page_title = 'Inquiries';
$active = 'inquiries';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="panel">
  <form class="flex gap wrap center" method="get" action="<?= attr(admin_url('inquiries.php')) ?>">
    <input class="input" type="search" name="q" value="<?= attr($q) ?>" placeholder="Search…" style="max-width:240px;">
    <select class="select" name="status" style="max-width:170px;" onchange="this.form.submit()">
      <option value="">All</option>
      <?php foreach (['new'=>'New','read'=>'Read','replied'=>'Replied'] as $k=>$lbl): ?><option value="<?= $k ?>"<?= $filter===$k?' selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-ghost btn-sm">Filter</button>
  </form>
</div>
<?php if ($rows): ?>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Name</th><th>Service</th><th>Received</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><a href="<?= attr(admin_url('inquiries.php?id=' . (int) $r['id'])) ?>"><span class="cell-title"><?= e($r['name']) ?></span></a><br><span class="cell-sub"><?= e($r['email']) ?></span></td>
        <td class="cell-sub"><?= e($r['service'] ?: '—') ?></td>
        <td class="cell-sub"><?= e(format_date($r['created_at'], 'M j, Y')) ?></td>
        <td><span class="pill <?= $r['status']==='replied'?'pill-green':($r['status']==='read'?'pill-gray':'pill-blue') ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('inquiries.php?id=' . (int) $r['id'])) ?>">Open</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="panel"><div class="admin-empty"><h3>No inquiries<?= $filter||$q?' match your filter':' yet' ?></h3><p>Messages sent through the contact form appear here.</p></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
