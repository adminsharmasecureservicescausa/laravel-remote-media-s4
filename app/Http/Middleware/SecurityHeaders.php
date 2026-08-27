<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach enterprise-grade security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $awsBucket = config('filesystems.disks.s3.bucket');
        $awsRegion = config('filesystems.disks.s3.region', 'us-east-1');
        $awsEndpoint = config('filesystems.disks.s3.endpoint');
        
        $s3Origins = "https://*.amazonaws.com https://*.s3.amazonaws.com https://*.s3.{$awsRegion}.amazonaws.com";
        if ($awsBucket) {
            $s3Origins .= " https://{$awsBucket}.s3.amazonaws.com https://{$awsBucket}.s3.{$awsRegion}.amazonaws.com";
        }
        if ($awsEndpoint) {
            $endpointHost = parse_url($awsEndpoint, PHP_URL_HOST) ?: $awsEndpoint;
            $s3Origins .= " https://{$endpointHost} https://*.{$endpointHost}";
        }

        // Strict Content Security Policy (CSP)
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net",
            "img-src 'self' data: https: blob:",
            "media-src 'self' blob: data: {$s3Origins}",
            "connect-src 'self' blob: {$s3Origins}",
            "font-src 'self' https://fonts.bunny.net data:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
