<?php

namespace App\Filament\Portal\Resources\Patients;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\Portal\Resources\Patients\Pages\ListPatients;
use App\Filament\Portal\Resources\Patients\Pages\PatientAppointments;
use App\Filament\Portal\Resources\Patients\Pages\PatientMedicalDocuments;
use App\Filament\Portal\Resources\Patients\Pages\ViewPatient;
use App\Filament\Portal\Resources\Patients\Schemas\PatientForm;
use App\Filament\Portal\Resources\Patients\Schemas\PatientInfolist;
use App\Filament\Portal\Resources\Patients\Tables\PatientsTable;
use App\Models\Patient;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Dog;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static ?string $breadcrumb = null;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('client_id', auth()->id())->count();
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewPatient::class,
            PatientAppointments::class,
            PatientMedicalDocuments::class
        ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'breed.name', 'species.name'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['breed', 'species']);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Species' => $record->species->name,
            'Breed' => $record->breed->name ?? '-',
            'Age' => $record->date_of_birth ? $record->date_of_birth->age . ' yrs' : '-',
        ];
    }

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', auth()->id())
            ->withCount(['reservations', 'medicalDocuments'])
            ->with(['species', 'breed']);
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
            //'create' => CreatePatient::route('/create'),
            'view' => ViewPatient::route('/{record}'),
            //'edit' => EditPatient::route('/{record}/edit'),
            'appointments' => PatientAppointments::route('/{record}/appointments'),
            'medical-documents' => PatientMedicalDocuments::route('/{record}/medical-documents'),
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
