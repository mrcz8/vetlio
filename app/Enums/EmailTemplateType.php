<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmailTemplateType : int implements HasLabel
{
    case CancelAppointment = 1;
    case NewAppointment = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CancelAppointment => 'Cancel appointment',
            self::NewAppointment => 'New appointment',
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
        };
    }

}
