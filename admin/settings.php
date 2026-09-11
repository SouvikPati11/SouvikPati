<?php
/** Admin: general site settings. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

$keys = [
    'site_name', 'tagline', 'short_bio', 'logo', 'favicon',
    'email', 'whatsapp', 'telegram', 'phone', 'location', 'currency_symbol', 'currency_code',
    'social_github', 'social_linkedin', 'social_x', 'social_instagram', 'footer_text',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    foreach ($keys as $k) {
        setting_save($pdo, $k, mb_substr(post_str($k), 0, 2000));
    }
    flash('success', 'Settings saved.');
    redirect(admin_url('settings.php'));
}

$page_title = 'Site Settings';
$active = 'settings';
include __DIR__ . '/includes/admin-header.php';
?>
<form method="post" action="<?= attr(admin_url('settings.php')) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div>
      <div class="panel">
        <div class="panel-head"><h2>Identity</h2></div>
        <div class="field"><label>Site name</label><input class="input" type="text" name="site_name" value="<?= attr(setting('site_name')) ?>"></div>
        <div class="field"><label>Tagline</label><input class="input" type="text" name="tagline" value="<?= attr(setting('tagline')) ?>"></div>
        <div class="field"><label>Short bio <span class="hint">— used in the hero &amp; footer</span></label><textarea class="textarea" name="short_bio" style="min-height:90px;"><?= e(setting('short_bio')) ?></textarea></div>
        <div class="field"><label>Footer note <span class="hint">(optional)</span></label><input class="input" type="text" name="footer_text" value="<?= attr(setting('footer_text')) ?>" placeholder="e.g. Built in India"></div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Contact</h2></div>
        <div class="field-row">
          <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= attr(setting('email')) ?>"></div>
          <div class="field"><label>Phone</label><input class="input" type="text" name="phone" value="<?= attr(setting('phone')) ?>"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>WhatsApp <span class="hint">— with country code</span></label><input class="input" type="text" name="whatsapp" value="<?= attr(setting('whatsapp')) ?>" placeholder="e.g. 919876543210"></div>
          <div class="field"><label>Telegram username</label><input class="input" type="text" name="telegram" value="<?= attr(setting('telegram')) ?>" placeholder="username"></div>
        </div>
        <div class="field"><label>Location</label><input class="input" type="text" name="location" value="<?= attr(setting('location')) ?>"></div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Social links</h2></div>
        <div class="field-row">
          <div class="field"><label>GitHub</label><input class="input" type="url" name="social_github" value="<?= attr(setting('social_github')) ?>" placeholder="https://github.com/…"></div>
          <div class="field"><label>LinkedIn</label><input class="input" type="url" name="social_linkedin" value="<?= attr(setting('social_linkedin')) ?>" placeholder="https://linkedin.com/in/…"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>X (Twitter)</label><input class="input" type="url" name="social_x" value="<?= attr(setting('social_x')) ?>" placeholder="https://x.com/…"></div>
          <div class="field"><label>Instagram</label><input class="input" type="url" name="social_instagram" value="<?= attr(setting('social_instagram')) ?>" placeholder="https://instagram.com/…"></div>
        </div>
      </div>
    </div>

    <div>
      <div class="panel">
        <div class="panel-head"><h2>Branding</h2></div>
        <div class="field"><label>Logo <span class="hint">— optional; text logo used if blank</span></label>
          <div class="flex gap center"><input class="input" type="text" id="s_logo" name="logo" value="<?= attr(setting('logo')) ?>" placeholder="/uploads/…"><button type="button" class="btn btn-ghost btn-sm" data-media-target="#s_logo" data-media-preview="#s_logo_prev">Pick</button></div>
          <img id="s_logo_prev" src="<?= attr(setting('logo')) ?>" <?= setting('logo') ? '' : 'hidden' ?> style="margin-top:.5rem;max-height:60px;background:#fff;border-radius:6px;padding:4px;">
        </div>
        <div class="field"><label>Favicon <span class="hint">— small square image</span></label>
          <div class="flex gap center"><input class="input" type="text" id="s_fav" name="favicon" value="<?= attr(setting('favicon')) ?>" placeholder="/uploads/…"><button type="button" class="btn btn-ghost btn-sm" data-media-target="#s_fav" data-media-preview="#s_fav_prev">Pick</button></div>
          <img id="s_fav_prev" src="<?= attr(setting('favicon')) ?>" <?= setting('favicon') ? '' : 'hidden' ?> style="margin-top:.5rem;max-height:40px;">
        </div>
      </div>
      <div class="panel">
        <div class="panel-head"><h2>Currency</h2></div>
        <div class="field-row">
          <div class="field"><label>Symbol</label><input class="input" type="text" name="currency_symbol" value="<?= attr(setting('currency_symbol', '₹')) ?>" maxlength="5"></div>
          <div class="field"><label>Code <span class="hint">— for schema</span></label><input class="input" type="text" name="currency_code" value="<?= attr(setting('currency_code', 'INR')) ?>" maxlength="5" placeholder="INR"></div>
        </div>
      </div>
      <div class="panel"><div class="form-actions"><button class="btn btn-primary" type="submit">Save settings</button></div></div>
    </div>
  </div>
</form>
<?php include __DIR__ . '/includes/media-modal.php'; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
