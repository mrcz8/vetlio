<?php

namespace App\Filament\Portal\Resources\Patients\Tables;

use App\Enums\PatientGender;
use App\Models\Patient;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
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
                    ->formatStateUsing(function ($state) {
                        return PatientGender::from($state)->getLabel();
                    })
                    ->searchable()
                    ->label('Gender'),

                TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date()
                    ->sortable()
                    ->description(function ($state) {
                        if ($state != null) {
                            return Carbon::parse($state)->diffInYears(now()) . ' years old';
                        }

                        return null;
                    }),

                TextColumn::make('remarks')
                    ->searchable()
                    ->label('Notes'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
