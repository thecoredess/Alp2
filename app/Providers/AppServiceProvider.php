<?php

namespace App\Providers;

use App\Support\MailSettings;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
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

        // Kegagalan DB di sini tidak boleh melumpuhkan boot aplikasi / artisan.
        try {
            if (Schema::hasTable('system_settings')) {
                MailSettings::applyToConfig();
            }
        } catch (\Throwable) {
            // Fallback: konfigurasi mail dalam .env kekal digunakan.
        }

        $this->embedMailLogo();
    }

    /**
     * Lekapkan logo DBKL pada setiap e-mel yang merujuk cid:alp-logo.
     *
     * Dilakukan di sini kerana komponen Blade (mail::header) mempunyai skop
     * tersendiri dan tidak dapat mencapai objek Message untuk memanggil embed().
     */
    private function embedMailLogo(): void
    {
        Event::listen(function (MessageSending $event): void {
            $html = (string) $event->message->getHtmlBody();

            if (! str_contains($html, 'cid:alp-logo')) {
                return;
            }

            $logo = public_path('images/logo-dbkl-email.png');

            if (is_file($logo)) {
                $event->message->embedFromPath($logo, 'alp-logo');
            }
        });
    }
}
