<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Reservations\Actions\EditAppointmentAction;
use App\Filament\App\Resources\Reservations\Schemas\ReservationForm;
use App\Filament\App\Resources\Reservations\Schemas\ReservationInfolist;
use App\Filament\App\Resources\Reservations\Tables\ReservationsTable;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ClientReservations extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'reservations';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::CalendarPlus;

    protected static ?string $title = 'Appointments';

    protected static ?string $navigationLabel = 'Appointments';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->fillForm(function ($data) {
                $data['client_id'] = $this->getRecord()->id;

                return $data;
            })
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->full_name;
    }

    public function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return ReservationInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReservationsTable::configure($table)
            ->recordActions([
                ViewAction::make(),
                EditAppointmentAction::make()
            ])
            ->filters([]);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(function (Builder $query) {
                    return $this->getRecord()->reservations_count;
                })
                ->icon(PhosphorIcons::Calendar)
                ->label('All'),
            'active' => Tab::make()
                ->label('Active')
                ->badge(function (Builder $query) {
                    return $this->getRecord()->reservations()->canceled(false)->count();
                })
                ->icon(PhosphorIcons::CalendarCheck)
                ->modifyQueryUsing(fn(Builder $query) => $query->canceled(false)),
            'canceled' => Tab::make()
                ->label('Canceled')
                ->badgeColor('danger')
                ->badge(function (Builder $query) {
                    return $this->getRecord()->reservations()->canceled(false)->count();
                })
                ->icon(PhosphorIcons::CalendarX)
                ->modifyQueryUsing(fn(Builder $query) => $query->canceled()),
        ];
    }
}
