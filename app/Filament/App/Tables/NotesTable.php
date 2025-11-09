<?php

namespace App\Filament\App\Tables;

use App\Models\Note;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('media_count')
                    ->formatStateUsing(function (int $state) {
                        return $state > 0 ? $state : '';
                    })
                    ->label('')
                    ->counts('media')
                    ->width('50px')
                    ->icon(function (Note $note) {
                        return $note->hasMedia() ? Heroicon::PaperClip : null;
                    }),

                TextColumn::make('note')
                    ->prefix(function (Note $note) {
                        return $note->title;
                    })
                    ->formatStateUsing(function (string $state) {
                        return str()->limit($state, 100);
                    })
                    ->html()
                    ->searchable()
                    ->limit(10)
                    ->label('Note'),

                TextColumn::make('user.full_name')
                    ->label('Created by'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created at'),
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
