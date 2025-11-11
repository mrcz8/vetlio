<?php

namespace App\Enums;

use App\Enums\Icons\PhosphorIcons;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AppointmentRequestStatus: int implements HasLabel, HasColor, HasIcon
{
    case Pending = 1;
    case Approved = 2;
    case Denied = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending request',
            self::Approved => 'Approved',
            self::Denied => 'Denied',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Denied => 'danger',
        };
    }

    public function getIcon(): \BackedEnum|string|null
    {
        return match ($this) {
            self::Pending => PhosphorIcons::Spinner,
            self::Approved => PhosphorIcons::CheckCircleBold,
            self::Denied => PhosphorIcons::XCircleBold,
        };
    }
}
