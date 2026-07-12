<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    private $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $summary = $this->dashboardService->getSummary();

        return apiResponse($summary, 'Dashboard data fetched successfully');
    }
}