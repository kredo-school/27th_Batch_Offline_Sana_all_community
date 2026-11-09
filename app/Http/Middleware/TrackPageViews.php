<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackPageViews
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $contentType = (string) $response->headers->get('Content-Type');

        if (
            $request->isMethod('GET')
            && $response->isSuccessful()
            && str_starts_with($contentType, 'text/html')
        ) {
            DB::table('page_views')->insert([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip() ?? 'unknown',
                'url' => $request->url(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
