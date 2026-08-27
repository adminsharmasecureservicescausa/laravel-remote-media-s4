<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CertificateAuthentication
{
    /**
     * Authenticate users automatically via TLS Client Certificate if provided.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if already authenticated via session
        if (Auth::check()) {
            return $next($request);
        }

        $sslVerify = $request->server('SSL_CLIENT_VERIFY') ?? $request->header('SSL-Client-Verify');
        $sslSubject = $request->server('SSL_CLIENT_S_DN') ?? $request->header('SSL-Client-S-DN');

        if ($sslVerify === 'SUCCESS' && $sslSubject) {
            $email = null;

            // Extract emailAddress from subject DN (e.g. emailAddress=admin@securestream.local or /emailAddress=...)
            if (preg_match('/(?:emailAddress|email)=([^\/,\s]+)/i', $sslSubject, $matches)) {
                $email = $matches[1];
            } elseif (preg_match('/CN=([^\/,\s]+)/i', $sslSubject, $matches)) {
                $cn = $matches[1];
                if (filter_var($cn, FILTER_VALIDATE_EMAIL)) {
                    $email = $cn;
                }
            }

            if ($email) {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => 'Certificate User (' . $email . ')',
                        'password' => bcrypt(str()->random(32)),
                        'email_verified_at' => now(),
                    ]
                );

                Auth::login($user, true);
            }
        }

        return $next($request);
    }
}
