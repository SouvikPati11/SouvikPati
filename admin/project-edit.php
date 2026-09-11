<?php
/** Admin: create / edit a portfolio project. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

$id = (int) get('id', 0);
$project = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM portfolio_projects WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) { flash('error', 'Project not found.'); redirect(admin_url('portfolio.php')); }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $title = post_str('title');
    $errors = [];
    if ($title === '') { $errors[] = 'Title is required.'; }
    if (!$errors) {
        $slug = unique_slug($pdo, 'portfolio_projects', slugify(post_str('slug') ?: $title), $id ?: null);
        $data = [
            'title'        => $title,
            'slug'         => $slug,
            'description'  => sanitize_html((string) post('description')),
            'category'     => post_str('category') ?: null,
            'technologies' => post_str('technologies') ?: null,
            'image'        => post_str('image') ?: null,
            'project_url'  => post_str('project_url') ?: null,
            'status'       => post_str('status') === 'inactive' ? 'inactive' : 'active',
            'is_featured'  => post('is_featured') ? 1 : 0,
            'sort_order'   => post_int('sort_order'),
            'seo_title'       => post_str('seo_title') ?: null,
            'seo_description' => post_str('seo_description') ?: null,
            'og_image'        => post_str('og_image') ?: null,
            'noindex'         => post('noindex') ? 1 : 0,
        ];
        if ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $pdo->prepare("UPDATE portfolio_projects SET $set WHERE id = :id")->execute($data + ['id' => $id]);
        } else {
            $cols = implode(', ', array_keys($data));
            $ph = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
            $pdo->prepare("INSERT INTO portfolio_projects ($cols) VALUES ($ph)")->execute($data);
            $id = (int) $pdo->lastInsertId();
        }
        flash('success', 'Project saved.');
        redirect(admin_url('project-edit.php?id=' . $id));
    }
    foreach ($errors as $er) { flash('error', $er); }
}

$v = fn(string $k, $d = '') => attr((string) ($project[$k] ?? $d));
$seo = $project ?: [];
$seo_show = [];

$page_title = $id ? 'Edit project' : 'New project';
$active = 'portfolio';
include __DIR__ . '/includes/admin-header.php';
?>
<form method="post" action="<?= attr(admin_url('project-edit.php' . ($id ? '?id=' . $id : ''))) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div>
      <div class="panel">
        <div class="field"><label>Title</label><input class="input" type="text" name="title" id="pTitle" data-slug-source="#pSlug" required value="<?= $v('title') ?>"></div>
        <div class="field"><label>Slug <span class="hint">— /work/…</span></label><input class="input" type="text" name="slug" id="pSlug" value="<?= $v('slug') ?>" placeholder="auto"></div>
        <div class="field"><label>Description</label>
          <?php $editor_name='description'; $editor_value=(string)($project['description']??''); $editor_placeholder='Describe the project, the problem and what you built…'; include __DIR__ . '/includes/editor.php'; ?>
        </div>
      </div>
      <?php include __DIR__ . '/includes/seo-fields.php'; ?>
    </div>
    <div>
      <div class="panel">
        <div class="field"><label>Status</label>
          <select class="select" name="status">
            <option value="active"<?= ($project['status'] ?? 'active') === 'active' ? ' selected' : '' ?>>Active</option>
            <option value="inactive"<?= ($project['status'] ?? '') === 'inactive' ? ' selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <label class="checkbox mb-2"><input type="checkbox" name="is_featured" value="1"<?= !empty($project['is_featured']) ? ' checked' : '' ?>><span>Feature on homepage</span></label>
        <div class="field"><label>Category</label><input class="input" type="text" name="category" value="<?= $v('category') ?>" placeholder="e.g. Web app"></div>
        <div class="field"><label>Technologies <span class="hint">— comma separated</span></label><input class="input" type="text" name="technologies" value="<?= $v('technologies') ?>" placeholder="PHP, MySQL, JavaScript"></div>
        <div class="field"><label>Project URL <span class="hint">(optional)</span></label><input class="input" type="url" name="project_url" value="<?= $v('project_url') ?>" placeholder="https://…"></div>
        <div class="field"><label>Sort order</label><input class="input" type="number" name="sort_order" value="<?= $v('sort_order', '0') ?>"></div>
      </div>
      <div class="panel">
        <div class="field"><label>Project image</label>
          <div class="flex gap center"><input class="input" type="text" id="pImage" name="image" value="<?= $v('image') ?>" placeholder="/uploads/…"><button type="button" class="btn btn-ghost btn-sm" data-media-target="#pImage" data-media-preview="#pImagePrev">Pick</button></div>
          <img id="pImagePrev" src="<?= $v('image') ?>" <?= $project && $project['image'] ? '' : 'hidden' ?> style="margin-top:.6rem;border-radius:8px;max-height:140px;">
        </div>
      </div>
      <div class="panel"><div class="form-actions"><button class="btn btn-primary" type="submit">Save project</button><a class="btn btn-ghost" href="<?= attr(admin_url('portfolio.php')) ?>">Cancel</a></div></div>
    </div>
  </div>
</form>
<?php include __DIR__ . '/includes/media-modal.php'; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
