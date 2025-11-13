<?php

namespace App\Filament\App\Clusters\Setup\Resources\CancelReasons;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Clusters\Setup\Resources\CancelReasons\Pages\ManageCancelReasons;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use App\Models\CancelReason;
use Awcodes\Palette\Forms\Components\ColorPicker;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CancelReasonResource extends Resource
{
    protected static ?string $model = CancelReason::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::CalendarX;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Cancel reasons';

    protected static ?string $label = 'cancel reason';

    protected static ?string $pluralLabel = 'cancel reasons';

    protected static string | UnitEnum | null $navigationGroup = 'Services';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Name')
                    ->maxLength(255),

                ColorPicker::make('color')
                    ->label('Color'),

                Toggle::make('active')
                    ->label('Active')
                    ->default(true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ColorColumn::make('color')
                    ->label('')
                    ->width('30px'),

                TextColumn::make('name')
                    ->searchable(),

                ToggleColumn::make('active'),

                CreatedAtColumn::make('created_at'),
                UpdatedAtColumn::make('updated_at'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCancelReasons::route('/'),
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
