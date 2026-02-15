<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeForwardedScheme
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! filter_var(env('TRUST_PROXIES', false), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        $forwardedProto = strtolower((string) $request->headers->get('x-forwarded-proto', ''));
        $isHttps = $forwardedProto === 'https';

        if (! $isHttps) {
            $cfVisitor = (string) $request->headers->get('cf-visitor', '');
            $isHttps = str_contains(strtolower($cfVisitor), '"scheme":"https"');
        }

        if ($isHttps) {
            $request->server->set('HTTPS', 'on');
            $request->server->set('REQUEST_SCHEME', 'https');
            $request->server->set('SERVER_PORT', 443);
        }

        return $next($request);
    }
}

