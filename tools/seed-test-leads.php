<?php
/**
 * Seeds 15 dummy leads spread across the last 30 days + a handful of
 * published posts into the local SQLite test DB so the admin dashboard
 * has something to render.
 *
 * Usage: php -c tools/php-dev.ini tools/seed-test-leads.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';
$pdo = new PDO('sqlite:' . ($_ENV['DB_NAME'] ?? __DIR__ . '/../storage/test.db'));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure the posts table exists in the local sqlite test DB. The
// production migration is the canonical MySQL schema; this is the
// minimum sqlite version of it for local testing.
$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS posts (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  slug           TEXT NOT NULL UNIQUE,
  title          TEXT NOT NULL,
  excerpt        TEXT,
  content        TEXT NOT NULL,
  featured_image TEXT,
  image_alt      TEXT,
  category_id    INTEGER,
  author_id      INTEGER,
  status         TEXT NOT NULL DEFAULT 'draft',
  views          INTEGER NOT NULL DEFAULT 0,
  published_at   TEXT,
  created_at     TEXT NOT NULL,
  updated_at     TEXT NOT NULL,
  deleted_at     TEXT
);
SQL);

// Wipe + reseed for deterministic test runs.
$pdo->exec('DELETE FROM leads');
$pdo->exec('DELETE FROM posts');

$names    = ['Aisha K.','Bilal M.','Carla J.','Daniyal S.','Erum F.','Faisal R.','Gulshan A.','Hassan Z.','Imran T.','Junaid B.','Kiran P.','Laila Q.','Mehmood G.','Nadia W.','Omar V.'];
$services = ['web-dev', 'shopify', 'ecommerce-mgmt', 'automation', 'other'];
$statuses = [
    // 5 new, 3 contacted, 3 qualified, 2 converted, 2 lost
    'new','new','new','new','new',
    'contacted','contacted','contacted',
    'qualified','qualified','qualified',
    'converted','converted',
    'lost','lost',
];

$stmt = $pdo->prepare(
    "INSERT INTO leads
       (name,email,phone,company,service,budget,message,source,status,
        ip_address,user_agent,created_at,updated_at)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
);

$now = time();
for ($i = 0; $i < 15; $i++) {
    $daysAgo = random_int(0, 29);    // spread across the last 30 days
    $secsAgo = $daysAgo * 86400 + random_int(0, 86399);
    $created = date('Y-m-d H:i:s', $now - $secsAgo);

    $name = $names[$i];
    $stmt->execute([
        $name,
        strtolower(preg_replace('/[^a-z]/i', '', explode(' ', $name)[0])) . $i . '@example.com',
        '+92 300 ' . str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
        'Sample Co ' . ($i + 1),
        $services[$i % count($services)],
        ['< $5k','$5k–$15k','$15k–$50k','$50k+','Not sure yet'][$i % 5],
        "Synthetic test lead #{$i} for dashboard rendering.",
        'website',
        $statuses[$i],
        '203.0.113.' . (10 + $i),
        'Mozilla/5.0 (test-seed)',
        $created,
        $created,
    ]);
}

$counts = $pdo->query(
    "SELECT status, COUNT(*) AS n FROM leads GROUP BY status ORDER BY status"
)->fetchAll(PDO::FETCH_ASSOC);
echo "✓ Seeded " . array_sum(array_column($counts, 'n')) . " leads:\n";
foreach ($counts as $r) printf("    %-10s  %d\n", $r['status'], $r['n']);

// ── Posts ───────────────────────────────────────────────────────────────────
$samplePosts = [
    ['why-core-web-vitals-still-matter-in-2026',          'Why Core Web Vitals still matter in 2026',          'Performance is no longer a nice-to-have.', 'published', 3],
    ['shopify-conversion-checklist-12-fixes',             'Shopify conversion checklist: the 12 fixes that move the needle', 'Pragmatic audit list.',     'published', 7],
    ['automating-order-ops-when-spreadsheets-stop-scaling','Automating order ops: when spreadsheets stop scaling', 'Stack we deploy.',                       'published', 14],
    ['php-front-controller-patterns',                     'PHP front-controller patterns we still use',         'Routing without a framework.',           'published', 21],
    ['draft-inventory-allocation-rules',                  'Draft: inventory allocation rules that don\'t blow up', 'Work in progress.',                   'draft',     0],
];

$pStmt = $pdo->prepare(
    "INSERT INTO posts
       (slug,title,excerpt,content,status,published_at,created_at,updated_at)
     VALUES (?,?,?,?,?,?,?,?)"
);
foreach ($samplePosts as [$slug, $title, $excerpt, $status, $daysAgo]) {
    $when = date('Y-m-d H:i:s', $now - $daysAgo * 86400);
    $pStmt->execute([
        $slug, $title, $excerpt, '<p>' . htmlspecialchars($excerpt) . '</p>',
        $status,
        $status === 'published' ? $when : null,
        $when, $when,
    ]);
}
$pCount = $pdo->query(
    "SELECT status, COUNT(*) AS n FROM posts GROUP BY status ORDER BY status"
)->fetchAll(PDO::FETCH_ASSOC);
echo "✓ Seeded " . array_sum(array_column($pCount, 'n')) . " posts:\n";
foreach ($pCount as $r) printf("    %-10s  %d\n", $r['status'], $r['n']);
