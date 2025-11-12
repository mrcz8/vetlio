<?php

namespace App\Filament\App\Resources\Reservations\Tables;

use App\Enums\Icons\PhosphorIcons;
use App\Enums\ReservationStatus;
use App\Filament\App\Actions\CancelReservationAction;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use App\Filament\App\Resources\Reservations\Actions\MoveBack;
use App\Filament\App\Resources\Reservations\Actions\MoveRight;
use App\Models\Reservation;
use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                BadgeableColumn::make('client.full_name')
                    ->description(fn($record) => $record->client->email)
                    ->suffixBadges([
                        Badge::make('hot')
                            ->label('Confirmed arrival')
                            ->color('success')
                            ->visible(fn(Reservation $record) => $record->confirmed_at),
                    ])->searchable()
                    ->sortable()
                    ->label('Client'),

                TextColumn::make('patient.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->patient->breed->name . ', ' . $record->patient->species->name)
                    ->label('Patient'),

                TextColumn::make('from')
                    ->sortable()
                    ->date()
                    ->description(fn($record) => $record->from->format('H:i') . ' - ' . $record->to->format('H:i'))
                    ->label('Reservation Time'),

                TextColumn::make('service.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->service->duration->format('i') . ' min')
                    ->label('Service'),

                TextColumn::make('serviceProvider.full_name')
                    ->searchable()
                    ->label('Doctor'),

                TextColumn::make('room.name')
                    ->label('Room'),
            ])
            ->persistFiltersInSession()
            ->deferFilters(false)
            ->filtersFormColumns(3)
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions(self::getRecordActions())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getFilters(): array
    {
        return [
            Filter::make('date')
                ->default()
                ->columns(2)
                ->schema([
                    DatePicker::make('from')
                        ->label('From')
                        ->default(now()->startOfDay()),

                    DatePicker::make('to')
                        ->label('To')
                        ->default(now()->endOfDay()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                        )
                        ->when(
                            $data['to'],
                            fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                        );
                }),

            SelectFilter::make('service_provider_id')
                ->native(false)
                ->relationship('serviceProvider', 'first_name')
                ->label('Doctor'),

            SelectFilter::make('room_id')
                ->native(false)
                ->relationship('room', 'name')
                ->label('Room'),
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            MoveBack::make('back')
                ->visible(function($record) {
                    return $record->status_id->canMoveBack();
                }),

            MoveRight::make('right')
                ->visible(fn($record) => $record->status_id->canMoveRight()),

            Action::make('create-medical-document')
                ->label('Create Medical Record')
                ->icon(PhosphorIcons::FilePlus)
                ->visible(fn($record) => $record->status_id->isInProcess())
                ->url(fn($record) => MedicalDocumentResource::getUrl('create', ['reservationId' => $record->uuid])),

            ViewAction::make(),

            ActionGroup::make([
                ClientCardAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                CancelReservationAction::make(),
            ]),
        ];
    }
}
