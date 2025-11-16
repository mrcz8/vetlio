<?php

namespace App\Filament\App\Resources\MedicalDocuments\Tables;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MedicalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('locked_at')
                    ->label('')
                    ->tooltip(function($record) {
                        return "Locked at: {$record->locked_at->format('d.m.Y H:i')} by {$record->userLocked->full_name}";
                    })
                    ->width('40px')
                    ->icon(function($record) {
                        return $record->locked_at ? Heroicon::LockClosed : null;
                    }),

                TextColumn::make('code')
                    ->label('Code')
                    ->iconColor('danger')
                    ->searchable(),

                TextColumn::make('reservation.from')
                    ->sortable()
                    ->dateTime('d.m.Y H:i')
                    ->label('Reservation'),

                TextColumn::make('patient.name')
                    ->label('Patient')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('client.full_name')
                    ->label('Client')
                    ->sortable()
                    ->searchable(['first_name', 'last_name',]),

                TextColumn::make('reason_for_coming')
                    ->label('Reason for visit')
                    ->searchable(),

                TextColumn::make('serviceProvider.full_name')
                    ->label('Doctor')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('items_sum_total')
                    ->money('EUR', 100)
                    ->label('Total')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->sum('items', 'total'),

                CreatedAtColumn::make('created_at'),
                UpdatedAtColumn::make('updated_at'),
            ])
            ->recordActions([
                ViewAction::make(),
                ClientCardAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(auth()->user()->administrator),
            ]);
    }
}
