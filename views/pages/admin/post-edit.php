<?php
/**
 * @var string $mode  'create' | 'edit'
 * @var array  $post
 * @var array  $categories
 * @var array  $errors
 * @var array  $old
 */
$isEdit  = $mode === 'edit';
$old     = $old    ?? [];
$errors  = $errors ?? [];

// Field-by-field merge: prefer flashed-back input on validation error.
$v = function (string $field, $default = '') use ($old, $post) {
    if (array_key_exists($field, $old))  return $old[$field];
    if (array_key_exists($field, $post)) return $post[$field];
    return $default;
};

$action = $isEdit ? '/admin/posts/' . (int) $post['id'] : '/admin/posts';
$err    = fn(string $f): string => isset($errors[$f]) ? '<span class="form-field__error">' . htmlspecialchars($errors[$f]) . '</span>' : '';
?>

<header class="post-edit__header">
  <div>
    <a class="lead-detail__back" href="/admin/posts">&larr; Back to posts</a>
    <h2 class="lead-detail__title">
      <?= $isEdit ? 'Edit post' : 'New post' ?>
      <?php if ($isEdit && !empty($post['status'])): ?>
        <span class="badge badge--<?= htmlspecialchars(strtolower((string) $post['status'])) ?>"><?= htmlspecialchars((string) $post['status']) ?></span>
      <?php endif; ?>
    </h2>
  </div>
  <?php if ($isEdit && !empty($post['status']) && $post['status'] === 'published' && !empty($post['slug'])): ?>
    <a class="btn btn--ghost btn--small" href="/blog/<?= htmlspecialchars((string) $post['slug']) ?>" target="_blank" rel="noopener">View on site &nearr;</a>
  <?php endif; ?>
</header>


<form
  class="post-edit"
  action="<?= htmlspecialchars($action) ?>"
  method="POST"
  enctype="multipart/form-data"
  data-post-edit-form
  novalidate>

  <?= \Core\Csrf::field() ?>

  <div class="post-edit__grid">

    <!-- ── Left column ─────────────────────────────────────────────────── -->
    <div class="post-edit__main">

      <article class="lead-card glass">
        <label class="form-field">
          <span class="form-field__label">Title</span>
          <input
            type="text" name="title"
            class="post-edit__title-input"
            value="<?= htmlspecialchars((string) $v('title')) ?>"
            required minlength="3" maxlength="200"
            <?= $isEdit ? '' : 'autofocus' ?>
            data-post-title>
          <?= $err('title') ?>
        </label>

        <label class="form-field">
          <span class="form-field__label">Slug</span>
          <input
            type="text" name="slug"
            value="<?= htmlspecialchars((string) $v('slug')) ?>"
            maxlength="180"
            placeholder="auto-generated from title"
            data-post-slug>
          <p class="post-edit__url-preview" data-slug-preview>
            <code>https://codentra.pk/blog/<span data-slug-display><?= htmlspecialchars((string) $v('slug')) ?></span></code>
          </p>
          <?= $err('slug') ?>
        </label>

        <label class="form-field">
          <span class="form-field__label">Excerpt <span class="form-field__counter" data-counter-for="excerpt">0/300</span></span>
          <textarea
            name="excerpt" rows="3" maxlength="300"
            data-counter-source="excerpt"
            placeholder="Short summary used on the blog index and in OG tags."><?= htmlspecialchars((string) $v('excerpt')) ?></textarea>
          <?= $err('excerpt') ?>
        </label>
      </article>

      <article class="lead-card glass">
        <header class="lead-card__head">
          <h3 class="lead-card__title">Content</h3>
          <span class="lead-card__muted">Markdown</span>
        </header>
        <textarea
          id="post-content"
          name="content" rows="18"
          required minlength="10" maxlength="50000"
          data-post-content><?= htmlspecialchars((string) $v('content')) ?></textarea>
        <?= $err('content') ?>
      </article>
    </div>


    <!-- ── Right column (sticky) ───────────────────────────────────────── -->
    <aside class="post-edit__side">

      <article class="lead-card glass">
        <h3 class="lead-card__title">Status</h3>
        <label class="post-edit__radio">
          <input type="radio" name="status" value="draft"
                 <?= ((string) $v('status', 'draft')) === 'draft' ? 'checked' : '' ?>>
          <span>Draft <small>— not visible publicly</small></span>
        </label>
        <label class="post-edit__radio">
          <input type="radio" name="status" value="published"
                 <?= ((string) $v('status', 'draft')) === 'published' ? 'checked' : '' ?>>
          <span>Published <small>— live on /blog</small></span>
        </label>
        <?= $err('status') ?>
      </article>

      <article class="lead-card glass">
        <h3 class="lead-card__title">Category</h3>
        <select name="category_id" class="lead-status-select">
          <option value="">— uncategorised —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $v('category_id', 0) === (int) $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?= $err('category_id') ?>
      </article>

      <article class="lead-card glass post-edit__image-card" data-image-zone>
        <h3 class="lead-card__title">Featured image</h3>

        <?php $existingPath = (string) ($post['featured_image'] ?? ''); ?>
        <div class="post-edit__image-preview <?= $existingPath ? 'has-image' : '' ?>" data-image-preview>
          <?php if ($existingPath): ?>
            <img src="<?= htmlspecialchars($existingPath) ?>" alt="" data-image-preview-img>
          <?php else: ?>
            <p class="post-edit__image-empty" data-image-empty>
              <strong>Drop an image here</strong>
              <span>or click to browse</span>
            </p>
          <?php endif; ?>
        </div>

        <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif" data-image-input hidden>

        <div class="post-edit__image-actions">
          <button type="button" class="btn btn--ghost btn--small" data-image-browse>
            <?= $existingPath ? 'Replace…' : 'Choose file…' ?>
          </button>
          <?php if ($isEdit && $existingPath): ?>
            <label class="post-edit__image-remove">
              <input type="checkbox" name="featured_image_remove" value="1">
              Remove image
            </label>
          <?php endif; ?>
        </div>

        <p class="post-edit__image-hint">JPG, PNG, WebP, or GIF · up to 5 MB.</p>

        <label class="form-field" style="margin-top: .75rem;">
          <span class="form-field__label">Alt text</span>
          <input type="text" name="image_alt" maxlength="255" value="<?= htmlspecialchars((string) $v('image_alt')) ?>">
        </label>
        <?= $err('featured_image') ?>
      </article>

      <article class="lead-card glass post-edit__seo">
        <button type="button" class="post-edit__seo-toggle" data-seo-toggle aria-expanded="false">
          <span>SEO overrides</span>
          <span aria-hidden="true">⌄</span>
        </button>
        <div class="post-edit__seo-body" data-seo-body hidden>
          <label class="form-field">
            <span class="form-field__label">Title override <span class="form-field__counter" data-counter-for="seo_title">0/65</span></span>
            <input type="text" name="seo_title" maxlength="65" data-counter-source="seo_title" value="<?= htmlspecialchars((string) $v('seo_title')) ?>" placeholder="Defaults to post title">
          </label>
          <label class="form-field">
            <span class="form-field__label">Meta description <span class="form-field__counter" data-counter-for="seo_description">0/160</span></span>
            <textarea name="seo_description" rows="3" maxlength="160" data-counter-source="seo_description" placeholder="Defaults to excerpt"><?= htmlspecialchars((string) $v('seo_description')) ?></textarea>
          </label>
          <p class="lead-card__muted">SEO overrides are stored but not yet rendered on /blog/{slug} — landing in a future phase.</p>
        </div>
      </article>

      <div class="post-edit__sticky-save">
        <button type="submit" class="btn btn--primary post-edit__save-btn">
          <?= $isEdit ? 'Save changes' : 'Create post' ?>
        </button>
        <?php if ($isEdit): ?>
          <button type="button" class="post-edit__delete" data-post-delete data-post-id="<?= (int) $post['id'] ?>">
            Delete post
          </button>
        <?php endif; ?>
      </div>

    </aside>
  </div>
</form>

<?php if ($isEdit): ?>
  <form id="post-delete-form" action="/admin/posts/<?= (int) $post['id'] ?>/delete" method="POST" hidden>
    <?= \Core\Csrf::field() ?>
  </form>
<?php endif; ?>


<!-- ── EasyMDE + marked (CDN, pinned exact versions) ─────────────────────── -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js"        defer></script>
