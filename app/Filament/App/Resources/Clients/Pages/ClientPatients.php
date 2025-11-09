<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Patients\PatientResource;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class ClientPatients extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'patients';

    protected static ?string $relatedResource = PatientResource::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Dog;

    protected static ?string $navigationLabel = 'Patients';

    protected static ?string $title = 'Patients';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->full_name;
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->patients_count;
    }

    public function form(Schema $schema): Schema
    {
        $form = PatientResource::form($schema);

        $form->getComponent('client_id')->disabled();

        return $form;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->fillForm(function ($data) {
                    $data['client_id'] = $this->getRecord()->id;
                    $data['dangerous'] = false;

                    return $data;
                })
        ];
    }

}
