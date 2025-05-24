<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log security headers application for monitoring
        \Log::debug('Security headers applied', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->check() ? auth()->id() : 'guest'
        ]);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Add Strict-Transport-Security header for HTTPS
        if (!app()->environment('local')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Enhanced Content Security Policy with Payment Gateway Support
        $cspDirectives = [
            "default-src" => "'self'",
            "script-src" => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://js.stripe.com https://checkout.stripe.com",
            "style-src" => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "img-src" => "'self' data: https://images.unsplash.com https://via.placeholder.com https://*.stripe.com",
            "font-src" => "'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "connect-src" => "'self' https://api.stripe.com https://api.paymongo.com",
            "frame-src" => "'self' https://js.stripe.com https://hooks.stripe.com",
            "object-src" => "'none'",
            "base-uri" => "'self'",
            "form-action" => "'self'",
            "frame-ancestors" => "'none'",
        ];

        $cspHeader = '';
        foreach ($cspDirectives as $directive => $value) {
            $cspHeader .= $directive . ' ' . $value . '; ';
        }

        $response->headers->set('Content-Security-Policy', trim($cspHeader));

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        return $response;
    }
}
