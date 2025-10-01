<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class FinancialAuditMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users and successful responses
        if (Auth::check() && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->logFinancialAction($request, $response);
        }

        return $response;
    }

    private function logFinancialAction(Request $request, Response $response): void
    {
        $method = $request->method();
        $route = $request->route();
        
        if (!$route) {
            return;
        }

        $routeName = $route->getName();
        $action = $this->determineAction($method, $routeName);
        
        if (!$action) {
            return;
        }

        $modelInfo = $this->extractModelInfo($route, $request);
        
        if (!$modelInfo) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelInfo['type'],
            'model_id' => $modelInfo['id'],
            'old_values' => $modelInfo['old_values'] ?? null,
            'new_values' => $modelInfo['new_values'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function determineAction(string $method, string $routeName): ?string
    {
        if (str_contains($routeName, 'invoices') || str_contains($routeName, 'expenses') || str_contains($routeName, 'payments')) {
            return match ($method) {
                'POST' => 'created',
                'PUT', 'PATCH' => 'updated',
                'DELETE' => 'deleted',
                default => null,
            };
        }

        return null;
    }

    private function extractModelInfo($route, Request $request): ?array
    {
        $routeName = $route->getName();
        
        if (str_contains($routeName, 'invoices')) {
            $invoice = $route->parameter('invoice');
            return [
                'type' => 'App\Models\Invoice',
                'id' => $invoice?->id ?? null,
                'new_values' => $method === 'POST' ? $request->validated() : null,
            ];
        }
        
        if (str_contains($routeName, 'expenses')) {
            $expense = $route->parameter('expense');
            return [
                'type' => 'App\Models\Expense',
                'id' => $expense?->id ?? null,
                'new_values' => $method === 'POST' ? $request->validated() : null,
            ];
        }
        
        if (str_contains($routeName, 'payments')) {
            $payment = $route->parameter('payment');
            return [
                'type' => 'App\Models\Payment',
                'id' => $payment?->id ?? null,
                'new_values' => $method === 'POST' ? $request->validated() : null,
            ];
        }

        return null;
    }
}