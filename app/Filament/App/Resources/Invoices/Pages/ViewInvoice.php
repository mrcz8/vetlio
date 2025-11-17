<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Filament\App\Resources\Invoices\HasInvoiceHeaderActions;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewInvoice extends ViewRecord
{
    use HasInvoiceHeaderActions;
    
    protected static string $resource = InvoiceResource::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    protected static ?string $navigationLabel = 'Invoice';

    public function getTitle(): string
    {
        return 'Invoice: ' . $this->getRecord()->code;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Client: ' . $this->getRecord()->client->full_name;
    }

}
