<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Filament\App\Resources\Invoices\HasInvoiceHeaderActions;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Schemas\ReminderForm;
use App\Filament\App\Tables\RemindersTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class InvoiceReminders extends ManageRelatedRecords
{
    use HasInvoiceHeaderActions;

    protected static string $resource = InvoiceResource::class;

    protected static string $relationship = 'reminders';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::Clock;

    protected static ?string $navigationLabel = 'Reminders';

    protected static ?string $title = 'Reminders';

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->reminders->count();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Invoice: ' . $this->getRecord()->code;
    }

    public function form(Schema $schema): Schema
    {
        return ReminderForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return RemindersTable::configure($table);
    }
}
