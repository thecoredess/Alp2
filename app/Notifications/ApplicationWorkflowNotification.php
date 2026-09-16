<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\User;
use App\Support\NotificationTarget;
use App\Support\NotificationTemplates;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationWorkflowNotification extends Notification
{
    public function __construct(
        public readonly Application $application,
        public readonly string $event,
        public readonly string $message,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable instanceof User && NotificationTemplates::mailEnabledFor($this->event, $notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        $app = $this->application;
        $target = NotificationTarget::for($this->toArray($notifiable), $notifiable);
        $actionExtras = [
            '{action_url}' => $target->url,
            '{action_label}' => $target->action,
        ];
        $body = NotificationTemplates::render(
            NotificationTemplates::body($this->event, $notifiable, $this->message),
            $app,
            $notifiable,
            $this->message,
            $actionExtras,
        );
        $subject = NotificationTemplates::render(
            NotificationTemplates::subject($this->event, $notifiable),
            $app,
            $notifiable,
            $this->message,
            $actionExtras,
        );

        return (new MailMessage)
            ->subject('[ALP] '.$subject)
            ->greeting('Assalamualaikum / Salam sejahtera '.$notifiable->name.',')
            ->line($body)
            ->line('No. Permohonan: '.$app->application_number)
            ->line('Tajuk: '.$app->project_title)
            ->action($target->action, $target->url)
            ->line('Sistem ALP DBKL');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'title' => $this->title(),
            'message' => $this->message,
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'url' => route('applications.show', $this->application, false),
        ];
    }

    private function title(): string
    {
        return match ($this->event) {
            'submission_acknowledged' => 'Permohonan berjaya dihantar',
            'submitted' => 'Permohonan dihantar',
            'awaiting_pegawai_jp' => 'Menunggu pengesyoran Pegawai JP',
            'revision_required' => 'Permohonan dikembalikan',
            'awaiting_peraku' => 'Menunggu perakuan',
            'awaiting_pepu' => 'Menunggu kelulusan PEPU',
            'approved' => 'Permohonan diluluskan',
            'awaiting_payment' => 'Sedia untuk proses bayaran',
            'rejected' => 'Permohonan ditolak',
            'payment_voucher' => 'Baucar pembayaran',
            'payment_paid' => 'Pembayaran selesai',
            'report_card_reminder' => 'Peringatan laporan aktiviti',
            'report_card_reminder_upcoming' => 'Peringatan laporan aktiviti (7 hari lagi)',
            'report_card_reminder_overdue' => 'Peringatan laporan aktiviti (tertunggak)',
            'report_card_submitted' => 'Laporan aktiviti menunggu semakan',
            'report_card_awaiting_pegawai_jp' => 'Laporan menunggu pengesahan Pegawai JP',
            'report_card_approved' => 'Laporan aktiviti disahkan',
            'report_card_returned' => 'Laporan aktiviti dikembalikan',
            default => NotificationTemplates::defaultSubject($this->event),
        };
    }
}
