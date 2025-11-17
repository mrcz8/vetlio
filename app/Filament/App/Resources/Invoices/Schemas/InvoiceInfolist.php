<?php

namespace App\Filament\App\Resources\Invoices\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                static::canceledInvoiceAlert(),
                Section::make()
                    ->contained()
                    ->schema([
                        Grid::make(3)
                            ->gap(false)
                            ->schema([
                                static::getOrganisationInformationBlock(),
                                static::getClientInformationBlock()
                                    ->columnStart(3),
                                static::invoiceInformation(),
                            ]),

                        RepeatableEntry::make('invoiceItems')
                            ->label('Invoice items')
                            ->table([
                                TableColumn::make('#')
                                    ->width('50px')
                                    ->alignCenter(),
                                TableColumn::make('Item'),
                                TableColumn::make('Quantity')
                                    ->alignEnd(),
                                TableColumn::make('Price')
                                    ->alignEnd(),
                                TableColumn::make('Total')
                                    ->alignEnd(),
                            ])
                            ->schema([
                                TextEntry::make('id')
                                    ->alignCenter()
                                    ->html(),
                                TextEntry::make('name')
                                    ->html()
                                    ->formatStateUsing(function (InvoiceItem $record) {
                                        return str(new HtmlString("{$record->name}"))
                                            ->append('</br>' . $record->description);
                                    }),
                                TextEntry::make('quantity')
                                    ->alignEnd(),
                                TextEntry::make('price')
                                    ->money('EUR')
                                    ->alignEnd(),
                                TextEntry::make('total')
                                    ->money('EUR')
                                    ->weight(FontWeight::Bold)
                                    ->alignEnd(),
                            ]),

                        Grid::make(3)
                            ->columnSpanFull()
                            ->schema([
                                ImageEntry::make('qrcode')
                                    ->hiddenLabel()
                                    ->columnSpan(1)
                                    ->label('QR Code'),
                                static::invoiceTotals(),
                            ]),

                        TextEntry::make('terms_and_conditions')
                            ->label(new HtmlString('<span class="font-bold">Terms and conditions:</span>'))
                            ->visible(fn(Invoice $record) => $record->terms_and_conditions)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function getOrganisationInformationBlock()
    {
        return Grid::make(1)
            ->gap(false)
            ->schema([
                TextEntry::make('code')
                    ->extraAttributes(['class' => 'mb-2'])
                    ->hiddenLabel()
                    ->weight(FontWeight::Bold)
                    ->color('info')
                    ->size(TextSize::Large),
                TextEntry::make('organisation.name')
                    ->hiddenLabel()
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Small),
                TextEntry::make('organisation.address')
                    ->hiddenLabel()
                    ->size(TextSize::Small),
                TextEntry::make('organisation.city')
                    ->hiddenLabel()
                    ->size(TextSize::Small)
                    ->state(function (Invoice $record) {
                        return $record->organisation->city . ', ' . $record->organisation->zip_code;
                    }),
            ]);
    }

    private static function getClientInformationBlock()
    {
        return Grid::make(1)
            ->gap(false)
            ->schema([
                TextEntry::make('code')
                    ->state('For client')
                    ->hiddenLabel()
                    ->alignEnd()
                    ->weight(FontWeight::Bold),

                TextEntry::make('client.full_name')
                    ->color('info')
                    ->hiddenLabel()
                    ->alignEnd()
                    ->weight(FontWeight::Bold),

                TextEntry::make('client.address')
                    ->hiddenLabel()
                    ->alignEnd()
                    ->size(TextSize::Small),

                TextEntry::make('client.city')
                    ->hiddenLabel()
                    ->alignEnd()
                    ->size(TextSize::Small)
                    ->state(function (Invoice $record) {
                        return $record->client->city . ', ' . $record->client->zip_code;
                    }),
            ]);
    }

    private static function invoiceInformation()
    {
        return Grid::make(1)
            ->columnStart(3)
            ->columnSpan(3)
            ->dense()
            ->schema([
                TextEntry::make('invoice_date')
                    ->label('Invoice date:')
                    ->inlineLabel()
                    ->alignEnd()
                    ->dateTime('d.m.Y H:i')
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('branch.name')
                    ->label('Branch')
                    ->alignRight()
                    ->inlineLabel()
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('payment_method_id')
                    ->label('Payment method')
                    ->alignRight()
                    ->inlineLabel()
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('user.full_name')
                    ->label('Created by:')
                    ->alignRight()
                    ->inlineLabel()
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('zki')
                    ->label('ZKI:')
                    ->alignRight()
                    ->visible(fn(Invoice $record) => $record->fiscalization_at)
                    ->inlineLabel()
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('jir')
                    ->label('JIR:')
                    ->visible(fn(Invoice $record) => $record->fiscalization_at)
                    ->inlineLabel()
                    ->alignRight()
                    ->weight(FontWeight::SemiBold),
            ]);
    }

    private static function canceledInvoiceAlert()
    {
        return SimpleAlert::make('canceled-invoice')
            ->visible(fn($record) => $record->storno_of_id != null)
            ->icon(PhosphorIcons::Invoice)
            ->danger()
            ->border()
            ->title('This is a canceled invoice')
            ->columnSpanFull();
    }

    private static function invoiceTotals()
    {
        return Grid::make(1)
            ->columnSpan(2)
            ->columns(1)
            ->gap(false)
            ->schema([
                TextEntry::make('total_base_price')
                    ->money('EUR')
                    ->label('Base amount')
                    ->alignRight()
                    ->columnStart(3)
                    ->inlineLabel()
                    ->weight(FontWeight::Bold)
                    ->alignEnd(),

                TextEntry::make('total_tax')
                    ->money('EUR')
                    ->label('Total VAT')
                    ->alignRight()
                    ->columnStart(3)
                    ->inlineLabel()
                    ->weight(FontWeight::Bold)
                    ->alignEnd(),

                TextEntry::make('total_discount')
                    ->money('EUR')
                    ->label('Total discount')
                    ->alignRight()
                    ->columnStart(3)
                    ->inlineLabel()
                    ->weight(FontWeight::Bold)
                    ->alignEnd(),

                TextEntry::make('total')
                    ->alignRight()
                    ->inlineLabel()
                    ->columnStart(3)
                    ->size(TextSize::Large)
                    ->money('EUR')
                    ->extraEntryWrapperAttributes([
                        'class' => 'mt-4',
                    ])
                    ->label(new HtmlString('<span class="text-2xl">Grand total:</span>'))
                    ->extraAttributes(['class' => 'text-2xl'])
                    ->weight(FontWeight::Bold),
            ]);
    }
}
