<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

    public function forecast(Request $request)
    {
        $months = (int) $request->get('months', 6);
        $window = (int) $request->get('window', 3);
        $userId = $request->user()->id;
        
        // Cache key includes user ID and parameters
        $cacheKey = "forecast_user_{$userId}_months_{$months}_window_{$window}";
        
        $forecastData = Cache::remember($cacheKey, 3600, function() use ($months, $window) {
            try {
                // Run forecast command and capture output
                $exitCode = Artisan::call('forecast:generate', [
                    '--months' => $months,
                    '--window' => $window,
                ]);
                
                $output = Artisan::output();
                $forecastData = json_decode($output, true);
                
                if (!$forecastData || $exitCode !== 0) {
                    throw new \Exception('Forecast generation failed');
                }
                
                return $forecastData;
            } catch (\Exception $e) {
                \Log::error('Forecast generation failed: ' . $e->getMessage());
                return [
                    'error' => 'Forecast kon niet worden gegenereerd',
                    'message' => 'Probeer het later opnieuw of neem contact op met support.'
                ];
            }
        });
        
        return response()->json($forecastData);
    }
}
