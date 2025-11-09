<?php

namespace App\Filament\App\Clusters\Setup\Resources\Banks;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Clusters\Setup\Resources\Banks\Pages\ManageBanks;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use App\Models\Bank;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Bank;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $label = 'bank';

    protected static ?string $pluralLabel = 'banks';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Banks';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    TextInput::make('name')
                        ->label('Name')
                        ->required(),

                    Toggle::make('active')
                        ->inline(false)
                        ->default(true)
                        ->label('Active'),
                ])->columnSpanFull(),

                TextInput::make('iban')
                    ->suffixIcon(PhosphorIcons::Bank)
                    ->label('IBAN')
                    ->columnSpanFull()
                    ->required(),

                TextArea::make('note')
                    ->columnSpanFull()
                    ->label('Note')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('iban')
                    ->label('IBAN')
                    ->searchable(),

                ToggleColumn::make('active')
                    ->label('Active'),

                TextColumn::make('note')
                    ->searchable()
                    ->label('Note'),

                CreatedAtColumn::make('created_at'),
                UpdatedAtColumn::make('updated_at'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBanks::route('/'),
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
