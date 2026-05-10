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

    public function createPost(array $data, int $authorId): int
    {
        $data['author_id'] = $authorId;
        $data['slug']      = $this->uniqueSlug($data['slug'] ?? $data['title'] ?? 'post');
        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        return $this->insert($data);
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
