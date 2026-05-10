<?php
declare(strict_types=1);

namespace Controllers\Admin;

class LeadsController extends \Core\Controller
{
    // ── GET /admin/leads ─────────────────────────────────────────────────────

    public function index(): void
    {
        $filters = $this->collectFilters();

        $result        = ['rows' => [], 'total' => 0, 'pages' => 1, 'page' => 1, 'per_page' => 25];
        $statusCounts  = array_fill_keys(\Models\Lead::STATUSES, 0);
        try {
            $lead          = new \Models\Lead();
            $result        = $lead->search($filters);
            $statusCounts  = $lead->statusCounts();
        } catch (\Throwable $e) {
            error_log('[LEAD-LIST] search-failed msg=' . $e->getMessage());
        }

        $this->seo->set([
            'title'   => 'Leads | Codentra Admin',
            'noindex' => true,
        ]);

        $this->render('admin/leads-index', [
            'pageTitle'    => 'Leads',
            'filters'      => $filters,
            'result'       => $result,
            'statusCounts' => $statusCounts,
            'services'     => \Models\Lead::SERVICES,
            'statuses'     => \Models\Lead::STATUSES,
        ], 'admin');
    }

    // ── GET /admin/leads/{id} ────────────────────────────────────────────────

    public function show(string $id): void
    {
        $id = (int) $id;
        try {
            $leadModel = new \Models\Lead();
            $lead      = $leadModel->find($id);
            $history   = $lead ? $leadModel->history($id) : [];
        } catch (\Throwable $e) {
            error_log('[LEAD-SHOW] load-failed id=' . $id . ' msg=' . $e->getMessage());
            $this->abort(404);
        }

        if (empty($lead)) $this->abort(404);

        $this->seo->set([
            'title'   => htmlspecialchars((string) ($lead['name'] ?? 'Lead')) . ' | Codentra Admin',
            'noindex' => true,
        ]);

        $this->render('admin/lead-detail', [
            'pageTitle' => $lead['name'] ?? 'Lead',
            'lead'      => $lead,
            'history'   => $history,
            'statuses'  => \Models\Lead::STATUSES,
        ], 'admin');
    }

    // ── POST /admin/leads/{id}/status ────────────────────────────────────────

    public function updateStatus(string $id): void
    {
        $id = (int) $id;
        $this->requireCsrf();

        $newStatus = (string) ($_POST['status'] ?? '');
        if (!in_array($newStatus, \Models\Lead::STATUSES, true)) {
            $this->json(['ok' => false, 'error' => 'invalid status'], 422);
        }

        try {
            $leadModel = new \Models\Lead();
            $existing  = $leadModel->find($id);
            if (!$existing) $this->json(['ok' => false, 'error' => 'not found'], 404);

            $oldStatus = (string) ($existing['status'] ?? '');
            $userId    = (int) ($_SESSION['user_id'] ?? 0) ?: null;

            $ok = $leadModel->updateStatus($id, $newStatus, $userId);
            if (!$ok) {
                $this->json(['ok' => false, 'error' => 'update failed'], 500);
            }

            error_log("[LEAD] status-changed id={$id} from={$oldStatus} to={$newStatus} by user_id=" . ($userId ?? '?'));

            $this->json([
                'ok'         => true,
                'status'     => $newStatus,
                'badge_html' => $this->renderBadge($newStatus),
            ]);
        } catch (\Throwable $e) {
            error_log('[LEAD] status-change-exception id=' . $id . ' msg=' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'server error'], 500);
        }
    }

    // ── POST /admin/leads/{id}/notes ─────────────────────────────────────────

    public function saveNotes(string $id): void
    {
        $id = (int) $id;
        $this->requireCsrf();

        // Plain text only — no HTML, preserve line breaks. Cap at 5000 chars.
        $rawNotes = (string) ($_POST['notes'] ?? '');
        $notes    = substr(strip_tags($rawNotes), 0, 5000);

        try {
            $leadModel = new \Models\Lead();
            $userId    = (int) ($_SESSION['user_id'] ?? 0) ?: null;
            $r         = $leadModel->updateNotes($id, $notes, $userId);
            $this->json(['ok' => true, 'saved_at' => $r['saved_at']]);
        } catch (\Throwable $e) {
            error_log('[LEAD] notes-save-exception id=' . $id . ' msg=' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'server error'], 500);
        }
    }

    // ── POST /admin/leads/{id}/delete ────────────────────────────────────────

    public function delete(string $id): void
    {
        $id = (int) $id;
        if (!\Core\Csrf::verify()) {
            $this->flashError('Your session expired. Please refresh and try again.');
            $this->redirect('/admin/leads/' . $id);
        }

        try {
            $leadModel = new \Models\Lead();
            $userId    = (int) ($_SESSION['user_id'] ?? 0) ?: null;
            $ok        = $leadModel->softDelete($id, $userId);
            if ($ok) {
                error_log("[LEAD] soft-deleted id={$id} by user_id=" . ($userId ?? '?'));
                $this->flashSuccess('Lead archived.');
            } else {
                $this->flashError('Could not archive that lead.');
            }
        } catch (\Throwable $e) {
            error_log('[LEAD] soft-delete-exception id=' . $id . ' msg=' . $e->getMessage());
            $this->flashError('Could not archive that lead.');
        }

        $this->redirect('/admin/leads');
    }

    // ── GET /admin/leads/export ──────────────────────────────────────────────

    public function export(): void
    {
        $filters = $this->collectFilters();

        try {
            $leadModel = new \Models\Lead();
            $rows      = $leadModel->searchAll($filters);
        } catch (\Throwable $e) {
            error_log('[LEAD] export-failed msg=' . $e->getMessage());
            $this->abort(500);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0) ?: null;
        error_log("[LEAD] export count=" . count($rows) . " by user_id=" . ($userId ?? '?'));

        $filename = 'leads-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, must-revalidate');
        header('Pragma: no-cache');

        // Stream straight to output. fputcsv handles quoting + Excel
        // wants \r\n line endings — pass it explicitly.
        $fh  = fopen('php://output', 'w');
        $cols = ['id','created_at','name','email','phone','company','service','budget','status','source','message','notes','ip_address'];
        fputcsv($fh, $cols, ',', '"', '\\', "\r\n");

        foreach ($rows as $row) {
            $line = [];
            foreach ($cols as $c) {
                $line[] = (string) ($row[$c] ?? '');
            }
            fputcsv($fh, $line, ',', '"', '\\', "\r\n");
        }
        fclose($fh);
        exit;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function collectFilters(): array
    {
        $allowedSort = ['created_at', 'name', 'status'];
        $sort = (string) ($_GET['sort'] ?? 'created_at');
        if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

        $dir = strtolower((string) ($_GET['dir'] ?? 'desc'));
        if (!in_array($dir, ['asc', 'desc'], true)) $dir = 'desc';

        return [
            'q'        => trim((string) ($_GET['q']       ?? '')),
            'status'   => (string) ($_GET['status']  ?? ''),
            'service'  => (string) ($_GET['service'] ?? ''),
            'from'     => (string) ($_GET['from']    ?? ''),
            'to'       => (string) ($_GET['to']      ?? ''),
            'sort'     => $sort,
            'dir'      => $dir,
            'page'     => max(1, (int) ($_GET['page']     ?? 1)),
            'per_page' => max(1, min(200, (int) ($_GET['per_page'] ?? 25))),
        ];
    }

    private function renderBadge(string $status): string
    {
        $cls  = 'badge badge--' . preg_replace('/[^a-z]/', '', strtolower($status));
        $text = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
        return "<span class=\"{$cls}\">{$text}</span>";
    }

    private function requireCsrf(): void
    {
        if (!\Core\Csrf::verify()) {
            $this->json(['ok' => false, 'error' => 'csrf'], 419);
        }
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
