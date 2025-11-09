<?php

namespace App\Filament\App\Clusters\Setup\Resources\Branches\Schemas;

use App\Models\PriceList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('address')
                    ->label('Address'),

                TextInput::make('city')
                    ->label('City'),

                TextInput::make('postal_code')
                    ->label('Postal Code'),

                TextInput::make('branch_mark')
                    ->required()
                    ->hint('Sequence will reset if changed')
                    ->unique(ignoreRecord: true)
                    ->label('Branch Code'),

                Select::make('price_list_id')
                    ->label('Primary Price List')
                    ->required()
                    ->options(PriceList::pluck('name', 'id')),

                Toggle::make('active')
                    ->default(true)
                    ->inline(false)
                    ->label('Active')
                    ->required(),
            ]);
    }
}
