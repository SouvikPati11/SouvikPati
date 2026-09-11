<?php
/**
 * Reusable rich-text editor.
 * Expects:
 *   $editor_name        — textarea name (e.g. 'content')
 *   $editor_value       — current HTML (already stored/sanitised)
 *   $editor_id          — unique id (default from name)
 *   $editor_placeholder — placeholder text
 */
if (!defined('BASE_PATH')) { exit; }
$editor_name = $editor_name ?? 'content';
$editor_id = $editor_id ?? ('ed_' . preg_replace('/[^a-z0-9]/i', '', $editor_name));
$editor_value = $editor_value ?? '';
$editor_placeholder = $editor_placeholder ?? 'Start writing…';
$ta_id = $editor_id . '_ta';
?>
<div class="editor-wrap" data-target="#<?= attr($ta_id) ?>">
  <div class="editor-toolbar">
    <button type="button" data-cmd="formatBlock" data-val="h2" title="Heading 2">H2</button>
    <button type="button" data-cmd="formatBlock" data-val="h3" title="Heading 3">H3</button>
    <button type="button" data-cmd="formatBlock" data-val="p" title="Paragraph">¶</button>
    <span class="sep"></span>
    <button type="button" data-cmd="bold" title="Bold"><strong>B</strong></button>
    <button type="button" data-cmd="italic" title="Italic"><em>I</em></button>
    <button type="button" data-cmd="underline" title="Underline" style="text-decoration:underline;">U</button>
    <span class="sep"></span>
    <button type="button" data-cmd="insertUnorderedList" title="Bullet list">•</button>
    <button type="button" data-cmd="insertOrderedList" title="Numbered list">1.</button>
    <button type="button" data-cmd="formatBlock" data-val="blockquote" title="Quote">❝</button>
    <button type="button" data-cmd="formatBlock" data-val="pre" title="Code block">&lt;/&gt;</button>
    <span class="sep"></span>
    <button type="button" data-cmd="createLink" title="Insert link">🔗</button>
    <button type="button" data-cmd="insertImage" title="Insert image">🖼</button>
    <button type="button" data-cmd="insertVideo" title="Embed YouTube">▶</button>
    <button type="button" data-cmd="insertTable" title="Insert table">▦</button>
    <span class="sep"></span>
    <button type="button" data-cmd="removeFormat" title="Clear formatting">⌫</button>
  </div>
  <div class="editor-area" contenteditable="true" data-placeholder="<?= attr($editor_placeholder) ?>"><?= $editor_value ?></div>
  <textarea id="<?= attr($ta_id) ?>" name="<?= attr($editor_name) ?>" hidden><?= e($editor_value) ?></textarea>
</div>
