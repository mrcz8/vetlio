<?php

namespace App\Filament\App\Resources\Reservations\Actions;

use App\Enums\Icons\PhosphorIcons;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;

class NewAppointmentAction extends CreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('New appointment');
        $this->modalWidth(Width::SixExtraLarge);
        $this->modalHeading('New appointment');
        $this->modalSubmitActionLabel('Create appointment');
        $this->modalIcon(PhosphorIcons::CalendarPlus);
        $this->color('success');
    }

    public static function getDefaultName(): ?string
    {
        return 'create-appointment-action';
    }
}
