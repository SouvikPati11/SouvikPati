<?php
/**
 * Public site footer / layout close.
 */
if (!defined('BASE_PATH')) { exit; }

$siteName = setting('site_name', 'Souvik Pati');
$footerServices = repo_active_services();
$year = date('Y');
$email    = setting('email');
$whatsapp = setting('whatsapp');
$telegram = setting('telegram');
$socials = [
    'GitHub'    => setting('social_github'),
    'LinkedIn'  => setting('social_linkedin'),
    'X'         => setting('social_x'),
    'Instagram' => setting('social_instagram'),
];
$socials = array_filter($socials);
$footerText = setting('footer_text');
?>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-about">
      <a class="brand" href="<?= attr(path()) ?>">
        <span class="brand-mark" aria-hidden="true">S</span>
        <span class="brand-name"><?= e($siteName) ?></span>
      </a>
      <p class="footer-bio"><?= e(setting('short_bio')) ?></p>
      <?php if ($socials): ?>
      <ul class="footer-social">
        <?php foreach ($socials as $label => $link): ?>
          <li><a href="<?= attr($link) ?>" rel="me noopener" target="_blank"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>

    <div class="footer-col">
      <h3>Services</h3>
      <ul>
        <?php foreach (array_slice($footerServices, 0, 6) as $svc): ?>
          <li><a href="<?= attr(path('services/' . $svc['slug'])) ?>"><?= e($svc['title']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= attr(path('services')) ?>">All services</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Explore</h3>
      <ul>
        <li><a href="<?= attr(path('blog')) ?>">Blog</a></li>
        <?php if (repo_has_projects()): ?><li><a href="<?= attr(path('work')) ?>">Work</a></li><?php endif; ?>
        <li><a href="<?= attr(path('about')) ?>">About</a></li>
        <li><a href="<?= attr(path('contact')) ?>">Contact</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Get in touch</h3>
      <ul>
        <?php if ($email): ?><li><a href="mailto:<?= attr($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
        <?php if ($whatsapp): ?><li><a href="https://wa.me/<?= attr(preg_replace('/[^0-9]/', '', $whatsapp)) ?>" target="_blank" rel="noopener">WhatsApp</a></li><?php endif; ?>
        <?php if ($telegram): ?><li><a href="https://t.me/<?= attr(ltrim($telegram, '@')) ?>" target="_blank" rel="noopener">Telegram</a></li><?php endif; ?>
        <li><a href="<?= attr(path('contact')) ?>">Start a project</a></li>
      </ul>
    </div>
  </div>

  <div class="container footer-bottom">
    <p>&copy; <?= $year ?> <?= e($siteName) ?><?= $footerText ? ' · ' . e($footerText) : '' ?></p>
    <ul class="footer-legal">
      <li><a href="<?= attr(path('privacy-policy')) ?>">Privacy</a></li>
      <li><a href="<?= attr(path('terms')) ?>">Terms</a></li>
      <li><a href="<?= attr(path('disclaimer')) ?>">Disclaimer</a></li>
    </ul>
  </div>
</footer>

<script src="<?= attr(path('assets/js/main.js')) ?>?v=1" defer></script>
</body>
</html>
