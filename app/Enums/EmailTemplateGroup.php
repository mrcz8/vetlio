<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmailTemplateGroup: int implements HasLabel
{
    case Appointments = 1;
    case Clients = 2;
    case MedicalDocuments = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Appointments => 'Appointments',
            self::Clients => 'Clients',
            self::MedicalDocuments => 'Medical Documents',
        };
    }

}
