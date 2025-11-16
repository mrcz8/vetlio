<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmailTemplateType : int implements HasLabel
{
    case CancelAppointment = 1;
    case NewAppointment = 2;
    case SendMedicalDocument = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CancelAppointment => 'Cancel appointment',
            self::NewAppointment => 'New appointment',
            self::SendMedicalDocument => 'Send medical document',
        };
    }

    public function groupId(): int
    {
        return $this->group()['id'];
    }

    public function groupLabel(): string
    {
        return $this->group()['label'];
    }

    public function group(): array
    {
        return match ($this) {
            self::NewAppointment,
            self::CancelAppointment => ['id' => 1, 'label' => 'Appointment'],
            self::SendMedicalDocument => ['id' => 3, 'label' => 'Medical Documents'],
        };
    }

}
