<?php
/** Blog post card. Expects $p (a post row with category_name/category_slug). */
if (!defined('BASE_PATH')) { exit; }
$img = $p['featured_image'] ?? '';
$rt  = (int) ($p['reading_time'] ?? 0);
?>
<article class="card post-card reveal">
  <a href="<?= attr(path('blog/' . $p['slug'])) ?>" class="post-thumb" aria-label="<?= attr($p['title']) ?>">
    <?php if ($img): ?>
      <img src="<?= attr(absolute_media_url($img)) ?>" alt="<?= attr($p['title']) ?>" loading="lazy" width="640" height="360">
    <?php else: ?>
      <span style="display:grid;place-items:center;height:100%;color:var(--text-dim);font-family:var(--font-display);font-size:2rem;opacity:.5;"><?= e(mb_substr($p['title'],0,1)) ?></span>
    <?php endif; ?>
  </a>
  <div class="post-body">
    <div class="post-meta">
      <?php if (!empty($p['category_name'])): ?><span class="tag-pill"><?= e($p['category_name']) ?></span><?php endif; ?>
      <?php if (!empty($p['published_at'])): ?><time datetime="<?= attr(iso_date($p['published_at'])) ?>"><?= e(format_date($p['published_at'])) ?></time><?php endif; ?>
      <?php if ($rt): ?><span><?= $rt ?> min read</span><?php endif; ?>
    </div>
    <h3><a href="<?= attr(path('blog/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></h3>
    <p><?= e($p['excerpt'] ?: excerpt_from((string)($p['content'] ?? ''), 130)) ?></p>
    <a class="read-more" href="<?= attr(path('blog/' . $p['slug'])) ?>">Read article →</a>
  </div>
</article>
