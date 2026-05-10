/**
 * Codentra admin — blog post create/edit page interactions.
 * Loaded only on /admin/posts/* routes (gated in views/layouts/admin.php).
 *
 * - Slug auto-generation from title (until user touches slug)
 * - EasyMDE markdown editor on #post-content
 * - Featured image: drop zone + click-to-browse + client-side preview
 *   + size/type pre-validation
 * - Excerpt + SEO meta-description char counters
 * - SEO panel collapse/expand
 * - Form dirty tracking + beforeunload guard (via AdminUI)
 * - Delete confirm modal
 * - Index search/filter delegation (mirrors admin-leads.js)
 */
(function () {
  'use strict';

  var UI = window.AdminUI || {
    toast:   function (m) { console.log('[toast]', m); },
    confirm: function (o) { return Promise.resolve(window.confirm((o && o.body) || 'Are you sure?')); },
    markFormDirty:    function () {},
    releaseFormDirty: function () {},
  };

  // ── Row-click navigation (posts index — mirrors leads behaviour) ──────
  var INTERACTIVE_SEL = 'input, button, a, label, select, textarea,'
                      + ' [data-no-row-click], .actions-cell';

  function rowFromTarget(target) {
    var row = target.closest('tr[data-href]');
    if (!row) return null;
    if (target.closest(INTERACTIVE_SEL)) return null;
    return row;
  }

  document.addEventListener('click', function (e) {
    var row = rowFromTarget(e.target);
    if (!row) return;
    var href = row.getAttribute('data-href');
    if (!href) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) {
      window.open(href, '_blank', 'noopener');
      return;
    }
    if (e.button !== 0) return;
    window.location.href = href;
  });
  document.addEventListener('auxclick', function (e) {
    if (e.button !== 1) return;
    var row = rowFromTarget(e.target);
    if (!row) return;
    e.preventDefault();
    window.open(row.getAttribute('data-href'), '_blank', 'noopener');
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    var row = rowFromTarget(e.target);
    if (row) window.location.href = row.getAttribute('data-href');
  });

  // ── Index page: search debounce + auto-submit on filter changes ────────
  var searchInput = document.querySelector('[data-leads-search]');
  var filterForm  = document.querySelector('[data-leads-filter]');
  if (searchInput && filterForm) {
    var st = null;
    searchInput.addEventListener('input', function () {
      clearTimeout(st);
      st = setTimeout(function () { filterForm.submit(); }, 300);
    });
  }
  document.querySelectorAll('[data-leads-filter] select, [data-leads-filter] input[type="date"]').forEach(function (el) {
    el.addEventListener('change', function () { filterForm && filterForm.submit(); });
  });

  // ── Edit page ──────────────────────────────────────────────────────────
  var form = document.querySelector('[data-post-edit-form]');
  if (!form) return;

  UI.markFormDirty(form);

  // Slug auto-fill from title — but only until the user touches it.
  var titleEl   = form.querySelector('[data-post-title]');
  var slugEl    = form.querySelector('[data-post-slug]');
  var slugDisp  = form.querySelector('[data-slug-display]');
  if (titleEl && slugEl) {
    var slugDirty = slugEl.value.trim() !== '';

    slugEl.addEventListener('input', function () {
      slugDirty = true;
      if (slugDisp) slugDisp.textContent = slugEl.value;
    });

    titleEl.addEventListener('input', function () {
      if (slugDirty) return;
      var s = slugify(titleEl.value);
      slugEl.value = s;
      if (slugDisp) slugDisp.textContent = s;
    });
  }

  function slugify(s) {
    return String(s).toLowerCase().trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .slice(0, 180);
  }

  // ── Char counters (excerpt + SEO) ──────────────────────────────────────
  document.querySelectorAll('[data-counter-source]').forEach(function (el) {
    var key   = el.dataset.counterSource;
    var label = document.querySelector('[data-counter-for="' + key + '"]');
    if (!label) return;
    var max = el.getAttribute('maxlength') || '?';
    function update() { label.textContent = (el.value.length) + '/' + max; }
    update();
    el.addEventListener('input', update);
  });

  // ── SEO panel toggle ───────────────────────────────────────────────────
  var seoToggle = form.querySelector('[data-seo-toggle]');
  var seoBody   = form.querySelector('[data-seo-body]');
  if (seoToggle && seoBody) {
    seoToggle.addEventListener('click', function () {
      var open = seoBody.hidden;
      seoBody.hidden = !open;
      seoToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // ── Featured image: drop-zone + click-to-browse + preview ──────────────
  var zone     = form.querySelector('[data-image-zone]');
  var input    = form.querySelector('[data-image-input]');
  var browse   = form.querySelector('[data-image-browse]');
  var preview  = form.querySelector('[data-image-preview]');

  var MAX_BYTES = 5 * 1024 * 1024;
  var ALLOW_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

  if (zone && input) {
    if (browse)  browse.addEventListener('click', function () { input.click(); });
    if (preview) preview.addEventListener('click', function (e) {
      if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') input.click();
    });

    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      if (f) handleFile(f);
    });

    ['dragenter', 'dragover'].forEach(function (ev) {
      zone.addEventListener(ev, function (e) {
        e.preventDefault(); e.stopPropagation();
        zone.classList.add('is-drag-over');
      });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      zone.addEventListener(ev, function (e) {
        e.preventDefault(); e.stopPropagation();
        zone.classList.remove('is-drag-over');
      });
    });
    zone.addEventListener('drop', function (e) {
      var dt = e.dataTransfer;
      if (!dt || !dt.files || !dt.files.length) return;
      // Assign to the file input so it submits with the form
      try { input.files = dt.files; } catch (err) { /* some browsers refuse */ }
      handleFile(dt.files[0]);
    });
  }

  function handleFile(file) {
    if (!file) return;

    if (ALLOW_MIME.indexOf(file.type) === -1) {
      UI.toast('Unsupported image type. Use JPG, PNG, WebP, or GIF.', 'error');
      input.value = '';
      return;
    }
    if (file.size > MAX_BYTES) {
      UI.toast('That image is over 5 MB. Pick a smaller one.', 'error');
      input.value = '';
      return;
    }

    if (!preview) return;
    var url = URL.createObjectURL(file);
    var existingImg = preview.querySelector('img[data-image-preview-img]');
    if (existingImg) {
      existingImg.src = url;
    } else {
      preview.innerHTML = '';
      var img = document.createElement('img');
      img.setAttribute('data-image-preview-img', '');
      img.alt = '';
      img.src = url;
      preview.appendChild(img);
    }
    preview.classList.add('has-image');
    var emptyEl = preview.querySelector('[data-image-empty]');
    if (emptyEl) emptyEl.remove();
  }

  // ── Delete via confirm modal ───────────────────────────────────────────
  var del = form.querySelector('[data-post-delete]');
  if (del) {
    del.addEventListener('click', function () {
      UI.confirm({
        title: 'Delete this post?',
        body:  'It will be archived (soft-deleted). You can restore via SQL if needed.',
        confirmLabel: 'Delete',
        danger: true,
      }).then(function (ok) {
        if (!ok) return;
        var f = document.getElementById('post-delete-form');
        if (f) {
          UI.releaseFormDirty(form); // suppress the unsaved-changes warning
          f.submit();
        }
      });
    });
  }

  // ── EasyMDE init ───────────────────────────────────────────────────────
  // EasyMDE is loaded with `defer` from CDN, so the constructor may not
  // exist at script-eval time. Poll briefly, then init.
  var editorEl = document.getElementById('post-content');
  if (!editorEl) return;

  var deadline = Date.now() + 4000; // 4s budget
  (function waitForEasyMDE() {
    if (typeof window.EasyMDE !== 'undefined') {
      initEditor(editorEl);
      return;
    }
    if (Date.now() > deadline) {
      console.warn('EasyMDE failed to load — falling back to plain textarea.');
      return;
    }
    setTimeout(waitForEasyMDE, 50);
  })();

  function initEditor(textarea) {
    /* global EasyMDE, marked */
    var mde = new EasyMDE({
      element: textarea,
      autofocus: false,
      spellChecker: false,
      status: ['lines', 'words'],
      // We load FontAwesome ourselves from cdn.jsdelivr.net (already in
      // CSP allow-list) with SRI integrity. EasyMDE's default loader pulls
      // an UN-PINNED build from maxcdn.bootstrapcdn.com — supply-chain
      // risk + extra origin on the CSP allow-list. See views/layouts/
      // admin.php for the pinned <link>.
      autoDownloadFontAwesome: false,
      previewClass: ['editor-preview', 'admin-mde-preview'],
      previewRender: function (plaintext) {
        if (typeof window.marked !== 'undefined') {
          window.marked.setOptions({ breaks: true, gfm: true });
          return window.marked.parse(plaintext);
        }
        return plaintext;
      },
      toolbar: [
        'bold', 'italic', 'heading', '|',
        'quote', 'unordered-list', 'ordered-list', '|',
        'link', 'image', 'code', '|',
        'preview', 'side-by-side', 'fullscreen',
      ],
    });

    // EasyMDE syncs back to the underlying textarea on submit, but only
    // on certain events. Force a sync on form submit so $_POST['content']
    // matches what's in the editor.
    form.addEventListener('submit', function () {
      mde.codemirror.save();
    });

    // Wire the editor into the dirty-tracker — CodeMirror events don't
    // bubble up as normal input events.
    mde.codemirror.on('change', function () {
      mde.codemirror.save();
      form.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }
})();
