<?php
/** Admin: manage admin accounts + change your own password. */
require __DIR__ . '/includes/guard.php';
$pdo = db();
$me = (int) ($CURRENT_ADMIN['id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');

    if ($action === 'password') {
        $current = (string) post('current_password');
        $new = (string) post('new_password');
        $confirm = (string) post('confirm_password');
        $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id=?');
        $stmt->execute([$me]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            flash('error', 'Your current password is incorrect.');
        } elseif (strlen($new) < 8) {
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            flash('error', 'New passwords do not match.');
        } else {
            $pdo->prepare('UPDATE admins SET password_hash=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $me]);
            flash('success', 'Password updated.');
        }
    } elseif ($action === 'profile') {
        $name = post_str('name');
        $email = post_str('email');
        if ($name !== '' && is_valid_email($email)) {
            try {
                $pdo->prepare('UPDATE admins SET name=?, email=? WHERE id=?')->execute([mb_substr($name,0,120), $email, $me]);
                flash('success', 'Profile updated.');
            } catch (PDOException $e) { flash('error', 'That email is already in use.'); }
        } else { flash('error', 'Enter a valid name and email.'); }
    } elseif ($action === 'create') {
        $name = post_str('name');
        $username = slugify(post_str('username'));
        $email = post_str('email');
        $pass = (string) post('password');
        if ($name && $username && is_valid_email($email) && strlen($pass) >= 8) {
            try {
                $pdo->prepare('INSERT INTO admins (name, username, email, password_hash) VALUES (?,?,?,?)')
                    ->execute([mb_substr($name,0,120), $username, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                flash('success', 'Admin account created.');
            } catch (PDOException $e) { flash('error', 'That username or email already exists.'); }
        } else { flash('error', 'All fields are required; password must be 8+ characters.'); }
    } elseif ($action === 'delete') {
        $id = post_int('id');
        if ($id === $me) {
            flash('error', 'You cannot delete your own account.');
        } elseif ((int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() <= 1) {
            flash('error', 'At least one admin account must remain.');
        } else {
            $pdo->prepare('DELETE FROM admins WHERE id=?')->execute([$id]);
            flash('success', 'Admin account deleted.');
        }
    }
    redirect(admin_url('users.php'));
}

$admins = $pdo->query('SELECT id, name, username, email, last_login_at, created_at FROM admins ORDER BY id ASC')->fetchAll();

$page_title = 'Admins & Security';
$active = 'users';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="form-grid">
  <div>
    <div class="panel">
      <div class="panel-head"><h2>Admin accounts</h2></div>
      <div class="table-wrap"><table class="data">
        <thead><tr><th>Name</th><th>Username</th><th>Last login</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($admins as $a): ?>
          <tr>
            <td><span class="cell-title"><?= e($a['name']) ?></span><?= (int)$a['id']===$me?' <span class="pill pill-blue">You</span>':'' ?><br><span class="cell-sub"><?= e($a['email']) ?></span></td>
            <td class="cell-sub"><?= e($a['username']) ?></td>
            <td class="cell-sub"><?= e($a['last_login_at'] ? format_date($a['last_login_at'], 'M j, Y') : 'Never') ?></td>
            <td><?php if ((int)$a['id'] !== $me): ?><form method="post" data-confirm="Delete this admin account?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form><?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Create admin</h2></div>
      <form method="post" action="<?= attr(admin_url('users.php')) ?>">
        <?= csrf_field() ?><input type="hidden" name="action" value="create">
        <div class="field-row">
          <div class="field"><label>Name</label><input class="input" type="text" name="name" required></div>
          <div class="field"><label>Username</label><input class="input" type="text" name="username" required autocomplete="off"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
          <div class="field"><label>Password <span class="hint">8+ chars</span></label><input class="input" type="password" name="password" required autocomplete="new-password"></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Create account</button></div>
      </form>
    </div>
  </div>

  <div>
    <div class="panel">
      <div class="panel-head"><h2>Your profile</h2></div>
      <form method="post" action="<?= attr(admin_url('users.php')) ?>">
        <?= csrf_field() ?><input type="hidden" name="action" value="profile">
        <div class="field"><label>Name</label><input class="input" type="text" name="name" value="<?= attr($CURRENT_ADMIN['name'] ?? '') ?>" required></div>
        <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= attr($CURRENT_ADMIN['email'] ?? '') ?>" required></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Update profile</button></div>
      </form>
    </div>
    <div class="panel">
      <div class="panel-head"><h2>Change password</h2></div>
      <form method="post" action="<?= attr(admin_url('users.php')) ?>">
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <div class="field"><label>Current password</label><input class="input" type="password" name="current_password" required autocomplete="current-password"></div>
        <div class="field"><label>New password <span class="hint">8+ chars</span></label><input class="input" type="password" name="new_password" required autocomplete="new-password"></div>
        <div class="field"><label>Confirm new password</label><input class="input" type="password" name="confirm_password" required autocomplete="new-password"></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Change password</button></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
