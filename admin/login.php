<?php
/** Admin login. */
$GUARD_PUBLIC = true;
require __DIR__ . '/includes/guard.php';

if (is_logged_in()) {
    redirect(admin_url('index.php'));
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $identifier = post_str('identifier');
        $password   = (string) post('password');
        [$ok, $msg] = auth_attempt($identifier, $password);
        if ($ok) {
            $to = $_SESSION['_intended'] ?? admin_url('index.php');
            unset($_SESSION['_intended']);
            // Only allow internal redirects.
            if (!str_starts_with((string) $to, '/')) { $to = admin_url('index.php'); }
            redirect($to);
        }
        $error = $msg;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin login — <?= e(setting('site_name', 'Souvik Pati')) ?></title>
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="7" fill="#5b8cff"/></svg>') ?>">
<link rel="stylesheet" href="<?= attr(path('assets/css/admin.css')) ?>?v=1">
</head>
<body class="login-page">
  <form class="login-card" method="post" action="<?= attr(admin_url('login.php')) ?>" novalidate>
    <div class="brand-mark">S</div>
    <h1>Welcome back</h1>
    <p class="sub">Sign in to manage <?= e(setting('site_name', 'your site')) ?>.</p>

    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>

    <?= csrf_field() ?>
    <div class="field">
      <label for="identifier">Username or email</label>
      <input class="input" id="identifier" name="identifier" type="text" required autofocus autocomplete="username" value="<?= attr(post_str('identifier')) ?>">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
  </form>
</body>
</html>
