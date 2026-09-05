<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // SUPER ADMIN melepasi semua semakan kebenaran.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(RoleName::SUPER_ADMIN->value) ? true : null;
        });

        // Dasar kata laluan minimum: 8 aksara, huruf & nombor.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());
    }
}
