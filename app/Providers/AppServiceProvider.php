<?php

namespace App\Providers;

use App\Enums\AdminPermission;
use App\Models\User;
use App\Services\Telegram\InitDataValidator;
use App\Services\Telegram\TelegramBot;
use App\Support\RolePermissions;
use App\Support\SiteSettings;
use App\Support\TelegramSettings;
use App\View\Composers\AdminNavigationComposer;
use App\View\Composers\StorefrontComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TelegramSettings::class);
        $this->app->scoped(TelegramBot::class, fn (): TelegramBot => new TelegramBot(
            $this->app->make(TelegramSettings::class)->botToken(),
        ));

        $this->app->scoped(SiteSettings::class);
        $this->app->scoped(RolePermissions::class);
        $this->app->scoped(StorefrontComposer::class);
        $this->app->scoped(AdminNavigationComposer::class);

        $this->app->scoped(InitDataValidator::class, fn (): InitDataValidator => new InitDataValidator(
            $this->app->make(TelegramSettings::class)->botToken(),
            $this->app->make(TelegramSettings::class)->initDataTtl(),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        foreach (AdminPermission::cases() as $permission) {
            Gate::define("access-{$permission->value}", fn (User $user): bool => $user->canAccess($permission));
        }

        View::composer(['storefront.*', 'components.storefront.*'], StorefrontComposer::class);
        View::composer(['components.admin.layout', 'components.admin.page-header'], AdminNavigationComposer::class);

        RateLimiter::for('webapp-writes', fn (Request $request): Limit => Limit::perMinute(10)
            ->by('tg:'.($request->attributes->get('telegram_user')['id'] ?? $request->ip())));

        RateLimiter::for('webapp-list', fn (Request $request): Limit => Limit::perMinute(60)
            ->by('tg:'.($request->attributes->get('telegram_user')['id'] ?? $request->ip())));

        RateLimiter::for('website-orders', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('admin-login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email', $request->session()->get('admin_login_phone'))).'|'.$request->ip()));

        RateLimiter::for('admin-login-code', fn (Request $request): array => [
            Limit::perMinute(3)->by('code-ip:'.$request->ip()),
            Limit::perHour(10)->by('code-phone:'.preg_replace('/\D/', '', (string) $request->input('phone'))),
        ]);
    }
}
