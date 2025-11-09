<?php

namespace App\Filament\App\Resources\Clients\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->width('40px')
                    ->circular()
                    ->label(''),

                TextColumn::make('fullName')
                    ->sortable()
                    ->searchable(true, function ($query, $search) {
                        return $query->where(function ($query) use ($search) {
                            return $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->label('Full name'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon(Heroicon::AtSymbol)
                    ->copyable()
                    ->label('Email'),

                TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->icon(Heroicon::Phone)
                    ->copyable()
                    ->label('Phone'),

                TextColumn::make('full_address')
                    ->sortable()
                    ->label('Address'),

                TextColumn::make('country.name')
                    ->icon(Heroicon::Flag)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Country'),

                SpatieTagsColumn::make('tags')
                    ->label('Tags'),

                TextColumn::make('items_to_pay_sum_total')
                    ->label('Total due')
                    ->default(0)
                    ->alignEnd()
                    ->weight(function ($state) {
                        return $state > 0 ? FontWeight::SemiBold : null;
                    })
                    ->color(function ($state) {
                        return $state > 0 ? 'danger' : null;
                    })
                    ->money('EUR', 100)
                    ->sum('itemsToPay', 'total'),

                TextColumn::make('invoices_sum_total')
                    ->label('Total paid')
                    ->color('success')
                    ->default(0)
                    ->alignRight()
                    ->weight(FontWeight::SemiBold)
                    ->money('EUR', 100)
                    ->sum('invoices', 'total'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
