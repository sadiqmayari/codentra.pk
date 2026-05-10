/**
 * Codentra admin — leads-management interactions.
 * Loaded only on /admin/leads/* routes (gated in views/layouts/admin.php).
 *
 * - Debounced search input → form submit
 * - Row clicks navigate (preserve cmd/ctrl-click, ignore actions cell)
 * - Lead detail: status dropdown AJAX
 * - Lead detail: notes auto-save (blur, Ctrl+Enter, 800ms debounce)
 * - Archive confirm modal
 * - Toast utility
 */
(function () {
  'use strict';

  // Shared UI primitives live on window.AdminUI (admin-shared.js).
  // Defensive shim in case admin-shared failed to load (degrades to
  // alert/confirm so the page still works).
  var UI = window.AdminUI || {
    toast:   function (m) { console.log('[toast]', m); },
    confirm: function (o) { return Promise.resolve(window.confirm((o && o.body) || 'Are you sure?')); },
    markFormDirty:    function () {},
    releaseFormDirty: function () {},
  };
  var toast        = UI.toast;
  var confirmModal = UI.confirm;

  // ── Row-click navigation (delegated from tbody) ────────────────────────
  // Lives at document level so it survives any future DOM swaps and
  // covers both index page tbody + any other table that sets data-href.
  // Selectors that should NOT trigger navigation when clicked.
  var INTERACTIVE_SEL = 'input, button, a, label, select, textarea,'
                      + ' [data-no-row-click], .actions-cell, .lead-copy';

  document.addEventListener('click', function (e) {
    var row = e.target.closest('tr[data-href]');
    if (!row) return;

    // Don't hijack clicks inside interactive elements within the row
    // (checkboxes, kebab menus, links to details, copy buttons, etc.).
    if (e.target.closest(INTERACTIVE_SEL)) return;

    var href = row.getAttribute('data-href');
    if (!href) return;

    // Cmd/Ctrl/Shift/middle-click → new tab. Let the browser do its
    // thing; don't preventDefault. We only force-open here because the
    // <tr> isn't an <a>, so the browser won't honour the modifier on
    // its own.
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) {
      window.open(href, '_blank', 'noopener');
      return;
    }

    if (e.button !== 0) return; // ignore right-click etc.
    window.location.href = href;
  });

  // Middle-click fires 'auxclick', not 'click', in modern browsers.
  document.addEventListener('auxclick', function (e) {
    if (e.button !== 1) return;
    var row = e.target.closest('tr[data-href]');
    if (!row) return;
    if (e.target.closest(INTERACTIVE_SEL)) return;
    var href = row.getAttribute('data-href');
    if (!href) return;
    e.preventDefault();
    window.open(href, '_blank', 'noopener');
  });

  // Keyboard: row is tabindex="0", Enter activates it as a link.
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    var row = e.target.closest('tr[data-href]');
    if (!row) return;
    if (e.target.closest(INTERACTIVE_SEL)) return;
    var href = row.getAttribute('data-href');
    if (href) window.location.href = href;
  });

  // ── Index page: debounced search ───────────────────────────────────────
  var searchInput = document.querySelector('[data-leads-search]');
  var filterForm  = document.querySelector('[data-leads-filter]');
  if (searchInput && filterForm) {
    var t = null;
    searchInput.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(function () { filterForm.submit(); }, 300);
    });
    // Submit on Enter immediately
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { clearTimeout(t); /* form submits naturally */ }
    });
  }
  // Also auto-submit on dropdown / date changes
  document.querySelectorAll('[data-leads-filter] select, [data-leads-filter] input[type="date"]').forEach(function (el) {
    el.addEventListener('change', function () { filterForm && filterForm.submit(); });
  });

  // ── Lead detail page ───────────────────────────────────────────────────
  var detail   = document.querySelector('.lead-detail__grid');
  if (detail) {
    var leadId = detail.dataset.leadId;
    var csrf   = detail.dataset.csrf;

    // Status dropdown → AJAX
    var statusSel = detail.querySelector('[data-status-select]');
    if (statusSel) {
      var prevValue = statusSel.value;
      statusSel.addEventListener('change', function () {
        var newStatus = statusSel.value;
        statusSel.disabled = true;

        var body = new URLSearchParams();
        body.set('status', newStatus);
        body.set('_csrf',  csrf);

        fetch('/admin/leads/' + leadId + '/status', {
          method: 'POST',
          headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
          body: body,
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, data: j }; }); })
        .then(function (res) {
          if (!res.ok || !res.data.ok) throw new Error(res.data.error || 'failed');
          // Update header badge
          var badge = document.querySelector('[data-badge-for="status"]');
          if (badge && res.data.badge_html) {
            var tmp = document.createElement('div');
            tmp.innerHTML = res.data.badge_html;
            badge.parentNode.replaceChild(tmp.firstChild, badge);
          }
          prevValue = newStatus;
          toast('Status updated to ' + newStatus, 'success');
        })
        .catch(function (err) {
          statusSel.value = prevValue;
          toast('Could not update status. ' + (err && err.message || ''), 'error');
        })
        .finally(function () { statusSel.disabled = false; });
      });
    }

    // Notes auto-save
    var notesEl = detail.querySelector('[data-notes]');
    var ind     = detail.querySelector('[data-notes-indicator]');
    if (notesEl) {
      var lastSaved = notesEl.value;
      var debounceT = null;
      var inflight  = false;

      function setIndicator(state, savedAt) {
        if (!ind) return;
        if (state === 'saving')  { ind.textContent = 'Saving…'; ind.className = 'lead-notes__indicator is-saving'; }
        if (state === 'saved')   {
          var when = savedAt ? ' ' + relativeShort(savedAt) : '';
          ind.textContent = 'Saved' + when;
          ind.className = 'lead-notes__indicator is-saved';
        }
        if (state === 'error')   { ind.textContent = 'Save failed — retry'; ind.className = 'lead-notes__indicator is-error'; }
        if (state === 'idle')    { ind.textContent = ''; ind.className = 'lead-notes__indicator'; }
      }
      function relativeShort(iso) {
        var t = new Date(iso).getTime(), now = Date.now();
        var s = Math.max(0, Math.round((now - t) / 1000));
        if (s < 5)    return 'just now';
        if (s < 60)   return s + 's ago';
        if (s < 3600) return Math.floor(s / 60) + 'm ago';
        return Math.floor(s / 3600) + 'h ago';
      }
      function save() {
        if (inflight) return;
        if (notesEl.value === lastSaved) return;
        inflight = true;
        setIndicator('saving');

        var body = new URLSearchParams();
        body.set('notes', notesEl.value);
        body.set('_csrf', csrf);

        fetch('/admin/leads/' + leadId + '/notes', {
          method: 'POST',
          headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
          body: body,
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, data: j }; }); })
        .then(function (res) {
          if (!res.ok || !res.data.ok) throw new Error(res.data.error || 'failed');
          lastSaved = notesEl.value;
          setIndicator('saved', res.data.saved_at);
        })
        .catch(function () { setIndicator('error'); })
        .finally(function () { inflight = false; });
      }

      notesEl.addEventListener('blur', function () {
        clearTimeout(debounceT);
        save();
      });
      notesEl.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(debounceT);
          save();
        }
      });
      // Debounced background save while typing
      notesEl.addEventListener('input', function () {
        clearTimeout(debounceT);
        debounceT = setTimeout(save, 800);
      });
    }

    // Archive form: intercept submit, show confirm modal
    var archiveForm = document.querySelector('[data-archive-form]');
    if (archiveForm) {
      archiveForm.addEventListener('submit', function (e) {
        e.preventDefault();
        confirmModal({
          title: 'Archive this lead?',
          body:  'It will be hidden from /admin/leads. The data is kept in the DB.',
          confirmLabel: 'Archive',
        }).then(function (ok) {
          if (ok) archiveForm.submit();
        });
      });
    }

    // Copy buttons
    detail.querySelectorAll('.lead-copy').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var v = btn.dataset.copy || '';
        if (navigator.clipboard) {
          navigator.clipboard.writeText(v).then(
            function () { toast('Copied', 'success'); },
            function () { toast('Could not copy', 'error'); }
          );
        }
      });
    });
  }
})();
