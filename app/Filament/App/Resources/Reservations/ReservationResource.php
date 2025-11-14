<?php

namespace App\Filament\App\Resources\Reservations;

use App\Filament\App\Resources\Reservations\Pages\DoctorReservations;
use App\Filament\App\Resources\Reservations\Pages\ListReservations;
use App\Filament\App\Resources\Reservations\Schemas\ReservationForm;
use App\Filament\App\Resources\Reservations\Schemas\ReservationInfolist;
use App\Filament\App\Resources\Reservations\Tables\ReservationsTable;
use App\Models\Reservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static ?string $recordTitleAttribute = 'from';

    protected static ?string $navigationLabel = 'Waiting Room';

    protected static ?string $label = 'reservation';

    protected static ?string $pluralLabel = 'reservations';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReservationInfolist::configure($schema);
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
            'index' => ListReservations::route('/'),
            //'create' => CreateReservation::route('/create'),
            //'view' => ViewReservation::route('/{record}'),
            //'edit' => EditReservation::route('/{record}/edit'),
            'doctors' => DoctorReservations::route('/doctors'),
        ];
    }
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['patient.breed', 'patient.species']);
}

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
