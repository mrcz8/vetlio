<?php

namespace App\Services\EmailTemplate\Tags;

use App\Contracts\EmailTagProvider;
use App\Models\Patient;
use App\Models\Reservation;

class PatientTags implements EmailTagProvider
{
    public function supports(mixed $model): bool
    {
        return $model instanceof Patient;
    }

    public static function getAvailableTags(): array
    {
        return [
            'patient.id' => 'Patient ID',
            'patient.code' => 'Patient Name',
        ];
    }

    public function resolve(mixed $model): array
    {
        return [
            'patient.id' => $model->id,
            'patient.name' => $model->name,
        ];
    }
}
