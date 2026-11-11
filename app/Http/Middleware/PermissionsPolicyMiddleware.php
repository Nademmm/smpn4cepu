<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionsPolicyMiddleware
{
    /**
     * Izinkan akses kamera dan sensor perangkat untuk fitur pemindai QR Code sirkulasi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Permissions-Policy', 'camera=*, microphone=*');
        $response->headers->set('Feature-Policy', "camera '*'; microphone '*'");

        return $response;
    }
}
