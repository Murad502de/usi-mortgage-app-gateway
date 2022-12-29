<?php

namespace App\Http\Middleware\Services\AmoCrm;

use App\Traits\Http\Middleware\Services\AmoCrm\amoTokenTrait;
use Closure;
use Illuminate\Http\Request;
use App\Exceptions\ForbiddenException;

class AmoTokenExpirationControl
{
    use amoTokenTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!self::amoToken()) {
            throw new ForbiddenException("Access denied");
        }

        return $next($request);
    }
}
