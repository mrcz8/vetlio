<?php

namespace App\Filament\App\Tables;

use App\Models\Document;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->description(fn (Document $record) => $record->description)
                    ->label('Title')
                    ->searchable(),

                TextColumn::make('creator.full_name')
                    ->label('Added by'),

                ToggleColumn::make('visible_in_portal')
                    ->label('Visible in portal'),

                TextColumn::make('media_count')
                    ->icon(Heroicon::PaperClip)
                    ->counts('media')
                    ->badge()
                    ->label('File count'),

                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
