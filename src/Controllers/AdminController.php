<?php
declare(strict_types=1);

namespace Controllers;

class AdminController extends \Core\Controller
{
    /**
     * GET /admin/dashboard — placeholder for Phase 7.
     * Reaching this method confirms AuthMiddleware passed.
     */
    public function dashboard(): void
    {
        $this->seo->set([
            'title'   => 'Dashboard | Codentra Admin',
            'noindex' => true,
        ]);

        $this->render('admin/dashboard', [
            'pageTitle' => 'Dashboard',
        ], 'admin');
    }

    /** GET /admin → /admin/dashboard */
    public function index(): void
    {
        $this->redirect('/admin/dashboard');
    }
}
