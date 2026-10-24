<?php

namespace App\Providers;

use App\Support\MailSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dasar kata laluan minimum: 8 aksara, huruf & nombor.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        if (Schema::hasTable('system_settings')) {
            MailSettings::applyToConfig();
        }

        RateLimiter::for('association-public-read', function (Request $request) {
            $token = hash('sha256', (string) $request->route('token'));

            return Limit::perMinute(45)->by($request->ip().'|'.$token);
        });

        RateLimiter::for('association-public-suggest', function (Request $request) {
            $token = hash('sha256', (string) $request->route('token'));

            return Limit::perMinute(25)->by($request->ip().'|'.$token);
        });

        RateLimiter::for('association-public-submit', function (Request $request) {
            $token = hash('sha256', (string) $request->route('token'));

            return [
                Limit::perMinute(8)->by($request->ip()),
                Limit::perHour(25)->by('assoc-submit|'.$token),
            ];
        });

        RateLimiter::for('association-public-upload', function (Request $request) {
            $token = hash('sha256', (string) $request->route('token'));

            return [
                Limit::perMinute(12)->by($request->ip()),
                Limit::perHour(40)->by('assoc-upload|'.$token),
            ];
        });
    }
}
