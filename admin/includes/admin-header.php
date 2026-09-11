<?php
/**
 * Admin layout header. Expects:
 *   $page_title (string), $active (string nav key), $CURRENT_ADMIN (array)
 */
if (!defined('BASE_PATH')) { exit; }

$page_title = $page_title ?? 'Dashboard';
$active = $active ?? '';
$admin = $CURRENT_ADMIN ?? current_admin();
$counts = repo_counts();

$menu = [
    'dashboard' => ['Dashboard',   'index.php',      'dashboard'],
    'services'  => ['Services',    'services.php',   'grid'],
    'posts'     => ['Blog Posts',  'posts.php',      'code'],
    'categories'=> ['Categories',  'categories.php', 'layers'],
    'tags'      => ['Tags',        'tags.php',       'zap'],
    'portfolio' => ['Portfolio',   'portfolio.php',  'app'],
    'faqs'      => ['FAQs',        'faqs.php',       'search'],
    'pages'     => ['Pages',       'pages.php',      'globe'],
    'media'     => ['Media',       'media.php',      'layers'],
    'inquiries' => ['Inquiries',   'inquiries.php',  'plug'],
    'redirects' => ['Redirects',   'redirects.php',  'automation'],
    'settings'  => ['Settings',    'settings.php',   'automation'],
    'seo'       => ['SEO & Search','seo.php',        'search'],
    'users'     => ['Admins',      'users.php',      'bot'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($page_title) ?> — Admin</title>
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="7" fill="#5b8cff"/><text x="16" y="22" font-family="Arial" font-size="16" font-weight="700" fill="#fff" text-anchor="middle">S</text></svg>') ?>">
<link rel="stylesheet" href="<?= attr(path('assets/css/admin.css')) ?>?v=1">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">
      <span class="brand-mark">S</span>
      <span><?= e(setting('site_name', 'Souvik Pati')) ?><small>Admin</small></span>
    </div>
    <nav class="admin-nav">
      <?php foreach ($menu as $key => $m): ?>
        <a href="<?= attr(admin_url($m[1])) ?>"<?= $active === $key ? ' class="active"' : '' ?>>
          <?= icon_svg($m[2], 'nav-ic') ?><span><?= e($m[0]) ?></span>
          <?php if ($key === 'inquiries' && $counts['inquiries_new'] > 0): ?><em class="badge"><?= (int) $counts['inquiries_new'] ?></em><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-foot">
      <a href="<?= attr(url()) ?>" target="_blank" rel="noopener">View site ↗</a>
      <a href="<?= attr(admin_url('logout.php')) ?>">Log out</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-btn" id="adminMenuBtn" aria-label="Toggle menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1 class="admin-title"><?= e($page_title) ?></h1>
      <div class="admin-user">
        <span class="admin-avatar"><?= e(mb_strtoupper(mb_substr((string) ($admin['name'] ?? 'A'), 0, 1))) ?></span>
        <span class="admin-user-name"><?= e($admin['name'] ?? '') ?></span>
      </div>
    </header>

    <div class="admin-content">
      <?php foreach (get_flashes() as $fl):
        $t = in_array($fl['type'], ['success','error','info'], true) ? $fl['type'] : 'info'; ?>
        <div class="flash flash-<?= $t ?>"><?= e($fl['message']) ?></div>
      <?php endforeach; ?>
