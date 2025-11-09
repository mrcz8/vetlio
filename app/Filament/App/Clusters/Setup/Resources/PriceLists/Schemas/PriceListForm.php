<?php

namespace App\Filament\App\Clusters\Setup\Resources\PriceLists\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PriceListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required(),

                        Toggle::make('active')
                            ->default(true)
                            ->label('Active')
                            ->required(),

                        CheckboxList::make('brances')
                            ->relationship('branches', 'name')
                            ->required()
                            ->label('Branches'),
                    ])
            ]);
    }
}
