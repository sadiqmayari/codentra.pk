<?php
declare(strict_types=1);

namespace Controllers\Admin;

class PostsController extends \Core\Controller
{
    public const STATUSES = ['draft', 'published'];

    // ── GET /admin/posts ─────────────────────────────────────────────────────

    public function index(): void
    {
        $filters = $this->collectFilters();

        $result        = ['rows' => [], 'total' => 0, 'pages' => 1, 'page' => 1, 'per_page' => 20];
        $statusCounts  = ['draft' => 0, 'published' => 0];
        $categories    = [];
        try {
            $postModel    = new \Models\Post();
            $result       = $postModel->search($filters);
            $statusCounts = $postModel->countByStatus();
            $categories   = $this->loadCategories();
        } catch (\Throwable $e) {
            error_log('[POST-LIST] search-failed msg=' . $e->getMessage());
        }

        $this->seo->set([
            'title'   => 'Blog Posts | Codentra Admin',
            'noindex' => true,
        ]);

        $this->render('admin/posts-index', [
            'pageTitle'    => 'Blog Posts',
            'filters'      => $filters,
            'result'       => $result,
            'statusCounts' => $statusCounts,
            'categories'   => $categories,
        ], 'admin');
    }

    // ── GET /admin/posts/new ─────────────────────────────────────────────────

    public function create(): void
    {
        $this->seo->set(['title' => 'New post | Codentra Admin', 'noindex' => true]);

        $this->render('admin/post-edit', [
            'pageTitle'  => 'New post',
            'mode'       => 'create',
            'post'       => $this->blankPost(),
            'categories' => $this->loadCategories(),
            'errors'     => $this->popSession('_errors', []),
            'old'        => $this->popSession('_old',    []),
        ], 'admin');
    }

    // ── GET /admin/posts/{id}/edit ──────────────────────────────────────────

    public function edit(string $id): void
    {
        $id = (int) $id;
        try {
            $post = (new \Models\Post())->find($id);
        } catch (\Throwable $e) {
            error_log('[POST-EDIT] load-failed id=' . $id . ' msg=' . $e->getMessage());
            $this->abort(404);
        }
        if (!$post) $this->abort(404);

        $this->seo->set([
            'title'   => 'Edit: ' . htmlspecialchars((string) ($post['title'] ?? '')) . ' | Codentra Admin',
            'noindex' => true,
        ]);

        $this->render('admin/post-edit', [
            'pageTitle'  => 'Edit post',
            'mode'       => 'edit',
            'post'       => $post,
            'categories' => $this->loadCategories(),
            'errors'     => $this->popSession('_errors', []),
            'old'        => $this->popSession('_old',    []),
        ], 'admin');
    }

    // ── POST /admin/posts ────────────────────────────────────────────────────

    public function store(): void
    {
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired. Please try again.');
            $this->redirect('/admin/posts/new');
        }

        $input = $this->collectInput($_POST);
        $errors = $this->validate($input);

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old']    = $input;
            $this->flashError('Please fix the highlighted fields.');
            $this->redirect('/admin/posts/new');
        }

        // Featured image upload (optional)
        $featuredPath = $input['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $r = (new \Core\ImageUploader())->upload($_FILES['featured_image'], 'uploads/posts/');
            if (!$r['ok']) {
                $_SESSION['_errors'] = ['featured_image' => $r['error']];
                $_SESSION['_old']    = $input;
                $this->flashError($r['error']);
                $this->redirect('/admin/posts/new');
            }
            $featuredPath = $r['path'];
        }

        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0) ?: null;
            $id = (new \Models\Post())->createPost([
                'title'          => $input['title'],
                'slug'           => $input['slug'],
                'excerpt'        => $input['excerpt'],
                'content'        => $input['content'],
                'featured_image' => $featuredPath,
                'image_alt'      => $input['image_alt'],
                'category_id'    => $input['category_id'],
                'status'         => $input['status'],
            ], $userId);

            error_log("[POST] created id={$id} title={$input['title']} status={$input['status']} by user_id=" . ($userId ?? '?'));
            $this->flashSuccess('Post created.');
            $this->redirect('/admin/posts/' . $id . '/edit');
        } catch (\Throwable $e) {
            error_log('[POST] store-exception msg=' . $e->getMessage());
            $_SESSION['_old'] = $input;
            $this->flashError('Could not create the post. Please try again.');
            $this->redirect('/admin/posts/new');
        }
    }

    // ── POST /admin/posts/{id} ───────────────────────────────────────────────

    public function update(string $id): void
    {
        $id = (int) $id;
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired. Please try again.');
            $this->redirect('/admin/posts/' . $id . '/edit');
        }

        $postModel = new \Models\Post();
        $existing  = $postModel->find($id);
        if (!$existing) $this->abort(404);

        $input  = $this->collectInput($_POST);
        $errors = $this->validate($input);
        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old']    = $input;
            $this->flashError('Please fix the highlighted fields.');
            $this->redirect('/admin/posts/' . $id . '/edit');
        }

        // Featured image — three branches: new upload, removed, unchanged
        $featuredPath = $existing['featured_image'];
        if (!empty($_POST['featured_image_remove'])) {
            $featuredPath = null;
        }
        if (!empty($_FILES['featured_image']['name'])) {
            $r = (new \Core\ImageUploader())->upload($_FILES['featured_image'], 'uploads/posts/');
            if (!$r['ok']) {
                $_SESSION['_errors'] = ['featured_image' => $r['error']];
                $_SESSION['_old']    = $input;
                $this->flashError($r['error']);
                $this->redirect('/admin/posts/' . $id . '/edit');
            }
            $featuredPath = $r['path'];
        }

        try {
            $userId  = (int) ($_SESSION['user_id'] ?? 0) ?: null;

            $updates = [
                'title'          => $input['title'],
                'slug'           => $input['slug'],
                'excerpt'        => $input['excerpt'],
                'content'        => $input['content'],
                'featured_image' => $featuredPath,
                'image_alt'      => $input['image_alt'],
                'category_id'    => $input['category_id'],
                'status'         => $input['status'],
            ];

            $changed = $this->changedFields($existing, $updates);
            $postModel->updatePost($id, $updates);

            error_log("[POST] updated id={$id} changed=" . implode(',', $changed) . " by user_id=" . ($userId ?? '?'));
            $this->flashSuccess('Saved.');
            $this->redirect('/admin/posts/' . $id . '/edit');
        } catch (\Throwable $e) {
            error_log('[POST] update-exception id=' . $id . ' msg=' . $e->getMessage());
            $_SESSION['_old'] = $input;
            $this->flashError('Could not save. Please try again.');
            $this->redirect('/admin/posts/' . $id . '/edit');
        }
    }

    // ── POST /admin/posts/{id}/delete ────────────────────────────────────────

    public function delete(string $id): void
    {
        $id = (int) $id;
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired.');
            $this->redirect('/admin/posts/' . $id . '/edit');
        }

        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0) ?: null;
            $ok     = (new \Models\Post())->softDelete($id, $userId);
            if ($ok) $this->flashSuccess('Post archived.');
            else     $this->flashError('Could not archive that post.');
        } catch (\Throwable $e) {
            error_log('[POST] delete-exception id=' . $id . ' msg=' . $e->getMessage());
            $this->flashError('Could not archive that post.');
        }
        $this->redirect('/admin/posts');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function collectFilters(): array
    {
        $allowedSort = ['updated_at', 'created_at', 'title', 'status', 'views'];
        $sort = (string) ($_GET['sort'] ?? 'updated_at');
        if (!in_array($sort, $allowedSort, true)) $sort = 'updated_at';

        $dir = strtolower((string) ($_GET['dir'] ?? 'desc'));
        if (!in_array($dir, ['asc', 'desc'], true)) $dir = 'desc';

        return [
            'q'           => trim((string) ($_GET['q'] ?? '')),
            'status'      => (string) ($_GET['status'] ?? ''),
            'category_id' => (int) ($_GET['category_id'] ?? 0),
            'sort'        => $sort,
            'dir'         => $dir,
            'page'        => max(1, (int) ($_GET['page'] ?? 1)),
            'per_page'    => max(1, min(200, (int) ($_GET['per_page'] ?? 20))),
        ];
    }

    /** @return array<string, mixed> */
    private function collectInput(array $src): array
    {
        $title  = trim(strip_tags((string) ($src['title']  ?? '')));
        $slug   = trim(preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($src['slug'] ?? ''))));
        $excerpt = trim(strip_tags((string) ($src['excerpt'] ?? '')));
        $content = (string) ($src['content'] ?? ''); // markdown — keep as-is
        $alt     = trim(strip_tags((string) ($src['image_alt'] ?? '')));
        $catId   = (int) ($src['category_id'] ?? 0) ?: null;
        $status  = (string) ($src['status'] ?? 'draft');
        if (!in_array($status, self::STATUSES, true)) $status = 'draft';

        return [
            'title'           => $title,
            'slug'            => $slug,
            'excerpt'         => $excerpt,
            'content'         => $content,
            'image_alt'       => $alt,
            'category_id'     => $catId,
            'status'          => $status,
            'featured_image'  => null, // controller decides path
        ];
    }

    /** @return array<string,string> */
    private function validate(array $input): array
    {
        $errors = [];

        $tlen = strlen($input['title']);
        if ($tlen < 3 || $tlen > 200) {
            $errors['title'] = 'Title must be between 3 and 200 characters.';
        }

        if ($input['slug'] !== '' && !preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $input['slug'])) {
            $errors['slug'] = 'Slug can contain only lowercase letters, numbers, and hyphens.';
        }

        if (strlen($input['excerpt']) > 300) {
            $errors['excerpt'] = 'Excerpt must be 300 characters or less.';
        }

        $clen = strlen($input['content']);
        if ($clen < 10 || $clen > 50000) {
            $errors['content'] = 'Content must be between 10 and 50,000 characters.';
        }

        if (!in_array($input['status'], self::STATUSES, true)) {
            $errors['status'] = 'Invalid status.';
        }

        if ($input['category_id'] !== null) {
            try {
                $stmt = \Database::getInstance()->prepare("SELECT 1 FROM `categories` WHERE `id` = ?");
                $stmt->execute([$input['category_id']]);
                if (!$stmt->fetchColumn()) $errors['category_id'] = 'That category no longer exists.';
            } catch (\Throwable) {
                // Categories table not available — skip validation
            }
        }

        return $errors;
    }

    /** @return array<int, array<string, mixed>> */
    private function loadCategories(): array
    {
        try {
            $stmt = \Database::getInstance()->query("SELECT `id`, `name`, `slug` FROM `categories` ORDER BY `name`");
            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function blankPost(): array
    {
        return [
            'id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '',
            'featured_image' => null, 'image_alt' => '',
            'category_id' => null, 'status' => 'draft',
            'published_at' => null, 'created_at' => null, 'updated_at' => null,
            'views' => 0,
        ];
    }

    /** Returns the keys whose value differs between $before and $after. */
    private function changedFields(array $before, array $after): array
    {
        $changed = [];
        foreach ($after as $k => $v) {
            $b = $before[$k] ?? null;
            if ((string) $b !== (string) $v) $changed[] = $k;
        }
        return $changed;
    }

    private function popSession(string $key, $default)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $val = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $val;
    }

    private function flashSuccess(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_flash'] = ['type' => 'success', 'msg' => $msg];
    }

    private function flashError(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_flash'] = ['type' => 'error', 'msg' => $msg];
    }
}
