<?php

namespace App\Filament\App\Actions;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;

class ViewInvoiceAction
{
    public static function make()
    {
        return Action::make('view-invoice')
            ->tooltip('View invoice')
            ->url(function ($record) {
                return InvoiceResource::getUrl('view', ['record' => $record]);
            })
            ->outlined()
            ->label('Invoice')
            ->icon(PhosphorIcons::InvoiceLight);
    }
}
