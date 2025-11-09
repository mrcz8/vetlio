<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ClientInvoices extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'invoices';

    protected static ?string $relatedResource = InvoiceResource::class;

    protected static ?string $navigationLabel = 'Invoices';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $title = 'Invoices';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Money;

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->url(function() {
                return InvoiceResource::getUrl('create', ['client' => $this->getRecord()->id]);
            })
        ];
    }
}
