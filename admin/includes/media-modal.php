<?php
/** Media picker modal markup. Include once near the end of pages that pick media. */
if (!defined('BASE_PATH')) { exit; }
?>
<div id="mediaPicker" hidden style="position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;padding:1rem;">
  <div style="background:var(--panel);border:1px solid var(--border);border-radius:14px;width:100%;max-width:760px;max-height:82vh;display:flex;flex-direction:column;overflow:hidden;">
    <div class="panel-head" style="padding:1rem 1.2rem;border-bottom:1px solid var(--border-soft);margin:0;">
      <h2 style="margin:0;">Select media</h2>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeMediaPicker()">Close</button>
    </div>
    <div style="padding:1.2rem;overflow-y:auto;">
      <div id="mediaPickerGrid" class="media-grid"><p class="muted">Loading…</p></div>
    </div>
    <div style="padding:.9rem 1.2rem;border-top:1px solid var(--border-soft);">
      <a href="<?= attr(admin_url('media.php')) ?>" target="_blank" rel="noopener" class="help">Need to upload something? Open the Media library ↗</a>
    </div>
  </div>
</div>
