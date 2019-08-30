<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\Traits\ResponseTrait;
use Auth;

class IsAdmin
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user() &&  Auth::user()->isAdmin() ) {
            return $next($request);
        }

        return $this->respondUnauthorized();
    }
}
