<?php

namespace App\Http\Middleware\Services\AmoCrm;

use App\Exceptions\ForbiddenException;
use App\Models\Services\amoCRM;
use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $amoCrmCredentials = amoCRM::all()->first();

        if (!$amoCrmCredentials) {
            throw new ForbiddenException("Access denied");
        }

        return $next($request);
    }
}
