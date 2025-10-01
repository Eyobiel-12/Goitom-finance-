<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        // Rate limit: 60 requests per minute per IP
        $maxAttempts = 60;
        $decayMinutes = 1;
        
        if ($this->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            return response()->json([
                'message' => 'Te veel verzoeken. Probeer het later opnieuw.',
                'retry_after' => $this->getRetryAfter($key)
            ], 429);
        }
        
        $this->hit($key, $decayMinutes);
        
        return $next($request);
    }

    private function resolveRequestSignature(Request $request): string
    {
        return 'rate_limit:' . $request->ip();
    }

    private function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $attempts = cache()->get($key, 0);
        return $attempts >= $maxAttempts;
    }

    private function hit(string $key, int $decayMinutes): void
    {
        $attempts = cache()->get($key, 0);
        cache()->put($key, $attempts + 1, now()->addMinutes($decayMinutes));
    }

    private function getRetryAfter(string $key): int
    {
        $ttl = cache()->getStore()->getRedis()->ttl($key);
        return max(0, $ttl);
    }
}
