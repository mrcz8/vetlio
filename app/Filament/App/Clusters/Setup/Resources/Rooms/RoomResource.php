<?php

namespace App\Filament\App\Clusters\Setup\Resources\Rooms;

use App\Filament\App\Clusters\Setup\Resources\Rooms\Pages\CreateRoom;
use App\Filament\App\Clusters\Setup\Resources\Rooms\Pages\EditRoom;
use App\Filament\App\Clusters\Setup\Resources\Rooms\Pages\ListRooms;
use App\Filament\App\Clusters\Setup\Resources\Rooms\Schemas\RoomForm;
use App\Filament\App\Clusters\Setup\Resources\Rooms\Tables\RoomsTable;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Models\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::HomeModern;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Rooms';

    protected static ?string $label = 'room';

    protected static ?string $pluralLabel = 'rooms';

    protected static string|null|\UnitEnum $navigationGroup = 'Clinic';

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            //'create' => CreateRoom::route('/create'),
            //'edit' => EditRoom::route('/{record}/edit'),
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
