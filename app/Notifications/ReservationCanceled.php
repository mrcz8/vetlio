<?php

namespace App\Notifications;

use App\Enums\EmailTemplateType;
use App\Enums\Icons\PhosphorIcons;
use App\Models\Reservation;
use App\Models\User;
use App\Services\EmailTemplate\EmailTemplateRenderer;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCanceled extends Notification
{
    use Queueable;

    private Reservation $reservation;

    private ?array $templateContent = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;

        $this->templateContent = $this->getEmailTemplateContent();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof User) return ['database'];

        if (!$notifiable->email) return ['database'];

        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): ?MailMessage
    {
        return (new MailMessage)
            ->subject($this->templateContent['subject'])
            ->markdown('emails.generic', [
                'body' => $this->templateContent['body'],
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if (!$notifiable instanceof User) return [];

        if($this->templateContent === null) return [];

        return FilamentNotification::make()
            ->title($this->templateContent['subject'])
            ->icon(PhosphorIcons::CalendarX)
            ->body($this->templateContent['body'])
            ->getDatabaseMessage();
    }

    private function getEmailTemplateContent(): ?array
    {
        $branch = $this->reservation->branch;

        return EmailTemplateRenderer::make()
            ->for(EmailTemplateType::CancelAppointment)
            ->withContext([
                'branch' => $branch,
                'client' => $this->reservation->client,
                'organisation' => $this->reservation->organisation,
                'appointment' => $this->reservation,
            ])
            ->forBranch($branch->id)
            ->resolve();
    }
}
