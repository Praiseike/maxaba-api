<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LastSeenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request first
        $response = $next($request);

        // Only track for authenticated users and successful requests
        if (Auth::check() && $response->getStatusCode() < 400) {
            $user = Auth::user();
            $userId = $user->id;
            $now = now();
            
            // Cache key for tracking last update
            $cacheKey = "last_seen_updated_{$userId}";
            
            // Only update if we haven't updated in the last 2 minutes (reduces DB calls)
            if (!Cache::has($cacheKey)) {
                if (config('queue.default') !== 'sync') {
                    dispatch(function () use ($userId, $now) {
                        DB::table('users')
                            ->where('id', $userId)
                            ->update(['last_seen_at' => $now]);
                    })->onQueue('low');
                } else {
                    // Direct database update
                    DB::table('users')
                        ->where('id', $userId)
                        ->update(['last_seen_at' => $now]);
                }
                

                Cache::put($cacheKey, true, now()->addMinutes(2));
            }
        }

        return $response;
    }
}