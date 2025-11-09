<?php

namespace App\Filament\App\Clusters\Setup\Resources\Branches;

use App\Filament\App\Clusters\Setup\Resources\Branches\Pages\CreateBranch;
use App\Filament\App\Clusters\Setup\Resources\Branches\Pages\EditBranch;
use App\Filament\App\Clusters\Setup\Resources\Branches\Pages\ListBranches;
use App\Filament\App\Clusters\Setup\Resources\Branches\Schemas\BranchForm;
use App\Filament\App\Clusters\Setup\Resources\Branches\Tables\BranchesTable;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationLabel = 'Branches';

    protected static ?string $label = 'branch';

    protected static ?string $pluralLabel = 'branches';

    protected static string|null|\UnitEnum $navigationGroup = 'Clinic';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
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
            'index' => ListBranches::route('/'),
            // 'create' => CreateBranch::route('/create'),
            // 'edit' => EditBranch::route('/{record}/edit'),
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
