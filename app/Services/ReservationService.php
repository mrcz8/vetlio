<?php

namespace App\Services;

use App\Enums\AppointmentRequestStatus;
use App\Events\AppointmentRequestApproved;
use App\Events\AppointmentRequestDenied;
use App\Models\AppointmentRequest;
use App\Models\Reservation;
use App\Notifications\AppointmentCanceledNotification;

class ReservationService
{
    public function cancel(Reservation $reservation, $cancelReasonId, $sendEmail = false): void
    {
        if ($reservation->canceled_at) return;

        $reservation->update([
            'canceled_at' => now(),
            'cancel_reason_id' => $cancelReasonId,
        ]);

        //Alert service provider
        $reservation->serviceProvider->notify(new AppointmentCanceledNotification($reservation));

        //Send email to client
        if ($sendEmail) {
            $reservation->client->notify(new AppointmentCanceledNotification($reservation));
        }
    }

    public function approveRequest(AppointmentRequest $appointmentRequest, ?string $note = null): void
    {
        $appointmentRequest->update([
            'approval_status_id' => AppointmentRequestStatus::Approved->value,
            'approval_by' => auth()->id(),
            'approval_at' => now(),
            'approval_note' => $note
        ]);

        //Create appointment

        event(new AppointmentRequestApproved($appointmentRequest));
    }

    public function denyRequest(AppointmentRequest $appointmentRequest, ?string $note = null): void
    {
        $appointmentRequest->update([
            'approval_status_id' => AppointmentRequestStatus::Denied->value,
            'approval_by' => auth()->id(),
            'approval_at' => now(),
            'approval_note' => $note
        ]);

        event(new AppointmentRequestDenied($appointmentRequest));
    }
}
