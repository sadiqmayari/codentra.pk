<?php
/**
 * @var array<string, array> $catalog
 * @var array<string, array> $sections
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 */
$errors = $errors ?? [];

// Group catalog by section so we render in the order CATALOG declares.
$bySection = [];
foreach ($catalog as $key => $meta) {
    $bySection[$meta['section']][] = $key;
}

$err = fn(string $f): string =>
    isset($errors[$f]) ? '<span class="form-field__error">' . htmlspecialchars($errors[$f]) . '</span>' : '';

$renderField = function (string $key, array $meta, $value) use ($err): string {
    $label = htmlspecialchars((string) ($meta['label'] ?? $key));
    $help  = !empty($meta['help']) ? '<p class="settings__help">' . htmlspecialchars($meta['help']) . '</p>' : '';
    $req   = !empty($meta['required']) ? '<abbr title="required">*</abbr>' : '';
    $max   = isset($meta['max']) ? ' maxlength="' . (int) $meta['max'] . '"' : '';
    $val   = htmlspecialchars((string) $value);
    $name  = htmlspecialchars($key);
    $invalid = isset($errors[$key]) ? ' aria-invalid="true"' : '';

    $type     = $meta['type'];
    $counter  = isset($meta['max'])
        ? ' <span class="form-field__counter" data-counter-for="' . $name . '">' . strlen((string) $value) . '/' . (int) $meta['max'] . '</span>'
        : '';

    if ($type === 'textarea') {
        $field = "<textarea name=\"{$name}\" rows=\"3\"{$max} data-counter-source=\"{$name}\"{$invalid}>{$val}</textarea>";
    } else {
        $htmlType = match ($type) {
            'email'   => 'email',
            'url'     => 'url',
            'phone'   => 'tel',
            'integer' => 'text',  // pattern-validated server-side
            default   => 'text',
        };
        $extra = match ($type) {
            'integer' => ' inputmode="numeric" pattern="[0-9]+"',
            default   => '',
        };
        $field = "<input type=\"{$htmlType}\" name=\"{$name}\" value=\"{$val}\"{$max}{$extra} data-counter-source=\"{$name}\"{$invalid}>";
    }

    return "<label class=\"form-field settings__field\">"
         . "<span class=\"form-field__label\">{$label} {$req}{$counter}</span>"
         . $field
         . $help
         . $err($key)
         . "</label>";
};
?>

<header class="settings-header">
  <h2 class="dash-header__title">Site Settings</h2>
  <p class="dash-header__welcome">Identity, contact, social, SEO, and business metadata. Changes are reflected on the public site immediately.</p>
</header>


<nav class="settings-tabs" aria-label="Settings sections">
  <?php foreach ($sections as $sectionKey => $sectionMeta): ?>
    <a href="#section-<?= htmlspecialchars($sectionKey) ?>" data-settings-tab="<?= htmlspecialchars($sectionKey) ?>"><?= htmlspecialchars($sectionMeta['label']) ?></a>
  <?php endforeach; ?>
</nav>


<form
  class="settings-form"
  action="/admin/settings"
  method="POST"
  data-settings-form
  novalidate>
  <?= \Core\Csrf::field() ?>

  <?php foreach ($sections as $sectionKey => $sectionMeta): ?>
    <section class="settings-section glass" id="section-<?= htmlspecialchars($sectionKey) ?>" data-settings-section="<?= htmlspecialchars($sectionKey) ?>">
      <header class="settings-section__head">
        <h3 class="lead-card__title"><?= htmlspecialchars($sectionMeta['label']) ?></h3>
        <p class="settings-section__desc"><?= htmlspecialchars($sectionMeta['desc']) ?></p>
      </header>

      <div class="settings-section__body">
        <?php foreach (($bySection[$sectionKey] ?? []) as $key): ?>
          <?= $renderField($key, $catalog[$key], $values[$key] ?? '') ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <div class="settings-save-bar">
    <button type="submit" class="btn btn--primary settings-save-btn">Save settings</button>
    <p class="settings-save-bar__hint">Saving flushes the public-page cache and the cached sitemap so visitors see your changes on the next request.</p>
  </div>
</form>


<script>
// Wait for DOMContentLoaded so the deferred admin-shared.js has run
// and exposed window.AdminUI before this code looks for it.
document.addEventListener('DOMContentLoaded', function () {
  // Tab nav: scroll-spy + smooth scroll to sections.
  var tabs     = Array.prototype.slice.call(document.querySelectorAll('[data-settings-tab]'));
  var sections = Array.prototype.slice.call(document.querySelectorAll('[data-settings-section]'));
  var byId = {};
  sections.forEach(function (s) { byId[s.dataset.settingsSection] = s; });

  function setActive(key) {
    tabs.forEach(function (t) {
      t.classList.toggle('is-active', t.dataset.settingsTab === key);
    });
  }

  // Smooth scroll on click + immediately mark active.
  tabs.forEach(function (t) {
    t.addEventListener('click', function (e) {
      e.preventDefault();
      var key = t.dataset.settingsTab;
      var el  = byId[key];
      if (!el) return;
      setActive(key);
      window.scrollTo({
        top: el.getBoundingClientRect().top + window.scrollY - 80,
        behavior: 'smooth',
      });
    });
  });

  // Scroll-spy via IntersectionObserver.
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) setActive(entry.target.dataset.settingsSection);
      });
    }, { rootMargin: '-20% 0px -60% 0px', threshold: 0 });
    sections.forEach(function (s) { io.observe(s); });
  }

  // Char counters
  document.querySelectorAll('[data-counter-source]').forEach(function (el) {
    var key = el.dataset.counterSource;
    var label = document.querySelector('[data-counter-for="' + key + '"]');
    if (!label) return;
    var max = el.getAttribute('maxlength') || '';
    function update() {
      label.textContent = el.value.length + (max ? ('/' + max) : '');
    }
    update();
    el.addEventListener('input', update);
  });

  // Form dirty tracking via AdminUI (admin-shared.js).
  if (window.AdminUI && window.AdminUI.markFormDirty) {
    var form = document.querySelector('[data-settings-form]');
    if (form) window.AdminUI.markFormDirty(form);
  }

  if (sections[0]) setActive(sections[0].dataset.settingsSection);
});
</script>
