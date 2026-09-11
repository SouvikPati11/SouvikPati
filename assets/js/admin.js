/* Admin panel interactions (vanilla JS) */
(function () {
  'use strict';

  /* ---- Sidebar toggle (mobile) ---- */
  var sidebar = document.getElementById('adminSidebar');
  var menuBtn = document.getElementById('adminMenuBtn');
  if (sidebar && menuBtn) {
    var overlay = document.createElement('div');
    overlay.className = 'admin-overlay';
    document.body.appendChild(overlay);
    var open = function () { sidebar.classList.add('open'); overlay.classList.add('show'); };
    var close = function () { sidebar.classList.remove('open'); overlay.classList.remove('show'); };
    menuBtn.addEventListener('click', function () {
      sidebar.classList.contains('open') ? close() : open();
    });
    overlay.addEventListener('click', close);
  }

  /* ---- Confirm destructive actions ---- */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (e) {
      if (!window.confirm(el.getAttribute('data-confirm'))) { e.preventDefault(); }
    });
    if (el.tagName === 'A') {
      el.addEventListener('click', function (e) {
        if (!window.confirm(el.getAttribute('data-confirm'))) { e.preventDefault(); }
      });
    }
  });

  /* ---- Auto slug from title ---- */
  function slugify(s) {
    return s.toString().toLowerCase().trim()
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
  document.querySelectorAll('[data-slug-source]').forEach(function (src) {
    var target = document.querySelector(src.getAttribute('data-slug-source'));
    if (!target) return;
    var touched = target.value.trim() !== '';
    target.addEventListener('input', function () { touched = true; });
    src.addEventListener('input', function () {
      if (!touched) { target.value = slugify(src.value); }
    });
  });

  /* ---- Tag input ---- */
  document.querySelectorAll('.tag-input').forEach(function (wrap) {
    var hidden = wrap.querySelector('input[type="hidden"]');
    var text = wrap.querySelector('input[type="text"]');
    var list = wrap.querySelector('.tag-input-list');
    var tags = (hidden.value || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);
    function render() {
      list.innerHTML = '';
      tags.forEach(function (t, i) {
        var span = document.createElement('span');
        span.className = 't';
        span.textContent = t;
        var b = document.createElement('button');
        b.type = 'button'; b.textContent = '×';
        b.addEventListener('click', function () { tags.splice(i, 1); sync(); });
        span.appendChild(b);
        list.appendChild(span);
      });
    }
    function sync() { hidden.value = tags.join(', '); render(); }
    function add() {
      var v = text.value.trim().replace(/,$/, '');
      if (v && tags.indexOf(v) === -1) { tags.push(v); }
      text.value = ''; sync();
    }
    text.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(); }
    });
    text.addEventListener('blur', add);
    render();
  });

  /* ---- Repeatable feature rows ---- */
  var featWrap = document.querySelector('.feature-rows');
  if (featWrap) {
    var addBtn = document.getElementById('addFeature');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'feature-row';
        row.innerHTML = '<input class="input" type="text" name="features[]" placeholder="Feature or deliverable">'
          + '<button type="button" class="btn btn-ghost btn-sm remove-feature">Remove</button>';
        featWrap.insertBefore(row, addBtn);
      });
    }
    featWrap.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-feature')) {
        e.target.closest('.feature-row').remove();
      }
    });
  }

  /* ---- Rich text editor ---- */
  document.querySelectorAll('.editor-wrap').forEach(function (wrap) {
    var area = wrap.querySelector('.editor-area');
    var textarea = document.querySelector(wrap.getAttribute('data-target'));
    if (!area || !textarea) return;

    function exec(cmd, val) { document.execCommand(cmd, false, val || null); area.focus(); sync(); }
    function sync() { textarea.value = area.innerHTML; }

    wrap.querySelectorAll('.editor-toolbar button').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var cmd = btn.getAttribute('data-cmd');
        var val = btn.getAttribute('data-val') || null;
        if (cmd === 'createLink') {
          var url = window.prompt('Link URL:', 'https://');
          if (url) exec('createLink', url);
        } else if (cmd === 'insertImage') {
          openMediaPicker(function (src) { exec('insertImage', src); });
        } else if (cmd === 'insertVideo') {
          var yt = window.prompt('YouTube video URL:');
          if (yt) {
            var id = (yt.match(/(?:v=|youtu\.be\/|embed\/)([\w-]{11})/) || [])[1];
            if (id) {
              exec('insertHTML', '<iframe src="https://www.youtube.com/embed/' + id + '" allowfullscreen loading="lazy"></iframe><p><br></p>');
            } else { alert('Could not read that YouTube URL.'); }
          }
        } else if (cmd === 'formatBlock') {
          exec('formatBlock', val);
        } else if (cmd === 'insertTable') {
          exec('insertHTML', '<table><thead><tr><th>Heading</th><th>Heading</th></tr></thead><tbody><tr><td>Cell</td><td>Cell</td></tr><tr><td>Cell</td><td>Cell</td></tr></tbody></table><p><br></p>');
        } else {
          exec(cmd, val);
        }
      });
    });
    area.addEventListener('input', sync);
    area.addEventListener('blur', sync);
    // Paste as plain text to avoid messy markup.
    area.addEventListener('paste', function (e) {
      e.preventDefault();
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, text);
    });
    // Ensure content is synced before submit.
    var form = area.closest('form');
    if (form) form.addEventListener('submit', sync);
  });

  /* ---- Media picker modal ---- */
  var pickerCallback = null;
  function openMediaPicker(cb) {
    pickerCallback = cb;
    var modal = document.getElementById('mediaPicker');
    if (!modal) { window.location.href = '/admin/media.php'; return; }
    modal.hidden = false;
    loadMedia();
  }
  window.openMediaPicker = openMediaPicker;

  function loadMedia() {
    var grid = document.getElementById('mediaPickerGrid');
    if (!grid) return;
    grid.innerHTML = '<p class="muted">Loading…</p>';
    fetch('/admin/media.php?ajax=list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.items || !d.items.length) { grid.innerHTML = '<p class="muted">No media yet. Upload from the Media page.</p>'; return; }
        grid.innerHTML = '';
        d.items.forEach(function (m) {
          var div = document.createElement('div');
          div.className = 'media-item';
          div.style.cursor = 'pointer';
          div.innerHTML = '<div class="thumb"><img src="' + m.url + '" alt="' + (m.alt || '') + '" loading="lazy"></div>';
          div.addEventListener('click', function () {
            if (pickerCallback) pickerCallback(m.url, m);
            closeMediaPicker();
          });
          grid.appendChild(div);
        });
      })
      .catch(function () { grid.innerHTML = '<p class="muted">Could not load media.</p>'; });
  }
  function closeMediaPicker() {
    var modal = document.getElementById('mediaPicker');
    if (modal) modal.hidden = true;
    pickerCallback = null;
  }
  window.closeMediaPicker = closeMediaPicker;

  document.querySelectorAll('[data-media-target]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var target = document.querySelector(btn.getAttribute('data-media-target'));
      var preview = btn.getAttribute('data-media-preview') ? document.querySelector(btn.getAttribute('data-media-preview')) : null;
      openMediaPicker(function (src) {
        if (target) target.value = src;
        if (preview) { preview.src = src; preview.hidden = false; }
      });
    });
  });
})();
