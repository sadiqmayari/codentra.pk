<?php
declare(strict_types=1);

namespace Models;

class Post extends \Core\Model
{
    protected string $table = 'posts';

    // ── Public reads ──────────────────────────────────────────────────────────

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.`name` AS `category_name`, c.`slug` AS `category_slug`,
                    u.`name` AS `author_name`
             FROM `posts` p
             LEFT JOIN `categories` c ON c.`id` = p.`category_id`
             LEFT JOIN `users`      u ON u.`id` = p.`author_id`
             WHERE p.`slug` = ? AND p.`deleted_at` IS NULL
             LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function published(int $page = 1, int $perPage = 9, ?int $categoryId = null): array
    {
        $where    = "p.`deleted_at` IS NULL AND p.`status` = 'published' AND p.`published_at` <= CURRENT_TIMESTAMP";
        $bindings = [];

        if ($categoryId !== null) {
            $where     .= " AND p.`category_id` = ?";
            $bindings[] = $categoryId;
        }

        $sql = "SELECT p.*, c.`name` AS `category_name`, c.`slug` AS `category_slug`
                FROM `posts` p
                LEFT JOIN `categories` c ON c.`id` = p.`category_id`
                WHERE {$where}
                ORDER BY p.`published_at` DESC";

        return $this->paginate($sql, $bindings, $page, $perPage);
    }

    public function countPublished(?int $categoryId = null): int
    {
        $where    = "`deleted_at` IS NULL AND `status` = 'published' AND `published_at` <= CURRENT_TIMESTAMP";
        $bindings = [];
        if ($categoryId !== null) {
            $where     .= " AND `category_id` = ?";
            $bindings[] = $categoryId;
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `posts` WHERE {$where}");
        $stmt->execute($bindings);
        return (int) $stmt->fetchColumn();
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE `posts` SET `views` = `views` + 1 WHERE `id` = ?");
        $stmt->execute([$id]);
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `posts`
             WHERE `deleted_at` IS NULL AND `status` = 'published'
             ORDER BY `published_at` DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Admin writes ──────────────────────────────────────────────────────────

    public function createPost(array $data, ?int $authorId = null): int
    {
        if ($authorId !== null) $data['author_id'] = $authorId;
        $data['slug']      = $this->uniqueSlug($data['slug'] ?? $data['title'] ?? 'post');
        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        return $this->insert($data);
    }

    public function updatePost(int $id, array $data): bool
    {
        $existing = $this->find($id);
        if (!$existing) return false;

        // First-publish: stamp published_at iff transitioning draft -> published
        // and we don't already have one. Republish (publish -> draft -> publish)
        // keeps the original published_at — so /blog ordering is stable.
        $newStatus = $data['status'] ?? $existing['status'];
        if (
            $newStatus === 'published'
            && empty($existing['published_at'])
            && empty($data['published_at'])
        ) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        // Slug uniqueness if it changed
        if (!empty($data['slug']) && $data['slug'] !== $existing['slug']) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $id);
        }

        return $this->update($id, $data);
    }

    public function softDelete(int $id, ?int $userId = null): bool
    {
        $stmt = $this->db->prepare("UPDATE `posts` SET `deleted_at` = ? WHERE `id` = ?");
        $ok   = $stmt->execute([date('Y-m-d H:i:s'), $id]);
        if ($ok) error_log("[POST] soft-deleted id={$id} by user_id=" . ($userId ?? '?'));
        return $ok;
    }

    /**
     * Filterable + sortable + paginated post search for /admin/posts.
     *
     * @param array{q?:string,status?:string,category_id?:int,sort?:string,dir?:string,page?:int,per_page?:int} $filters
     * @return array{rows: array, total: int, pages: int, page: int, per_page: int}
     */
    public function search(array $filters): array
    {
        [$where, $bindings] = $this->buildSearchWhere($filters, 'p.');

        $countSql = "SELECT COUNT(*) FROM `posts` p WHERE " . implode(' AND ', $where);
        $stmt     = $this->db->prepare($countSql);
        $stmt->execute($bindings);
        $total = (int) $stmt->fetchColumn();

        $sortMap = [
            'updated_at' => 'p.`updated_at`',
            'created_at' => 'p.`created_at`',
            'title'      => 'p.`title`',
            'status'     => 'p.`status`',
            'views'      => 'p.`views`',
        ];
        $sortKey = $filters['sort'] ?? 'updated_at';
        $sortCol = $sortMap[$sortKey] ?? 'p.`updated_at`';
        $dir     = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $page    = max(1, (int) ($filters['page']     ?? 1));
        $perPage = max(1, min(200, (int) ($filters['per_page'] ?? 20)));
        $offset  = ($page - 1) * $perPage;

        $sql = "SELECT p.*,
                       c.`name` AS `category_name`,
                       u.`name` AS `author_name`
                FROM `posts` p
                LEFT JOIN `categories` c ON c.`id` = p.`category_id`
                LEFT JOIN `users`      u ON u.`id` = p.`author_id`
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$sortCol} {$dir}, p.`id` DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['updated_human'] = \Core\Time::timeAgo($row['updated_at'] ?? null);
            $row['updated_iso']   = \Core\Time::iso($row['updated_at']   ?? null);
        }
        unset($row);

        return [
            'rows'     => $rows,
            'total'    => $total,
            'pages'    => max(1, (int) ceil($total / $perPage)),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * @param string $prefix  Optional column prefix (e.g. 'p.') so the
     *                        same WHERE works inside a JOIN'd query.
     * @return array{0: array<int, string>, 1: array<int, mixed>}
     */
    private function buildSearchWhere(array $filters, string $prefix = ''): array
    {
        $col      = fn(string $c): string => $prefix . '`' . $c . '`';
        $where    = [$col('deleted_at') . ' IS NULL'];
        $bindings = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[]    = '(' . $col('title') . ' LIKE ? OR ' . $col('slug') . ' LIKE ?)';
            $like       = '%' . $q . '%';
            $bindings[] = $like; $bindings[] = $like;
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status !== '' && $status !== 'all' && in_array($status, ['draft', 'published'], true)) {
            $where[]    = $col('status') . ' = ?';
            $bindings[] = $status;
        }

        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[]    = $col('category_id') . ' = ?';
            $bindings[] = $categoryId;
        }

        return [$where, $bindings];
    }

    /** @return array{draft:int,published:int} */
    public function countByStatus(): array
    {
        $stmt = $this->db->query(
            "SELECT `status`, COUNT(*) AS n FROM `posts`
             WHERE `deleted_at` IS NULL GROUP BY `status`"
        );
        $out = ['draft' => 0, 'published' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['status']] = (int) $row['n'];
        }
        return $out;
    }

    public function publish(int $id): bool
    {
        return $this->update($id, [
            'status'       => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function unpublish(int $id): bool
    {
        return $this->update($id, ['status' => 'draft']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $this->slugify($base);
        $i    = 1;
        $try  = $slug;

        while ($this->slugExists($try, $ignoreId)) {
            $i++;
            $try = $slug . '-' . $i;
        }
        return $try;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        $sql      = "SELECT COUNT(*) FROM `posts` WHERE `slug` = ?";
        $bindings = [$slug];
        if ($ignoreId !== null) {
            $sql       .= " AND `id` != ?";
            $bindings[] = $ignoreId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim($value, '-') ?: 'post';
    }
}
