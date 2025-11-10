<?php

namespace App\Filament\Portal\Resources\Patients\Tables;

use App\Models\Patient;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->width('40px')
                    ->grow(false)
                    ->circular(),

                TextColumn::make('name')
                    ->sortable()
                    ->description(function (Patient $record) {
                        return $record->breed->name . ', ' . $record->species->name;
                    })
                    ->searchable()
                    ->label('Name'),

                TextColumn::make('gender_id')
                    ->sortable()
                    ->searchable()
                    ->label('Gender'),

                TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date()
                    ->sortable()
                    ->description(function ($state) {
                        if ($state != null) {
                            return Carbon::parse($state)->age . ' years old';
                        }

                        return null;
                    }),

                TextColumn::make('remarks')
                    ->searchable()
                    ->label('Remarks'),

                TextColumn::make('allergies')
                    ->label('Allergies')
                    ->badge()
                    ->color('danger'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
