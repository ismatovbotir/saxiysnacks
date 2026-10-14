<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel language: the signed-in admin's choice, else the choice made on the login page, else Uzbek.
 */
class SetAdminLocale
{
    public const string DEFAULT_LOCALE = 'uz';

    /**
     * @var array<string, string>
     */
    public const array LOCALES = ['uz' => "O'zbekcha", 'ru' => 'Русский', 'en' => 'English'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?? $request->session()->get('admin_locale');

        app()->setLocale(array_key_exists((string) $locale, self::LOCALES) ? $locale : self::DEFAULT_LOCALE);

        return $next($request);
    }
}
