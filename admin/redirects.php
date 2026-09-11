<?php
/** Admin: URL redirects (301 / 302 / 410). */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');
    if ($action === 'save') {
        $id = post_int('id');
        $source = '/' . ltrim(trim(post_str('source')), '/');
        $code = (int) post_int('status_code', 301);
        if (!in_array($code, [301, 302, 410], true)) { $code = 301; }
        $target = $code === 410 ? null : ('/' . ltrim(trim(post_str('target')), '/'));
        $activeF = post('is_active') ? 1 : 0;
        if ($source !== '/' && ($code === 410 || $target)) {
            try {
                if ($id) {
                    $pdo->prepare('UPDATE redirects SET source=?, target=?, status_code=?, is_active=? WHERE id=?')->execute([$source, $target, $code, $activeF, $id]);
                } else {
                    $pdo->prepare('INSERT INTO redirects (source, target, status_code, is_active) VALUES (?,?,?,?)')->execute([$source, $target, $code, $activeF]);
                }
                flash('success', 'Redirect saved.');
            } catch (PDOException $e) {
                flash('error', 'A redirect for that source already exists.');
            }
        } else { flash('error', 'Source (and target, unless 410) are required.'); }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM redirects WHERE id=?')->execute([post_int('id')]);
        flash('success', 'Redirect deleted.');
    }
    redirect(admin_url('redirects.php'));
}

$edit = null;
if ($eid = (int) get('edit', 0)) {
    $stmt = $pdo->prepare('SELECT * FROM redirects WHERE id=?');
    $stmt->execute([$eid]);
    $edit = $stmt->fetch() ?: null;
}
$rows = $pdo->query('SELECT * FROM redirects ORDER BY id DESC')->fetchAll();

$page_title = 'Redirects';
$active = 'redirects';
include __DIR__ . '/includes/admin-header.php';
?>
<p class="help mb-2">Create 301 (permanent) or 302 (temporary) redirects, or return 410 (Gone) for removed content. Sources are matched against the request path, e.g. <code>/old-page</code>.</p>
<div class="form-grid">
  <div>
    <?php if ($rows): ?>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>Source</th><th>Target</th><th>Code</th><th>Active</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td class="cell-sub"><?= e($r['source']) ?></td>
          <td class="cell-sub"><?= e($r['target'] ?: '—') ?></td>
          <td><span class="pill pill-blue"><?= (int) $r['status_code'] ?></span></td>
          <td><?= $r['is_active'] ? 'Yes' : 'No' ?></td>
          <td><div class="row-actions">
            <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('redirects.php?edit=' . (int) $r['id'])) ?>">Edit</a>
            <form method="post" style="display:inline;" data-confirm="Delete this redirect?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?>
    <div class="panel"><div class="admin-empty"><h3>No redirects</h3><p>Add redirects when you move or remove content to keep SEO intact.</p></div></div>
    <?php endif; ?>
  </div>
  <div>
    <div class="panel">
      <div class="panel-head"><h2><?= $edit ? 'Edit redirect' : 'Add redirect' ?></h2></div>
      <form method="post" action="<?= attr(admin_url('redirects.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <div class="field"><label>Source path</label><input class="input" type="text" name="source" required value="<?= attr((string) ($edit['source'] ?? '')) ?>" placeholder="/old-url"></div>
        <div class="field"><label>Target path <span class="hint">— not needed for 410</span></label><input class="input" type="text" name="target" value="<?= attr((string) ($edit['target'] ?? '')) ?>" placeholder="/new-url"></div>
        <div class="field"><label>Type</label>
          <select class="select" name="status_code">
            <?php foreach ([301=>'301 — Permanent',302=>'302 — Temporary',410=>'410 — Gone'] as $c=>$lbl): ?>
              <option value="<?= $c ?>"<?= ((int)($edit['status_code'] ?? 301))===$c?' selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="checkbox mb-2"><input type="checkbox" name="is_active" value="1"<?= (!$edit || $edit['is_active']) ? ' checked' : '' ?>><span>Active</span></label>
        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Add' ?></button><?php if ($edit): ?><a class="btn btn-ghost" href="<?= attr(admin_url('redirects.php')) ?>">Cancel</a><?php endif; ?></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
