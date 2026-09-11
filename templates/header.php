<?php
/**
 * Public site header / layout open.
 * Expects (optional): $active_nav ('services'|'work'|'blog'|'about'|'contact')
 * SEO must be configured with seo_set() before including this file.
 */
if (!defined('BASE_PATH')) { exit; }

$active_nav = $active_nav ?? '';
$siteName   = setting('site_name', 'Souvik Pati');
$favicon    = setting('favicon');
$logo       = setting('logo');
$gaId       = setting('ga_measurement_id');
$gtmId      = setting('gtm_id');

$nav = [
    'services' => ['label' => 'Services', 'href' => path('services')],
    'work'     => ['label' => 'Work',     'href' => path('work')],
    'blog'     => ['label' => 'Blog',     'href' => path('blog')],
    'about'    => ['label' => 'About',    'href' => path('about')],
    'contact'  => ['label' => 'Contact',  'href' => path('contact')],
];
// Hide the Work link when there is no portfolio yet.
if (!repo_has_projects()) {
    unset($nav['work']);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b0d10">
<?php seo_render_head(); ?>
<?php if ($favicon): ?>
<link rel="icon" href="<?= attr(absolute_media_url($favicon)) ?>">
<?php else: ?>
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="7" fill="#0b0d10"/><text x="16" y="22" font-family="Arial,Helvetica,sans-serif" font-size="16" font-weight="700" fill="#ffffff" text-anchor="middle">S</text></svg>') ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap">
<link rel="stylesheet" href="<?= attr(path('assets/css/style.css')) ?>?v=1">
<?php if ($gtmId): ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= attr($gtmId) ?>');</script>
<?php endif; ?>
<?php if ($gaId && !$gtmId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= attr($gaId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= attr($gaId) ?>');</script>
<?php endif; ?>
</head>
<body>
<?php if ($gtmId): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= attr($gtmId) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="siteHeader">
  <div class="container header-inner">
    <a class="brand" href="<?= attr(path()) ?>" aria-label="<?= attr($siteName) ?> — home">
      <?php if ($logo): ?>
        <img src="<?= attr(absolute_media_url($logo)) ?>" alt="<?= attr($siteName) ?>" class="brand-logo" width="140" height="32">
      <?php else: ?>
        <span class="brand-mark" aria-hidden="true">S</span>
        <span class="brand-name"><?= e($siteName) ?></span>
      <?php endif; ?>
    </a>

    <nav class="main-nav" aria-label="Primary">
      <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-menu" id="navMenu">
        <?php foreach ($nav as $key => $item): ?>
          <li><a href="<?= attr($item['href']) ?>"<?= $active_nav === $key ? ' class="active" aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
        <li class="nav-cta"><a href="<?= attr(path('contact')) ?>" class="btn btn-sm btn-primary">Start a project</a></li>
      </ul>
    </nav>
  </div>
</header>

<main id="main">
<?php
// Render any queued flash messages.
foreach (get_flashes() as $fl):
    $type = in_array($fl['type'], ['success', 'error', 'info'], true) ? $fl['type'] : 'info';
?>
<div class="container"><div class="flash flash-<?= $type ?>" role="status"><?= e($fl['message']) ?></div></div>
<?php endforeach; ?>
