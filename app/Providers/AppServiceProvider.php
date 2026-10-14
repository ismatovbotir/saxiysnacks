<?php

namespace App\Providers;

use App\Services\Telegram\InitDataValidator;
use App\Services\Telegram\TelegramBot;
use App\Support\SiteSettings;
use App\View\Composers\StorefrontComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $this->app->singleton(TelegramBot::class, fn (): TelegramBot => new TelegramBot(
            config('services.telegram.bot_token'),
        ));

        $this->app->scoped(SiteSettings::class);
        $this->app->scoped(StorefrontComposer::class);

        $this->app->singleton(InitDataValidator::class, fn (): InitDataValidator => new InitDataValidator(
            config('services.telegram.bot_token'),
            config('services.telegram.init_data_ttl'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        View::composer(['storefront.*', 'components.storefront.*'], StorefrontComposer::class);

        RateLimiter::for('webapp-writes', fn (Request $request): Limit => Limit::perMinute(10)
            ->by('tg:'.($request->attributes->get('telegram_user')['id'] ?? $request->ip())));

        RateLimiter::for('admin-login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
    }
}
