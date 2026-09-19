<?php

/**
 * Hantar satu contoh setiap templat e-mel notifikasi ke satu alamat untuk semakan reka bentuk.
 * Guna: php scripts/hantar_templat_emel.php <emel> [--hantar]
 * Tanpa --hantar ia hanya menyenaraikan templat (dry run).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Support\MailSettings;
use App\Support\NotificationTemplates;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Mail;

$target = $argv[1] ?? null;
$send = in_array('--hantar', $argv, true);

if (! $target) {
    exit("Sila beri alamat e-mel.\n");
}

MailSettings::applyToConfig();

$application = Application::whereRaw('CHAR_LENGTH(purpose) >= 15')
    ->orderByDesc('id')
    ->first()
    ?? Application::orderByDesc('id')->first();

if (! $application) {
    exit("Tiada permohonan dalam pangkalan data.\n");
}

printf(
    "permohonan contoh : %s — %s\nmailer            : %s via %s\ndari              : %s <%s>\nke                : %s\nmod               : %s\n\n",
    $application->application_number,
    $application->project_title,
    config('mail.default'),
    config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'),
    config('mail.from.name'),
    config('mail.from.address'),
    $target,
    $send ? 'HANTAR' : 'semak sahaja (dry run)'
);

$markdown = app(Markdown::class)->theme((string) config('mail.markdown.theme', 'default'));

$hantar = 0;
$gagal = 0;

foreach (NotificationTemplates::roles() as $role => $roleLabel) {
    $user = User::with('roles')->whereHas('roles', fn ($q) => $q->where('name', $role))->first();

    if (! $user) {
        printf("[%s] TIADA pengguna dengan peranan ini — dilangkau\n", $roleLabel);

        continue;
    }

    foreach (NotificationTemplates::eventsForRole($role) as $event) {
        $notification = new ApplicationWorkflowNotification(
            $application,
            $event,
            NotificationTemplates::defaultBody($event),
        );

        try {
            $mail = $notification->toMail($user);
            $subject = (string) $mail->subject;

            if ($send) {
                $html = (string) $markdown->render('notifications::email', $mail->data());
                $text = (string) $markdown->renderText('notifications::email', $mail->data());

                Mail::send([], [], function ($message) use ($target, $subject, $html, $text): void {
                    $message->to($target)->subject($subject);
                    $message->getSymfonyMessage()->html($html)->text($text);
                });

                usleep(700_000);
            }

            $hantar++;
            printf("  OK  %-38s %-18s %s\n", $event, $role, $subject);
        } catch (\Throwable $e) {
            $gagal++;
            printf("  XX  %-38s %-18s %s\n", $event, $role, $e->getMessage());
        }
    }
}

printf("\nberjaya: %d   gagal: %d\n", $hantar, $gagal);
