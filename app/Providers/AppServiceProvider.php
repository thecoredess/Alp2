<?php

namespace App\Providers;

use App\Support\MailSettings;
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
    }
}
