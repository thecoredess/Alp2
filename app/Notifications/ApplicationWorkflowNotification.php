<?php

namespace App\Notifications;

use App\Models\Application;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = $this->application;

        return (new MailMessage)
            ->subject('[ALP] '.$this->title())
            ->greeting('Assalamualaikum / Salam sejahtera,')
            ->line($this->message)
            ->line('No. Permohonan: '.$app->application_number)
            ->line('Tajuk: '.$app->project_title)
            ->action('Buka Permohonan', route('applications.show', $app))
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
            'submitted' => 'Permohonan dihantar',
            'revision_required' => 'Permohonan dikembalikan',
            'awaiting_peraku' => 'Menunggu perakuan',
            'awaiting_pepu' => 'Menunggu kelulusan PEPU',
            'approved' => 'Permohonan diluluskan',
            'awaiting_payment' => 'Sedia untuk proses bayaran',
            'rejected' => 'Permohonan ditolak',
            'payment_voucher' => 'Baucar pembayaran',
            'payment_paid' => 'Pembayaran selesai',
            'report_card_reminder' => 'Peringatan laporan aktiviti',
            'report_card_submitted' => 'Laporan aktiviti menunggu semakan',
            'report_card_awaiting_pegawai_jp' => 'Laporan menunggu pengesahan Pegawai JP',
            'report_card_approved' => 'Laporan aktiviti disahkan',
            'report_card_returned' => 'Laporan aktiviti dikembalikan',
            default => 'Kemaskini permohonan',
        };
    }
}
