<?php
/** Admin dashboard. */
require __DIR__ . '/includes/guard.php';

$pdo = db();
$counts = repo_counts();

$recentPosts = $pdo->query(
    "SELECT id, title, status, published_at, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

$recentInquiries = $pdo->query(
    "SELECT id, name, email, service, status, created_at FROM contact_inquiries ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

$page_title = 'Dashboard';
$active = 'dashboard';
include __DIR__ . '/includes/admin-header.php';
?>
<div class="stat-grid">
  <div class="stat"><div class="k">Published posts</div><div class="v"><?= $counts['posts_published'] ?> <small>/ <?= $counts['posts_total'] ?> total</small></div></div>
  <div class="stat"><div class="k">Drafts</div><div class="v"><?= $counts['posts_draft'] ?></div></div>
  <div class="stat"><div class="k">Services</div><div class="v"><?= $counts['services'] ?></div></div>
  <div class="stat"><div class="k">Projects</div><div class="v"><?= $counts['projects'] ?></div></div>
  <div class="stat"><div class="k">New inquiries</div><div class="v"><?= $counts['inquiries_new'] ?> <small>/ <?= $counts['inquiries'] ?> total</small></div></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Quick actions</h2></div>
  <div class="flex gap wrap">
    <a href="<?= attr(admin_url('post-edit.php')) ?>" class="btn btn-primary">+ New blog post</a>
    <a href="<?= attr(admin_url('service-edit.php')) ?>" class="btn btn-ghost">+ New service</a>
    <a href="<?= attr(admin_url('project-edit.php')) ?>" class="btn btn-ghost">+ New project</a>
    <a href="<?= attr(admin_url('media.php')) ?>" class="btn btn-ghost">Upload media</a>
    <a href="<?= attr(admin_url('settings.php')) ?>" class="btn btn-ghost">Site settings</a>
  </div>
</div>

<div class="form-grid">
  <div class="panel">
    <div class="panel-head"><h2>Recent posts</h2><a href="<?= attr(admin_url('posts.php')) ?>" class="btn btn-ghost btn-sm">View all</a></div>
    <?php if ($recentPosts): ?>
      <div class="recent-list">
        <?php foreach ($recentPosts as $p): ?>
          <a href="<?= attr(admin_url('post-edit.php?id=' . (int) $p['id'])) ?>">
            <span class="cell-title"><?= e($p['title']) ?></span>
            <span class="pill <?= $p['status'] === 'published' ? 'pill-green' : ($p['status'] === 'draft' ? 'pill-gray' : 'pill-amber') ?>"><?= e(ucfirst($p['status'])) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted">No posts yet. <a href="<?= attr(admin_url('post-edit.php')) ?>" style="color:var(--accent);">Write your first article →</a></p>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Recent inquiries</h2><a href="<?= attr(admin_url('inquiries.php')) ?>" class="btn btn-ghost btn-sm">View all</a></div>
    <?php if ($recentInquiries): ?>
      <div class="recent-list">
        <?php foreach ($recentInquiries as $q): ?>
          <a href="<?= attr(admin_url('inquiries.php?id=' . (int) $q['id'])) ?>">
            <span><span class="cell-title"><?= e($q['name']) ?></span><br><span class="cell-sub"><?= e($q['service'] ?: $q['email']) ?> · <?= e(format_date($q['created_at'], 'M j')) ?></span></span>
            <?php if ($q['status'] === 'new'): ?><span class="pill pill-blue">New</span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted">No inquiries yet. When someone contacts you through the site, they'll appear here.</p>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
