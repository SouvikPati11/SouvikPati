<?php
/** Pagination control. Expects $pg (from paginate()). */
if (!defined('BASE_PATH')) { exit; }
if (empty($pg) || $pg['pages'] <= 1) { return; }

$cur = $pg['current'];
$pages = $pg['pages'];

// Build a compact page window.
$window = [];
for ($i = 1; $i <= $pages; $i++) {
    if ($i === 1 || $i === $pages || ($i >= $cur - 2 && $i <= $cur + 2)) {
        $window[] = $i;
    }
}
$window = array_values(array_unique($window));
?>
<nav class="pagination" aria-label="Pagination">
  <?php if ($pg['has_prev']): ?>
    <a href="<?= attr(query_with(['page' => $cur - 1])) ?>" rel="prev" aria-label="Previous page">‹</a>
  <?php else: ?><span class="disabled">‹</span><?php endif; ?>

  <?php $prev = 0; foreach ($window as $i): ?>
    <?php if ($prev && $i - $prev > 1): ?><span class="disabled">…</span><?php endif; ?>
    <?php if ($i === $cur): ?>
      <span class="current" aria-current="page"><?= $i ?></span>
    <?php else: ?>
      <a href="<?= attr(query_with(['page' => $i === 1 ? null : $i])) ?>"><?= $i ?></a>
    <?php endif; ?>
    <?php $prev = $i; endforeach; ?>

  <?php if ($pg['has_next']): ?>
    <a href="<?= attr(query_with(['page' => $cur + 1])) ?>" rel="next" aria-label="Next page">›</a>
  <?php else: ?><span class="disabled">›</span><?php endif; ?>
</nav>
