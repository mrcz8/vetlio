<?php

namespace App\Filament\App\Resources\MedicalDocuments\Schemas;

use App\Filament\App\Actions\ViewInvoiceAction;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class MedicalDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SimpleAlert::make('locked')
                    ->danger()
                    ->visible(fn($record) => $record->locked_at)
                    ->border()
                    ->columnSpanFull()
                    ->action(
                        Action::make('unlock')
                            ->action(fn($record) => $record->update([
                                'locked_at' => null,
                                'locked_user_id' => null,
                            ]))
                            ->color('danger')
                            ->icon(Heroicon::LockClosed)
                            ->link()
                            ->requiresConfirmation()
                            ->label('Unlock')
                    )
                    ->description(function ($record) {
                        return new HtmlString(
                            "This document was locked on <b>{$record->locked_at->format('d.m.Y H:i')} "
                            . "( {$record->locked_at->diffForHumans()})</b> by employee: "
                            . "<b>{$record->userLocked->full_name}</b>"
                        );
                    }),

                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('content')
                        ->hiddenLabel()
                        ->html()
                    ]),

                RepeatableEntry::make('items')
                    ->columnSpanFull()
                    ->label(fn($record) => 'Items (' . count($record->items ?? []) . ')')
                    ->table([
                        TableColumn::make('Item')->width('300px')->markAsRequired(),
                        TableColumn::make('Quantity')->markAsRequired()->alignEnd(),
                        TableColumn::make('Price')->markAsRequired()->alignEnd(),
                        TableColumn::make('VAT')->alignEnd(),
                        TableColumn::make('Discount')->alignEnd(),
                        TableColumn::make('Total')->alignEnd(),
                        TableColumn::make('Paid')->alignEnd()->width('100px'),
                    ])
                    ->schema([
                        TextEntry::make('priceable.name'),
                        TextEntry::make('quantity')->alignEnd(),
                        TextEntry::make('price')->money('EUR')->alignEnd(),
                        TextEntry::make('tax')->money('EUR')->alignEnd(),
                        TextEntry::make('discount')->money('EUR')->alignEnd(),
                        TextEntry::make('total')
                            ->alignEnd()
                            ->weight(FontWeight::Bold)
                            ->money('EUR'),
                        IconEntry::make('invoice.payed')
                            ->afterContent(function ($record) {
                                return ViewInvoiceAction::make()
                                    ->record($record->invoice)
                                    ->hiddenLabel()
                                    ->visible(fn() => $record->invoice)
                                    ->tooltip(fn() => $record->invoice->code)
                                    ->extraAttributes(['class' => 'mt-1']);
                            })
                            ->alignEnd()
                            ->boolean(),
                    ]),

                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('items_sum_total')
                            ->columnStart(4)
                            ->label('Total amount:')
                            ->alignRight()
                            ->inlineLabel()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->sum('items', 'total')
                            ->money('EUR', 100),
                    ]),
            ]);
    }
}
