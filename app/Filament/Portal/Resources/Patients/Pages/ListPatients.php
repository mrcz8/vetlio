<?php

namespace App\Filament\Portal\Resources\Patients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\Portal\Resources\Patients\PatientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage your pets’ profiles and stay informed about their health, treatments, and visits.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function ($data) {
                    $data['client_id'] = auth()->id();

                    return $data;
                })
                ->modalHeading('Add new pet')
                ->icon(PhosphorIcons::Dog)
                ->modalIcon(PhosphorIcons::Dog)
                ->label('Add new pet'),
        ];
    }
}
