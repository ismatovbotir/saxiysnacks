<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Local development convenience: opens the admin panel without the login form.
 * Active only when APP_ENV=local AND ADMIN_DEV_AUTO_LOGIN=true.
 */
class LoginAdminInDevelopment
{
    public function handle(Request $request, Closure $next): Response
    {
        if (self::isEnabled() && Auth::guest()) {
            $admin = User::query()->where('email', config('auth.default_admin.email'))->first()
                ?? User::query()->where('role', UserRole::Owner)->oldest('id')->first();

            if ($admin !== null) {
                Auth::login($admin);
            }
        }

        return $next($request);
    }

    public static function isEnabled(): bool
    {
        return app()->environment('local') && config('auth.dev_auto_login') === true;
    }
}
