<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS if configured to do so
        if (config('https.force_https') && !$request->isSecure() && app()->environment('production')) {
            // Log the redirect for monitoring
            \Log::info('HTTP to HTTPS redirect', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        // Add security headers for HTTPS
        if ($request->isSecure() || config('https.force_https')) {
            $response = $this->addSecurityHeaders($response);
        }

        return $response;
    }

    /**
     * Add security headers to the response
     *
     * @param Response $response
     * @return Response
     */
    private function addSecurityHeaders(Response $response): Response
    {
        // HTTP Strict Transport Security (HSTS)
        if (config('https.hsts.enabled')) {
            $hstsValue = 'max-age=' . config('https.hsts.max_age');
            
            if (config('https.hsts.include_subdomains')) {
                $hstsValue .= '; includeSubDomains';
            }
            
            if (config('https.hsts.preload')) {
                $hstsValue .= '; preload';
            }
            
            $response->headers->set('Strict-Transport-Security', $hstsValue);
        }

        // Upgrade insecure requests
        if (config('https.upgrade_insecure_requests')) {
            $response->headers->set('Content-Security-Policy', 'upgrade-insecure-requests');
        }

        // Secure referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
