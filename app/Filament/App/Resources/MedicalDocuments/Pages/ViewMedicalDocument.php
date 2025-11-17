<?php

namespace App\Filament\App\Resources\MedicalDocuments\Pages;

use App\Filament\App\Resources\MedicalDocuments\HasMedicalDocumentHeaderActions;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewMedicalDocument extends ViewRecord
{
    use HasMedicalDocumentHeaderActions;

    protected static string $resource = MedicalDocumentResource::class;

    protected static ?string $navigationLabel = 'View';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->patient->description;
    }

}
