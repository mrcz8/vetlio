<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Payments\Schemas\PaymentForm;
use App\Filament\App\Resources\Payments\Tables\PaymentsTable;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use UnitEnum;

class ClientPayments extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'payments';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::CurrencyEur;

    protected static ?string $title = 'Payments';

    protected static ?string $navigationLabel = 'Payments';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public function getTablePluralModelLabel(): ?string
    {
        return 'payments';
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->payments_count;
    }

    public function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        $table = PaymentsTable::configure($table);

        $table->getColumn('client.full_name')->hidden();

        return $table;
    }
}
