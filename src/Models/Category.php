<?php
declare(strict_types=1);

namespace Models;

class Category extends \Core\Model
{
    protected string $table       = 'categories';
    protected bool   $softDeletes = false;

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `categories` WHERE `slug` = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function withPostCounts(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, COUNT(p.`id`) AS `post_count`
             FROM `categories` c
             LEFT JOIN `posts` p ON p.`category_id` = c.`id`
                                 AND p.`status` = 'published'
                                 AND p.`deleted_at` IS NULL
             GROUP BY c.`id`
             ORDER BY c.`name` ASC"
        );
        return $stmt->fetchAll();
    }

    public function createCategory(string $name, string $slug, ?string $description = null): int
    {
        return $this->insert([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
        ]);
    }
}
