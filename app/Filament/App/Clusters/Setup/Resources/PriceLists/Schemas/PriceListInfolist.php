<?php

namespace App\Filament\App\Clusters\Setup\Resources\PriceLists\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PriceListInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),

                IconEntry::make('active')
                    ->label('Active')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Updated at')
                    ->placeholder('-'),
            ]);
    }
}
