<?php

namespace App\Subscribers;

use App\Events\AppointmentRequestApproved;
use App\Events\AppointmentRequestDenied;
use App\Notifications\AppointmentRequestDeniedNotification;
use Illuminate\Events\Dispatcher;

class AppointmentRequestSubscriber
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            AppointmentRequestApproved::class => 'handleRequestApproved',
            AppointmentRequestDenied::class => 'handleRequestDenied',
        ];
    }

    public function handleRequestApproved(AppointmentRequestApproved $event): void
    {
        //Handle event approved...
    }

    public function handleRequestDenied(AppointmentRequestDenied $event): void
    {
        $client = $event->appointmentRequest->client;

        //Notify client request is denied
        $client->notify(new AppointmentRequestDeniedNotification($event->appointmentRequest));
    }
}
