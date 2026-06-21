<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    /**
     * Inject DashboardService dependency.
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the Admin Dashboard.
     */
    public function admin(): View
    {
        // Enforce role check
        if (Auth::user()->user_type !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return view('admin.dashboard');
    }

    /**
     * Show the Standard User Dashboard with full tenant telemetry data.
     */
    public function user(): View
    {
        // Enforce role check
        if (Auth::user()->user_type !== 'user') {
            abort(403, 'Unauthorized access.');
        }

        $userId = Auth::id();

        // Retrieve telemetry metrics, charts, activity logs and campaigns via Service layer
        $metrics = $this->dashboardService->getMetrics($userId);
        $chartData = $this->dashboardService->getMessageChartData($userId);
        $recentActivity = $this->dashboardService->getRecentActivity($userId);
        $campaigns = $this->dashboardService->getCampaignSummaries($userId);

        return view('user.dashboard', compact('metrics', 'chartData', 'recentActivity', 'campaigns'));
    }
}
