<?php
/** Admin: create / edit a static page. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

$id = (int) get('id', 0);
$page = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    if (!$page) { flash('error', 'Page not found.'); redirect(admin_url('pages.php')); }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $title = post_str('title');
    $errors = [];
    if ($title === '') { $errors[] = 'Title is required.'; }
    // Reserved slugs that would clash with fixed routes.
    $reserved = ['services','blog','work','portfolio','contact','admin','install','assets','uploads','sitemap.xml','robots.txt'];
    $slug = slugify(post_str('slug') ?: $title);
    if (in_array($slug, $reserved, true)) { $errors[] = 'That slug is reserved. Please choose another.'; }

    if (!$errors) {
        $slug = unique_slug($pdo, 'pages', $slug, $id ?: null);
        $data = [
            'title'   => $title,
            'slug'    => $slug,
            'content' => sanitize_html((string) post('content')),
            'status'  => post_str('status') === 'draft' ? 'draft' : 'published',
            'seo_title'       => post_str('seo_title') ?: null,
            'seo_description' => post_str('seo_description') ?: null,
            'canonical_url'   => post_str('canonical_url') ?: null,
            'og_title'        => post_str('og_title') ?: null,
            'og_description'  => post_str('og_description') ?: null,
            'og_image'        => post_str('og_image') ?: null,
            'noindex'         => post('noindex') ? 1 : 0,
        ];
        if ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $pdo->prepare("UPDATE pages SET $set WHERE id = :id")->execute($data + ['id' => $id]);
        } else {
            $cols = implode(', ', array_keys($data));
            $ph = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
            $pdo->prepare("INSERT INTO pages ($cols) VALUES ($ph)")->execute($data);
            $id = (int) $pdo->lastInsertId();
        }
        flash('success', 'Page saved.');
        redirect(admin_url('page-edit.php?id=' . $id));
    }
    foreach ($errors as $er) { flash('error', $er); }
}

$v = fn(string $k, $d = '') => attr((string) ($page[$k] ?? $d));
$seo = $page ?: [];
$seo_show = ['canonical'];

$page_title = $id ? 'Edit page' : 'New page';
$active = 'pages';
include __DIR__ . '/includes/admin-header.php';
?>
<form method="post" action="<?= attr(admin_url('page-edit.php' . ($id ? '?id=' . $id : ''))) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div>
      <div class="panel">
        <div class="field"><label>Title</label><input class="input" type="text" name="title" id="pgTitle" data-slug-source="#pgSlug" required value="<?= $v('title') ?>"></div>
        <div class="field"><label>Slug <span class="hint">— /<span id="slugPreview"></span></span></label><input class="input" type="text" name="slug" id="pgSlug" value="<?= $v('slug') ?>" placeholder="auto" <?= !empty($page['is_system']) ? 'readonly' : '' ?>></div>
        <div class="field"><label>Content</label>
          <?php $editor_name='content'; $editor_value=(string)($page['content']??''); $editor_placeholder='Write the page content…'; include __DIR__ . '/includes/editor.php'; ?>
        </div>
      </div>
      <?php include __DIR__ . '/includes/seo-fields.php'; ?>
    </div>
    <div>
      <div class="panel">
        <div class="field"><label>Status</label>
          <select class="select" name="status">
            <option value="published"<?= ($page['status'] ?? 'published') === 'published' ? ' selected' : '' ?>>Published</option>
            <option value="draft"<?= ($page['status'] ?? '') === 'draft' ? ' selected' : '' ?>>Draft</option>
          </select>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save page</button><a class="btn btn-ghost" href="<?= attr(admin_url('pages.php')) ?>">Cancel</a></div>
      </div>
    </div>
  </div>
</form>
<?php include __DIR__ . '/includes/media-modal.php'; ?>
<script>(function(){var s=document.getElementById('pgSlug'),p=document.getElementById('slugPreview');function u(){if(p)p.textContent=s.value;}if(s){s.addEventListener('input',u);u();}})();</script>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
