<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate(['locale' => ['required', Rule::in(array_keys(SetAdminLocale::LOCALES))]]);

        $request->session()->put('admin_locale', $validated['locale']);
        $request->user()?->update(['locale' => $validated['locale']]);

        return back();
    }
}
