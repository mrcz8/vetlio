<?php

namespace App\Notifications;

use App\Enums\Icons\PhosphorIcons;
use App\Models\AppointmentRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AppointmentRequestDeniedNotification extends Notification
{
    use Queueable;

    private AppointmentRequest $appointmentRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(AppointmentRequest $appointmentRequest)
    {
        $this->appointmentRequest = $appointmentRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable->email) {
            return ['mail', 'database'];
        }

        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $clinicName = $this->appointmentRequest->organisation->name;
        return (new MailMessage)
            ->error()
            ->subject($clinicName . ' - Appointment Request Denied')
            ->greeting("Hi {$notifiable->first_name},")
            ->line("Unfortunately your appointment request has been denied.")
            ->line("Requested for date: {$this->appointmentRequest->from->format('d.m.Y H:i')}")
            ->line("Patient: {$this->appointmentRequest->patient->name}")
            ->line("Service: {$this->appointmentRequest->service->name}")
            ->line("Location: {$this->appointmentRequest->branch->name}")
            ->lineIf($this->appointmentRequest->reason, "Reason: {$this->appointmentRequest->reason}")
            ->line('Please contact us if you have any questions.')
            ->salutation('Thank you for being with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Your appointment request has been denied')
            ->icon(PhosphorIcons::CalendarX)
            ->body(function () {
                $scheduleAt = $this->appointmentRequest->from->format('H:i');
                $patient = $this->appointmentRequest->patient->name;

                return "Sorry, your appointment request planned at {$scheduleAt} for {$patient} has been denied.";
            })
            ->getDatabaseMessage();
    }

}
