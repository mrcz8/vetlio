<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Schemas\NoteForm;
use App\Filament\App\Tables\NotesTable;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class ClientNotes extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'notes';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Note;

    protected static ?string $navigationLabel = 'Notes';

    protected static ?string $title = 'Notes';

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->notes_count;
    }

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
        return NoteForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return NotesTable::configure($table);
    }
}
