<?php

namespace App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Tables;

use App\Enums\EmailTemplateGroup;
use App\Enums\EmailTemplateType;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name'),

                TextColumn::make('group_id')
                    ->sortable()
                    ->label('Group')
                    ->formatStateUsing(function ($record) {
                        return EmailTemplateGroup::from($record->group_id)->getLabel();
                    })
                    ->badge(),

                TextColumn::make('type_id')
                    ->sortable()
                    ->label('Type')
                    ->formatStateUsing(function ($state) {
                        return EmailTemplateType::from($state)->getLabel();
                    })
                    ->badge(),

                ToggleColumn::make('active')
                    ->sortable()
                    ->label('Active'),

                CreatedAtColumn::make('created_at'),

                UpdatedAtColumn::make('updated_at'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
