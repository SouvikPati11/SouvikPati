<?php
/** Admin: SEO & search-engine settings. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

$keys = [
    'site_url', 'default_seo_title', 'default_meta_description', 'default_og_image',
    'google_verification', 'bing_verification', 'ga_measurement_id', 'gtm_id',
    'contact_notify_email', 'enable_email_notifications',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    foreach ($keys as $k) {
        if ($k === 'enable_email_notifications') {
            setting_save($pdo, $k, post('enable_email_notifications') ? '1' : '0');
        } elseif ($k === 'site_url') {
            setting_save($pdo, $k, rtrim(mb_substr(post_str($k), 0, 255), '/'));
        } else {
            setting_save($pdo, $k, mb_substr(post_str($k), 0, 2000));
        }
    }
    flash('success', 'SEO settings saved.');
    redirect(admin_url('seo.php'));
}

$page_title = 'SEO & Search';
$active = 'seo';
include __DIR__ . '/includes/admin-header.php';
$siteUrl = setting('site_url');
?>
<form method="post" action="<?= attr(admin_url('seo.php')) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div>
      <div class="panel">
        <div class="panel-head"><h2>Defaults</h2></div>
        <div class="field"><label>Site URL <span class="hint">— used in sitemap, canonical &amp; social tags</span></label><input class="input" type="url" name="site_url" value="<?= attr($siteUrl) ?>" placeholder="https://souvikpati.in"></div>
        <div class="field"><label>Default SEO title <span class="hint">— homepage &amp; fallback</span></label><input class="input" type="text" name="default_seo_title" value="<?= attr(setting('default_seo_title')) ?>"></div>
        <div class="field"><label>Default meta description</label><textarea class="textarea" name="default_meta_description" style="min-height:80px;" maxlength="300"><?= e(setting('default_meta_description')) ?></textarea></div>
        <div class="field"><label>Default OG image</label>
          <div class="flex gap center"><input class="input" type="text" id="def_og" name="default_og_image" value="<?= attr(setting('default_og_image')) ?>" placeholder="/uploads/…"><button type="button" class="btn btn-ghost btn-sm" data-media-target="#def_og" data-media-preview="#def_og_prev">Pick</button></div>
          <img id="def_og_prev" src="<?= attr(setting('default_og_image')) ?>" <?= setting('default_og_image') ? '' : 'hidden' ?> style="margin-top:.5rem;max-height:120px;border-radius:8px;">
        </div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Verification &amp; analytics</h2></div>
        <div class="field"><label>Google Search Console <span class="hint">— verification content value</span></label><input class="input" type="text" name="google_verification" value="<?= attr(setting('google_verification')) ?>" placeholder="e.g. abc123… (the content of the meta tag)"></div>
        <div class="field"><label>Bing Webmaster <span class="hint">— msvalidate.01 value</span></label><input class="input" type="text" name="bing_verification" value="<?= attr(setting('bing_verification')) ?>"></div>
        <div class="field-row">
          <div class="field"><label>Google Analytics ID</label><input class="input" type="text" name="ga_measurement_id" value="<?= attr(setting('ga_measurement_id')) ?>" placeholder="G-XXXXXXX"></div>
          <div class="field"><label>Tag Manager ID</label><input class="input" type="text" name="gtm_id" value="<?= attr(setting('gtm_id')) ?>" placeholder="GTM-XXXXXX"></div>
        </div>
        <p class="help">If a Tag Manager ID is set, it is used and the Analytics tag is left out to avoid double-counting.</p>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Contact notifications</h2></div>
        <label class="checkbox mb-2"><input type="checkbox" name="enable_email_notifications" value="1"<?= setting_bool('enable_email_notifications') ? ' checked' : '' ?>><span>Email me when a new inquiry arrives</span></label>
        <div class="field"><label>Notification email <span class="hint">— defaults to your contact email</span></label><input class="input" type="email" name="contact_notify_email" value="<?= attr(setting('contact_notify_email')) ?>"></div>
        <p class="help">Uses PHP <code>mail()</code>. On some shared hosts this needs an SMTP/email setup to deliver reliably.</p>
      </div>
    </div>

    <div>
      <div class="panel">
        <h2 style="font-size:1rem;">Search tools</h2>
        <ul class="recent-list" style="font-size:.9rem;">
          <li style="padding:.6rem 0;border-bottom:1px solid var(--border-soft);"><strong>Sitemap</strong><br><a href="<?= attr(url('sitemap.xml')) ?>" target="_blank" rel="noopener" style="color:var(--accent);"><?= e($siteUrl ?: '') ?>/sitemap.xml</a></li>
          <li style="padding:.6rem 0;border-bottom:1px solid var(--border-soft);"><strong>robots.txt</strong><br><a href="<?= attr(url('robots.txt')) ?>" target="_blank" rel="noopener" style="color:var(--accent);"><?= e($siteUrl ?: '') ?>/robots.txt</a></li>
          <li style="padding:.6rem 0;"><strong>Submit your sitemap</strong><br><span class="dim">Add the sitemap URL in Google Search Console &amp; Bing Webmaster Tools after verifying the site.</span></li>
        </ul>
      </div>
      <div class="panel"><div class="form-actions"><button class="btn btn-primary" type="submit">Save SEO settings</button></div></div>
    </div>
  </div>
</form>
<?php include __DIR__ . '/includes/media-modal.php'; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
