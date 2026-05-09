<?php
declare(strict_types=1);

namespace Controllers;

class LeadController extends \Core\Controller
{
    /**
     * Handles every lead submission — full contact form AND home mini-form
     * (both POST to `/contact` with a hidden `source` field that distinguishes them).
     *
     * Flow: CSRF → rate limit → honeypot → server validation →
     *       persist to leads table → send notification email (graceful) →
     *       redirect to /contact/thanks.
     */
    public function submit(): void
    {
        // ── 1. CSRF ────────────────────────────────────────────────────────────
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired. Please refresh the page and try again.');
            $this->redirect('/contact');
        }

        // ── 2. Rate limit (5 / hour per IP) ────────────────────────────────────
        $rl = \Middleware\RateLimit::check('lead-submit', RATE_CONTACT_LIMIT, RATE_CONTACT_WINDOW);
        if (!$rl['ok']) {
            $minutes = max(1, (int) ceil($rl['retry_after'] / 60));
            $this->flashError(
                "You've sent a lot of messages already. Please try again in {$minutes} minute"
                . ($minutes === 1 ? '' : 's')
                . ", or email " . SITE_EMAIL . ' directly.'
            );
            $this->redirect('/contact');
        }

        // ── 3. Read input + honeypot ───────────────────────────────────────────
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

        // Bots fill the hidden "website" field. Pretend success so they don't
        // learn this is the trap.
        if ($input['website'] !== '') {
            error_log('[LEAD] honeypot triggered ip=' . \Core\Request::clientIp());
            $this->markJustSubmitted($input);
            $this->redirect('/contact/thanks');
        }

        // ── 4. Server validation ───────────────────────────────────────────────
        $errors = $this->validate($input);
        if (!empty($errors)) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old']    = $input;
            $this->flashError('Please fix the highlighted fields and try again.');
            $this->redirect('/contact#contact-form');
        }

        // ── 5. Persist (DB is the canonical record) ────────────────────────────
        $leadId = null;
        try {
            $lead   = new \Models\Lead();
            $leadId = $lead->submit($input);
            error_log("[LEAD] saved id={$leadId} source={$input['source']} email={$input['email']} ip=" . \Core\Request::clientIp());
        } catch (\Throwable $e) {
            error_log('[LEAD] DB save failed: ' . $e->getMessage());
            $this->flashError(
                "We couldn't save your message just now. Please email "
                . SITE_EMAIL . ' directly — sorry for the inconvenience.'
            );
            $_SESSION['_old'] = $input;
            $this->redirect('/contact#contact-form');
        }

        // ── 6. Send notification email (graceful — never blocks success) ───────
        $emailOk = $this->sendNotificationEmail($leadId, $input);
        if (!$emailOk) {
            error_log("[LEAD] email notification failed for id={$leadId} (lead is still saved)");
        }

        // ── 7. Done — rotate CSRF and redirect to thanks page ──────────────────
        \Core\Csrf::rotate();
        $this->markJustSubmitted($input);
        $this->redirect('/contact/thanks');
    }

    public function thanks(): void
    {
        $just = $_SESSION['_lead_just_submitted'] ?? null;
        unset($_SESSION['_lead_just_submitted']);

        $this->seo->set([
            'title'       => 'Thanks — we\'ve got your message | Codentra',
            'description' => 'Thanks for getting in touch. We\'ll reply within one business day.',
            'canonical'   => SITE_URL . '/contact/thanks',
            'noindex'     => true,
        ]);

        $this->render('contact-thanks', [
            'name'    => $just['name']    ?? null,
            'service' => $just['service'] ?? null,
        ]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    private function validate(array $input): array
    {
        $errors = [];

        $nameLen = strlen($input['name']);
        if ($nameLen < 2 || $nameLen > 120) {
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

        $msgLen = strlen($input['message']);
        if ($msgLen < 10 || $msgLen > 2000) {
            $errors['message'] = 'Message must be between 10 and 2000 characters.';
        }

        return $errors;
    }

    // ── Email ─────────────────────────────────────────────────────────────────

    /**
     * Sends the notification email using PHP's built-in mail().
     * Returns true on success, false otherwise — caller decides what to do.
     * Never throws.
     */
    private function sendNotificationEmail(int $leadId, array $input): bool
    {
        try {
            $to      = SITE_EMAIL;
            $subject = $this->emailSubject($input);
            [$body, $headers] = $this->emailBody($leadId, $input);

            // Suppress PHP warning when no MTA is configured (e.g. local dev).
            $ok = @mail($to, $subject, $body, $headers);
            return (bool) $ok;
        } catch (\Throwable $e) {
            error_log('[LEAD] email exception: ' . $e->getMessage());
            return false;
        }
    }

    private function emailSubject(array $input): string
    {
        $name    = $this->headerSafe($input['name']) ?: 'Unknown';
        $service = $input['service'] !== '' ? ucwords(str_replace('-', ' ', $input['service'])) : 'general enquiry';
        return "[Codentra] New lead from {$name} — {$service}";
    }

    /**
     * @return array{0:string,1:string} [body, headers]
     */
    private function emailBody(int $leadId, array $input): array
    {
        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $row = function (string $label, string $value): string {
            $value = trim($value) === '' ? '<em style="color:#888;">—</em>' : nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            return "<tr>"
                 . "<td style=\"padding:8px 12px;font-weight:600;color:#0A1C28;width:140px;background:#f4f6f8;\">{$label}</td>"
                 . "<td style=\"padding:8px 12px;color:#222;\">{$value}</td>"
                 . "</tr>";
        };

        $when = date('Y-m-d H:i:s');
        $ip   = \Core\Request::clientIp();
        $ua   = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $body = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:24px;background:#0A1C28;font-family:Arial,Helvetica,sans-serif;color:#fff;">
  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;margin:0 auto;background:#fff;color:#222;border-radius:12px;overflow:hidden;">
    <tr>
      <td style="padding:24px 24px 16px;background:#0A1C28;color:#fff;">
        <h1 style="margin:0;font-size:18px;letter-spacing:.02em;">
          <span style="color:#2A9D8F;">●</span> Codentra — new lead
        </h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,.7);font-size:13px;">
          Lead #{$leadId} · {$h($when)}
        </p>
      </td>
    </tr>
    <tr><td style="padding:0;">
      <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
        {$row('Name',    $input['name'])}
        {$row('Email',   $input['email'])}
        {$row('Phone',   $input['phone'])}
        {$row('Company', $input['company'])}
        {$row('Service', $input['service'])}
        {$row('Budget',  $input['budget'])}
        {$row('Source',  $input['source'])}
        {$row('Message', $input['message'])}
      </table>
    </td></tr>
    <tr><td style="padding:16px 24px;background:#f4f6f8;color:#666;font-size:12px;line-height:1.6;">
      <strong>IP:</strong> {$h($ip)}<br>
      <strong>User-Agent:</strong> {$h($ua)}<br>
      <strong>Reply directly to this email</strong> to respond — Reply-To is set to the lead's address.
    </td></tr>
  </table>
</body></html>
HTML;

        // ── Headers (CRLF, sanitized to prevent injection) ────────────────────
        $fromName    = 'Codentra Notifications';
        $fromAddress = 'noreply@codentra.pk';
        $replyName   = $this->headerSafe($input['name'])  ?: 'Lead';
        $replyEmail  = filter_var($input['email'], FILTER_VALIDATE_EMAIL) ?: SITE_EMAIL;

        $headers = implode("\r\n", [
            "From: {$fromName} <{$fromAddress}>",
            "Reply-To: {$replyName} <{$replyEmail}>",
            "Return-Path: {$fromAddress}",
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: Codentra/1.0',
            'X-Lead-Id: ' . $leadId,
        ]);

        return [$body, $headers];
    }

    /** Strip CR/LF + control chars to prevent header injection. */
    private function headerSafe(string $value): string
    {
        return trim(preg_replace('/[\r\n\t]+/', ' ', $value));
    }

    // ── Session helpers ──────────────────────────────────────────────────────

    private function markJustSubmitted(array $input): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_lead_just_submitted'] = [
            'name'    => $input['name'],
            'service' => $input['service'],
        ];
    }

    private function flashError(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['_flash'] = ['type' => 'error', 'msg' => $msg];
    }
}
