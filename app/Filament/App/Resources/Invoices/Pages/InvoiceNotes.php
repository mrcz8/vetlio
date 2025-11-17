<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Invoices\HasInvoiceHeaderActions;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Schemas\NoteForm;
use App\Filament\App\Tables\NotesTable;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class InvoiceNotes extends ManageRelatedRecords
{
    use HasInvoiceHeaderActions;

    protected static string $resource = InvoiceResource::class;

    protected static string $relationship = 'notes';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Note;

    protected static ?string $navigationLabel = 'Notes';

    protected static ?string $title = 'Notes';

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->notes->count();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Invoice: ' . $this->getRecord()->code;
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
