<?php

namespace App\Filament\Portal\Resources\Patients;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\Portal\Resources\Patients\Pages\CreatePatient;
use App\Filament\Portal\Resources\Patients\Pages\EditPatient;
use App\Filament\Portal\Resources\Patients\Pages\ListPatients;
use App\Filament\Portal\Resources\Patients\Pages\ViewPatient;
use App\Filament\Portal\Resources\Patients\Schemas\PatientForm;
use App\Filament\Portal\Resources\Patients\Schemas\PatientInfolist;
use App\Filament\Portal\Resources\Patients\Tables\PatientsTable;
use App\Models\Patient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Dog;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Pet';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pets';
    }

    public static function form(Schema $schema): Schema
    {
        return PatientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PatientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
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
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'view' => ViewPatient::route('/{record}'),
            'edit' => EditPatient::route('/{record}/edit'),
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
