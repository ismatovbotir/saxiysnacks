<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use App\Support\LoginCodes;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Back-office sign-in, two ways: email + password, or phone + a 6-digit code sent by the Telegram bot.
 * Either way only staff accounts that have an email address get in.
 */
class AuthController extends Controller
{
    private const string PHONE_SESSION_KEY = 'admin_login_phone';

    public function create(Request $request): View
    {
        $phone = $request->session()->get(self::PHONE_SESSION_KEY);

        return view('admin.auth.login', [
            'method' => $request->query('via') === 'phone' || $phone ? 'phone' : 'email',
            'codePhone' => $phone,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = [
            ...$request->validated(),
            'role' => fn ($query) => $query->where('role', '!=', UserRole::Client),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        return $this->signedIn($request);
    }

    /**
     * Step 1 of phone sign-in. The answer is the same whether or not the number belongs to anyone,
     * so the form cannot be used to find out who works here.
     */
    public function sendCode(Request $request, LoginCodes $codes): RedirectResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:32']]);
        $phone = Phone::normalize($request->input('phone'));

        if ($phone === null) {
            throw ValidationException::withMessages(['phone' => __('Enter the phone number with the country code, e.g. +998 90 123 45 67.')]);
        }

        $user = User::query()->where('phone', $phone)->first();

        if ($user?->canUseBackOffice()) {
            $codes->send($user);
        }

        $request->session()->put(self::PHONE_SESSION_KEY, $phone);

        return redirect()->route('admin.login', ['via' => 'phone'])->with('status', __('If this number belongs to a staff member with Telegram linked, the bot has sent a 6-digit code.'));
    }

    /**
     * Step 2 of phone sign-in: check the code sent to the number entered in step 1.
     */
    public function verifyCode(Request $request, LoginCodes $codes): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'regex:/^\d{6}$/']], ['code.regex' => __('The code has 6 digits.')]);

        $phone = $request->session()->get(self::PHONE_SESSION_KEY);
        $user = $phone ? User::query()->where('phone', $phone)->first() : null;

        if ($user === null || ! $user->canUseBackOffice() || ! $codes->verify($user, $request->string('code')->value())) {
            throw ValidationException::withMessages(['code' => __('The code is wrong or has expired. Request a new one.')]);
        }

        $request->session()->forget(self::PHONE_SESSION_KEY);
        Auth::login($user, $request->boolean('remember'));

        return $this->signedIn($request);
    }

    /**
     * Start over with another number.
     */
    public function resetPhone(Request $request): RedirectResponse
    {
        $request->session()->forget(self::PHONE_SESSION_KEY);

        return redirect()->route('admin.login', ['via' => 'phone']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function signedIn(Request $request): RedirectResponse
    {
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
