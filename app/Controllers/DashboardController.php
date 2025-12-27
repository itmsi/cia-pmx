<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * Display dashboard
     * GET /dashboard
     */
    public function index()
    {
        $userId = session()->get('user_id');

        $dashboardData = $this->dashboardService->getDashboardData($userId);

        return view('dashboard/index', [
            'dashboardData' => $dashboardData
        ]);
    }
}
