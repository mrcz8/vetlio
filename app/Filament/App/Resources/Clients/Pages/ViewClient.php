<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Filament\App\Actions\SendEmailAction;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Clients\Widgets\ClientStats;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use function Spatie\LaravelPdf\Support\pdf;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'View client';

    protected static ?string $navigationLabel = 'View client';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->full_name;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClientStats::make(['client' => $this->getRecord()])
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            SendEmailAction::make(),
            EditAction::make(),
        ];
    }
}
