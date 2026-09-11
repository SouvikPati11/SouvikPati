<?php
/**
 * Installer for souvikpati.in.
 *
 * Standalone: it runs before config/config.php exists, so it does NOT use the
 * application bootstrap. It checks requirements, creates the database schema,
 * seeds sensible starter content, creates the first admin and writes the
 * config file. After install it refuses to run again.
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_name('SPINSTALL');
session_start();

define('ROOT', dirname(__DIR__));
$configFile = ROOT . '/config/config.php';
$schemaFile = ROOT . '/database/database.sql';

// --- CSRF (standalone) -----------------------------------------------------
if (empty($_SESSION['icsrf'])) { $_SESSION['icsrf'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['icsrf'];
function icheck(): bool {
    return isset($_POST['_csrf']) && is_string($_POST['_csrf']) && hash_equals($_SESSION['icsrf'] ?? '', $_POST['_csrf']);
}
function h(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$installed = is_file($configFile);
$errors = [];
$done = false;

// --- Handle "remove installer" action (post-install) -----------------------
if ($installed && ($_POST['action'] ?? '') === 'remove' && icheck()) {
    // Best-effort self-delete of the install directory.
    $dir = __DIR__;
    foreach (glob($dir . '/*') ?: [] as $f) { @unlink($f); }
    @rmdir($dir);
    if (!is_dir($dir)) {
        header('Location: /');
        exit;
    }
    $errors[] = 'Could not delete the installer automatically. Please delete the /install folder manually via your file manager or FTP.';
}

// --- Requirements ----------------------------------------------------------
$req = [
    'PHP 8.0+'                 => version_compare(PHP_VERSION, '8.0.0', '>='),
    'PDO MySQL extension'      => extension_loaded('pdo_mysql'),
    'mbstring extension'       => extension_loaded('mbstring'),
    'fileinfo extension'       => extension_loaded('fileinfo'),
    'JSON support'             => function_exists('json_encode'),
    'config/ is writable'      => is_writable(ROOT . '/config'),
    'uploads/ is writable'     => is_writable(ROOT . '/uploads'),
    'Schema file present'      => is_file($schemaFile),
];
$reqOk = !in_array(false, $req, true);

// --- Handle install submit -------------------------------------------------
if (!$installed && ($_POST['action'] ?? '') === 'install') {
    if (!icheck()) {
        $errors[] = 'Security token mismatch. Please reload and try again.';
    } elseif (!$reqOk) {
        $errors[] = 'Please resolve the failing requirements first.';
    } else {
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = (string) ($_POST['db_pass'] ?? '');
        $siteUrl = rtrim(trim($_POST['site_url'] ?? ''), '/');
        $siteName = trim($_POST['site_name'] ?? 'Souvik Pati');
        $adminName = trim($_POST['admin_name'] ?? '');
        $adminUser = trim($_POST['admin_user'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminPass = (string) ($_POST['admin_pass'] ?? '');

        if ($dbName === '' || $dbUser === '') { $errors[] = 'Database name and user are required.'; }
        if ($siteUrl === '' || !filter_var($siteUrl, FILTER_VALIDATE_URL)) { $errors[] = 'A valid site URL is required (e.g. https://souvikpati.in).'; }
        if ($adminName === '' || $adminUser === '') { $errors[] = 'Admin name and username are required.'; }
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid admin email is required.'; }
        if (strlen($adminPass) < 8) { $errors[] = 'Admin password must be at least 8 characters.'; }

        $pdo = null;
        if (!$errors) {
            try {
                $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                $errors[] = 'Could not connect to the database: ' . $e->getMessage();
            }
        }

        if (!$errors && $pdo) {
            try {
                // Import schema.
                $sql = file_get_contents($schemaFile);
                if ($sql === false) { throw new RuntimeException('Could not read the schema file.'); }
                $pdo->exec($sql);

                // Seed content + settings + admin.
                require __DIR__ . '/seed.php';
                install_seed($pdo, [
                    'site_name'  => $siteName,
                    'site_url'   => $siteUrl,
                    'admin_email'=> $adminEmail,
                ]);

                // Create admin.
                $stmt = $pdo->prepare('INSERT INTO admins (name, username, email, password_hash) VALUES (?,?,?,?)');
                $stmt->execute([$adminName, $adminUser, $adminEmail, password_hash($adminPass, PASSWORD_DEFAULT)]);

                // Write config file.
                $appKey = bin2hex(random_bytes(24));
                $cfg = "<?php\n"
                    . "// Generated by the installer on " . date('Y-m-d H:i:s') . " UTC\n"
                    . "define('DB_HOST', " . var_export($dbHost, true) . ");\n"
                    . "define('DB_NAME', " . var_export($dbName, true) . ");\n"
                    . "define('DB_USER', " . var_export($dbUser, true) . ");\n"
                    . "define('DB_PASS', " . var_export($dbPass, true) . ");\n"
                    . "define('DB_CHARSET', 'utf8mb4');\n"
                    . "define('SITE_URL', " . var_export($siteUrl, true) . ");\n"
                    . "define('APP_KEY', " . var_export($appKey, true) . ");\n"
                    . "define('APP_ENV', 'production');\n";
                if (@file_put_contents($configFile, $cfg) === false) {
                    throw new RuntimeException('Could not write config/config.php. Check that the config/ folder is writable.');
                }
                @chmod($configFile, 0640);

                $done = true;
                $installed = true;
            } catch (Throwable $e) {
                $errors[] = 'Installation failed: ' . $e->getMessage();
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install — souvikpati.in</title>
<style>
  :root{color-scheme:dark;}
  *{box-sizing:border-box;}
  body{margin:0;background:#0b0d10;color:#eef1f5;font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;line-height:1.6;}
  .wrap{max-width:640px;margin:0 auto;padding:2.5rem 1.25rem 4rem;}
  h1{font-size:1.7rem;letter-spacing:-.02em;} h2{font-size:1.15rem;margin-top:2rem;}
  .card{background:#14171e;border:1px solid #1e232b;border-radius:14px;padding:1.5rem;margin-bottom:1.4rem;}
  label{display:block;font-size:.85rem;font-weight:600;margin:.9rem 0 .35rem;}
  .hint{font-weight:400;color:#6b7280;}
  input{width:100%;font-family:inherit;font-size:.95rem;color:#eef1f5;background:#0b0d10;border:1px solid #262b34;border-radius:9px;padding:.65rem .8rem;}
  input:focus{outline:none;border-color:#5b8cff;box-shadow:0 0 0 3px rgba(91,140,255,.14);}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
  @media(max-width:520px){.row{grid-template-columns:1fr;}}
  .btn{display:inline-block;background:#5b8cff;color:#fff;border:0;border-radius:999px;padding:.8rem 1.6rem;font-weight:600;font-size:.95rem;cursor:pointer;text-decoration:none;margin-top:1.4rem;}
  .req{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #1e232b;font-size:.9rem;}
  .req:last-child{border-bottom:0;}
  .ok{color:#6ee7b7;} .bad{color:#fca5a5;}
  .alert{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.35);color:#fecaca;padding:.8rem 1rem;border-radius:9px;margin-bottom:1rem;font-size:.9rem;}
  .success{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.35);color:#a7f3d0;padding:.8rem 1rem;border-radius:9px;margin-bottom:1rem;}
  .brand{display:inline-flex;align-items:center;gap:.6rem;font-weight:700;margin-bottom:.5rem;}
  .mark{width:36px;height:36px;border-radius:9px;display:grid;place-items:center;background:linear-gradient(135deg,#5b8cff,#7c6cff);color:#fff;}
  code{background:#0b0d10;padding:.1rem .4rem;border-radius:5px;font-size:.85em;}
  .muted{color:#9aa3af;font-size:.9rem;}
</style>
</head>
<body>
<div class="wrap">
  <div class="brand"><span class="mark">S</span> <span>Website Installer</span></div>

  <?php if ($done): ?>
    <div class="card">
      <h1>Installation complete 🎉</h1>
      <div class="success">Your site is installed and the configuration file has been written.</div>
      <p class="muted">For security you must now remove the installer. Click the button below to delete the <code>/install</code> folder automatically, or delete it manually via your file manager / FTP.</p>
      <form method="post" action="/install/">
        <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="action" value="remove">
        <button class="btn" type="submit">Remove installer &amp; finish</button>
      </form>
      <p class="muted" style="margin-top:1.4rem;">Then log in at <a href="/admin/login.php" style="color:#5b8cff;">/admin/login.php</a> with the admin account you just created.</p>
    </div>
  <?php elseif ($installed): ?>
    <div class="card">
      <h1>Already installed</h1>
      <?php foreach ($errors as $er): ?><div class="alert"><?= h($er) ?></div><?php endforeach; ?>
      <p class="muted">A configuration file already exists, so the installer is disabled. For security, please remove the <code>/install</code> folder.</p>
      <form method="post" action="/install/">
        <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="action" value="remove">
        <button class="btn" type="submit">Delete installer folder</button>
      </form>
      <p class="muted" style="margin-top:1rem;"><a href="/admin/login.php" style="color:#5b8cff;">Go to admin login →</a></p>
    </div>
  <?php else: ?>
    <div class="card">
      <h1>Install your website</h1>
      <p class="muted">This sets up the database, creates your admin account and writes the configuration. Have your database details from Hostinger / cPanel ready.</p>
    </div>

    <div class="card">
      <h2 style="margin-top:0;">Requirements</h2>
      <?php foreach ($req as $name => $pass): ?>
        <div class="req"><span><?= h($name) ?></span><span class="<?= $pass ? 'ok' : 'bad' ?>"><?= $pass ? '✓ OK' : '✗ Fix' ?></span></div>
      <?php endforeach; ?>
      <?php if (!$reqOk): ?><p class="muted" style="margin-top:1rem;">Resolve the items marked “Fix”, then reload this page. For writable folders, set permissions to <code>755</code> (or <code>775</code>) on <code>config/</code> and <code>uploads/</code>.</p><?php endif; ?>
    </div>

    <?php foreach ($errors as $er): ?><div class="alert"><?= h($er) ?></div><?php endforeach; ?>

    <form method="post" action="/install/">
      <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="install">

      <div class="card">
        <h2 style="margin-top:0;">Database</h2>
        <label>Database host</label>
        <input type="text" name="db_host" value="<?= h($_POST['db_host'] ?? 'localhost') ?>" required>
        <label>Database name</label>
        <input type="text" name="db_name" value="<?= h($_POST['db_name'] ?? '') ?>" required>
        <div class="row">
          <div><label>Database user</label><input type="text" name="db_user" value="<?= h($_POST['db_user'] ?? '') ?>" required></div>
          <div><label>Database password</label><input type="password" name="db_pass" value=""></div>
        </div>
      </div>

      <div class="card">
        <h2 style="margin-top:0;">Site</h2>
        <label>Site URL <span class="hint">— full URL, no trailing slash</span></label>
        <input type="url" name="site_url" value="<?= h($_POST['site_url'] ?? 'https://souvikpati.in') ?>" required>
        <label>Site name</label>
        <input type="text" name="site_name" value="<?= h($_POST['site_name'] ?? 'Souvik Pati') ?>" required>
      </div>

      <div class="card">
        <h2 style="margin-top:0;">Admin account</h2>
        <div class="row">
          <div><label>Your name</label><input type="text" name="admin_name" value="<?= h($_POST['admin_name'] ?? '') ?>" required></div>
          <div><label>Username</label><input type="text" name="admin_user" value="<?= h($_POST['admin_user'] ?? '') ?>" required autocomplete="off"></div>
        </div>
        <label>Email</label>
        <input type="email" name="admin_email" value="<?= h($_POST['admin_email'] ?? '') ?>" required>
        <label>Password <span class="hint">— at least 8 characters</span></label>
        <input type="password" name="admin_pass" required autocomplete="new-password">
      </div>

      <button class="btn" type="submit"<?= $reqOk ? '' : ' disabled style="opacity:.5;cursor:not-allowed;"' ?>>Install now</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
