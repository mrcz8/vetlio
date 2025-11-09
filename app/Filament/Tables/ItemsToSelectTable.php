<?php

namespace App\Filament\Tables;

use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ItemsToSelectTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->query(Service::query()->whereHas('currentPrice'))
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->label(__('tables.items_to_select.columns.code')),

                TextColumn::make('name')
                    ->label(__('tables.items_to_select.columns.name'))
                    ->searchable(),

                TextColumn::make('serviceGroup.name')
                    ->label(__('tables.items_to_select.columns.group'))
                    ->searchable(),

                TextColumn::make('currentPrice.price')
                    ->money('EUR')
                    ->numeric(2)
                    ->alignRight()
                    ->label(__('tables.items_to_select.columns.price')),

                TextColumn::make('currentPrice.vat_percentage')
                    ->numeric(2)
                    ->alignRight()
                    ->suffix('%')
                    ->label(__('tables.items_to_select.columns.vat')),

                TextColumn::make('currentPrice.price_with_vat')
                    ->alignRight()
                    ->money('EUR')
                    ->weight(FontWeight::Bold)
                    ->label(__('tables.items_to_select.columns.price_with_vat')),
            ])
            ->filters([
                SelectFilter::make('service_group_id')
                    ->label(__('tables.items_to_select.filters.group'))
                    ->relationship('serviceGroup', 'name'),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
