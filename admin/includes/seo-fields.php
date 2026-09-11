<?php
/**
 * Reusable SEO fields block for admin editors.
 * Expects:
 *   $seo (array|null)  — current values (keys: seo_title, seo_description,
 *                        seo_keywords, canonical_url, og_title, og_description,
 *                        og_image, noindex)
 *   $seo_show (array)  — optional list of extra fields to show: 'keywords', 'canonical'
 */
if (!defined('BASE_PATH')) { exit; }
$seo = $seo ?? [];
$seo_show = $seo_show ?? [];
$val = fn(string $k) => attr((string) ($seo[$k] ?? ''));
?>
<details class="seo-box">
  <summary>SEO &amp; social settings <span class="help">(optional — sensible defaults are used if left blank)</span></summary>
  <div class="seo-inner">
    <div class="field">
      <label>SEO title <span class="hint">— shown in search results &amp; the browser tab</span></label>
      <input class="input" type="text" name="seo_title" maxlength="190" value="<?= $val('seo_title') ?>">
    </div>
    <div class="field">
      <label>Meta description <span class="hint">— ~155 characters recommended</span></label>
      <textarea class="textarea" name="seo_description" maxlength="300" style="min-height:70px;"><?= e((string) ($seo['seo_description'] ?? '')) ?></textarea>
    </div>
    <?php if (in_array('keywords', $seo_show, true)): ?>
    <div class="field">
      <label>SEO keywords <span class="hint">— comma separated (optional, low impact)</span></label>
      <input class="input" type="text" name="seo_keywords" maxlength="255" value="<?= $val('seo_keywords') ?>">
    </div>
    <?php endif; ?>
    <?php if (in_array('canonical', $seo_show, true)): ?>
    <div class="field">
      <label>Canonical URL <span class="hint">— only set if this content lives elsewhere</span></label>
      <input class="input" type="url" name="canonical_url" maxlength="255" value="<?= $val('canonical_url') ?>" placeholder="https://…">
    </div>
    <?php endif; ?>
    <div class="field-row">
      <div class="field">
        <label>Open Graph title</label>
        <input class="input" type="text" name="og_title" maxlength="190" value="<?= $val('og_title') ?>">
      </div>
      <div class="field">
        <label>OG image</label>
        <div class="flex gap center">
          <input class="input" type="text" id="og_image_input" name="og_image" value="<?= $val('og_image') ?>" placeholder="/uploads/…">
          <button type="button" class="btn btn-ghost btn-sm" data-media-target="#og_image_input">Pick</button>
        </div>
      </div>
    </div>
    <div class="field">
      <label>Open Graph description</label>
      <textarea class="textarea" name="og_description" maxlength="300" style="min-height:60px;"><?= e((string) ($seo['og_description'] ?? '')) ?></textarea>
    </div>
    <label class="checkbox">
      <input type="checkbox" name="noindex" value="1"<?= !empty($seo['noindex']) ? ' checked' : '' ?>>
      <span>Discourage search engines from indexing this page (noindex)</span>
    </label>
  </div>
</details>
