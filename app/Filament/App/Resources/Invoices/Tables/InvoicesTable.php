<?php

namespace App\Filament\App\Resources\Invoices\Tables;

use App\Enums\Icons\PhosphorIcons;
use App\Enums\PaymentMethod;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Models\Client;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->icon(function ($record) {
                        return $record->fiscalization_at
                            ? PhosphorIcons::CheckCircleBold
                            : PhosphorIcons::XCircleBold;
                    })
                    ->iconColor(function ($record) {
                        return $record->fiscalization_at ? 'success' : 'danger';
                    })
                    ->tooltip(function ($record) {
                        return $record->fiscalization_at
                            ? 'Invoice successfully fiscalized'
                            : 'Invoice not fiscalized';
                    })
                    ->searchable()
                    ->label('Code'),

                TextColumn::make('branch.name')
                    ->sortable()
                    ->searchable()
                    ->label('Branch'),

                TextColumn::make('client.full_name')
                    ->sortable()
                    ->searchable()
                    ->label('Client')
                    ->icon(PhosphorIcons::User),

                TextColumn::make('invoice_date')
                    ->sortable()
                    ->date()
                    ->label('Invoice date'),

                TextColumn::make('payment_method_id')
                    ->sortable()
                    ->label('Payment method'),

                TextColumn::make('user.full_name')
                    ->sortable()
                    ->searchable()
                    ->label('Created by'),

                TextColumn::make('total_base_price')
                    ->label('Base amount')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null),

                TextColumn::make('total_tax')
                    ->label('VAT amount')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null),

                TextColumn::make('total_discount')
                    ->label('Discount')
                    ->numeric(2)
                    ->sortable()
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric(2)
                    ->sortable()
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null)
                    ->weight(FontWeight::Bold),

                CreatedAtColumn::make('created_at'),
            ])
            ->filters([
                TernaryFilter::make('storno_of_id')
                    ->label('Cancelled')
                    ->nullable(),

                SelectFilter::make('payment_method_id')
                    ->label('Payment method')
                    ->native(false)
                    ->multiple()
                    ->options(PaymentMethod::class),

                SelectFilter::make('user_id')
                    ->multiple()
                    ->label('Created by')
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn(User $record) => $record->full_name)
                    ->native(false),

                SelectFilter::make('client_id')
                    ->multiple()
                    ->label('Client')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn(Client $record) => $record->full_name)
                    ->native(false),
            ], layout: FiltersLayout::Modal)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->slideOver()
                    ->label('Filter'),
            )
            ->recordActions([
                ViewAction::make(),
                ClientCardAction::make(),
            ]);
    }
}
