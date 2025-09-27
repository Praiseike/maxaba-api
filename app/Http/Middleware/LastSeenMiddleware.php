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

        if (Auth::check() && $response->getStatusCode() < 400) {

            if (Auth::guard('admin')->check()) {
                // return $response;
            }

            $user = Auth::user();

            $userId = $user->id;
            $now = now();

            $cacheKey = "last_seen_updated_{$userId}";

            // \Log::info("Skipping last seen update for user {$userId}, updated recently.");
            // Direct database update
            DB::table('users')
                ->where('id', $userId)
                ->update(['last_seen_at' => $now]);
            // Only update if we haven't updated in the last 2 minutes (reduces DB calls)
            // if (Cache::has($cacheKey)) {


            // } else
            //     Cache::put($cacheKey, true, now()->addMinutes(2));
        }

        return $response;
    }
}