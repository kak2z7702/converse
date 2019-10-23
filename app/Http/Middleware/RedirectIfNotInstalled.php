<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Storage;

class RedirectIfNotInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Storage::exists('installed') && $request->path() != 'welcome' && $request->path() != 'install') 
            return redirect()->route('welcome');

        return $next($request);
    }
}
