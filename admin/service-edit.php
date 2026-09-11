<?php
/** Admin: create / edit a service. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

$id = (int) get('id', 0);
$service = null;
$features = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) { flash('error', 'Service not found.'); redirect(admin_url('services.php')); }
    $features = repo_service_features($id);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();

    $title = post_str('title');
    $slug  = post_str('slug') ?: $title;
    $slug  = slugify($slug);
    $errors = [];
    if ($title === '') { $errors[] = 'Title is required.'; }

    if (!$errors) {
        $slug = unique_slug($pdo, 'services', $slug, $id ?: null);
        $priceRaw = post_str('starting_price');
        $price = $priceRaw === '' ? null : (float) preg_replace('/[^0-9.]/', '', $priceRaw);

        $data = [
            'title'             => $title,
            'slug'              => $slug,
            'icon'              => post_str('icon') ?: 'layers',
            'short_description' => mb_substr(post_str('short_description'), 0, 400),
            'full_description'  => sanitize_html((string) post('full_description')),
            'starting_price'    => $price,
            'price_unit'        => post_str('price_unit') ?: null,
            'price_note'        => post_str('price_note') ?: null,
            'image'             => post_str('image') ?: null,
            'status'            => post_str('status') === 'inactive' ? 'inactive' : 'active',
            'sort_order'        => post_int('sort_order'),
            'seo_title'         => post_str('seo_title') ?: null,
            'seo_description'   => post_str('seo_description') ?: null,
            'seo_keywords'      => post_str('seo_keywords') ?: null,
            'og_title'          => post_str('og_title') ?: null,
            'og_description'    => post_str('og_description') ?: null,
            'og_image'          => post_str('og_image') ?: null,
            'noindex'           => post('noindex') ? 1 : 0,
        ];

        if ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $stmt = $pdo->prepare("UPDATE services SET $set WHERE id = :id");
            $stmt->execute($data + ['id' => $id]);
        } else {
            $cols = implode(', ', array_keys($data));
            $ph = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
            $stmt = $pdo->prepare("INSERT INTO services ($cols) VALUES ($ph)");
            $stmt->execute($data);
            $id = (int) $pdo->lastInsertId();
        }

        // Rebuild feature rows.
        $pdo->prepare('DELETE FROM service_features WHERE service_id = ?')->execute([$id]);
        $feats = (array) ($_POST['features'] ?? []);
        $ins = $pdo->prepare('INSERT INTO service_features (service_id, feature, sort_order) VALUES (?, ?, ?)');
        $order = 0;
        foreach ($feats as $f) {
            $f = trim((string) $f);
            if ($f !== '') { $ins->execute([$id, mb_substr($f, 0, 255), $order++]); }
        }

        flash('success', 'Service saved.');
        redirect(admin_url('service-edit.php?id=' . $id));
    }

    foreach ($errors as $er) { flash('error', $er); }
}

$v = fn(string $k, $d = '') => attr((string) ($service[$k] ?? $d));
$seo = $service ?: [];
$seo_show = ['keywords'];

$page_title = $id ? 'Edit service' : 'New service';
$active = 'services';
include __DIR__ . '/includes/admin-header.php';
?>
<form method="post" action="<?= attr(admin_url('service-edit.php' . ($id ? '?id=' . $id : ''))) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div>
      <div class="panel">
        <div class="field">
          <label>Title</label>
          <input class="input" type="text" name="title" id="svcTitle" data-slug-source="#svcSlug" required value="<?= $v('title') ?>">
        </div>
        <div class="field">
          <label>Slug <span class="hint">— the URL: /services/<span id="slugPreview"></span></span></label>
          <input class="input" type="text" name="slug" id="svcSlug" value="<?= $v('slug') ?>" placeholder="auto-generated from title">
        </div>
        <div class="field">
          <label>Short description <span class="hint">— shown on cards &amp; listings</span></label>
          <textarea class="textarea" name="short_description" maxlength="400" style="min-height:70px;"><?= e((string) ($service['short_description'] ?? '')) ?></textarea>
        </div>
        <div class="field">
          <label>Full description</label>
          <?php
          $editor_name = 'full_description';
          $editor_value = (string) ($service['full_description'] ?? '');
          $editor_placeholder = 'Describe the service, benefits and what is included…';
          include __DIR__ . '/includes/editor.php';
          ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>What's included</h2></div>
        <div class="feature-rows">
          <?php foreach ($features as $f): ?>
            <div class="feature-row"><input class="input" type="text" name="features[]" value="<?= attr($f) ?>"><button type="button" class="btn btn-ghost btn-sm remove-feature">Remove</button></div>
          <?php endforeach; ?>
          <button type="button" class="btn btn-ghost btn-sm" id="addFeature">+ Add feature</button>
        </div>
      </div>

      <?php include __DIR__ . '/includes/seo-fields.php'; ?>
    </div>

    <div>
      <div class="panel">
        <div class="field">
          <label>Status</label>
          <select class="select" name="status">
            <option value="active"<?= ($service['status'] ?? 'active') === 'active' ? ' selected' : '' ?>>Active (visible)</option>
            <option value="inactive"<?= ($service['status'] ?? '') === 'inactive' ? ' selected' : '' ?>>Inactive (hidden)</option>
          </select>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Starting price</label>
            <input class="input" type="text" name="starting_price" value="<?= $service && $service['starting_price'] !== null ? e(rtrim(rtrim(number_format((float)$service['starting_price'], 2, '.', ''), '0'), '.')) : '' ?>" placeholder="e.g. 9999">
          </div>
          <div class="field">
            <label>Sort order</label>
            <input class="input" type="number" name="sort_order" value="<?= $v('sort_order', '0') ?>">
          </div>
        </div>
        <div class="field">
          <label>Price unit <span class="hint">— optional, e.g. /project</span></label>
          <input class="input" type="text" name="price_unit" value="<?= $v('price_unit') ?>" placeholder="(blank)">
        </div>
        <div class="field">
          <label>Price note</label>
          <input class="input" type="text" name="price_note" maxlength="190" value="<?= $v('price_note') ?>" placeholder="e.g. Final quote depends on scope">
        </div>
      </div>

      <div class="panel">
        <div class="field">
          <label>Icon</label>
          <select class="select" name="icon" id="svcIcon">
            <?php foreach (icon_keys() as $k): ?>
              <option value="<?= attr($k) ?>"<?= ($service['icon'] ?? 'layers') === $k ? ' selected' : '' ?>><?= e(ucfirst($k)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Feature image <span class="hint">(optional)</span></label>
          <div class="flex gap center">
            <input class="input" type="text" id="svcImage" name="image" value="<?= $v('image') ?>" placeholder="/uploads/…">
            <button type="button" class="btn btn-ghost btn-sm" data-media-target="#svcImage" data-media-preview="#svcImagePrev">Pick</button>
          </div>
          <img id="svcImagePrev" src="<?= $v('image') ?>" <?= $service && $service['image'] ? '' : 'hidden' ?> style="margin-top:.6rem;border-radius:8px;max-height:120px;">
        </div>
      </div>

      <div class="panel">
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Save service</button>
          <a href="<?= attr(admin_url('services.php')) ?>" class="btn btn-ghost">Cancel</a>
          <?php if ($id): ?><a href="<?= attr(url('services/' . $service['slug'])) ?>" target="_blank" rel="noopener" class="help">View ↗</a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/includes/media-modal.php'; ?>
<script>
(function(){
  var slug=document.getElementById('svcSlug'), prev=document.getElementById('slugPreview');
  function upd(){ if(prev) prev.textContent=slug.value; }
  if(slug){ slug.addEventListener('input',upd); upd(); }
})();
</script>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
