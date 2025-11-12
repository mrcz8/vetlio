<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmailTemplateGroup: int implements HasLabel
{
    case Appointments = 1;
    case Clients = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Appointments => 'Appointments',
            self::Clients => 'Clients',
        };
    }

}
