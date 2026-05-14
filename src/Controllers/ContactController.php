<?php
declare(strict_types=1);

namespace Controllers;

/**
 * Renders the contact page (GET only).
 *
 * All POST submissions — full form AND the home mini-form — are handled by
 * \Controllers\LeadController::submit so there's a single source of truth
 * for validation, persistence, email, and rate-limiting.
 */
class ContactController extends \Core\Controller
{
    public function index(): void
    {
        $this->seo->set([
            'title'       => 'Contact — Start a project with Codentra',
            'description' => 'Tell us about your project. We reply within one business day.',
            'canonical'   => SITE_URL . '/contact',
        ])
        ->addJsonLd(\Seo::breadcrumbSchema([
            ['Home', '/'],
            ['Contact', '/contact'],
        ]))
        // LocalBusiness only on /contact — gives Google a clear "this is
        // the page to surface for 'codentra contact' queries" signal,
        // alongside the site-wide Organization schema rendered by the
        // main layout.
        ->addJsonLd(\Seo::localBusinessSchema());

        $flash = $_SESSION['_flash']  ?? null;
        $old   = $_SESSION['_old']    ?? [];
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_flash'], $_SESSION['_old'], $_SESSION['_errors']);

        $this->render('contact', compact('flash', 'old', 'errors'));
    }
}
