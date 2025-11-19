<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Filament\App\Actions\SendEmailAction;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Clients\Widgets\ClientStats;
use App\Filament\App\Resources\Patients\Actions\NewPatientAction;
use App\Filament\App\Resources\Reservations\Actions\NewAppointmentAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'View client';

    protected static ?string $navigationLabel = 'View client';

    protected function getHeaderWidgets(): array
    {
        return [
            ClientStats::make(['client' => $this->getRecord()])
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            NewAppointmentAction::make(),
            NewPatientAction::make(),
            SendEmailAction::make(),
            EditAction::make(),
        ];
    }
}
