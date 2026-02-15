<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply CSP in production or when explicitly enabled
        if (env('APP_ENV') === 'production' || env('CSP_ENABLED', false)) {
            $csp = $this->buildCspHeader();
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // HSTS header (only in production with HTTPS)
        if (env('APP_ENV') === 'production' && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Build the Content Security Policy header value.
     */
    protected function buildCspHeader(): string
    {
        $cdnUrl = env('CDN_URL', '');
        $appUrl = env('APP_URL', '');

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$cdnUrl}",
            "style-src 'self' 'unsafe-inline' {$cdnUrl}",
            "img-src 'self' data: https: {$cdnUrl}",
            "font-src 'self' data: {$cdnUrl}",
            "connect-src 'self' {$appUrl}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', array_filter($directives));
    }
}
