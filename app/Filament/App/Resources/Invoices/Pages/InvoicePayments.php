<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Invoices\HasInvoiceHeaderActions;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Resources\Payments\Schemas\PaymentForm;
use App\Filament\App\Resources\Payments\Tables\PaymentsTable;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Number;
use Livewire\Livewire;

class InvoicePayments extends ManageRelatedRecords
{
    use HasInvoiceHeaderActions;

    protected static string $resource = InvoiceResource::class;

    protected static string $relationship = 'payments';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::CurrencyEur;

    protected static ?string $title = 'Payments for invoice';

    protected static ?string $navigationLabel = 'Payments';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Invoice: ' . $this->getRecord()->code;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return intval(self::getNavigationBadge() > 0) ? 'success' : 'danger';
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->payments->count();
    }

    public function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        $table = PaymentsTable::configure($table);

        $table->getColumn('invoice.code')->hidden();

        return $table;
    }
}
