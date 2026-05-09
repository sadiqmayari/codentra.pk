<?php
/**
 * @var array $posts
 * @var int   $page
 * @var int   $totalPages
 * @var int   $total
 * @var bool  $dbError
 */
?>

<header class="page-hero">
  <div class="container">
    <p class="section__eyebrow" data-reveal>Blog</p>
    <h1 class="page-hero__title" data-reveal data-reveal-delay="100">
      Engineering &amp; e-commerce notes from the team.
    </h1>
    <p class="page-hero__sub" data-reveal data-reveal-delay="200">
      Practical writeups on web performance, Shopify conversion, e-commerce ops, and
      automation — drawn from real projects we've shipped.
    </p>
  </div>
</header>


<section class="section blog-index" aria-labelledby="blog-list">
  <div class="container">
    <h2 id="blog-list" class="visually-hidden">Articles</h2>

    <?php if ($dbError): ?>
      <div class="alert alert--error" role="status">
        We can't reach the article database right now. Please check back shortly — or
        <a href="/contact">get in touch</a> if this persists.
      </div>
    <?php elseif (empty($posts)): ?>
      <div class="empty-state glass" data-reveal>
        <h3>No articles yet.</h3>
        <p>The first post is in the oven. Subscribe via the form on our <a href="/contact">contact page</a> to be notified.</p>
      </div>
    <?php else: ?>

      <ul class="blog-grid">
        <?php foreach ($posts as $i => $p): ?>
          <li class="blog-card glass" data-reveal data-reveal-delay="<?= ($i % 3) * 80 ?>">
            <?php if (!empty($p['featured_image'])): ?>
              <a class="blog-card__media" href="/blog/<?= htmlspecialchars($p['slug']) ?>" aria-hidden="true" tabindex="-1">
                <img
                  src="<?= htmlspecialchars($p['featured_image']) ?>"
                  alt=""
                  loading="lazy"
                  decoding="async"
                  width="640" height="360">
              </a>
            <?php endif; ?>

            <div class="blog-card__body">
              <?php if (!empty($p['category_name'])): ?>
                <p class="blog-card__cat"><?= htmlspecialchars($p['category_name']) ?></p>
              <?php endif; ?>

              <h3 class="blog-card__title">
                <a href="/blog/<?= htmlspecialchars($p['slug']) ?>">
                  <?= htmlspecialchars($p['title']) ?>
                </a>
              </h3>

              <?php if (!empty($p['excerpt'])): ?>
                <p class="blog-card__excerpt"><?= htmlspecialchars($p['excerpt']) ?></p>
              <?php endif; ?>

              <p class="blog-card__meta">
                <time datetime="<?= htmlspecialchars(date('c', strtotime($p['published_at'] ?? $p['created_at']))) ?>">
                  <?= htmlspecialchars(date('M j, Y', strtotime($p['published_at'] ?? $p['created_at']))) ?>
                </time>
              </p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($totalPages > 1): ?>
        <nav class="blog-pagination" aria-label="Pagination">
          <?php if ($page > 1): ?>
            <a class="btn btn--ghost" href="/blog?page=<?= $page - 1 ?>" rel="prev">&larr; Newer</a>
          <?php endif; ?>

          <span class="blog-pagination__status">Page <?= $page ?> of <?= $totalPages ?></span>

          <?php if ($page < $totalPages): ?>
            <a class="btn btn--ghost" href="/blog?page=<?= $page + 1 ?>" rel="next">Older &rarr;</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</section>
