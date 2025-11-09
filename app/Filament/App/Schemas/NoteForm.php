<?php

namespace App\Filament\App\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required(),

                RichEditor::make('note')
                    ->label('Note')
                    ->required()
                    ->extraAttributes([
                        'style' => 'min-height: 200px',
                    ]),

                SpatieMediaLibraryFileUpload::make('attachments')
                    ->multiple()
                    ->label('Attachments')
            ]);
    }
}
