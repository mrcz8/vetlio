<?php

namespace App\Services\EmailTags;

use App\Contracts\EmailTagProvider;
use App\Models\Client;
use App\Models\Reservation;

class AppointmentTags implements EmailTagProvider
{
    public function supports(mixed $model): bool
    {
        return $model instanceof Reservation;
    }

    public static function getAvailableTags(): array
    {
        return [
            'appointment.id' => 'Appointment ID',
            'appointment.code' => 'Appointment Code',
        ];
    }

    public function resolve(mixed $model): array
    {
        return [
            'appointment.id' => $model->id,
            'appointment.code' => $model->code,
        ];
    }
}
