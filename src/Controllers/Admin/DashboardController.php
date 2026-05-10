<?php
declare(strict_types=1);

namespace Controllers\Admin;

class DashboardController extends \Core\Controller
{
    public function index(): void
    {
        $this->seo->set([
            'title'   => 'Dashboard | Codentra Admin',
            'noindex' => true,
        ]);

        // KPIs
        $kpis     = [
            'total'         => 0,
            'newThisWeek'   => 0,
            'conversion'    => null,
            'publishedPosts'=> 0,
            'weekDelta'     => 0.0,
        ];
        $chartData      = [];
        $recentLeads    = [];
        $recentPosts    = [];

        try {
            $leadModel = new \Models\Lead();
            $postModel = new \Models\Post();

            $kpis['total']          = $leadModel->countActive();
            $kpis['newThisWeek']    = $leadModel->newThisWeek();
            $kpis['conversion']     = $leadModel->conversionRate();
            $kpis['weekDelta']      = $leadModel->weekDelta();
            $kpis['publishedPosts'] = $postModel->countPublished();

            $chartData   = $leadModel->dailyCounts(30);
            $recentLeads = $leadModel->recent(5);
            $recentPosts = $postModel->recent(5);
        } catch (\Throwable $e) {
            error_log(
                '[DASHBOARD] data-fetch-failed class=' . $e::class
                . ' msg=' . ($e->getMessage() !== '' ? $e->getMessage() : '<empty>')
                . ' at '  . $e->getFile() . ':' . $e->getLine()
            );
            // Render the page anyway — empty/zero values are correct fallbacks.
        }

        $this->render('admin/dashboard', [
            'pageTitle'   => 'Dashboard',
            'kpis'        => $kpis,
            'chartData'   => $chartData,
            'recentLeads' => $recentLeads,
            'recentPosts' => $recentPosts,
        ], 'admin');
    }
}
