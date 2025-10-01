<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request): Response
    {
        $dashboardData = $this->dashboardService->getDashboardData($request->user());
        
        return Inertia::render('Dashboard', $dashboardData);
    }
}
