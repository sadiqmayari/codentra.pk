<?php
declare(strict_types=1);

namespace Controllers;

class ErrorController extends \Core\Controller
{
    public function notFound(): void
    {
        http_response_code(404);

        $this->seo->set([
            'title'       => '404 — Page Not Found | Codentra',
            'description' => 'The page you were looking for doesn\'t exist.',
            'canonical'   => SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/'),
        ]);

        $this->render('errors/404');
    }
}
