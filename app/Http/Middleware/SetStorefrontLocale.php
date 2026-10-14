<?php

namespace App\Http\Middleware;

use App\Support\Storefront;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetStorefrontLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        app()->setLocale($route->parameter('locale', Storefront::DEFAULT_LOCALE));
        $route->forgetParameter('locale');

        return $next($request);
    }
}
