<?php
declare(strict_types=1);

namespace Controllers;

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
        ]));

        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);

        $this->render('contact', compact('flash', 'old', 'errors'));
    }

    public function submit(): void
    {
        // CSRF
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired. Please try again.');
            $this->redirect('/contact');
        }

        // Rate limit (5 / hour per IP)
        $rl = \Middleware\RateLimit::check('contact', RATE_CONTACT_LIMIT, RATE_CONTACT_WINDOW);
        if (!$rl['ok']) {
            $minutes = max(1, (int) ceil($rl['retry_after'] / 60));
            $this->flashError("Too many requests. Please try again in {$minutes} minute" . ($minutes === 1 ? '' : 's') . '.');
            $this->redirect('/contact');
        }

        // Validate
        $input = [
            'name'    => trim((string) ($_POST['name']    ?? '')),
            'email'   => trim((string) ($_POST['email']   ?? '')),
            'phone'   => trim((string) ($_POST['phone']   ?? '')),
            'company' => trim((string) ($_POST['company'] ?? '')),
            'service' => trim((string) ($_POST['service'] ?? '')),
            'budget'  => trim((string) ($_POST['budget']  ?? '')),
            'message' => trim((string) ($_POST['message'] ?? '')),
            'source'  => trim((string) ($_POST['source']  ?? 'contact-page')),
            'website' => trim((string) ($_POST['website'] ?? '')), // honeypot
        ];

        // Honeypot — bots fill the hidden "website" field; humans don't see it
        if ($input['website'] !== '') {
            // Pretend success so bots don't learn the field is the trap
            $this->flashSuccess('Thanks — we\'ll be in touch shortly.');
            $this->redirect('/contact#contact-form');
        }

        $errors = $this->validate($input);
        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old']    = $input;
            $this->redirect('/contact#contact-form');
        }

        // Persist
        try {
            $lead = new \Models\Lead();
            $lead->submit($input);
            // TODO: send notification email to SITE_EMAIL when SMTP is configured
            \Core\Csrf::rotate();
            $this->flashSuccess('Thanks — we got your message. We\'ll reply within one business day.');
        } catch (\Throwable $e) {
            $this->flashError('Something went wrong on our side. Please email ' . SITE_EMAIL . ' directly.');
            $_SESSION['_old'] = $input;
        }

        $this->redirect('/contact#contact-form');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    private function validate(array $input): array
    {
        $errors = [];

        if (strlen($input['name']) < 2 || strlen($input['name']) > 120) {
            $errors['name'] = 'Please enter your name (2–120 characters).';
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($input['phone'] !== '' && !preg_match('/^[0-9 +()\-]{6,40}$/', $input['phone'])) {
            $errors['phone'] = 'Phone number contains invalid characters.';
        }

        if (strlen($input['company']) > 160) {
            $errors['company'] = 'Company name is too long.';
        }

        if ($input['service'] !== '' && !in_array($input['service'], \Models\Lead::SERVICES, true)) {
            $errors['service'] = 'Please choose one of the listed services.';
        }

        if (strlen($input['message']) < 10 || strlen($input['message']) > 2000) {
            $errors['message'] = 'Message must be between 10 and 2000 characters.';
        }

        return $errors;
    }

    // ── Flash helpers ────────────────────────────────────────────────────────

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
