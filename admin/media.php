<?php
/** Admin: media library — upload, list, delete. */
require __DIR__ . '/includes/guard.php';
$pdo = db();

const MEDIA_MAX_BYTES = 5242880; // 5 MB
const MEDIA_ALLOWED = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    'image/svg+xml' => 'svg',
];

$uploadsDir = BASE_PATH . '/uploads';

/** Minimal SVG sanitiser — removes scripts, event handlers and external refs. */
function sanitize_svg_file(string $path): bool
{
    $svg = file_get_contents($path);
    if ($svg === false) { return false; }
    // Drop script/foreignObject blocks and inline event handlers / js: URIs.
    $svg = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg) ?? $svg;
    $svg = preg_replace('#<foreignObject\b[^>]*>.*?</foreignObject>#is', '', $svg) ?? $svg;
    $svg = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $svg) ?? $svg;
    $svg = preg_replace('#(href|xlink:href)\s*=\s*("|\')\s*javascript:[^"\']*("|\')#i', '', $svg) ?? $svg;
    $svg = preg_replace('#<!ENTITY[^>]*>#i', '', $svg) ?? $svg;
    return file_put_contents($path, $svg) !== false;
}

/** AJAX list for the media picker. */
if (get('ajax') === 'list') {
    $items = [];
    foreach ($pdo->query('SELECT * FROM media ORDER BY created_at DESC LIMIT 200') as $m) {
        $items[] = [
            'id'  => (int) $m['id'],
            'url' => url($m['filepath']),
            'alt' => $m['alt_text'],
            'name'=> $m['filename'],
        ];
    }
    json_response(['items' => $items]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf();
    $action = post_str('action');

    if ($action === 'delete') {
        $id = post_int('id');
        $stmt = $pdo->prepare('SELECT filepath FROM media WHERE id=?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            $abs = BASE_PATH . '/' . ltrim($row['filepath'], '/');
            $real = realpath($abs);
            // Only delete files that are genuinely inside the uploads dir.
            if ($real && str_starts_with($real, realpath($uploadsDir) ?: $uploadsDir)) {
                @unlink($real);
            }
            $pdo->prepare('DELETE FROM media WHERE id=?')->execute([$id]);
            flash('success', 'File deleted.');
        }
        redirect(admin_url('media.php'));
    }

    if ($action === 'alt') {
        $pdo->prepare('UPDATE media SET alt_text=? WHERE id=?')->execute([mb_substr(post_str('alt_text'),0,255), post_int('id')]);
        flash('success', 'Alt text updated.');
        redirect(admin_url('media.php'));
    }

    // Upload.
    if (!empty($_FILES['file']['name'])) {
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Upload failed (error code ' . (int) $f['error'] . ').');
            redirect(admin_url('media.php'));
        }
        if ($f['size'] > MEDIA_MAX_BYTES) {
            flash('error', 'File is too large. Maximum size is 5 MB.');
            redirect(admin_url('media.php'));
        }
        if (!is_uploaded_file($f['tmp_name'])) {
            flash('error', 'Invalid upload.');
            redirect(admin_url('media.php'));
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']) ?: '';
        if (!isset(MEDIA_ALLOWED[$mime])) {
            flash('error', 'Unsupported file type. Allowed: JPG, PNG, WebP, GIF, SVG.');
            redirect(admin_url('media.php'));
        }
        $ext = MEDIA_ALLOWED[$mime];

        // Build safe, unique filename.
        $baseName = pathinfo($f['name'], PATHINFO_FILENAME);
        $baseName = slugify($baseName);
        $baseName = $baseName !== 'item' ? $baseName : 'file';
        $fname = $baseName . '-' . date('Ymd') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        // Organise by year/month.
        $subdir = date('Y/m');
        $destDir = $uploadsDir . '/' . $subdir;
        if (!is_dir($destDir) && !@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            flash('error', 'Could not create the uploads directory. Check permissions on /uploads.');
            redirect(admin_url('media.php'));
        }
        $dest = $destDir . '/' . $fname;

        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            flash('error', 'Could not save the file. Is /uploads writable?');
            redirect(admin_url('media.php'));
        }
        @chmod($dest, 0644);

        $width = null; $height = null;
        if ($ext === 'svg') {
            sanitize_svg_file($dest);
        } else {
            $size = @getimagesize($dest);
            if ($size === false) {
                @unlink($dest);
                flash('error', 'That file is not a valid image.');
                redirect(admin_url('media.php'));
            }
            $width = $size[0]; $height = $size[1];
        }

        $filepath = '/uploads/' . $subdir . '/' . $fname;
        $pdo->prepare('INSERT INTO media (filename, filepath, mime_type, width, height, filesize, uploaded_by) VALUES (?,?,?,?,?,?,?)')
            ->execute([$fname, $filepath, $mime, $width, $height, (int) $f['size'], (int) ($CURRENT_ADMIN['id'] ?? 0) ?: null]);

        flash('success', 'File uploaded.');
        redirect(admin_url('media.php'));
    }

    flash('error', 'Please choose a file to upload.');
    redirect(admin_url('media.php'));
}

$rows = $pdo->query('SELECT * FROM media ORDER BY created_at DESC LIMIT 300')->fetchAll();

$page_title = 'Media Library';
$active = 'media';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="panel">
  <div class="panel-head"><h2>Upload</h2></div>
  <form method="post" action="<?= attr(admin_url('media.php')) ?>" enctype="multipart/form-data" class="flex gap wrap center">
    <?= csrf_field() ?>
    <input class="input" type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg" required style="max-width:340px;">
    <button class="btn btn-primary" type="submit">Upload</button>
    <span class="help">Max 5 MB · JPG, PNG, WebP, GIF, SVG</span>
  </form>
</div>

<?php if ($rows): ?>
<div class="media-grid">
  <?php foreach ($rows as $m): ?>
  <div class="media-item">
    <div class="thumb"><img src="<?= attr(url($m['filepath'])) ?>" alt="<?= attr($m['alt_text']) ?>" loading="lazy"></div>
    <div class="meta">
      <span class="fn"><?= e($m['filename']) ?></span>
      <?= $m['width'] ? (int) $m['width'] . '×' . (int) $m['height'] . ' · ' : '' ?><?= $m['filesize'] ? round($m['filesize'] / 1024) . ' KB' : '' ?>
    </div>
    <div class="meta-actions">
      <button type="button" onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= attr(url($m['filepath'])) ?>');this.textContent='Copied';">Copy URL</button>
      <form method="post" style="flex:1;" data-confirm="Delete this file?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button type="submit" style="width:100%;color:var(--danger);border-color:rgba(248,113,113,.4);">Delete</button></form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="panel"><div class="admin-empty"><h3>No media yet</h3><p>Upload images to use as featured images, OG images and in your content.</p></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
