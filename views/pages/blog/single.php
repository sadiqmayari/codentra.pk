<?php /** @var array $post */
$publishedAt = $post['published_at'] ?? $post['created_at'];
?>

<article class="blog-post">

  <header class="page-hero blog-post__hero">
    <div class="container">
      <?php if (!empty($post['category_name'])): ?>
        <p class="section__eyebrow blog-post__cat">
          <a href="/blog"><?= htmlspecialchars($post['category_name']) ?></a>
        </p>
      <?php else: ?>
        <p class="section__eyebrow"><a href="/blog">Blog</a></p>
      <?php endif; ?>

      <h1 class="page-hero__title blog-post__title" data-reveal>
        <?= htmlspecialchars($post['title']) ?>
      </h1>

      <p class="blog-post__meta" data-reveal data-reveal-delay="100">
        <span><?= htmlspecialchars($post['author_name'] ?? 'Codentra') ?></span>
        <span aria-hidden="true">·</span>
        <time datetime="<?= htmlspecialchars(date('c', strtotime($publishedAt))) ?>">
          <?= htmlspecialchars(date('F j, Y', strtotime($publishedAt))) ?>
        </time>
      </p>
    </div>
  </header>

  <?php if (!empty($post['featured_image'])): ?>
    <figure class="blog-post__figure">
      <div class="container">
        <img
          src="<?= htmlspecialchars($post['featured_image']) ?>"
          alt="<?= htmlspecialchars($post['image_alt'] ?? $post['title']) ?>"
          width="1200" height="675"
          fetchpriority="high"
          decoding="async">
      </div>
    </figure>
  <?php endif; ?>

  <div class="container blog-post__body" data-reveal>
    <div class="prose">
      <?= $post['content'] /* trusted: stored as HTML by an admin */ ?>
    </div>
  </div>

  <footer class="container blog-post__footer">
    <a class="btn btn--ghost" href="/blog">&larr; Back to all articles</a>
    <a class="btn btn--cta" href="/contact">Talk to us about a project</a>
  </footer>

</article>
