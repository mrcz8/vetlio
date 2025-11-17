<?php

namespace App\Filament\App\Resources\Reservations\Actions;

use App\Filament\App\Resources\Reservations\Schemas\ReservationForm;
use Filament\Actions\EditAction;

class EditAppointmentAction extends EditAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->schema(function ($schema) {
            return ReservationForm::configure($schema)
                ->columns(2);
        });
        $this->disabled(function ($record) {
            return $record->is_canceled || !$record->status_id->isOrdered();
        });

    }
}
