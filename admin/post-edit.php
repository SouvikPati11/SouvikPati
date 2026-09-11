<?php
/** Admin: create / edit a blog post. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

$id = (int) get('id', 0);
$post = null;
$postTags = '';

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { flash('error', 'Post not found.'); redirect(admin_url('posts.php')); }
    $tagRows = repo_post_tags($id);
    $postTags = implode(', ', array_column($tagRows, 'name'));
}

$categories = $pdo->query('SELECT id, name FROM blog_categories ORDER BY name ASC')->fetchAll();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();

    $title = post_str('title');
    $slug  = slugify(post_str('slug') ?: $title);
    $errors = [];
    if ($title === '') { $errors[] = 'Title is required.'; }

    if (!$errors) {
        $slug = unique_slug($pdo, 'blog_posts', $slug, $id ?: null);
        $content = sanitize_html((string) post('content'));
        $excerpt = mb_substr(post_str('excerpt'), 0, 500) ?: excerpt_from($content, 160);

        // Reading time: use provided value or estimate.
        $rt = post_int('reading_time');
        if ($rt <= 0) { $rt = estimate_reading_time($content); }

        // Status + publish date. Scheduled = published status + future date.
        $status = post_str('status') === 'draft' ? 'draft' : 'published';
        $publishInput = post_str('published_at');
        $publishedAt = null;
        if ($status === 'published') {
            if ($publishInput !== '') {
                $ts = strtotime($publishInput);
                $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
            } elseif ($post && $post['published_at']) {
                $publishedAt = $post['published_at'];
            } else {
                $publishedAt = date('Y-m-d H:i:s');
            }
        } elseif ($publishInput !== '') {
            // Preserve a chosen date even for drafts.
            $ts = strtotime($publishInput);
            $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : null;
        }

        $catId = post_int('category_id') ?: null;

        $data = [
            'title'          => $title,
            'slug'           => $slug,
            'excerpt'        => $excerpt,
            'content'        => $content,
            'featured_image' => post_str('featured_image') ?: null,
            'category_id'    => $catId,
            'author_id'      => (int) ($CURRENT_ADMIN['id'] ?? 0) ?: null,
            'author_name'    => post_str('author_name') ?: ($CURRENT_ADMIN['name'] ?? null),
            'reading_time'   => $rt,
            'status'         => $status,
            'published_at'   => $publishedAt,
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
            $stmt = $pdo->prepare("UPDATE blog_posts SET $set WHERE id = :id");
            $stmt->execute($data + ['id' => $id]);
        } else {
            $cols = implode(', ', array_keys($data));
            $ph = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
            $stmt = $pdo->prepare("INSERT INTO blog_posts ($cols) VALUES ($ph)");
            $stmt->execute($data);
            $id = (int) $pdo->lastInsertId();
        }

        // Sync tags.
        $tagNames = array_filter(array_map('trim', explode(',', post_str('tags'))));
        $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?')->execute([$id]);
        $findTag = $pdo->prepare('SELECT id FROM blog_tags WHERE slug = ? LIMIT 1');
        $insTag  = $pdo->prepare('INSERT INTO blog_tags (name, slug) VALUES (?, ?)');
        $linkTag = $pdo->prepare('INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)');
        foreach ($tagNames as $tn) {
            $ts = slugify($tn);
            $findTag->execute([$ts]);
            $tagId = $findTag->fetchColumn();
            if (!$tagId) { $insTag->execute([mb_substr($tn, 0, 120), $ts]); $tagId = (int) $pdo->lastInsertId(); }
            $linkTag->execute([$id, (int) $tagId]);
        }

        flash('success', 'Post saved.');
        redirect(admin_url('post-edit.php?id=' . $id));
    }

    foreach ($errors as $er) { flash('error', $er); }
}

$v = fn(string $k, $d = '') => attr((string) ($post[$k] ?? $d));
$seo = $post ?: [];
$seo_show = ['canonical'];
$pubValue = '';
if ($post && $post['published_at']) { $pubValue = date('Y-m-d\TH:i', strtotime($post['published_at'])); }

$page_title = $id ? 'Edit post' : 'New post';
$active = 'posts';
include __DIR__ . '/includes/admin-header.php';
?>
<form method="post" action="<?= attr(admin_url('post-edit.php' . ($id ? '?id=' . $id : ''))) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div>
      <div class="panel">
        <div class="field">
          <label>Title</label>
          <input class="input" type="text" name="title" id="postTitle" data-slug-source="#postSlug" required value="<?= $v('title') ?>">
        </div>
        <div class="field">
          <label>Slug <span class="hint">— /blog/<span id="slugPreview"></span></span></label>
          <input class="input" type="text" name="slug" id="postSlug" value="<?= $v('slug') ?>" placeholder="auto-generated">
        </div>
        <div class="field">
          <label>Content</label>
          <?php
          $editor_name = 'content';
          $editor_value = (string) ($post['content'] ?? '');
          $editor_placeholder = 'Write your article…';
          include __DIR__ . '/includes/editor.php';
          ?>
        </div>
        <div class="field">
          <label>Excerpt <span class="hint">— optional; auto-generated from content if blank</span></label>
          <textarea class="textarea" name="excerpt" maxlength="500" style="min-height:70px;"><?= e((string) ($post['excerpt'] ?? '')) ?></textarea>
        </div>
      </div>

      <?php include __DIR__ . '/includes/seo-fields.php'; ?>
    </div>

    <div>
      <div class="panel">
        <div class="field">
          <label>Status</label>
          <select class="select" name="status">
            <option value="published"<?= ($post['status'] ?? 'draft') === 'published' ? ' selected' : '' ?>>Published</option>
            <option value="draft"<?= ($post['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
          </select>
        </div>
        <div class="field">
          <label>Publish date &amp; time <span class="hint">— set a future time to schedule</span></label>
          <input class="input" type="datetime-local" name="published_at" value="<?= attr($pubValue) ?>">
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Save</button>
          <?php if ($id): ?>
            <a href="<?= attr(url('blog/' . $post['slug'])) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Preview ↗</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel">
        <div class="field">
          <label>Category</label>
          <select class="select" name="category_id">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id'] ?>"<?= ((int)($post['category_id'] ?? 0)) === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="help mt-1"><a href="<?= attr(admin_url('categories.php')) ?>" target="_blank" style="color:var(--accent);">Manage categories ↗</a></p>
        </div>
        <div class="field tag-input">
          <label>Tags <span class="hint">— press Enter or comma</span></label>
          <input type="hidden" name="tags" value="<?= attr($postTags) ?>">
          <input class="input" type="text" placeholder="Add a tag…">
          <div class="tag-input-list"></div>
        </div>
      </div>

      <div class="panel">
        <div class="field">
          <label>Featured image</label>
          <div class="flex gap center">
            <input class="input" type="text" id="postImage" name="featured_image" value="<?= $v('featured_image') ?>" placeholder="/uploads/…">
            <button type="button" class="btn btn-ghost btn-sm" data-media-target="#postImage" data-media-preview="#postImagePrev">Pick</button>
          </div>
          <img id="postImagePrev" src="<?= $v('featured_image') ?>" <?= $post && $post['featured_image'] ? '' : 'hidden' ?> style="margin-top:.6rem;border-radius:8px;max-height:140px;">
        </div>
        <div class="field-row">
          <div class="field">
            <label>Author</label>
            <input class="input" type="text" name="author_name" value="<?= $v('author_name', $CURRENT_ADMIN['name'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Read time <span class="hint">(min)</span></label>
            <input class="input" type="number" name="reading_time" value="<?= $v('reading_time') ?>" placeholder="auto">
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/includes/media-modal.php'; ?>
<script>
(function(){var s=document.getElementById('postSlug'),p=document.getElementById('slugPreview');function u(){if(p)p.textContent=s.value;}if(s){s.addEventListener('input',u);u();}})();
</script>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
