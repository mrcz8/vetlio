<?php

namespace App\Filament\App\Clusters\Setup\Resources\PriceLists;

use App\Filament\App\Clusters\Setup\Resources\PriceLists\Pages\CreatePriceList;
use App\Filament\App\Clusters\Setup\Resources\PriceLists\Pages\EditPriceList;
use App\Filament\App\Clusters\Setup\Resources\PriceLists\Pages\ListPriceLists;
use App\Filament\App\Clusters\Setup\Resources\PriceLists\Pages\ViewPriceList;
use App\Filament\App\Clusters\Setup\Resources\PriceLists\Schemas\PriceListForm;
use App\Filament\App\Clusters\Setup\Resources\PriceLists\Schemas\PriceListInfolist;
use App\Filament\App\Clusters\Setup\Resources\PriceLists\Tables\PriceListsTable;
use App\Filament\App\Clusters\Setup\Resources\Services\RelationManagers\PricesRelationManager;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Models\PriceList;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyEuro;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationLabel = 'Price Lists';

    protected static ?string $label = 'price list';

    protected static ?string $pluralLabel = 'price lists';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return PriceListForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PriceListInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceListsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PricesRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceLists::route('/'),
            'create' => CreatePriceList::route('/create'),
            'view' => ViewPriceList::route('/{record}'),
            'edit' => EditPriceList::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
