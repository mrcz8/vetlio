<?php

namespace App\Filament\App\Clusters\Setup\Resources\Services\Tables;

use App\Filament\App\Clusters\Setup\Resources\Services\RelationManagers\PricesRelationManager;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use App\Models\ServiceGroup;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with(['serviceGroup', 'users', 'rooms', 'currentPrice']);
            })
            ->columns([
                ColorColumn::make('color')
                    ->label('')
                    ->width('30px'),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->description(fn($record) => $record->code)
                    ->searchable(),

                TextColumn::make('serviceGroup.name')
                    ->label('Group')
                    ->searchable(),

                ToggleColumn::make('active')
                    ->label('Active'),

                TextColumn::make('duration')
                    ->time('H:i')
                    ->label('Duration')
                    ->sortable(),

                TextColumn::make('users.full_name')
                    ->label('Staff'),

                TextColumn::make('rooms.name')
                    ->label('Rooms'),

                TextColumn::make('currentPrice.price')
                    ->money('EUR')
                    ->label('Price'),

                TextColumn::make('currentPrice.vat_percentage')
                    ->numeric()
                    ->suffix('%')
                    ->label('VAT'),

                TextColumn::make('currentPrice.price_with_vat')
                    ->money('EUR')
                    ->weight(FontWeight::Bold)
                    ->label('Price with VAT'),

                CreatedAtColumn::make('created_at'),
                UpdatedAtColumn::make('updated_at'),
            ])
            ->filters([
                SelectFilter::make('service_group_id')
                    ->options(ServiceGroup::pluck('name', 'id'))
                    ->native(false)
                    ->multiple()
                    ->label('Group'),

                SelectFilter::make('users')
                    ->relationship('users', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn(User $record) => $record->full_name)
                    ->native(false)
                    ->multiple()
                    ->label('Staff'),
            ])
            ->recordActions([
                RelationManagerAction::make('lesson-relation-manager')
                    ->label('Prices')
                    ->compact()
                    ->slideOver()
                    ->icon(Heroicon::CurrencyEuro)
                    ->relationManager(PricesRelationManager::make()),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
