/**
 * Codentra admin — shared UI primitives.
 * Loaded BEFORE every per-route admin module (admin-leads.js,
 * admin-posts.js, …) so they can rely on `window.AdminUI`.
 *
 * Exposes:
 *   AdminUI.toast(message, type?)
 *   AdminUI.confirm({ title, body, confirmLabel?, cancelLabel?, danger? })
 *   AdminUI.markFormDirty(formEl)
 *   AdminUI.releaseFormDirty(formEl)
 */
(function () {
  'use strict';

  if (window.AdminUI) return; // idempotent

  // ── Toast ──────────────────────────────────────────────────────────────
  function toast(msg, type) {
    var el = document.createElement('div');
    el.className = 'admin-toast admin-toast--' + (type || 'info');
    el.textContent = msg;
    document.body.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('is-visible'); });
    setTimeout(function () {
      el.classList.remove('is-visible');
      setTimeout(function () { el.remove(); }, 250);
    }, 2800);
  }

  // ── Confirm modal ──────────────────────────────────────────────────────
  function confirmModal(opts) {
    opts = opts || {};
    return new Promise(function (resolve) {
      var bg = document.createElement('div');
      bg.className = 'admin-modal-bg';
      bg.innerHTML = ''
        + '<div class="admin-modal glass" role="dialog" aria-modal="true">'
        +   '<h3>' + (opts.title || 'Are you sure?') + '</h3>'
        +   '<p>'  + (opts.body  || '') + '</p>'
        +   '<div class="admin-modal__actions">'
        +     '<button type="button" class="btn btn--ghost btn--small" data-cancel>'
        +       (opts.cancelLabel || 'Cancel') + '</button>'
        +     '<button type="button" class="btn '
        +       (opts.danger ? 'btn--cta' : 'btn--primary')
        +       ' btn--small" data-confirm>'
        +       (opts.confirmLabel || 'Confirm') + '</button>'
        +   '</div>'
        + '</div>';
      document.body.appendChild(bg);
      requestAnimationFrame(function () { bg.classList.add('is-visible'); });

      function close(value) {
        bg.classList.remove('is-visible');
        setTimeout(function () { bg.remove(); }, 200);
        document.removeEventListener('keydown', esc);
        resolve(value);
      }
      function esc(e) { if (e.key === 'Escape') close(false); }

      bg.querySelector('[data-cancel]').addEventListener('click', function () { close(false); });
      bg.querySelector('[data-confirm]').addEventListener('click', function () { close(true); });
      bg.addEventListener('click', function (e) { if (e.target === bg) close(false); });
      document.addEventListener('keydown', esc);
      bg.querySelector('[data-confirm]').focus();
    });
  }

  // ── Form dirty tracking + beforeunload guard ──────────────────────────
  // One global handler for any form decorated by markFormDirty(); we use a
  // WeakSet so released forms can be garbage collected.
  var dirtyForms = new WeakSet();
  var anyDirty = false;

  function recheckAnyDirty() {
    // We can't iterate a WeakSet, so let listening forms tell us via a
    // data attribute. Cheap and avoids leaks.
    anyDirty = !!document.querySelector('form[data-dirty="1"]');
  }

  window.addEventListener('beforeunload', function (e) {
    if (!anyDirty) return;
    // Modern browsers ignore the message but show their generic prompt.
    e.preventDefault();
    e.returnValue = '';
    return '';
  });

  function markFormDirty(form) {
    if (!form || dirtyForms.has(form)) return;
    dirtyForms.add(form);

    // Snapshot initial values so we don't fire dirty on a no-op input
    // event (e.g. focus → blur on a select).
    var snapshot = serialise(form);

    var onChange = function () {
      var now = serialise(form);
      if (now !== snapshot) {
        form.setAttribute('data-dirty', '1');
      } else {
        form.removeAttribute('data-dirty');
      }
      recheckAnyDirty();
    };
    form.addEventListener('input',  onChange);
    form.addEventListener('change', onChange);

    form.addEventListener('submit', function () {
      form.removeAttribute('data-dirty');
      recheckAnyDirty();
    });
  }

  function releaseFormDirty(form) {
    if (!form) return;
    form.removeAttribute('data-dirty');
    recheckAnyDirty();
  }

  function serialise(form) {
    var fd = new FormData(form);
    var pairs = [];
    fd.forEach(function (value, key) {
      // File inputs return File objects — serialise just name/size so a
      // re-selection of the same file isn't seen as dirty.
      if (value instanceof File) {
        pairs.push(key + '=' + value.name + ':' + value.size);
      } else {
        pairs.push(key + '=' + value);
      }
    });
    return pairs.sort().join('&');
  }

  window.AdminUI = {
    toast: toast,
    confirm: confirmModal,
    markFormDirty: markFormDirty,
    releaseFormDirty: releaseFormDirty,
  };
})();
