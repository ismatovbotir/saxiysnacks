<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only staff accounts with an email address may use the back office; anyone else is signed out.
 */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->canUseBackOffice() === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', __('This account cannot open the back office.'));
        }

        return $next($request);
    }
}
