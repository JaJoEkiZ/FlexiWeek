<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->timezone) {
            $timezone = auth()->user()->timezone;

            // Ajustar el timezone para esta petición
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
            // Carbon detecta automáticamente el timezone del sistema
        }

        return $next($request);
    }
}
