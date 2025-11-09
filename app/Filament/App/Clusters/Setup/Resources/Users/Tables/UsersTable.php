<?php

namespace App\Filament\App\Clusters\Setup\Resources\Users\Tables;

use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label(''),

                TextColumn::make('code')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Code'),

                TextColumn::make('fullName')
                    ->description(function (User $record) {
                        return $record->titule;
                    })
                    ->label('User'),

                TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),

                ToggleColumn::make('active')
                    ->label('Active'),

                IconColumn::make('service_provider')
                    ->boolean()
                    ->label('Veterinarian')
                    ->alignCenter(),

                TextColumn::make('primaryBranch.name')
                    ->label('Primary Branch'),

                CreatedAtColumn::make('created_at'),

                UpdatedAtColumn::make('updated_at'),
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
