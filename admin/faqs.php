<?php
/** Admin: FAQs (global or per-service). */
require __DIR__ . '/includes/guard.php';
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');
    if ($action === 'save') {
        $id = post_int('id');
        $q = post_str('question');
        $a = post_str('answer');
        if ($q !== '' && $a !== '') {
            $svc = post_int('service_id') ?: null;
            $active = post('is_active') ? 1 : 0;
            $order = post_int('sort_order');
            if ($id) {
                $pdo->prepare('UPDATE faqs SET question=?, answer=?, service_id=?, is_active=?, sort_order=? WHERE id=?')
                    ->execute([mb_substr($q,0,300), $a, $svc, $active, $order, $id]);
            } else {
                $pdo->prepare('INSERT INTO faqs (question, answer, service_id, is_active, sort_order) VALUES (?,?,?,?,?)')
                    ->execute([mb_substr($q,0,300), $a, $svc, $active, $order]);
            }
            flash('success', 'FAQ saved.');
        } else { flash('error', 'Question and answer are required.'); }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM faqs WHERE id=?')->execute([post_int('id')]);
        flash('success', 'FAQ deleted.');
    } elseif ($action === 'toggle') {
        $pdo->prepare('UPDATE faqs SET is_active = 1 - is_active WHERE id=?')->execute([post_int('id')]);
    }
    redirect(admin_url('faqs.php'));
}

$services = $pdo->query('SELECT id, title FROM services ORDER BY sort_order ASC')->fetchAll();
$svcMap = [];
foreach ($services as $s) { $svcMap[(int) $s['id']] = $s['title']; }

$edit = null;
if ($eid = (int) get('edit', 0)) {
    $stmt = $pdo->prepare('SELECT * FROM faqs WHERE id=?');
    $stmt->execute([$eid]);
    $edit = $stmt->fetch() ?: null;
}
$rows = $pdo->query('SELECT * FROM faqs ORDER BY service_id IS NOT NULL, service_id, sort_order ASC, id ASC')->fetchAll();

$page_title = 'FAQs';
$active = 'faqs';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="form-grid">
  <div>
    <?php if ($rows): ?>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Question</th><th>Scope</th><th>Order</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td class="cell-title"><?= e($r['question']) ?></td>
            <td class="cell-sub"><?= e($r['service_id'] ? ($svcMap[(int) $r['service_id']] ?? 'Service') : 'Global') ?></td>
            <td><?= (int) $r['sort_order'] ?></td>
            <td><form method="post" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-ghost btn-sm"><?= $r['is_active'] ? 'Yes' : 'No' ?></button></form></td>
            <td><div class="row-actions">
              <a class="btn btn-ghost btn-sm" href="<?= attr(admin_url('faqs.php?edit=' . (int) $r['id'])) ?>">Edit</a>
              <form method="post" style="display:inline;" data-confirm="Delete this FAQ?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
            </div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="panel"><div class="admin-empty"><h3>No FAQs yet</h3><p>Add questions that appear on the homepage (Global) or on a specific service page.</p></div></div>
    <?php endif; ?>
  </div>
  <div>
    <div class="panel">
      <div class="panel-head"><h2><?= $edit ? 'Edit FAQ' : 'Add FAQ' ?></h2></div>
      <form method="post" action="<?= attr(admin_url('faqs.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <div class="field"><label>Question</label><input class="input" type="text" name="question" maxlength="300" required value="<?= attr((string) ($edit['question'] ?? '')) ?>"></div>
        <div class="field"><label>Answer</label><textarea class="textarea" name="answer" required><?= e((string) ($edit['answer'] ?? '')) ?></textarea></div>
        <div class="field"><label>Show on</label>
          <select class="select" name="service_id">
            <option value="">Global (homepage)</option>
            <?php foreach ($services as $s): ?>
              <option value="<?= (int) $s['id'] ?>"<?= ((int)($edit['service_id'] ?? 0)) === (int)$s['id'] ? ' selected' : '' ?>><?= e($s['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-row">
          <div class="field"><label>Sort order</label><input class="input" type="number" name="sort_order" value="<?= attr((string) ($edit['sort_order'] ?? '0')) ?>"></div>
          <div class="field" style="display:flex;align-items:flex-end;"><label class="checkbox"><input type="checkbox" name="is_active" value="1"<?= (!$edit || $edit['is_active']) ? ' checked' : '' ?>><span>Active</span></label></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Add' ?></button><?php if ($edit): ?><a class="btn btn-ghost" href="<?= attr(admin_url('faqs.php')) ?>">Cancel</a><?php endif; ?></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
