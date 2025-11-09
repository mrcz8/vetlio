<?php

namespace App\Filament\App\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->placeholder('File name')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->placeholder('File description')
                    ->label('Description')
                    ->maxLength(255),

                Toggle::make('visible_in_portal')
                    ->label('Visible in portal'),

                SpatieMediaLibraryFileUpload::make('media')
                    ->label('Files')
                    ->downloadable()
                    ->collection('documents')
                    ->multiple(),
            ]);
    }
}
