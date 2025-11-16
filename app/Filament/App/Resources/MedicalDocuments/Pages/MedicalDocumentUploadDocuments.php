<?php

namespace App\Filament\App\Resources\MedicalDocuments\Pages;

use App\Filament\App\Resources\MedicalDocuments\HasHeaderActions;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use App\Filament\App\Schemas\DocumentForm;
use App\Filament\App\Tables\DocumentsTable;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class MedicalDocumentUploadDocuments extends ManageRelatedRecords
{
    use HasHeaderActions;

    protected static string $resource = MedicalDocumentResource::class;

    protected static string $relationship = 'documents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaperClip;

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $title = 'Documents';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->patient->description;
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->documents_count;
    }

    public function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }
}
