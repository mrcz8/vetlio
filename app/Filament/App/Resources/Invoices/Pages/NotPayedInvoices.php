<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use BackedEnum;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class NotPayedInvoices extends ListInvoices
{
    protected static string $resource = InvoiceResource::class;

    protected string $view = 'filament.app.resources.invoices.pages.not-payed-invoices';

    protected static ?string $navigationLabel = 'Unpaid invoices';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::MoneyWavyLight;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationParentItem = 'Invoices';

    public function getSubheading(): string|Htmlable|null
    {
        return 'A list of all unpaid invoices';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([])
            ->emptyStateActions([])
            ->modifyQueryUsing(fn($query) => $query->notPayed());
    }
}
