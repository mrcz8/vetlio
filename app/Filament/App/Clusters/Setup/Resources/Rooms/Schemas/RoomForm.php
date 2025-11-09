<?php

namespace App\Filament\App\Clusters\Setup\Resources\Rooms\Schemas;

use Awcodes\Palette\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('code')
                    ->label('Code')
                    ->required(),

                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->required(),

                ColorPicker::make('color')
                    ->label('Room Color')
                    ->required(),

                Toggle::make('active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
