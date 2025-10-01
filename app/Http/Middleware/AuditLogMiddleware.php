<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        // Log sensitive operations
        if ($this->shouldLog($request)) {
            $this->logAuditEvent($request, $response, $duration);
        }
        
        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        $sensitiveRoutes = [
            'invoices.store',
            'invoices.update',
            'invoices.destroy',
            'payments.store',
            'payments.update',
            'payments.destroy',
            'expenses.store',
            'expenses.update',
            'expenses.destroy',
        ];
        
        $routeName = $request->route()?->getName();
        
        return in_array($routeName, $sensitiveRoutes);
    }

    private function logAuditEvent(Request $request, Response $response, float $duration): void
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();
        $method = $request->method();
        $url = $request->fullUrl();
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $statusCode = $response->getStatusCode();
        
        Log::channel('audit')->info('Financial operation', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'route' => $routeName,
            'method' => $method,
            'url' => $url,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'timestamp' => now()->toISOString(),
            'request_data' => $this->sanitizeRequestData($request),
        ]);
    }

    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
}
