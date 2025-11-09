<?php

namespace App\Filament\App\Clusters\Setup\Resources\Users;

use App\Filament\App\Clusters\Setup\Resources\Users\Pages\CreateUser;
use App\Filament\App\Clusters\Setup\Resources\Users\Pages\EditUser;
use App\Filament\App\Clusters\Setup\Resources\Users\Pages\ListUsers;
use App\Filament\App\Clusters\Setup\Resources\Users\Pages\ViewUser;
use App\Filament\App\Clusters\Setup\Resources\Users\Schemas\UserForm;
use App\Filament\App\Clusters\Setup\Resources\Users\Schemas\UserInfolist;
use App\Filament\App\Clusters\Setup\Resources\Users\Tables\UsersTable;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $label = 'user';

    protected static ?string $pluralLabel = 'users';

    protected static string | UnitEnum | null $navigationGroup = 'Clinic';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            //'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            //'edit' => EditUser::route('/{record}/edit'),
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
