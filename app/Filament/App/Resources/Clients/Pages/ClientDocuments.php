<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Schemas\DocumentForm;
use App\Filament\App\Tables\DocumentsTable;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ClientDocuments extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'documents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaperClip;

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $title = 'Documents';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
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
